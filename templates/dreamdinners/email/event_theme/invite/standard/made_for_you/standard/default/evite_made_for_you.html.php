<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0" align="left">
	<tr bgcolor="#FFFFFF">
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/default/dream-dinners-horiz-grey-line-header-650x75.gif" alt="Dream Dinners" width="650" height="75"></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/standard/made_for_you/standard/<?php echo $this->session['menu_directory']; ?>/graphic.jpg" alt="Meal Prep Workshop Menu" /></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td style="padding:20px;"><p align="center"><span class="title">You have been invited to try our Pick Up service</span></p>
			<p align="center">
				Want to give Dream Dinners a try, but don't have enough time to assemble your dinners this month? Choose a pick up session and we will assemble delicious dinners for your family. You simply pick up your dinners during the selected pick up window. A nominal fee may apply.
			</p>
			<p align="center"><?php echo $this->message; ?><br>
</p>
			<p align="center"><strong>When:</strong><br />
				<?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
                <br /><?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?><br>
</p>
            <p align="center"><strong>Where:</strong><br />
						Dream Dinners<br />
						<?php echo $this->session['address_line1']; ?><br />
						<?php echo $this->session['address_line2']; ?><br />
						<?php echo $this->session['city']; ?>,&nbsp;<?php echo $this->session['state_id']; ?>&nbsp;&nbsp;<?php echo $this->session['postal_code']; ?><br />
						<?php echo $this->session['telephone_day']; ?><br>
</p>

            <p align="center"><a href="<?php echo $this->referral_link; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/default/view-menu-order.png" alt="View menu and order" width="300" height="45" border="0"></a></p>
		</td>
	</tr>
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/default/invite-footer-gray.gif"  width="650" height="50" alt="dreamdinners.com"></td>
	</tr>
</table>
</body>
</html>