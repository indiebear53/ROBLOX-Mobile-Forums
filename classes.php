<?php
require_once "classes/EnhancedObject.php";

class Group extends EnhancedObject {
	public $name;
	public $id;
	public function getUrl() {
		return "http://www.roblox.com/Groups/Group.aspx?gid=" . $this->id;
	}
}

class Role extends EnhancedObject {
	public $name;
	public $rank;
}

class GroupInfo extends EnhancedObject {
	public $group;
	public $role;

	public static function fromJsonEntry($json) {
		$gi = new GroupInfo();
		$gi->group = new Group();
		$gi->group->name = $json->GroupName;
		$gi->group->id = $json->GroupId;
		$gi->role = new Role();
		$gi->role->name = $json->RoleSetName;
		$gi->role->rank = $json->RoleSetRank;
		return $gi;
	}
}

class User extends EnhancedObject {
	private static $_instances = array();

	# Properties
	public $name;
	public $joinDate;
	public $postCount;
	public $online;
	public $isMod;
	public $groupInfo;

	private $_fullyLoaded;

	public function getUrl() {
		return "http://roblox.com/User.aspx?username=" . $this->name;
	}
	public function getImg() {
		return "http://www.roblox.com/Thumbs/Avatar.ashx?x=100&y=100&Format=Png&username=" . $this->name;
	}
	public function getSmallImg() {
		return "http://www.roblox.com/Thumbs/Avatar.ashx?x=48&y=48&Format=Png&username=" . $this->name;
	}
	/**
	 * Used to prettify the ugly join date
	 * Makes it a bit more easier to read
	 */
	public function prettifyJoinDate() {
		$month = substr($this->joinDate,2,5);
		$day = substr($this->joinDate,0,2);
		$year = substr($this->joinDate,6);
		$joinDate = $month . " " . $day . "," . $year;
		return $joinDate;
	}

	/**
	 * Private constructor to create a user with a given name
	 */
	private function __construct($name) {
		$this->name = $name;
		self::$_instances[$name] = $this;
	}
	/**
	 * Get either a new or existing user object based on a name
	 */
	public function byName($name) {
		$u = @self::$_instances[$name] or $u = new User($name);
		return $u;
	}

	/**
	 * Parses an author object out of the corresponding author box in a thread list
	 */
	public static function fromPostAuthorInfo($domElement) {
		$authorName = trim(substr($domElement->childNodes->item(0)->nodeValue,2));
		$author = User::byName($authorName);

		if(!$author->_fullyLoaded) {
			$authorIcon = $domElement->getElementsByTagName('img')->item(0)->getAttribute('src');
			// Assigning author values
			$author->online = $authorIcon == "/Forum/skins/default/images/user_IsOnline.gif";
			$author->joinDate = trim(substr($domElement->childNodes->item(2)->nodeValue,8));

			// Figure out if the poster is a mod/top poster/both and adjust information accordingly
			if ($author->joinDate == "") {
				// Figure out if mod
				$modIndic = $domElement->getElementsByTagName('img')->item(3)->getAttribute('src');
				if (substr($modIndic,1,36) == "Forum/skins/default/images/users_top" && $domElement->getElementsByTagName('img')->length == 5) {
					// They're a mod and a top poster
					$author->isMod = true;
					$author->joinDate = trim(substr($domElement->childNodes->item(4)->nodeValue,8));
					$author->postCount = trim(substr($domElement->childNodes->item(5)->nodeValue,13));
				}
				if ($modIndic == "/Forum/skins/default/images/users_moderator.gif") {
					// If they're just a mod
					$author->isMod = true;
					$author->joinDate = trim(substr($domElement->childNodes->item(3)->nodeValue,8));
					$author->postCount = trim(substr($domElement->childNodes->item(4)->nodeValue,13));
				}
			}
			else {
				$author->postCount = trim(substr($domElement->childNodes->item(3)->nodeValue,13));
			}
			$author->_fullyLoaded = true;
		}
		return $author;
	}

	/**
	 * Populates every author object in existance with their group info
	 */
	public static function populateGroupInfo() {
		# Get a list of the names of unpopulated users
		$names = array();
		foreach(self::$_instances as $name => $user)
			if(!$user->groupInfo)
				$names[] = $name;

		$url = "http://www.roblox.com/Groups/GetPrimaryGroupInfo.ashx?users=".implode(',', $names);

		$data = file_get_contents($url);
		$parsed = array();
		foreach(json_decode($data) as $username => $entry) {
			self::$_instances[$username]->groupInfo = GroupInfo::fromJsonEntry($entry);
		}
	}
}

class Post extends EnhancedObject {
	public $author;
	public $title;
	public $topic;
	public $content;
	public $date;

	public $titleIsOriginal; # to reduce duplicate titles

	public function getWrappedDate() {
		$parts = explode(' ', $this->date);
		if(count($parts) == 5)
			return array("{$parts[0]} {$parts[1]} {$parts[2]}", "{$parts[3]} {$parts[4]}");
		else
			return array($this->date);
	}
}
