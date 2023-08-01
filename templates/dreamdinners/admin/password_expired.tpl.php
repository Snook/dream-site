<?php $this->setOnLoad('cookieCheck();'); ?>
<?php $this->assign('page_title', 'Your Password has expired'); ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?php echo (!empty($this->page_title)) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - Quick, Healthy and Easy Family Dinners'; ?></title>
<?php include $this->loadTemplate('admin/page_head_css.tpl.php'); ?>
</head>
<body>

<div id="login_panel">

<div>Your Dream Dinners Franchise Administration password has expired.</div>

<div>

	<p>You have been sent an email containing a link that will allow you to change your password. If you do not receive the email you can try again by clicking "Forgot your password?"
		link to resend the email or you can contact Dream Dinners support for assistance.
</div>

<div style="text-align:left;margin-top:20px;">
	<?php include $this->loadTemplate('customer/subtemplate/login/login_form.tpl.php'); ?>
</div>

</div>

<?php include $this->loadTemplate('admin/page_footer_javascript.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer_onload.tpl.php'); ?>

</body>
</html>