<div>
	&#8226; <?php echo CTemplate::dateTimeFormat($item['time'], TIME, $this->store, CONCISE); ?>
	<span data-tooltip="<?php echo CUser::userTypeText($item['user_type']); ?>"><a href="main.php?page=admin_user_details&amp;id=<?php echo $item['user_id']; ?>" target="_blank"><?php echo $item['user']; ?></a></span>
	<b><?php echo $item['type']; ?></b>
	<?php echo (($item['order_type'] == 'WEB') ? 'Customer' : 'BackOffice'); ?>

	<?php if (!empty($item['order_data']) && !empty($item['order_data']->opted_to_customize_recipes) && $item['order_data']->opted_to_customize_recipes == true) {
		$this->order_customization_json = $item['order_data']->order_customization;
		$feeStr = "Customization Fee: $".$this->moneyFormat($item['order_data']->subtotal_meal_customization_fee)."<br>";
		$feeStr .= "Total Customized Meals: ".$item['order_data']->total_customized_meal_count."<br><br>";?>
		<i class="dd-icon icon-customize text-orange" style="font-size: 75%;" data-tooltip="<?php echo $feeStr; include $this->loadTemplate('admin/subtemplate/store_activity/data/order_customization_alert.tpl.php');?>"></i>
	<?php } ?>

	<?php if (!empty($item['session_data'])) { // session data not available for initial order after it's been rescheduled ?>
		<span class="type"><?php echo CCalendar::sessionTypeNote($item['session_data']['session_type_true']); ?></span>
	<?php } ?>
	Order <a href="main.php?page=admin_order_mgr&amp;order=<?php echo $item['order_id']; ?>" target="_blank"> <?php echo $item['order_id']; ?></a>
	with <?php echo $item['item_count']; ?> items
	<?php if (!empty($item['total_efl_item_count'])) { // show efl item count so store's know items are needed to be pulled from freezer ?>
		<span class="badge badge-yellow badge-pill"><?php echo $item['total_efl_item_count']; ?> EFL item<?php echo (($item['total_efl_item_count'] > 1) ? 's' : ''); ?></span>
	<?php } ?>
	for $<?php echo $item['total']; ?>
	<?php if ($item['type'] == 'RESCHEDULED') { ?>
		<span class="text-white-space-nowrap"><?php echo $item['date_string']; ?></span>
	<?php } else if ($item['type'] == 'PLACED' || $item['type'] == 'SAVED') { ?>
		<span class="text-white-space-nowrap"> for  <a href="main.php?page=admin_main&amp;session=<?php echo $item['session_data']['id']; ?>" target="_blank">
				<?php echo CTemplate::dateTimeFormat($item['session'], NORMAL); ?> session </a></span>
	<?php } else if ($item['type'] == 'EDITED') { ?>
		<button class="show_edit_notes astext" data-index="<?php echo $index; ?>">&bigtriangledown;</button>
		<ul class="edit_note_content collapse list-unstyled px-3" data-index="<?php echo $index; ?>">
			<li class="additional-info"><?php echo $item['notes']; ?></li>
		</ul>
	<?php } ?>
</div>