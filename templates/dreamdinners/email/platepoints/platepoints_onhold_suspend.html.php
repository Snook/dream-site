<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table role="presentation" width="550"  border="0" cellspacing="0" cellpadding="0" class="border">
	<tr>
		<td colspan="2"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints-logo-orange-header-400x60.png" alt="PLATEPOINTS" border="0" width="400" height="60" /></td>
	</tr>
	<tr>
		<td style="padding:10px 10px 5px 10px;"><p>Dear <?php echo $this->firstname;?>, </p>
			<p>Your PLATEPOINTS loyalty account has been put on hold.</p>
			<p>While your account is on hold, you will not earn PLATEPOINTS or new Dinner Dollars on future orders and activities. If you have Dinner Dollars currently available in your account, you will not be able to redeem them unless you reactivate PLATEPOINTS. Dinner Dollars are still subject to the original expiration date.</p>
			<p>If you feel this is an error or you wish to remove the hold, please contact your local store.</p>
			<p>YWe look forward to seeing you again.<br/>
-Your Dream Dinners Team</p>

	</tr>
</table>
<table role="presentation" width="550"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left"><img src="<?=EMAIL_IMAGES_PATH?>/email/platepoints/platepoints-footer-grey.png" alt="" width="550" height="50"></td>
	</tr>
</table>
</body>
</html>