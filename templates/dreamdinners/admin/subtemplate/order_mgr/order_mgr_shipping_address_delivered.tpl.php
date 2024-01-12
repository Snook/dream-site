<div class="row mb-4">
	<div class="col-md-12">
		<div class="form-row">
			<div class="form-group col">
				<div class="input-group">
					<div class="input-group-prepend input-group-text">
						<i class="far fa-address-book font-size-medium-small mx-2"></i>
					</div>
					<?php echo $this->form_direct_order['address_book_select_html']; ?>
				</div>
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-md-q">
			<?php echo $this->form_direct_order['shipping_postal_code_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_firstname_html']; ?>
			</div>
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_lastname_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_direct_order['shipping_address_line1_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_direct_order['shipping_address_line2_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_direct_order['shipping_city_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_state_id_html']; ?>
			</div>
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_phone_number_html']; ?>
			</div>
			<div class="form-group col-md-6 collapse shipping_phone_number_new_div">
				<?php echo $this->form_direct_order['shipping_phone_number_new_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-3 shipping_is_gift_div">
				<?php echo $this->form_direct_order['shipping_is_gift_html']; ?>
			</div>

			<div class="form-group col-md-6 collapse shipping_email_address_div">
				<?php echo $this->form_direct_order['shipping_email_address_html']; ?>
				(adding an email address here will send an email to the gift recipient)
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<span><input type="button" class="btn btn-primary btn-sm" onclick="setShipToAddressAndSaveOrder(this); return false;" value="Set As Ship To Address" /></span>
				<?php if ($this->orderState  == "NEW") { ?>
					<span>Please provide at least the Postal Code and set as ship to address to Save this order.</span>
				<?php } else if ($this->orderState  == "SAVED") { ?>
					<span>You must provide at the the Postal Code. If you change the Postal Code the current session may change to meet the requirements of the Distribution Center for that location. The full Address will be required to activate this saved order.</span>
				<?php } else if ($this->orderState  == "ACTIVE") { ?>
					<span>This order is active. The Postal code can no longer be edited.</span>
				<?php } ?>
			</div>
		</div>
	</div>
</div>