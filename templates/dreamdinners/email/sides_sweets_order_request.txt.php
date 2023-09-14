<a href="<?php echo HTTPS_BASE; ?>?page=admin_user_details&id=<?php echo $this->user->id; ?>"><?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?></a> would like the following freezer items added to their order on <a href="<?php echo HTTPS_BASE; ?>?page=admin_order_mgr&order=<?php echo $this->order_details['id']; ?>"><?php echo CTemplate::dateTimeFormat($this->order_details['session_start'], VERBOSE); ?></a>

Payment method: <?php echo $this->payment; ?>
Use Dinner Dollars: <?php echo $this->use_dinner_dollars; ?>

Guest email: <?php echo $this->user->primary_email; ?>
Phone number: <?php echo $this->user->telephone_1; ?>

Desired menu items:
<?php foreach ($this->desired_items AS $menu_item) { ?>
* <?php echo $menu_item['quantity_desired']; ?> - <?php echo $menu_item['item_detail']->menu_item_name; ?><?php echo ((!empty($menu_item['item_detail']->is_store_special)) ? ' - ' . $menu_item['item_detail']->pricing_type_info['pricing_type_name'] : ''); ?> ($<?php echo $menu_item['item_detail']->store_price; ?>)
<?php } ?>