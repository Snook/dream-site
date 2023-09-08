<html lang="english">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table role="presentation" width="550" border="0" cellspacing="0" cellpadding="0" class="border">
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/platepoints-generic-header.png" alt="PLATEPOINTS" border="0" width="550" height="120"/></td>
	</tr>
	<tr>
		<td style="padding:10px 15px 5px 15px;">
			<p><?php echo $this->firstname; ?>,</p>

			<p>Thank you for testing <?php echo $this->title; ?>, one of our new Dream Dinners recipes. We want to hear what you think, because your opinion will help shape our menu in the
				coming months. Please prepare your dinner for your family and complete the survey in your <a href="<?php echo HTTPS_BASE; ?>?page=my_surveys">My Test Recipes</a>
				section of your DreamDinners.com account.</p>

			<p>If you have any questions, please email us at <a href="mailto:customerservice@dreamdinners.com">customerservice@dreamdinners.com</a>.</p>

			<p>On behalf of all of us, we'd like to thank you for using DreamDinners.com. We look forward to seeing you soon.</p>

			<p>Sincerely,<br/>
				Dream Dinners<br/>
			</p></td>
	</tr>
</table>
<table role="presentation" width="550" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left"><img src="<?= EMAIL_IMAGES_PATH ?>/email/platepoints/platepoints-footer-grey.png" width="550" height="50"></td>
	</tr>
	<tr>
		<td align="right" style="padding: 5px"><a href="<?= HTTPS_BASE ?>?page=session_menu">Order</a> | <a href="<?= HTTPS_BASE ?>?page=my_account">My
				Account</a> | <a href="<?= HTTPS_BASE ?>?page=my_platepoints">My PLATEPOINTS</a></td>
	</tr>
	<tr>
		<td align="left" style="padding: 5px"><i>*Dinner Dollars can be redeemed on orders above 36 servings, Sides &amp; Sweets freezer items and Made for You service fees at participating
				locations.</i></td>
	</tr>
</table>
</body>
</html>