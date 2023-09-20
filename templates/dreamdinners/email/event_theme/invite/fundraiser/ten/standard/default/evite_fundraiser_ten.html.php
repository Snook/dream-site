<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0" align="left">
	<tr bgcolor="#FFFFFF">
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/default/dream-dinners-horiz-grey-line-header-650x75.gif" alt="" width="650" height="75"></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/graphic.jpg" alt="Fundraiser Menu" /></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td style="padding:20px;"><p align="center"><span class="title-black">YOU'RE INVITED!</span></p>
			<p align="center">
				Help <?php echo $this->session['fundraiser_name']; ?> reach our fundraising goal.
			</p>
			<p align="center">Join us to assemble three delicious meals to take home and enjoy with your family for just $60. During this exclusive event, you will enjoy samples, learn more about how easy family dinners can be, and help a deserving cause. $10 from each purchase is automatically donated back to our organization. </p>
			<p>Attendance is by invitation only, and space is limited. RSVP today to reserve your space. <a href="<?php echo HTTPS_SERVER; ?>/how-it-works">Learn more about how Dream Dinners works here</a>.</p>
			<p align="center"><?php echo $this->message; ?></p>
			<p align="center"><a href="<?php echo $this->referral_link; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/default/invite-rsvp-button-orange.png" alt="RSVP Button" width="300" height="45" border="0"></a></p>
		</td>
	</tr>
	<tr>
		<td>
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="15">
				<tr bgcolor="#e87722" style="padding: 15px;">
					<td width="48%" align="left" valign="top">
						<span style="color:#000;">
						<strong>Organization:</strong><br />
						<?php echo $this->session['fundraiser_name']; ?><br /><br />
						<strong>When:</strong><br />
						<?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?><br />
						<?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?></strong></span>
					</td>
					<td width="48%" align="left" valign="top">
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
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/default/invite-footer-gray.gif" width="650" height="50" alt="DreamDinners.com"></td>
	</tr>
    <tr>
		<td><br/><p><i>*Fundraising sessions are limited to one fundraising order per household per fundraising organization.</i></p></td>
	</tr>

</table>
</body>
</html>