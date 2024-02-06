<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px">&nbsp;</td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Special Request</span></p></td>
	</tr>
</table>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td><p class="sectionhead"><b>CUSTOMER INFORMATION</b></p>
			Name:&nbsp;<?php echo $this->customer_name ?><br />
			Email:&nbsp;<?php echo $this->customer_primary_email ?>
			<p>&nbsp; </p>
			<p class="sectionhead"><b>SPECIAL INSTRUCTIONS/REQUESTS</b></p>
			<p><?php echo $this->orderInfo['order_user_notes'] ?></p>
			<p>&nbsp; </p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
</table>
<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>

</body>
</html>