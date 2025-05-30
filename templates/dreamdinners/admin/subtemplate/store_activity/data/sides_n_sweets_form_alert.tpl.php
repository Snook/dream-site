<a href="<?php echo HTTPS_BASE; ?>backoffice/user-details?id=<?php echo $this->user->id; ?>"><?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?></a>  would like the following freezer items added to their order on <a href="<?php echo HTTPS_BASE; ?>backoffice/order-mgr?order=<?php echo $this->order_details['id']; ?>"><?php echo CTemplate::dateTimeFormat($this->order_details['session_start'], VERBOSE); ?></a><br>
<br>
Payment method: <?php echo $this->payment; ?><br>
Use Dinner Dollars: <?php echo $this->use_dinner_dollars; ?><br>
<br>
Guest email: <?php echo $this->user->primary_email; ?><br>
Phone number: <?php echo $this->user->telephone_1; ?><br>
<br>
Desired menu items:<br>
<?php foreach ($this->desired_items AS $menu_item) { ?>
	* <?php echo $menu_item['quantity_desired']; ?> - <?php echo $menu_item['item_detail']->menu_item_name; ?><?php echo ((!empty($menu_item['item_detail']->is_store_special)) ? ' - ' . $menu_item['item_detail']->pricing_type_info['pricing_type_name'] : ''); ?> ($<?php echo $menu_item['item_detail']->store_price; ?>)<br>
<?php } ?>