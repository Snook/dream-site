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
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/graphic.jpg" alt="Meal Prep Workshop Menu" /></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td style="padding:20px;"><p align="center"><span class="title">LET'S COOK UP SOME FUN!</span></p>
			<p align="center">
				You’re invited to my free Meal Prep Workshop. We'll enjoy a fun event together and learn the secret to making easy homemade meals at my exclusive, free event. You’ll receive three delicious, medium-size meals that are already prepped and ready to enjoy at home with your family.<br><br>
				There are limited spaces available, and this offer is only available for guests of this event. Use the link below to view the menu items. <b>Contact us to reserve your spot today!</b></a>
			</p>
			<p align="center"><?php echo $this->message; ?></p>
			<p align="center"><a href="<?php echo $this->referral_link; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/default/invite-rsvp-button-green.png" alt="RSVP" width="300" height="45" border="0"></a></p>
		</td>
	</tr>
	<tr>
		<td>
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="15">
				<tr bgcolor="#b9bf33" style="padding: 15px;">
					<td width="48%" align="left" valign="top">
						<span style="color:#000;">
						<strong>Host:</strong><br />
						<?php echo $this->session['session_host_informal_name']; ?><br />
						<?php echo $this->session['session_host_primary_email']; ?><br /><br />
						</span>
						<span style="color:#000;"><strong>Invite Code:</strong><br />
						<?php echo $this->session['session_password']; ?></span>
					</td>
					<td width="48%" align="left" valign="top"><span style="color:#000;"><strong>When:</strong><br />
						<?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?><br />
						<?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?></strong><br /></span>
						<br />
						<span style="color:#000;"><strong>Where:</strong><br />
						Dream Dinners<br />
						<?php echo $this->session['address_line1']; ?> <?php echo $this->session['address_line2']; ?><br />
						<?php echo $this->session['city']; ?>,&nbsp;<?php echo $this->session['state_id']; ?>&nbsp;&nbsp;<?php echo $this->session['postal_code']; ?><br />
						<?php echo $this->session['telephone_day']; ?></span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/default/invite-footer-gray.gif" width="650" height="50" alt="Dreamdinners.com">
	  <p><i>*Meal Prep Workshop sessions are limited to one Meal Prep Workshop order per household per Meal Prep Workshop session.</i></p></td>
	</tr>
</table>
</body>
</html>