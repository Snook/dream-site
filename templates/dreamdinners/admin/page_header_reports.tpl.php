<?php
 	$this->assign('topnav','reports');
 	$this->setScript('head', SCRIPT_PATH . '/admin/reports_datefield.js');
 	$this->setCSS(CSS_PATH . '/admin/admin-styles-reports.css');
 	include $this->loadTemplate('admin/page_header.tpl.php'); ?>

 	<script src="<?php echo SCRIPT_PATH; ?>/admin/vendor/calendarDateInput.js" type="text/javascript"></script>


<?php  	if (!empty($PAGETITLE))
 		echo '<h3 style="text-align:center">' . $PAGETITLE . "</h3>";
?>
