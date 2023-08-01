<?php $this->setScriptVar('TO = 999999999999999999999;'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/admin/reports_map_activity.min.js'); ?>
<!DOCTYPE html>
<html class="h-100">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo (!empty($this->page_title)) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - Quick, Healthy and Easy Family Dinners'; ?></title>
	<?php include $this->loadTemplate('admin/page_head_css.tpl.php'); ?>
</head>

<body class="h-100">

<div class="container-fluid h-100">
	<div class="row h-100">
		<div class="col-10 h-100 p-0">
			<div class="h-100" id="map"></div>
		</div>
		<div class="col-2 h-100 p-0">
			<ul class="h-100 overflow-auto list-unstyled" id="event"></ul>
		</div>
	</div>
</div>

<?php include $this->loadTemplate('admin/page_footer_javascript.tpl.php'); ?>

<script async defer
		src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_APIKEY; ?>&callback=initMap">
</script>

<?php include $this->loadTemplate('admin/page_footer_onload.tpl.php'); ?>

</body>
</html>