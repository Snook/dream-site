<div>
	&#8226; <?php echo CTemplate::dateTimeFormat($item['time'], TIME, $this->store, CONCISE); ?>
	<?php if (!empty($item['user_id'])) { ?>
		<span data-tooltip="<?php echo $item['user_type']; ?>"><a href="?page=admin_user_details&amp;id=<?php echo $item['user_id']; ?>" target="_blank"><?php echo $item['firstname']; ?> <?php echo $item['lastname']; ?></a></span>
	<?php } ?>
	<b><?php echo $item['action']; ?></b>
	<?php echo $item['menu_name']; ?>
	<a href="?page=item&amp;recipe=<?php echo $item['recipe_id']; ?>&amp;ov_menu=<?php echo $item['menu_id']; ?>" target="_blank"><?php echo $item['menu_item_name']; ?></a>
	<span class="text-white-space-nowrap">Recipe #<?php echo $item['recipe_id']; ?> - <?php echo (($item['menu_item_category_id'] != 9) ? $item['pricing_type_info']['pricing_type_name_short_w_qty'] : 'S&amp;S') ; ?></span>
</div>