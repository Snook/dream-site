<table width="100%" border="0">
	<thead>
	<tr>
		<td colspan="6" style="padding-top:2px;color:green;font-size:13px;font-weight:bold;">Recent Orders for <?php echo $this->customerName?></td>
	</tr>
	<tr>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Order Date</td>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Status</td>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Session Location</td>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Session Date</td>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Order Total</td>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Balance Due</td>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Type of Order</td>
		<td class="bgcolor_dark catagory_row_compact" style="height:15px;">Servings<br />Count</td>
	</tr>
	<?php
	$active_menus = CMenu::getActiveMenuArray();

	if( !empty( $this->user_orders) )
	{
	foreach( $this->user_orders as $order )
	{
	?>
	</thead>
	<tbody>
	<tr>
		<td class="order_history_cell" style="white-space: nowrap;"><?=$this->dateTimeFormat($order['timestamp_created'], NORMAL, $order['store_id'], CONCISE);?></td>
		<td class="order_history_cell"><?=$order['status'];?></td>
		<td class="order_history_cell"><?=$order['store_name'];?></td>
		<td class="order_history_cell" style="white-space: nowrap;"><?php if ($this->current_order == $order['order_id'] ) echo "<span style='color:red'>This Order</span>"; else echo $this->dateTimeFormat($order['session_start'], NORMAL);?></td>
		<td class="order_history_cell"><?= $order['grand_total'] ?></td>
		<td class="order_history_cell"><?=$order['balance_due'];?></td>
		<td class="order_history_cell"><?= COrders::getTypeofOrderDisplayString($order['type_of_order']); ?></td>
		<td class="order_history_cell"><?= $order['servings_total_count'] ?></td>
	</tr>
	<tr>
		<td class="order_history_cell" colspan="8" style="text-align: right;">

			<?php if ($this->current_order != $order['order_id'] ) { ?>
				<a class="button" href="?page=admin_order_history&amp;id=<?php echo $order['user_id']; ?>&amp;order=<?php echo $order['order_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">View Order</a>
				<?php if ($order['canEdit']) { ?><a class="button" href="?page=admin_order_mgr&order=<?=$order['order_id'];?>&back=<?=urlencode($_SERVER['REQUEST_URI'])?>">Edit</a><?php } ?>
			<?php } ?>

			<?php if (!empty($order['order_admin_notes']) || !empty($order['order_user_notes'])) { ?><a class="button" id="view_notes-<?= $order['order_id'] ?>" data-order_id="<?= $order['order_id'] ?>">Notes</a><?php } ?>
			<?php if ($order['session_start'] > '2014-11-01 00:00:00' && $order['status'] != CBooking::CANCELLED) { ?>
				<a href="?page=print&amp;order=<?php echo $order['order_id']; ?>&amp;freezer=true" class="button" target="_blank">Freezer Sheet</a>
				<a href="?page=print&amp;order=<?php echo $order['order_id']; ?>&amp;nutrition=true" class="button" target="_blank">Nutritionals</a>
				<?php if (array_key_exists($order['idmenu'] + 1, $active_menus)) { ?>
					<a href="?page=print&amp;order=<?php echo $order['order_id']; ?>&amp;core=true" class="button" target="_blank">Next Month's Menu</a>
				<?php } ?>
			<?php } ?>
		</td>
	</tr>
	</tbody>
	<?php if (!empty($order['order_user_notes'])) { ?>
		<tbody id="order_user_notes-<?= $order['order_id'] ?>" style="display: none;">
		<tr>
			<td class="order_history_cell">Special Instructions</td>
			<td colspan="7" class="order_history_cell" style="text-align: left;"><?= $order['order_user_notes'] ?></td>
		</tr>
		</tbody>
	<?php } ?>
	<?php if (!empty($order['order_admin_notes'])) { ?>
		<tbody id="order_admin_notes-<?= $order['order_id'] ?>" style="display: none;">
		<tr>
			<td class="order_history_cell">Order Notes</td>
			<td colspan="7" class="order_history_cell" style="text-align: left;"><?= $order['order_admin_notes'] ?></td>
		</tr>
		</tbody>
	<?php } ?>
	<?php
	}
	}
	else
	{
		?>
		<tbody>
		<tr>
			<td colspan="8" class="order_history_cell"><i>No Other Orders</i></td>
		</tr>
		</tbody>
	<?php } ?>
</table>

