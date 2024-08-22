<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="650" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>Your Sides &amp; Sweets request has been emailed to the store.</p>
			<p>Desired menu items:</p>
			<uL>
				<?php foreach ($this->desired_items AS $menu_item) { ?>
					<li><?php echo $menu_item['quantity_desired']; ?> - <?php echo $menu_item['item_detail']->menu_item_name; ?><?php echo ((!empty($menu_item['item_detail']->is_store_special)) ? ' - ' . $menu_item['item_detail']->pricing_type_info['pricing_type_name'] : ''); ?> ($<?php echo $menu_item['item_detail']->store_price; ?>)</li>
				<?php } ?>
			</uL>

		</td>
	</tr>
</table>

</body>
</html>