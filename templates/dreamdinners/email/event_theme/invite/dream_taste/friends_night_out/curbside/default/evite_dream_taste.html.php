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
		<td style="padding:20px;">
			<p align="center"><span class="title">YOU'RE INVITED!</span></p>
			<p align="center">Join me for a Friends Night Out. This event gives you the chance to see how easy it is to experience homemade meals with your family. </p>
			<p align="center">Everyone who RSVP's will receive one free, medium-size meal to take home and enjoy.</p>
			<p align="center">In addition to the FREE dinner, you will have the opportunity to get a one-time special offer during signup. This event has limited spots available, <a href="<?php echo $this->referral_link; ?>">so RSVP today!</a></p>
			<p align="center"><?php echo $this->message; ?></p>
			<p align="center"><a href="<?php echo $this->referral_link; ?>"><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/default/rsvp-now-olive-button-185x40.gif" alt="RSVP" width="185" height="40" border="0"></a></p>
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
					<td width="48%" align="left" valign="top"><span style="color:#000;"><strong>Pick Up Date:</strong><br />
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
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/default/homemade-made-easy-grey-footer-650x40.gif" alt="Dream Dinners" width="650" height="40"></td>
	</tr>
</table>
</body>
</html>