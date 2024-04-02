<!DOCTYPE html>
<html lang="en" itemscope itemtype="https://schema.org/Article" prefix="fb: https://www.facebook.com/2008/fbml">
<head>
	<title lang="en-us">Dream Dinners Help</title>
	<meta name="robots" content="noindex">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="<?php echo  CSS_PATH; ?>/customer/dreamdinners.min.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="container-fluid">

	<div class="row my-2 d-print-none">
		<div class="col text-right">
			<a class="btn btn-primary btn-sm" href="javascript:window.print();">Print This Page</a>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<?php include $this->loadTemplate($this->report_template); ?>
		</div>
	</div>

	<div class="row my-2 d-print-none">
		<div class="col text-right">
			<a class="btn btn-primary btn-sm" href="javascript:window.close();">Close Window</a>
			<a class="btn btn-primary btn-sm" href="javascript:window.print();">Print This Page</a>
		</div>
	</div>

</div>

<?php include $this->loadTemplate('admin/page_footer_javascript.tpl.php'); ?>

</body>
</html>