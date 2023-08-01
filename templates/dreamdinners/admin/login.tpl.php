<?php $this->setOnLoad('cookieCheck();'); ?>
<?php $this->assign('page_title', 'Admin Login'); ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo (!empty($this->page_title)) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - Quick, Healthy and Easy Family Dinners'; ?></title>
	<?php include $this->loadTemplate('admin/page_head_css.tpl.php'); ?>
</head>
<body>

<div id="login_panel">

	<div>Login to the Dream Dinners Franchise Administration web site.</div>

	<div class="mt-4">
		<?php include $this->loadTemplate('admin/subtemplate/login/login_form.tpl.php'); ?>
	</div>

</div>

<?php include $this->loadTemplate('admin/page_footer_javascript.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer_onload.tpl.php'); ?>

</body>
</html>