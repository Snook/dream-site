<?php if (!empty($this->pagination)) { ?>
	<div class="row">
		<div class="col">
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="button orders-page-prev<?php echo ($this->pagination_prev) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="button orders-page-next<?php echo ($this->pagination_next) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php } ?>
<?php if (!empty($this->no_more_rows)) { ?>
	<table id="orders_history_table">
	<tbody>
	<tr>
		<td colspan="7">No more orders.</td>
	</tr>
	</tbody>
	</table>
<?php } else{ ?>
<table id="orders_history_table">
	<?php if(!empty($this->orders)) { ?>
		<thead>
		<tr>
			<th>Order Date</th>
			<th>Session Location</th>
			<th>Confirmation Number</th>
			<th>Status</th>
			<th>Type of Order</th>
			<th>Servings Count</th>
			<th>Order Total</th>
		</tr>
		</thead>
		<?php foreach($this->orders as $order) {
			$is_delivered = ($order['session_type'] == CSession::DELIVERED);?>
			<tbody class="<?php if (empty($order['is_future']) || $order['is_future'] == false) { ?>is_past<?php } if ($is_delivered) { ?> is_delivered<?php } ?> order_history_row">
			<tr>
				<td colspan="2">

					<?php if( $is_delivered ){ ?>
						Delivery Date:
						<?php if (CStore::userHasAccessToStore($order['store_id']) && empty($order['session_is_deleted'])) { ?>
							<a href="/backoffice?session=<?php echo $order['session_id']; ?>&amp;order=<?php echo $order['order_id']; ?>"><?php echo $this->dateTimeFormat($order['session_start'], MONTH_DAY_YEAR); ?></a>
						<?php } else { ?>
							<span<?php if (!empty($order['session_is_deleted'])) { ?> style="text-decoration: line-through;" data-tooltip="Session Deleted"<?php } ?>><?php echo $this->dateTimeFormat($order['session_start'], MONTH_DAY_YEAR); ?></span>
						<?php } ?>
					<?php } else { ?>
						Session Date:
						<?php if (CStore::userHasAccessToStore($order['store_id']) && empty($order['session_is_deleted'])) { ?>
							<a href="/backoffice?session=<?php echo $order['session_id']; ?>&amp;order=<?php echo $order['order_id']; ?>"><?php echo $this->sessionTypeDateTimeFormat($order['session_start'], $order['session_type_subtype'], NORMAL)?></a>
						<?php } else { ?>
							<span<?php if (!empty($order['session_is_deleted'])) { ?> style="text-decoration: line-through;" data-tooltip="Session Deleted"<?php } ?>><?php echo $this->sessionTypeDateTimeFormat($order['session_start'], $order['session_type_subtype'], NORMAL)?></span>
						<?php } ?>
					<?php } ?>
				</td>
				<td colspan="5" style="text-align:right">
					<?php if ($order['session_start'] > '2014-11-01 00:00:00' && $order['status'] != CBooking::CANCELLED && $order['status'] != CBooking::SAVED) { ?>
						<?php if( !$is_delivered ){ ?><a href="/print?order=<?php echo $order['order_id']; ?>&amp;freezer=true" class="btn btn-primary btn-sm" target="_blank">Freezer Sheet</a><?php } ?>
						<a href="/print?order=<?php echo $order['order_id']; ?>&amp;nutrition=true" class="btn btn-primary btn-sm" target="_blank">Nutritionals</a>

						<?php if (array_key_exists($order['idmenu'] + 1, $this->active_menus)) { ?>
							<a href="/print?order=<?php echo $order['order_id']; ?>&amp;core=true" class="btn btn-primary btn-sm" target="_blank">Next Month's Menu</a>
						<?php } ?>
					<?php } ?>

					<?php if (CStore::userHasAccessToStore($order['store_id'])) { ?>

						<?php if (!empty($order['can_reschedule']) && $order['status'] == CBooking::ACTIVE && !$this->emergency_mode) { ?>
							<a id="gd_reschedule-<?php echo $order['order_id']; ?>" data-store_id="<?php echo $order['store_id']; ?>" data-session_id="<?php echo $order['session_id']; ?>" data-order_id="<?php echo $order['order_id']; ?>" data-menu_id="<?php echo $order['idmenu']; ?>" class="btn btn-primary btn-sm">Reschedule</a>
						<?php } ?>

						<?php if ($order['canEdit'] && !$this->emergency_mode) { ?>
							<a class="btn btn-primary btn-sm" href="/backoffice/order-mgr?order=<?php echo $order['order_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Edit</a>
							<?php if ($order['status'] != CBooking::SAVED) {
								$cancelPrefix = ($is_delivered ? "gd_cancel_delivered_order-" : "gd_cancel_order-"); ?>
								<span id="<?php echo $cancelPrefix; ?><?php echo $order['id']; ?>" data-user_id="<?php echo $this->user["id"]; ?>" data-store_id="<?php echo $order['store_id']; ?>" data-session_id="<?php echo $order['session_id']; ?>" data-order_id="<?php echo $order['order_id']; ?>" data-menu_id="<?php echo $order['idmenu']; ?>" class="btn btn-primary btn-sm">Cancel Order</span>
							<?php } else { ?>
								<span id="gd_delete_order-<?php echo $order['id']; ?>" data-user_id="<?php echo $this->user['id']; ?>" data-store_id="<?php echo $order['store_id']; ?>" data-session_id="<?php echo $order['session_id']; ?>" data-order_id="<?php echo $order['order_id']; ?>" data-menu_id="<?php echo $order['idmenu']; ?>" class="btn btn-primary btn-sm">Delete Order</span>
							<?php } } else if (!$this->emergency_mode) { ?>
							<a class="btn btn-primary btn-sm" href="/backoffice/order-mgr?order=<?php echo $order['order_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Edit Payments</a>
						<?php  } ?>

						<span data-view_order_details="<?php echo $order['order_id']; ?>" data-booking_id="<?php echo $order['booking_id']; ?>" class="button <?php if (!CStore::userHasAccessToStore($order['store_id'])) { ?>disabled<?php } ?>">View Order</span>

						<a href="/backoffice/order-details-view-all?customer_print_view=1&amp;session_id=<?php echo $order['session_id']; ?>&amp;booking_id=<?php echo $order['booking_id']; ?>&amp;menuid=<?php echo $order['idmenu']; ?>" target="_blank"  class="btn btn-primary btn-sm">Print</a>

					<?php } // end userHasAccessToStore ?>


					<?php if (DD_SERVER_NAME != 'LIVE') { ?>
						<a href="<?php echo $_SERVER['REQUEST_URI']?>&send_test_reminder_email=true&order_id=<?php echo $order['order_id']; ?>"  class="btn btn-primary btn-sm">Send Test Reminder Email</a>
					<?php  } ?>


				</td>
			</tr>
			<tr>
				<td><?php echo $this->dateTimeFormat($order['timestamp_created'], NORMAL, $order['store_id'], CONCISE); ?></td>
				<td><?php echo $order['store_name']; ?></td>
				<td><?php echo $order['order_confirmation']; ?></td>
				<td<?php if ($order['status_text'] == CBooking::SAVED) { ?> style="background-color: #FFED8C;"<?php } ?>><?php echo $order['status_text']; ?></td>
				<?php if ($is_delivered) { ?>
					<td>DELIVERED</td>
				<?php  }else{ ?>
					<td><?php echo COrders::getTypeofOrderDisplayString($order['type_of_order']); ?></td>
				<?php  } ?>
				<td><?php echo $order['servings_total_count']; ?></td>
				<td>$<?php echo $order['grand_total']; ?></td>
			</tr>
			<tr>
				<td colspan="7" data-view_order_details_table="<?php echo $order['booking_id']; ?>" class="order_details_table"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" alt="Processing" /> Loading...</td>
			</tr>
			<?php if (CStore::userHasAccessToStore($order['store_id'])) { ?>
				<?php if (!empty($order['order_user_notes'])) { ?>
					<tr>
						<td>Special Instructions</td>
						<td colspan="6"><?php echo $order['order_user_notes']; ?></td>
					</tr>
				<?php } ?>
				<?php if (!empty($order['order_admin_notes'])) { ?>
					<tr>
						<td>Order Notes</td>
						<td colspan="6"><?php echo $order['order_admin_notes']; ?></td>
					</tr>
				<?php } ?>
			<?php } ?>
			<?php if (CStore::userHasAccessToStore($order['store_id']) && $order['status'] == CBooking::CANCELLED ) { ?>
				<?php if (!empty($order['reason_for_cancellation'])) { ?>
					<tr>
						<td>Reason for Cancellation</td>
						<td colspan="6"><?php echo $order['reason_for_cancellation']; ?></td>
					</tr>
				<?php } ?>
				<?php if (!is_null($order['declined_MFY_option'])) { ?>
					<tr>
						<td>Declined Made For You option</td>
						<td colspan="6"><?php echo ($order['declined_MFY_option'] ? 'YES' : 'NO'); ?></td>
					</tr>
				<?php } ?>
				<?php if (!is_null($order['declined_to_reschedule'])) { ?>
					<tr>
						<td>Declined to reschedule</td>
						<td colspan="6"><?php echo ($order['declined_to_reschedule'] ? 'YES' : 'NO'); ?></td>
					</tr>
				<?php } ?>
			<?php } ?>
			</tbody>
			<tbody><tr><td colspan="7" style="height:2px; padding:0px;"></td><tr></tbody>
		<?php } ?>


	<?php } else { ?>
		<tbody>
		<tr>
			<td colspan="7">No Upcoming Orders</td>
		</tr>
		</tbody>
	<?php } ?>
</table>
<?php }  ?>
<?php if (!empty($this->pagination)) { ?>
	<div class="row">
		<div class="col">
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="button orders-page-prev<?php echo ($this->pagination_prev) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="button orders-page-next<?php echo ($this->pagination_next) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php } ?>