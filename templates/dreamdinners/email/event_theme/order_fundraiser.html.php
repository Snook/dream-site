<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>?static=how_it_works">How It Works</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">RSVP and Order Confirmation</span></p></td>
	</tr>
</table>

<?php include $this->loadTemplateIfElse('email/event_theme/order/confirmation/'. $this->sessionInfo['dream_taste_theme_string_default'] . '/order_confirmation.tpl.php', 'email/event_theme/order/confirmation/'. $this->sessionInfo['dream_taste_theme_string'] . '/order_confirmation.tpl.php'); ?>

<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email_dream_taste.tpl.php'); ?>

</body>
</html>