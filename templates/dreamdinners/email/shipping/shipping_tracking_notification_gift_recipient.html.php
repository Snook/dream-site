<html lang="en">
<head>
</head>
<body>
<table role="presentation" width="500" border="0" align="center" cellpadding="5" cellspacing="0">
	<tr>
		<td align="center" style="padding: 10px"><a href="<?php echo HTTPS_SERVER; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></a></td>
	</tr>
</table>
<table role="presentation" width="500" align="center">
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/delivered/delivered-box-doorstep-500x300.jpg" width="500" height="300" alt="Dream Dinners"></td>
	</tr>
</table>
<table role="presentation" width="500" border="0" align="center" cellpadding="10" cellspacing="0">
	<tr>
		<td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><strong>Your Dream Dinners order has shipped!</strong></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">Your gift of freshly assembled dinners from <?php echo $this->DAO_orders->firstname; ?> <?php echo $this->DAO_orders->lastname; ?> will be delivered to your home on <?php echo $this->DAO_orders->DAO_session->sessionStartDateTime()->format("F j, Y"); ?>. Make sure to place your dinners in your refrigerator as soon as you can if you plan on eating them in the next few days or in the freezer for future use.</p>

			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Delivery Date:</b> <?php echo $this->DAO_orders->DAO_session->sessionStartDateTime()->format("F j, Y"); ?></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Tracking Number:</b> <?php echo $this->DAO_orders->DAO_orders_shipping->tracking_number; ?></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;"><b>Shipping Address:</b><br/>
				<?php echo $this->DAO_orders->DAO_orders_address->generateAddressHTML(); ?></p>
			<hr>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">If you have questions please contact Dream Dinners <?php echo $this->DAO_orders->DAO_store->store_name; ?> at <?php echo $this->DAO_orders->DAO_store->telephone_day; ?> or via email by replying.</p>
		</td>
	</tr>
</table>
</body>
</html>