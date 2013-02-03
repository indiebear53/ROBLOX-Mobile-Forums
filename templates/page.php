<?php
function templatePage($title, $makeContent, $makeFooter = NULL) {

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset='utf-8' />
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
	<script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
	<script>
	$(document).bind("mobileinit", function(){
		$.extend($.mobile, {
			defaultPageTransition: 'slide'
		});
	});
	</script>
	<script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
	<style type = "text/css">
		a {
			text-decoration: none;
		}
		body .ui-li .ui-li-desc {
			white-space: normal;
		}
		body {
			text-overflow: visible;
		}
	</style>
	<script type="text/javascript" src = "includes/ga_tracking.js"></script>
</head>
<body>
	<div data-role="page" data-add-back-btn="true">
		<!--Header-->
		<div data-role="header" data-theme = "b" data-position = "fixed">
			<a href="index.php" data-role="button"
			   data-icon="home" data-direction="reverse"
			   data-iconpos="notext" class="ui-btn-right">Home</a>
			<div style="text-align: center; padding: 3px;">
				<img src = "includes/forumslogo.png">
			</div>
		</div>
		<!--/Header-->
		<!--Body-->
		<div data-role="content">
			<?php $makeContent() ?>
		</div>
		<?php if($makeFooter): ?>
		<div data-role="footer" data-theme="b" class="ui-bar">
			<?php $makeFooter() ?>
		</div>
	<?php endif ?>
	</div>
</body>
</html>
<?php

}