<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>Dream Dinners | Help</title>
<link href="<?= CSS_PATH ?>/admin/admin-styles.css" rel="stylesheet" type="text/css" />
</head>
<body>

<script type="text/javascript">
//<![CDATA[
function printWindow()
{
	browserVersion = parseInt(navigator.appVersion);
	if (browserVersion >= 4) window.print()
}
//]]>
</script>

<div style="text-align:right;padding-right:10px;"><a href="javascript:printWindow()"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25;" /> Print This Page</a></div>

<div class="page" style="width:100%;padding:0px;margin:0px;"><div style="margin:10px;"><?php include $this->loadTemplate($this->report_template); ?></div></div>

<div style="text-align:right;padding-right:10px;"><a href="javascript:printWindow();"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25;" /> Print This Page</a></div>
<div style="text-align:right;padding-right:10px;"><a href="javascript:window.close();"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/exclamation.png" alt="Close Window!" style="vertical-align:middle;margin-bottom:.25;" /> Close Window</a></div>

</body>
</html>