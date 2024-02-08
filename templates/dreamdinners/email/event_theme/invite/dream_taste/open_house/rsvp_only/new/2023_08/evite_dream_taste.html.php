<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0" align="left">
	<tr bgcolor="#FFFFFF">
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/dream-dinners-horiz-grey-line-header-650x75.gif" alt="Dream Dinners" width="650" height="75"></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/graphic.jpg" alt="Open House Event" /></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td style="padding:20px;"><p align="center"><span class="title">You're Invited!</span></p>
			<p align="left">
				This summer, the Dream Dinners Foundation is proud to partner with The Leukemia & Lymphoma Society (LLS) for The Dare to Dream Project, supporting pediatric blood cancer patients.</p>
			<p align="left">
				Join us to learn more about The Dare to Dream Project at our Leukemia and Lymphoma Society Open House Event. Enjoy delicious samples, fun giveaways and help us support a great cause.</p>	
			<p align="left"><?php echo $this->message; ?></p>
			<p align="center"><a href="<?php echo $this->referral_link; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/invite-rsvp-button-green.png" alt="RSVP" width="300" height="45" border="0"></a></p>
		</td>
	</tr>
	<tr>
		<td>
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="10">
				<tr bgcolor="#b9bf33" style="padding: 5px;">
					<td width="35%" align="left" valign="top">
						<span style="color:#000;"><strong>When:</strong><br />
						<?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?><br />
							<?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?></strong><br /></span>
					</td>
					<td width="35%" align="left" valign="top">
						<span style="color:#000;"><strong>Where:</strong><br />
						Dream Dinners<br />
						<?php echo $this->session['address_line1']; ?> <?php echo $this->session['address_line2']; ?><br />
						<?php echo $this->session['city']; ?>,&nbsp;<?php echo $this->session['state_id']; ?>&nbsp;&nbsp;<?php echo $this->session['postal_code']; ?><br />
						<?php echo $this->session['telephone_day']; ?></span>
					</td>
					<td width="35%" align="left" valign="top">
          <span style="color:#000;"><strong>RSVP:</strong><br />
						<?php echo $this->referral_link; ?></span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/invite-footer-gray.gif" alt="dream dinners" width="650" height="50">
		<p><i>*This event is open to anyone who is new to Dream Dinners or has not ordered in over 12 months.</i></p>
    </td>
	</tr>
</table>
</body>
</html>