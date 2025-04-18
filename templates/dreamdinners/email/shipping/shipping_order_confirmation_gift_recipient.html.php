<html lang="en">
<head>
</head>
<body>
<table role="presentation" width="500" border="0" align="center" cellpadding="5" cellspacing="0">
	<tr>
		<td align="center" style="padding: 10px"><a href="<?php echo HTTPS_SERVER; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_logotype_darkgrey_300x28.png" alt="Dream Dinners" width="300" height="28"></a></td>
	</tr>
</table>
<table role="presentation" width="500" align="center">
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/delivered/delivered-box-doorstep-500x300.jpg" width="500" height="300" alt="Dream Dinners"></td>
	</tr>
</table>
<table role="presentation" width="500" border="0" align="center" cellpadding="10" cellspacing="0">
	<tr>
		<td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><strong>You have been gifted Dream Dinners!</strong></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">Your four delicious family-style dinners will arrive on your doorstep prepped and ready to cook. Once they arrive, place your dinners directly in your refrigerator to enjoy over the next few days or place in the freezer to save for future use. Then prepare based on the easy to follow, step-by-step cooking instructions.</p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>From:</b> <?php echo $this->orderObj->getUser()->firstname; ?> <?php echo $this->orderObj->getUser()->lastname; ?></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Expected Delivery Date:</b> <?php echo CTemplate::dateTimeFormat($this->orderObj->findSession()->session_start, FULL_MONTH_DAY_YEAR); ?></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Shipping To:</b> <?php echo $this->orderObj->orderAddress->firstname; ?> <?php echo $this->orderObj->orderAddress->lastname; ?>, <?php echo $this->orderObj->orderAddress->address_line1; ?> <?php echo (!empty($this->orderObj->orderAddress->address_line2) ? $this->orderObj->orderAddress->address_line2 : ''); ?>
				<?php echo $this->orderObj->orderAddress->city; ?>, <?php echo $this->orderObj->orderAddress->state_id; ?> <?php echo $this->orderObj->orderAddress->postal_code; ?></p>
			<hr>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">If you have questions please contact Dream Dinners <?php echo $this->sessionInfo['store_name']?> at <?php echo $this->sessionInfo['telephone_day'] ?> or via email by replying.</p>
		</td>
	</tr>
</table>
</body>
</html>