Your Sides &amp; Sweets request has been emailed to the store.

Desired menu items:
<?php foreach ($this->desired_items AS $menu_item) { ?>
* <?php echo $menu_item['quantity_desired']; ?> - <?php echo $menu_item['item_detail']->menu_item_name; ?><?php echo ((!empty($menu_item['item_detail']->is_store_special)) ? ' - ' . $menu_item['item_detail']->pricing_type_info['pricing_type_name'] : ''); ?> ($<?php echo $menu_item['item_detail']->store_price; ?>)
<?php } ?>