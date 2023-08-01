<?php $active_menus = CMenu::getActiveMenuArray(); ?>
<?php if (!empty($this->pagination)) { ?>
	<div class="row">
		<div class="col">
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm btn-block orders-page-prev<?php echo ($this->pagination_prev) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm btn-block orders-page-next<?php echo ($this->pagination_next) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php } ?>
<?php if ($this->no_more_rows) { ?>
	<table id="orders_history_table">
		<tbody>
		<tr>
			<td colspan="7">There are no more orders.</td>
		</tr>
		</tbody>
	</table>
<?php } else{ ?>
<?php $rowcount = 1; foreach ($this->orders as $order) { $rowcount++; ?>
	<?php $showDateStyle = ($order['session_type'] == CSession::DELIVERED || $order['session_type_subtype'] == CSession::WALK_IN)? MONTH_DAY_YEAR : CONCISE_NO_SECONDS; ?>
	<div class="row mb-2 py-2 <?php echo ($rowcount % 2) ? ' bg-gray border-top border-bottom' : '' ;?>">
		<div class="col-xl-6">
			<div class="row">
				<div class="col-8 col-sm-6 text-center text-sm-left order-sm-1">
					<p><a class="font-weight-bold" href="/main.php?page=order_details&amp;order=<?php echo $order['id']; ?>"><?php echo CTemplate::dateTimeFormat($order['session_start'], $showDateStyle); ?></a></p>
				</div>
				<div class="col-2 col-sm-2 text-center text-sm-right mb-2 order-sm-3">$<?php echo $order['grand_total']; ?></div>
				<div class="col-sm-4 text-center text-sm-left order-sm-2">
					<p><?php echo COrders::getCustomerActionStringFrom($order['fully_qualified_order_type']); ?></p>
				</div>
			</div>
		</div>

		<div class="col-xl-6">
			<div class="row">
				<?php if ($order['future_session']) { ?>
					<?php if ($order['session_type'] != CSession::DELIVERED && $order['session_type_subtype'] != CSession::WALK_IN) { ?>
						<div class="col-6 mb-1 col-md-auto">
							<a class="btn btn-primary btn-sm btn-block" href="/main.php?page=my_events&amp;sid=<?php echo $order['session_id']; ?>">Invite Friends</a>
						</div>
					<?php } ?>
					<?php if (isset($order['reschedulable']) && $order['reschedulable'] && $order['session_type_subtype'] != CSession::WALK_IN) { ?>
						<div class="col-6 mb-1 col-md-auto">
							<a class="btn btn-secondary btn-sm btn-block" href="/main.php?page=session&amp;reschedule=<?php echo $order['id']; ?>">Reschedule</a>
						</div>
					<?php } ?>
					<?php if (isset($order['has_freezer_inventory']) && $order['has_freezer_inventory'] && $order['session_type_subtype'] != CSession::WALK_IN) { ?>
						<div class="col-6 mb-1 col-md-auto">
							<a class="btn btn-primary btn-sm btn-block" href="main.php?page=sides_and_sweets_order_form&amp;id=<?php echo $order['id']; ?>">Sides &amp; Sweets Request Form</a>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if ($order['status'] != CBooking::CANCELLED) { ?>
					<?php if ($order['can_rate_my_meals'] && $order['session_start'] > '2012-01-01 00:00:00' && !$order['future_session']) { ?>
						<div class="col-12 mb-1 col-md-auto">
							<a class="btn btn-primary btn-sm btn-block" href="/main.php?page=my_meals&amp;order=<?php echo $order['id']; ?>">&#9733; Rate Items</a>
						</div>
					<?php } ?>
					<?php if ($order['session_start'] > '2014-11-01 00:00:00' && $order['session_type'] != CSession::DELIVERED) { ?>
						<div class="col-6 mb-1 col-md-auto">
							<a class="btn btn-primary btn-sm btn-block" href="/main.php?page=print&amp;order=<?php echo $order['id']; ?>&amp;freezer=true" target="_blank">Freezer Sheet</a>
						</div>
						<div class="col-6 mb-1 col-md-auto">
							<a class="btn btn-primary btn-sm btn-block" href="/main.php?page=print&amp;order=<?php echo $order['id']; ?>&amp;nutrition=true" target="_blank">Nutritionals</a>
						</div>
						<?php if (array_key_exists($order['menu_id'] + 1, $active_menus)) { ?>
							<div class="col-12 col-md-auto">
								<a class="btn btn-primary btn-sm btn-block" href="/main.php?page=print&amp;order=<?php echo $order['id']; ?>&amp;core=true" target="_blank"><i class="dd-icon icon-print mr-2"></i>Next Month's Menu</a>
							</div>
						<?php } ?>
					<?php } ?>
					<?php if ($order['session_type'] == CSession::DELIVERED ) { ?>
						<div class="col-6 mb-1 col-md-auto">
							<a class="btn btn-primary btn-sm btn-block" href="/main.php?page=print&amp;order=<?php echo $order['id']; ?>&amp;nutrition=true" target="_blank">Nutritionals</a>
						</div>
						<?php if ($order['tracking_number']) { ?>
							<div class="col-6 mb-1 col-md-auto" style="margin-left: auto;">
								<span style="font-size: .95rem;">FedEx #: <a target="_blank" href="<?php echo CAppUtil::fedexTrackingUrl($order['tracking_number']); ?>"><?php echo $order['tracking_number']; ?></a></span>
							</div>
						<?php } ?>
					<?php } ?>
				<?php } else { ?>
					<div class="col text-center text-md-left">
						<span class="text-muted">Canceled</span>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>
<?php } ?>
<?php if (!empty($this->pagination)) { ?>
	<div class="row">
		<div class="col">
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm btn-block orders-page-prev<?php echo ($this->pagination_prev) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm btn-block orders-page-next<?php echo ($this->pagination_next) ? '' : ' disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php } ?>
