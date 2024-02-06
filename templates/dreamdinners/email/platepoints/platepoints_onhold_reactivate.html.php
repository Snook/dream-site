<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table role="presentation" width="550" border="0" cellspacing="0" cellpadding="0" class="border">
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
<table role="presentation" width="550" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left"><img src="<?php echo EMAIL_IMAGES_PATH?>/email/platepoints/platepoints-footer-grey.png" alt="" width="550" height="50"></td>
	</tr>
	<tr>
		<td align="right" style="padding: 5px"><a href="<?php echo HTTPS_BASE ?>session-menu">Order</a> | <a href="<?php echo HTTPS_BASE ?>my-account">My Account</a> | <a href="<?php echo HTTPS_BASE ?>my-platepoints">My PLATEPOINTS</a></td>
	</tr>
	<tr>
		<td align="left" style="padding: 5px"><p><i>*Dinner Dollars are awarded after your 3rd standard order and can be applied on qualifying items on your 4th visit and beyond. Dinner Dollars can only be redeemed at participating stores. Dinner Dollars are not available on preferred guest accounts.</i></p></td>
	</tr>
</table>
</body>
</html>