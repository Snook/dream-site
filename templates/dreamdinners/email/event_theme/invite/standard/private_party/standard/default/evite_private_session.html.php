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
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/standard/private_party/standard/<?php echo $this->session['menu_directory']; ?>/graphic.jpg" alt="Private Party Menu Images" /></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td style="padding:20px;">
			<p align="center"><span class="title">YOU'RE INVITED TO A PRIVATE PARTY!</span></p>
			<p align="center">Join <?php echo $this->session['session_host_informal_name']; ?> at a Private Party to simplify your mealtime and get prepped family-style dinners to cook at home. Dream Dinners will take care of all of the shopping, chopping, and clean up. All you have to do is relax and enjoy some extra time with your friends and family.</p>
			<p align="center">This event has limited spots available, so <a href="<?php echo $this->referral_link; ?>">RSVP today!</a></p>
			<p align="center"><?php echo $this->message; ?></p>
			<p align="center"><strong>When:</strong><br />
				<?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
        <br /><?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?></p>
      <p align="center"><strong>Where:</strong><br />
						Dream Dinners<br />
						<?php echo $this->session['address_line1']; ?><br />
						<?php echo $this->session['address_line2']; ?><br />
						<?php echo $this->session['city']; ?>,&nbsp;<?php echo $this->session['state_id']; ?>&nbsp;&nbsp;<?php echo $this->session['postal_code']; ?><br />
						<?php echo $this->session['telephone_day']; ?></p>
      <p align="center"><strong>Invite Code:</strong><br />
						<?php echo $this->session['session_password']; ?></p>

      <p align="center"><a href="<?php echo $this->referral_link; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/default/rsvp-now-olive-button-185x40.gif" alt="RSVP" width="185" height="40" border="0"></a></p>
		</td>
	</tr>

	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/event_theme/default/homemade-made-easy-grey-footer-650x40.gif" width="650" height="40" alt="dreamdinners.com"></td>
	</tr>
</table>
</body>
</html>