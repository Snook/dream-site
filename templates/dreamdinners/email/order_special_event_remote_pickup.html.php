<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>my-account">My Account</a> | <a href="<?=HTTPS_BASE ?>my-meals">Rate My Meals</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Confirmation</span></p></td>
	</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>Thank you for placing an order with Dream Dinners. We look forward to seeing you at the community pick up location during your scheduled pick up time.</p>
			<p>Your fully prepped dinners will be waiting when you arrive. Please bring a cooler or box to transport your dinners home. It is important that you arrive during your scheduled time. </p>
			<p>Community Pick Up Location:<br>
				<?php echo $this->sessionInfo['session_title']; ?><br>
				<?php echo $this->sessionInfo['session_remote_location']->address_line1 . ((!empty($this->sessionInfo['session_remote_location']->address_line2)) ? ' ' . $this->sessionInfo['session_remote_location']->address_line2 : '') . ', ' . $this->sessionInfo['session_remote_location']->city . ', ' . $this->sessionInfo['session_remote_location']->state_id . ' ' .$this->sessionInfo['session_remote_location']->postal_code; ?>
			<p>If you have questions about your order please contact the store.</p>
			<p>See you soon!</p>
			</p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
		</td>
	</tr>
</table>
<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>

</body>
</html>