<html>
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#fff">
		<td align="center" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_logotype_darkgrey_300x28.png" alt="Dream Dinners" width="300" height="28"></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Pick Up Order Reminder</span></p></td>
	</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>Dear <?php echo $this->DAO_user->firstname; ?>, <br /><br />
			  It's almost time to pick up your meals. We're looking forward to seeing you when it's convenient during your pick up window on <b><?php echo $this->DAO_session->sessionStartDateTime()->format("F j, Y - g:i A"); ?> to <?php echo $this->DAO_session->sessionEndDateTime()->format("g:i A"); ?></b> at our <b><?php echo $this->DAO_store->store_name; ?></b> location.</p>

			<?php if ($this->DAO_user_digest->visit_count == 1) { ?>
				<?php include $this->loadTemplate('email/session_reminder/session_tips.html.php'); ?>
			<?php } else { ?>
				<?php include $this->loadTemplate('email/session_reminder/session_tips.html.php'); ?>
			<?php } ?>
			<p>We look forward to seeing you soon.</p>
			<p>Enjoy!<br/>
			  Dream Dinners</p>
		</td>
	</tr>
	<tr>
		<td>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"><br/>
			<p><b>Not feeling well?</b><br/>
				If you are experiencing a fever or other illness symptoms within 24 hours of your pick up or assembly session, please call to reschedule your visit.</p>

			<p><b>Reschedule and Cancellation Policy</b><br/>
				If you need to reschedule or cancel your order, contact us six days prior to your order date. Cancellations with six or more days’ notice will receive a full refund. Cancellations within five or fewer days’ notice will be subject to a 25% restocking fee.</p>

			<p><a href="<?php echo HTTPS_BASE; ?>location/<?php echo $this->DAO_store->id?>">Contact your local store</a> | <a href="<?php echo HTTPS_BASE; ?>terms">View Terms and Conditions</a></p>
		</td>
	</tr>
</table>
</body>
</html>