<table width="100%" border="0">
	<thead>
	<tr>
		<td colspan="5" style="padding-top:2px;color:green;font-size:13px;font-weight:bold;">Orders for this Session &mdash; <?php echo $this->dateTimeFormat($this->session_start, VERBOSE);?></td>
	</tr>
	<tr>
		<td class="bgcolor_dark catagory_row_compact">Guest Name</td>
		<td class="bgcolor_dark catagory_row_compact">Order Date</td>
		<td class="bgcolor_dark catagory_row_compact">Status</td>
		<td class="bgcolor_dark catagory_row_compact">Order Total</td>
		<td class="bgcolor_dark catagory_row_compact">Balance Due</td>
		<td class="bgcolor_dark catagory_row_compact">Type of Order</td>
		<td class="bgcolor_dark catagory_row_compact">Servings<br />Count</td>
	</tr>
	<?php
	if (!empty($this->session_orders))
	{
	foreach($this->session_orders as $order)
	{
	if ($order['status'] == 'ACTIVE' || $order['status'] == 'SAVED') {
	?>
	</thead>
	<tbody>
	<tr>
		<td class="order_history_cell" style="white-space: nowrap;"><?=$order['firstname'] . " " . $order['lastname'] ;?></td>
		<td class="order_history_cell" style="white-space: nowrap;"><?=$this->dateTimeFormat($order['order_time'], NORMAL, $order['store_id'], CONCISE);?></td>
		<td class="order_history_cell"><?php if ($order['order_id'] == $this->current_order) echo "<span style='color:red'>This Order</span>"; else echo $order['status'];?></td>
		<td class="order_history_cell"><?=$order['grand_total'];?></td>
		<td class="order_history_cell"><span class="<?=$order['balance_due_css'];?>"><?=$order['balance_due'];?></span></td>
		<td class="order_history_cell"><?= COrders::getTypeofOrderDisplayString($order['type_of_order']); ?></td>
		<td class="order_history_cell"><?= $order['servings_total_count'] ?></td>
	</tr>
	<tr>
		<td class="order_history_cell" colspan="7" style="text-align: right;">

			<?php if ($this->current_order != $order['order_id'] ) { ?>
				<a class="button" href="?page=admin_order_history&amp;id=<?php echo $order['user_id']; ?>&amp;order=<?php echo $order['order_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">View Order</a>
				<?php if ($order['can_edit']) { ?><a class="button" href="?page=admin_order_mgr&order=<?=$order['order_id'];?>&back=<?=urlencode($_SERVER['REQUEST_URI'])?>">Edit</a><?php } ?>
			<?php } ?>

			<?php if (!empty($order['order_admin_notes']) || !empty($order['order_user_notes'])) { ?><a class="button" id="view_notes-<?= $order['order_id'] ?>" data-order_id="<?= $order['order_id'] ?>">Notes</a><?php } ?>
			<?php if ($order['session_start'] > '2014-11-01 00:00:00' && $order['status'] != CBooking::CANCELLED) { ?>
				<a href="?page=print&amp;order=<?php echo $order['order_id']; ?>&amp;freezer=true" class="button" target="_blank">Freezer Sheet</a>
				<a href="?page=print&amp;order=<?php echo $order['order_id']; ?>&amp;nutrition=true" class="button" target="_blank">Nutritionals</a>
				<?php if (array_key_exists($order['idmenu'] + 1, $active_menus)) { ?>
					<a href="?page=print&amp;order=<?php echo $order['order_id']; ?>&amp;core=true" class="button" target="_blank">Next Month's Menu</a>
				<?php } ?>
			<?php } ?>
		</td>
	</tr>
	</tbody>
	<?php if (!empty($order['order_user_notes'])) { ?>
		<tbody id="order_user_notes-<?= $order['order_id'] ?>" style="display: none;">
		<tr>
			<td class="order_history_cell">Special Instructions</td>
			<td colspan="7" class="order_history_cell" style="text-align: left;"><?= $order['order_user_notes'] ?></td>
		</tr>
		</tbody>
	<?php } ?>
	<?php if (!empty($order['order_admin_notes'])) { ?>
		<tbody id="order_admin_notes-<?= $order['order_id'] ?>" style="display: none;">
		<tr>
			<td class="order_history_cell">Order Notes</td>
			<td colspan="7" class="order_history_cell" style="text-align: left;"><?= $order['order_admin_notes'] ?></td>
		</tr>
		</tbody>
	<?php } ?>
	<?php
	} }
	}
	else
	{
		?>
		<tbody>
		<tr>
			<td colspan="8" class="order_history_cell">No History</td>
		</tr>
		</tbody>
	<?php } ?>
</table>