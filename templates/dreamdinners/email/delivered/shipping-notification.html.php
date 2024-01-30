<html lang="en">
<head>
</head>
<body>
<table role="presentation" width="500" border="0" align="center" cellpadding="5" cellspacing="0">
	<tr>
		<td align="center" style="padding: 10px"><a href="<?php echo HTTPS_SERVER; ?>"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></a></td>
	</tr>
</table>
<table role="presentation" width="500" align="center">
	<tr>
		<td><img src="<?=EMAIL_IMAGES_PATH?>/delivered/delivered-box-doorstep-500x300.jpg" width="500" height="300" alt="Dream Dinners"></td>
	</tr>
</table>
<table role="presentation" width="500" border="0" align="center" cellpadding="10" cellspacing="0">
	<tr>
		<td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><strong>Your Dream Dinners order has shipped!</strong></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">Your freshly assembled dinners will be delivered to your home on your selected delivery date. Make sure to place your dinners in your refrigerator as soon as you can if you plan on eating them in the next few days or in the freezer for future use.</p>
			
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Delivery Date:</b> <?php echo CTemplate::dateTimeFormat($this->orderObj->findSession()->session_start, FULL_MONTH_DAY_YEAR); ?></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Tracking Number:</b> </p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Shipping Address:</b><br/>
				<?php echo $this->orderObj->orderAddress->address_line1; ?> <?php echo (!empty($this->orderObj->orderAddress->address_line2) ? $this->orderObj->orderAddress->address_line2 : ''); ?><br/>
				<?php echo $this->orderObj->orderAddress->city; ?>, <?php echo $this->orderObj->orderAddress->state_id; ?> <?php echo $this->orderObj->orderAddress->postal_code; ?></p>
			<hr>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">If you have questions please contact Dream Dinners <?=$this->sessionInfo['store_name']?> at <?= $this->sessionInfo['telephone_day'] ?> or via email by replying.</p>
		</td>
	</tr>
</table>
</body>
</html>