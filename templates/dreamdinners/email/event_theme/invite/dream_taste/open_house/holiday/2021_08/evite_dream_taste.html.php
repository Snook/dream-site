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
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/graphic.jpg" alt="Open House Meal Prep Workshop" /></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td style="padding:20px;"><p align="center"><span class="title">Celebrate with our Summer Celebrations Trial Offer</span></p>
			<p align="center">
				For only $<?php echo $this->session['dream_taste_price']; ?>, Dream Dinners has you covered for both your next summer celebration and the week that follows. </p>
				
				<p align="center">During this exclusive pick up event, you will get a backyard BBQ bundle including our Herb Crusted Steaks, Chipotle Maple Corn, and a Peach Crisp plus three medium dinners from our delicious menu. Your backyard BBQ comes in our family-size that serves 4 - 6 people.</p>
				
				<p align="center">We’re excited to introduce you to the Dream Dinners experience and make your summer homemade, made easy. Our offer is only for guests of this event, and spaces are limited. Use the link below to reserve your spot today!
			</p>
			<p align="center"><?php echo $this->message; ?></p>
			<p align="center"><a href="<?php echo $this->referral_link; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/invite-rsvp-button-green.png" alt="order" width="300" height="45" border="0"></a></p>
		</td>
	</tr>
	<tr>
		<td>
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="15">
				<tr bgcolor="#b9bf33" style="padding: 15px;">
					<td width="48%" align="left" valign="top">
					 <span style="color:#000;">
						<strong>Pick Up Time:</strong><br />
						<?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?><br />
							<?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?><br /><br />
						
						<strong>Invite Code:</strong><br />
						<?php echo $this->session['session_password']; ?><br>
					 <i>(Not all events require a code)</i></span>
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
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/invite-footer-gray.gif" alt="dream dinners" width="650" height="50">
    <p><i>*Trial Events are valid for new and reacquired guests only. Events are limited to one Trial Event order per household.</i></p></td>
	</tr>
</table>
</body>
</html>