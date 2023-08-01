<!-- Credit Card -->
<?php if (!$this->isGiftCardOnlyOrder && !empty($this->card_references) && !$this->isEditDeliveredOrder) { ?>
	<div class="row mb-2 pb-2">
		<div class="col">
			<h2 class="font-weight-bold font-size-medium-small">Your credit cards</h2>

			<?php foreach($this->card_references AS $thisRef) {
				if ($thisRef['stale']) { ?>
					<div class="form-row mb-1" id="row-cc_ref-<?php echo $thisRef['ucf_id']; ?>" data-num_cc_refs="<?php echo count($this->card_references); ?>">
						<div class="col pl-3">
							<div class="custom-control custom-radio custom-control-inline">
								<label class="custom-control-label" for="cc_pay_id<?php echo $thisRef['ucf_id']; ?>"><img src="<?php echo IMAGES_PATH; ?>/ccicon/<?php echo strtolower($thisRef['card_type']); ?>_icon.gif" data-credit_card_logo="<?php echo $thisRef['card_type']; ?>" class="icon-credit-card dimmed_dd_image"> <?php echo $thisRef['card_type']; ?> ending in <?php echo  $thisRef['cc_number']; ?></label>
							</div>
							<span class="collapse" id="checkout_title-cc_ref-<?php echo $thisRef['ucf_id']; ?>">Stored Credit Card <?php echo $thisRef['card_type']; ?> ending in <?php echo $thisRef['cc_number']; ?></span>
							<i id="remove-cc_ref-<?php echo $thisRef['ucf_id']; ?>" class="far fa-trash-alt text-green-dark-extra ml-2"></i>
							<br /><p class="text-gray-600 font-size-small mb-1">This saved card cannot be referenced. Please add and save this card again if you would like to use it.</p>
						</div>
					</div>
				<?php } else { ?>
				<div class="form-row mb-1" id="row-cc_ref-<?php echo $thisRef['ucf_id']; ?>" data-num_cc_refs="<?php echo count($this->card_references); ?>">
					<div class="col pl-3">
						<div class="custom-control custom-radio custom-control-inline">
							<input class="custom-control-input" type="radio" id="cc_pay_id<?php echo $thisRef['ucf_id']; ?>" name="cc_pay_id" value="<?php echo $thisRef['ucf_id']; ?>" required <?php if ($thisRef['card_count'] == 1) { ?>checked<?php } ?> />
							<label class="custom-control-label" for="cc_pay_id<?php echo $thisRef['ucf_id']; ?>"><img src="<?php echo IMAGES_PATH; ?>/ccicon/<?php echo strtolower($thisRef['card_type']); ?>_icon.gif" data-credit_card_logo="<?php echo $thisRef['card_type']; ?>" class="icon-credit-card"> <?php echo $thisRef['card_type']; ?> ending in <?php echo  $thisRef['cc_number']; ?></label>
						</div>
						<span class="collapse" id="checkout_title-cc_ref-<?php echo $thisRef['ucf_id']; ?>">Stored Credit Card <?php echo $thisRef['card_type']; ?> ending in <?php echo $thisRef['cc_number']; ?></span>
						<i id="remove-cc_ref-<?php echo $thisRef['ucf_id']; ?>" class="far fa-trash-alt text-green-dark-extra ml-2"></i>
					</div>
				</div>
			<?php } } ?>
			<div class="form-row mt-3">
				<div class="col pl-3">
					<span class="btn btn-sm btn-outline-green-dark-extra" role="button" data-toggle="collapse" data-parent="#add_new_cc" href="#add_new_cc" aria-expanded="<?php echo ((empty($this->card_references)) ? 'true' : 'false'); ?>" aria-controls="add_new_cc"><i class="fas fa-plus-square"></i> Add New Card</span>
				</div>
			</div>
		</div>
	</div>
<?php } ?>

