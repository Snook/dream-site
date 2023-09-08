<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>?page=my_meals">Rate My Meals</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Canceled</span></p></td>
	</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>Dear <?= $this->customer_name ?>, </p>
			<p>Your Home Delivery order, scheduled for <b><?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?></b> at our <b><?=$this->sessionInfo['store_name']?></b> location has been canceled.</p>
			<p>If you have any questions or concerns regarding this order please contact us. The details of the canceled order are listed below.</p>
			<p>Thank you</p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
		</td>
	</tr>
</table>
<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>

</body>
</html>