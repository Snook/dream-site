<div class="form-row">
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="menu">Menu</label>
		<?php echo $this->form_create_session['menu_html']; ?>
	</div>
	<div class="form-group col-md-6">
		<label class="font-weight-bold">Location</label>
		<input type="text" class="form-control" value="<?php echo $this->store->store_name; ?>" disabled>
	</div>
	<div class="form-group col-md-4">
		<label class="font-weight-bold" for="session_date">Session Date</label>
		<?php echo $this->form_create_session['session_date_html'];?>
	</div>
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="custom_close_interval">Session Close</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">
					<?php echo $this->form_create_session['close_interval_type_html'][CSession::HOURS]; ?>
				</div>
			</div>
			<?php echo $this->form_create_session['custom_close_interval_html']; ?>
			<div class="input-group-append">
				<div class="input-group-text">
					<?php echo $this->form_create_session['close_interval_type_html'][CSession::ONE_FULL_DAY]; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="form-row">
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="session_type">Session Type</label>
		<?php echo $this->form_create_session['session_type_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="delivered_supports_delivery">Delivery Blackout</label>
		<?php echo $this->form_create_session['delivered_supports_delivery_html']; ?>
	</div>
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="delivered_supports_shipping">Pickup Blackout</label>
		<?php echo $this->form_create_session['delivered_supports_shipping_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-3">
		<label class="font-weight-bold" for="available_slots">Maximum Order Capacity</label>
		<?php echo $this->form_create_session['available_slots_html']?>
	</div>
	<div class="form-group col-md-3 collapse show" id="discount_row">
		<label class="font-weight-bold" for="enable_discount">Session Discount</label>
		<div class="input-group">
			<?php echo $this->form_create_session['discount_value_html']?>
			<div class="input-group-append">
				<div class="input-group-text">
					0-10%
				</div>
			</div>
		</div>
	</div>
	<?php if ($this->allow_assembly_fee) { ?>
	<div class="form-group col-md-3 collapse" id="session_assembly_fee_row">
		<label class="font-weight-bold" for="session_assembly_fee">Override Assembly Fee</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">
					<?php echo $this->form_create_session['session_assembly_fee_enable_html']?>
				</div>
			</div>
			<?php echo $this->form_create_session['session_assembly_fee_html']?>
		</div>
	</div>
	<?php } ?>
</div>

<div class="form-row collapse show" id="session_title_row">
	<div class="form-group col">
		<label class="font-weight-bold" for="session_title">Additional Info</label>
		<?php echo $this->form_create_session['session_title_html']?>
		<div id="session_title_warn" class="text-danger font-weight-bold mt-2"></div>
	</div>
</div>

<div class="form-row collapse" id="session_details_row">
	<div class="form-group col">
		<label class="font-weight-bold" for="session_details">Session Details</label>
		<?php echo $this->form_create_session['session_details_html']?>
		<div id="session_details_warn" class="text-danger font-weight-bold mt-2"></div>
	</div>
</div>

<div class="form-row">
	<div class="form-group col">
		<label class="font-weight-bold" for="admin_notes">Administrative Notes</label>
		<?php echo $this->form_create_session['admin_notes_html']?>
	</div>
</div>
