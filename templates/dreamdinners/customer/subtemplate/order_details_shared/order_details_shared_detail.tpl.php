<div class="container">
	<div class="row bg-gray-light mb-4">
		<?php if ($this->sessionInfo['session_type'] == CSession::DELIVERED) { ?>
			<?php include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_info_box.tpl.php'); ?>
		<?php } else { ?>
			<?php include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_info_standard.tpl.php'); ?>
		<?php } ?>
		<div class="col-md-4 col-6 text-md-right text-center py-3 col-12">
			<h3 class="font-weight-bold text-uppercase font-size-medium-small">Order Details</h3>
			<?php if ($this->orderDetailsArray['bookingStatus'] == CBooking::CANCELLED) { ?>
				<p class="text-danger">Canceled</p>
			<?php } ?>
			<div>
				Ordered on <?php echo CTemplate::dateTimeFormat($this->orderInfo['timestamp_created']);?>
			</div>
			<?php if ($this->orderInfo['ltd_round_up_value'] > 0) { ?>
				<div>
					Donated <?php echo CTemplate::moneyFormat($this->orderInfo['ltd_round_up_value']); ?>
				</div>
			<?php } ?>
			<div>
				Menu total $<?php echo CTemplate::moneyFormat($this->orderInfo['grand_total']); ?>
			</div>
			<div>
				Grand total $<?php echo CTemplate::moneyFormat(floatval($this->orderInfo['grand_total']) + floatval($this->orderInfo['ltd_round_up_value'])); ?>
			</div>
			<?php foreach ($this->paymentInfo as $payment) { ?>
				<?php if (is_array($payment) && $payment['payment_type'] == 'CC') { ?>
					<div>Payment $<?php echo $payment['total']['other']; ?> on <?php echo $payment['credit_card_type']['other']; ?> (<?php echo substr($payment['payment_number']['other'], 12, 4); ?>)
						<?php if (isset($payment['deposit'])) echo "(" . $payment['deposit']['title'] . ")"; ?>
						<?php if (isset($payment['delayed_date'])) echo "(" . $payment['delayed_date']['other'] . ")"; ?>
					</div>
				<?php } else if (is_array($payment) && $payment['payment_type'] == 'CASH') { ?>
					<div>Payment $<?php echo $payment['total']['other']; ?> in Cash</div>
				<?php } else if (is_array($payment) && $payment['payment_type'] == 'STORE_CREDIT') { ?>
					<div>Payment $<?php echo $payment['total']['other']; ?> of Store Credit</div>
				<?php } else if (is_array($payment) && $payment['payment_type'] == 'GIFT_CARD') { ?>
					<div>Payment $<?php echo $payment['total']['other']; ?> on Gift Card</div>
				<?php } ?>
				<?php if (is_array($payment) && $payment['payment_type'] == 'REFUND' ) { ?>
					<div class="text-danger">Refund $<?php echo $payment['total']['other']; ?> to <?php echo $payment['credit_card_type']['other']; ?> (<?php echo substr($payment['payment_number']['other'], 12, 4); ?>)</div>
				<?php } ?>
				<?php if (is_array($payment) && $payment['payment_type'] == 'REFUND_GIFT_CARD'  ) { ?>
					<div class="text-danger">Refund $<?php echo $payment['total']['other']; ?> on Gift Card</div>
				<?php } ?>
			<?php } ?>

			<?php if (!empty($this->orderInfo['order_user_notes'])) { ?>
				<div class="pt-4">
					Special Instructions: <?php echo $this->orderInfo['order_user_notes']; ?>
				</div>
			<?php } ?>

			<?php if ( $this->sessionInfo['session_type'] == CSession::DELIVERED) { ?>
				<h3 class="font-weight-bold text-uppercase font-size-medium-small mt-3">Shipping Address</h3>
			<?php } ?>
			<?php if ( $this->sessionInfo['session_type'] == CSession::DELIVERY) { ?>
				<h3 class="font-weight-bold text-uppercase font-size-medium-small mt-3">Delivery Details</h3>
			<?php } ?>
			<?php if ($this->sessionInfo['session_type_subtype'] == CSession::DELIVERY || $this->sessionInfo['session_type'] == CSession::DELIVERED) { ?>
				<div><?php echo $this->orderInfo['orderAddress']['firstname']; ?> <?php echo $this->orderInfo['orderAddress']['lastname']; ?></div>
				<div><?php echo $this->orderInfo['orderAddress']['address_line1']; ?></div>
				<?php echo (!empty($this->orderInfo['orderAddress']['address_line2'])) ? "<div>" . $this->orderInfo['orderAddress']['address_line2'] . "</div>" : ''; ?>
				<div><?php echo $this->orderInfo['orderAddress']['city']; ?>, <?php echo $this->orderInfo['orderAddress']['state_id']; ?> <?php echo $this->orderInfo['orderAddress']['postal_code']; ?></div>
				<div><?php echo $this->orderInfo['orderAddress']['telephone_1']; ?></div>
			<?php } ?>
		</div>
	</div>

	<?php if (!$this->sessionInfo['is_past'] && $this->sessionInfo['session_type'] != CSession::DELIVERED) { ?>
		<div class="row mb-4 d-print-none justify-content-center">
			<?php if (false) { // disabled add to calendar ?>
				<div class="col-12 col-sm-6 col-lg-3 mb-1">
					<div class="btn-group btn-block">
						<div class="addeventatc btn btn-primary" data-styling="none" aria-haspopup="true" aria-expanded="false">
							<i class="dd-icon icon-calendar_add font-size-extra-extra-large"></i>
							<div>Add to Calendar</div>
							<span class="start collapse"><?php echo CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP); ?></span>
							<span class="end collapse"><?php echo  CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP); ?></span>
							<span class="timezone collapse"><?php echo $this->storeInfo['PHPTimeZone'] ?></span>
							<span class="title collapse">Dream Dinners Session</span>
							<span class="description collapse">Bring your cooler to easily transport your meals home. If you have any questions regarding this or any other Dream Dinners information please contact the store. Email: <?php echo $this->storeInfo['email_address']; ?> Phone: <?php echo $this->storeInfo['telephone_day']; ?></span>
							<span class="location collapse"><?php if(strpos($this->sessionInfo['session_type_subtype'],'PICKUP') !== false)
								{
									$Remote_AddressObj = $this->sessionInfo['session_remote_location'];
									$Remote_Address = $Remote_AddressObj->address_line1 . ", " . (!empty($Remote_AddressObj->address_line2) ? $Remote_AddressObj->address_line2
											. ", " : "") . $Remote_AddressObj->city . " " . $Remote_AddressObj->state_id . ", " . $Remote_AddressObj->postal_code;
									echo $Remote_Address;
								}
								else{ echo $this->storeInfo['address_line1']; ?>
									<?php echo (!empty($this->storeInfo['address_line2'])) ? ' ' . $this->storeInfo['address_line2'] : ''; ?>
									<?php echo $this->storeInfo['city']; ?>
									<?php echo $this->storeInfo['state_id']; ?>
									<?php echo $this->storeInfo['postal_code']; }?></span>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="col-12 col-sm-6 col-lg-3 mb-1">
				<a class="btn btn-primary btn-block" href="/my-events?sid=<?php echo $this->sessionInfo['id']; ?>">
					<i class="dd-icon icon-email_1 font-size-extra-extra-large"></i>
					<div>Invite Friends</div>
				</a>
			</div>
			<?php if ($this->sessionInfo['session_type'] != CSession::DELIVERY) { ?>
				<div class="col-12 col-sm-6 col-lg-3 mb-1">
					<button class="btn btn-primary btn-block"
							data-share-social="facebook,twitter"
							data-share-title="<?php echo $this->share_message['title']; ?>"
							data-share-text="<?php echo $this->share_message['message']; ?>"
							data-share-url="<?php echo HTTPS_BASE . 'session/' . $this->sessionInfo['id']; ?>-<?php echo CUser::getCurrentUser()->id;?>">
						<i class="dd-icon icon-share2 font-size-extra-extra-large"></i>
						<div>Share Session</div>
					</button>
				</div>
			<?php } ?>
			<div class="col-12 col-sm-6 col-lg-3 mb-1">
				<button class="btn btn-cyan btn-block"
						data-share-social="facebook,twitter"
						data-share-title="Dream Dinners"
						data-share-text="Join me at Dream Dinners to get easy, delicious dinners prepped for your family. Save time and check dinner off your to do list."
						data-share-url="<?php echo HTTPS_BASE; ?>share/<?php echo CUser::getCurrentUser()->id; ?>">
					<i class="dd-icon icon-friend font-size-extra-extra-large"></i>
					<div>Refer Friends</div>
				</button>
			</div>
		</div>
	<?php } ?>

	<?php include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared.tpl.php'); ?>

</div>