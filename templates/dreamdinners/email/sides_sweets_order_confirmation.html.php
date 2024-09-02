<html lang="en">
<head>
</head>
<body>
<table role="presentation" width="600" border="0" align="center" cellpadding="5" cellspacing="0">
	<tr>
		<td align="center" style="padding: 10px"><a href="<?php echo HTTPS_SERVER; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_logotype_darkgrey_300x28.png" alt="Dream Dinners" width="300" height="28"></a></td>
	</tr>
</table>
<table role="presentation" width="600" border="0" align="center" cellpadding="20" cellspacing="0">
	<tr>
		<td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 24px; color: #444444;"><strong>Your Sides &amp; Sweets request has been emailed to the store.</strong></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">We will contact you if we have any questions. Here is the list of items you requested:</p>
			<ul style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">
				<?php foreach ($this->desired_items AS $menu_item) { ?>
					<li><?php echo $menu_item['quantity_desired']; ?> - <?php echo $menu_item['item_detail']->menu_item_name; ?><?php echo ((!empty($menu_item['item_detail']->is_store_special)) ? ' - ' . $menu_item['item_detail']->pricing_type_info['pricing_type_name'] : ''); ?> ($<?php echo $menu_item['item_detail']->store_price; ?>)</li>
				<?php } ?>
			</ul>
			<hr>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">If you have any questions, please reach out to us at <?php echo $this->DAO_booking->DAO_store->telephone_day; ?> or via email by replying.</p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">&nbsp; </p>
		</td>
	</tr>
</table>
</body>
</html>