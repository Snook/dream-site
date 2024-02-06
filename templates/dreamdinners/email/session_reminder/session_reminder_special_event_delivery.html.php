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
		<td style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Home Delivery Order Reminder</span></p></td>
	</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>Dear <?php echo $this->DAO_user->firstname; ?>, <br /><br />
			  Your delivery is coming up soon. We're looking forward to seeing you during your home delivery window on <b><?php echo $this->DAO_session->sessionStartDateTime()->format("F j, Y - g:i A"); ?> to <?php echo $this->DAO_session->sessionEndDateTime()->format("g:i A"); ?></b>.</p>

			<p><b>What to Expect:</b></p>
			<ol>
				<li>Be home and ready to place your first 3 meals in the fridge to thaw and the rest in the freezer.</li>
				<li>Add a few of our delicious sides, breakfast and sweets to your order by <a href="<?php echo HTTPS_BASE; ?>freezer">completing your request today.</a></li>
				<li><a href="<?php echo HTTPS_BASE; ?>session-menu">Place your next order to reserve your preferred delivery time.</a></li>
			</ol>

			<p>We look forward to seeing you soon.</p>
			<p>Enjoy!<br/>
			  Dream Dinners</p>
		</td>
	</tr>
	<tr>
		<td>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"><br/>
			<p><b>Delivery Policy</b>
				You understand that someone will have to be home in order accept the delivery and if the driver arrives and no one is available to accept the delivery, the driver may leave the order at my front door and take a photo before they leave. If the driver cannot leave the order for any reason, they may have to return the order back to the store. This will incur an additional return delivery fee and a possible restocking fee. Delivery fees are non-refundable within 48 hours of the scheduled delivery date and time. If a new date is selected to deliver the order again, a new delivery fee will be charged.</p>

			<p><b>Reschedule and Cancellation Policy</b><br/>
				If you need to reschedule or cancel your order, contact us six days prior to your order date. Cancellations with six or more days’ notice will receive a full refund. Cancellations within five or fewer days’ notice will be subject to a 25% restocking fee.</p>

			<p><a href="http://blog.dreamdinners.com">Dream Dinners Blog</a> | <a href="<?php echo HTTPS_BASE; ?>locations/<?php echo $this->DAO_store->id?>">Contact your local store</a> | <a href="<?php echo HTTPS_BASE; ?>terms">View Terms and Conditions</a></p>
		</td>
	</tr>
</table>
</body>
</html>