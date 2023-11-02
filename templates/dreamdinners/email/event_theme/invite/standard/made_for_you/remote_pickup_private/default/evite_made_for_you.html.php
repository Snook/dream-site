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
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/event_theme/standard/made_for_you/remote_pickup_private/<?php echo $this->session['menu_directory']; ?>/graphic.jpg" alt="monthly prepped meals" /></td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td style="padding:20px;"><p align="center"><span class="title">You’re Invited to a Community Pick Up Event</span></p>
			<p align="center">
		  Join me at this special pick up event to fill your freezer with delicious, easy meals for your family. You will choose your perfectly prepped dinners from Dream Dinners monthly menu, then the Dream Dinners team will assemble your dinners at their local assembly kitchen. You simply pick up your dinners at the community pick up location at <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?> on <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>. </p>
			<p align="center">Spend less time in the kitchen and more time savoring moments together. </p>
			<p align="center"><?php echo $this->message; ?><br>
			</p>
			<p align="center"><strong>When:</strong><br />
				<?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?> at <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>
			</p>
			<p align="center"><strong>Community Pick Up Location:</strong><br>
				Hosted by <?php echo $this->session['session_host_firstname']; ?> <br />
				<?php echo $this->session['session_remote_location']->address_line1 . ((!empty($this->session['session_remote_location']->address_line2)) ? ' ' . $this->session['session_remote_location']->address_line2 : '') . ', ' . $this->session['session_remote_location']->city . ', ' . $this->session['session_remote_location']->state_id . ' ' .$this->session['session_remote_location']->postal_code; ?></p>


			<p align="center"><strong>For questions about ordering contact Dream Dinners at:</strong><br />
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