<?php if ($this->isEditDeliveredOrder) { ?>
	<div class="row mb-2 pb-2">
		<div class="col">
			<h2 class="font-weight-bold font-size-medium-small"><?php echo $this->delta_is_refund ?  'refund':'charge'; ?> payment method</h2>

			<?php foreach($this->card_references AS $thisRef) { ?>
				<div class="form-row mb-1" id="row-cc_ref-<?php echo $thisRef['payment_id']; ?>" data-num_cc_refs="<?php echo count($this->card_references); ?>">
					<div class="col pl-3">
						<div class="custom-control custom-radio custom-control-inline">
							<?php $payTypePrefix = is_null($thisRef['card_type']) ? 'gc' : 'cc';?>
							<?php if($thisRef['has_multiple_refund']){?>
								<input class="custom-control-input" type="checkbox" id="<?php echo $payTypePrefix; ?>_pay_id_edit_order_<?php echo $thisRef['payment_id']; ?>" name="<?php echo $payTypePrefix; ?>_pay_id_edit_order_multi[]" value="<?php echo $thisRef['payment_id']; ?>" checked onclick="return false;"/>
							<?php }else{ ?>
								<input class="custom-control-input" type="radio" id="<?php echo $payTypePrefix; ?>_pay_id_edit_order_<?php echo $thisRef['payment_id']; ?>" name="<?php echo $payTypePrefix; ?>_pay_id_edit_order" value="<?php echo $thisRef['payment_id']; ?>" required <?php if ($thisRef['card_count'] == 1) { ?>checked<?php } ?> />
							<?php } ?>
							<label class="custom-control-label" for="<?php echo $payTypePrefix; ?>_pay_id_edit_order_<?php echo $thisRef['payment_id']; ?>">
							<?php if(is_null($thisRef['card_type'])){?>
								Gift Card
							<?php }else{ ?>
								<img src="<?php echo IMAGES_PATH; ?>/ccicon/<?php echo strtolower($thisRef['card_type']); ?>_icon.gif" data-credit_card_logo="<?php echo $thisRef['card_type']; ?>" class="icon-credit-card"> <?php echo $thisRef['card_type']; ?>
							<?php } ?>
							<?php echo  $thisRef['cc_number']; ?> <?php echo $thisRef['has_multiple_refund'] ?  ' - $'.CTemplate::moneyFormat($thisRef['refund_portion']):'' ?></label>
						</div>
						<span class="collapse" id="checkout_title-cc_ref-<?php echo $thisRef['payment_id']; ?>">Stored Credit Card <?php echo $thisRef['card_type']; ?> ending in <?php echo $thisRef['cc_number']; ?></span>
					</div>
				</div>
			<?php } ?>
			<?php if(!$this->delta_is_refund && !$this->only_gift_cards) { ?>
				<div class="form-row mt-3">
					<div class="col pl-3">
						<span class="btn btn-sm btn-outline-green-dark-extra" role="button" data-toggle="collapse" data-parent="#add_new_cc" href="#add_new_cc" aria-expanded="<?php echo ((empty($this->card_references)) ? 'true' : 'false'); ?>" aria-controls="add_new_cc"><i class="fas fa-plus-square"></i> Use New Card</span>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<div id="add_new_cc" class="row mb-2 collapse <?php echo (($this->isGiftCardOnlyOrder || empty($this->card_references)) ? 'show' : ''); ?>">
	<div class="col">
		<h2 class="font-weight-bold font-size-medium-small">Card details</h2>

		<p id="enough_funds_alert" class="collapse text-warning">You have enough store credit and/or Gift Card funds to pay for this order. You do not need to enter a credit card payment.</p>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_payment['ccNameOnCard_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_payment['ccType_html']; ?>
			</div>
			<div class="form-group col-md-6 d-none d-md-block">
				<?php foreach( $this->arCardIcons as $szType => $arAttributes ) { ?>
					<?php if( $arAttributes['accepted'] === true ) { ?>
						<img src="<?php echo IMAGES_PATH; ?>/ccicon/<?php echo $arAttributes['filename']; ?>" data-credit_card_logo="<?php echo $szType; ?>" class="card-icons" />
					<?php } } ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_payment['ccNumber_html']; ?>
				<div class="credit_card_warning text-muted collapse">The number does not match the selected card type.</div>
			</div>
		</div>
		<div class="form-row mb-4">
			<div class="form-group col-4">
				<?php echo $this->form_payment['ccMonth_html']; ?>
			</div>
			<div class="form-group col-4">
				<?php echo $this->form_payment['ccYear_html']; ?>
			</div>
			<div class="form-group col-4">
				<?php echo $this->form_payment['ccSecurityCode_html']; ?>
			</div>
		</div>
		<div class="col-md-12 px-0 mb-2">
			<h2 class="font-weight-bold font-size-medium-small">Billing Address</h2>
			<div class="form-row">
				<div class="form-group col-md-12">
					<?php echo $this->form_payment['billing_address_html']; ?>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-12">
					<?php echo $this->form_payment['billing_address2_html']; ?>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-12">
					<?php echo $this->form_payment['billing_city_html']; ?>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<?php echo $this->form_payment['billing_state_id_html']; ?>
				</div>
				<div class="form-group col-md-6">
					<?php echo $this->form_payment['billing_postal_code_html']; ?>
				</div>
			</div>

			<?php if (isset($this->form_payment['save_cc_as_ref_html'])) { ?>
				<div class="form-row">
					<div class="form-group pl-3">
						<?php echo $this->form_payment['save_cc_as_ref_html'] ?>
					</div>
				</div>
			<?php } ?>

		</div>
	</div>
</div>

<?php if (isset($this->form_payment['is_store_specific_flat_rate_delayed_payment_html']["0"]) && $this->canProvideNewDepositMechanisms) { ?>
	<div class="row mb-2">
		<div class="col">
			<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left">Payment method</h2>

			<div class="form-row">
				<div class="form-group pl-3">
					<?php echo $this->form_payment['is_store_specific_flat_rate_delayed_payment_html']["0"]; ?>
					<?php echo $this->form_payment['is_store_specific_flat_rate_delayed_payment_html']["1"]; ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (!$this->isGiftCardOnlyOrder && !$this->User->hasEnrolledInPlatePoints() && !$this->isPreferred){ ?>
<div class="row mb-4">
	<div class="col">
	<div class="form-row">
		<div class="col-md-9 col-9">
			<h2 class="font-weight-bold font-size-medium-small">Enroll in PlatePoints</h2>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group pl-3">
			<div class="custom-control custom-checkbox">
				<input type="checkbox" class="custom-control-input" name="enroll_in_plate_points" id="enroll_in_plate_points">
				<label for="enroll_in_plate_points" class="custom-control-label">Enroll me in PlatePoints. I agree to the <a href="/main.php?static=terms#platepoints" target="_blank">program terms.</a></label>
			</div>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-12 mt-2">
			<div class="row ml-1">
				Birthday
			</div>
			<div class="row">
				<div class="col-md-6 pr-md-1 mb-2 mb-md-0">
					<?php echo $this->form_payment['birthday_month_html']; ?>
				</div>
				<div class="col-md-6 pl-md-1">
					<?php echo $this->form_payment['birthday_year_html']; ?>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>
<?php } ?>
