<div class="row mb-4">
	<div class="col-md-12">
		<?php if ($this->cart_info['sessionObj']->isDelivery()) { ?>
			<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left mb-4">Home Delivery Address</h2>
		<?php } else { ?>
			<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left mb-4">Delivery Address</h2>
		<?php } ?>

		<?php if ($this->cart_info['sessionObj']->isDelivered() && !$this->isEditDeliveredOrder) { ?>
			<div class="form-row">
				<div class="form-group col">
					<div class="input-group">
						<div class="input-group-prepend input-group-text">
							<i class="far fa-address-book font-size-medium-small mx-2"></i>
						</div>
						<?php echo $this->form_payment['address_book_select_html']; ?>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_payment['shipping_firstname_html']; ?>
			</div>
			<div class="form-group col-md-6">
				<?php echo $this->form_payment['shipping_lastname_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_payment['shipping_address_line1_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_payment['shipping_address_line2_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_payment['shipping_city_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_payment['shipping_state_id_html']; ?>
				<?php echo $this->form_payment['shipping_state_html']; ?>
			</div>
			<div class="form-group col-md-6">
				<div class="input-group">
					<?php echo $this->form_payment['shipping_postal_code_html']; ?>
					<?php if ($this->cart_info['sessionObj']->isDelivered() &&  !$this->isEditDeliveredOrder) { ?>
						<div class="input-group-append">
							<a href="/?page=locations&amp;zip=<?php echo $this->cart_info['cart_info_array']['postal_code']; ?>" class="btn btn-primary box-change-zip">Change</a>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_payment['shipping_phone_number_html']; ?>
			</div>
			<?php if (!$this->cart_info['sessionObj']->isDelivered()) { ?>
				<div class="form-group col-md-6 <?php echo ((isset($this->hasNewShippingContactNumber) && $this->hasNewShippingContactNumber) ? "" : "collapse"); ?> shipping_phone_number_new_div">
					<?php echo $this->form_payment['shipping_phone_number_new_html']; ?>
				</div>
			<?php } ?>
			<?php if ($this->cart_info['sessionObj']->isDelivery()) { ?>
				<div class="form-group col">
					<span class="text-muted font-size-small">*Please provide the mobile number of the person who will be home during the delivery window. The delivery driver will contact them when they are on the way.</span>
				</div>
			<?php } ?>
			<?php if ($this->cart_info['sessionObj']->isDelivered()) { ?>
				<div class="form-group col-md-6">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text <?php echo $this->isEditDeliveredOrder ? 'bg-white' : ''; ?>">
								<?php echo $this->form_payment['shipping_is_gift_html']; ?>
							</div>
						</div>
						<?php echo $this->form_payment['shipping_gift_email_address_html']; ?>
					</div>
					<div class="text-muted font-size-small">Adding an email address here will send an email to the gift recipient.</div>
				</div>
			<?php } ?>
		</div>
		<?php if ($this->cart_info['sessionObj']->isDelivery()) { ?>
			<div class="form-row">
				<div class="form-group col-md-12">
					<h3 class="font-size-small text-uppercase font-weight-semi-bold">Delivery instructions</h3>
					<?php echo $this->form_payment['shipping_address_note_html']; ?>
				</div>
			</div>
		<?php } ?>
		<div class="form-row update_contact_row collapse">
			<div class="form-group col-md-12">
				<div class="custom-control custom-radio custom-control-inline">
					<input class="custom-control-input" id="address_book_update_update" name="address_book_update" value="update" type="radio" checked />
					<label class="custom-control-label cursor-pointer" for="address_book_update_update">Update contact?</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input class="custom-control-input" id="address_book_update_new" name="address_book_update" value="new" type="radio" />
					<label class="custom-control-label cursor-pointer" for="address_book_update_new">Create new contact?</label>
				</div>
			</div>
		</div>
	</div>
</div>