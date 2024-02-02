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
			<p>Your PLATEPOINTS Loyalty has been reactivated.</p>
			<p>You will start to earn PLATEPOINTS and Dinner Dollars again.</p>
			<p>If you have any questions please contact your local store.</p>
			<p>We look forward to seeing you again.<br/>
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