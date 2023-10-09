<div class="form-row">
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="menu">Menu</label>
		<?php echo $this->form_create_session['menu_html']; ?>
	</div>
	<div class="form-group col-md-6">
		<label class="font-weight-bold">Location</label>
		<input type="text" class="form-control" value="<?php echo $this->CurrentBackOfficeStore->store_name; ?>" disabled>
	</div>
	<div class="form-group col-md-3">
		<label class="font-weight-bold" for="session_date">Session Date</label>
		<?php echo $this->form_create_session['session_date_html'];?>
	</div>
	<div class="form-group col-md-3">
		<label class="font-weight-bold" for="session_time">Session Start Time</label>
		<?php echo $this->form_create_session['session_time_html']?>
	</div>
	<div class="form-group col-md-3">
		<label class="font-weight-bold" for="session_time">Session End Time</label>
		<?php echo $this->form_create_session['session_end_time_html']?>
	</div>
	<div class="form-group col-md-3">
		<label class="font-weight-bold" for="duration_minutes">Session Duration</label>
		<div class="input-group">
			<?php echo $this->form_create_session['duration_minutes_html']?>
			<div class="input-group-append">
				<div class="input-group-text">
					Minutes
				</div>
			</div>
		</div>
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
	<?php if ($this->allowsMealCustomization) { ?>
	<div class="form-group col-md-6" id="meal_customization_close_row">
		<label class="font-weight-bold" for="custom_close_interval">Close For Customization</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">
					<?php echo $this->form_create_session['meal_customization_close_interval_type_html'][CSession::HOURS]; ?>
				</div>
			</div>
			<?php echo $this->form_create_session['meal_customization_close_interval_html']; ?>
			<div class="input-group-append">
				<div class="input-group-text">
					<?php echo $this->form_create_session['meal_customization_close_interval_type_html'][CSession::FOUR_FULL_DAYS]; ?>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="session_lead">Session Lead</label>
		<?php echo $this->form_create_session['session_lead_html']?>
	</div>
</div>

<div class="form-row">
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="session_type">Session Type</label>
		<?php echo $this->form_create_session['session_type_html']; ?>
		<?php if (!$this->CurrentBackOfficeStore->dream_taste_opt_out && !$this->hasDreamtTasteType) { ?>
			<div class="font-italic text-muted font-size-small">*Note: Meal Prep Workshop/Fundraiser sessions can only be created after menu items for the month are made available</div>
		<?php } ?>
	</div>
	<div class="form-group col-md-6 collapse" id="session_type_subtype_row">
		<label class="font-weight-bold" for="session_type_subtype">Session Theme</label>
		<?php echo $this->form_create_session['session_type_subtype_html']?>
	</div>
	<div class="form-group col-md-6 collapse show" id="standard_session_type_subtype_row">
		<label class="font-weight-bold" for="standard_session_type_subtype">Session Theme</label>
		<?php echo $this->form_create_session['standard_session_type_subtype_html']?>
	</div>
	<div class="form-group col-md-6 collapse" id="dream_taste_theme_selection">
		<label class="font-weight-bold" for="dream_taste_theme">Session Theme</label>
		<?php echo $this->form_create_session['dream_taste_theme_html']?>
	</div>
	<?php if (!empty($this->form_create_session['fundraiser_theme_html'])) { ?>
		<div class="form-group col-md-6 collapse" id="fundraiser_theme_selection">
			<label class="font-weight-bold" for="fundraiser_theme">Session Theme</label>
			<?php echo $this->form_create_session['fundraiser_theme_html']?>
		</div>
	<?php } ?>
	<div class="form-group col-md-6 collapse" id="fundraiser_recipient_selection">
		<label class="font-weight-bold" for="fundraiser_recipient">Fundraiser</label>
		<?php echo $this->form_create_session['fundraiser_recipient_html']?>
	</div>
	<div class="form-group col-md-6 collapse" id="session_pickup_location_row">
		<label class="font-weight-bold" for="session_pickup_location">Pick Up Location</label>
		<?php echo $this->form_create_session['session_pickup_location_html']?>
	</div>
</div>

<div class="form-row">
	<div class="form-group col-md-3">
		<label class="font-weight-bold" for="available_slots">Maximum Order Capacity</label>
		<?php echo $this->form_create_session['available_slots_html']?>
	</div>
	<?php if ($this->CurrentBackOfficeStore->storeSupportsIntroOrders($this->Menu->id)) { ?>
		<div class="form-group col-md-3 collapse show" id="intro_slots_row">
			<label class="font-weight-bold" for="introductory_slots">Starter Pack Order Capacity</label>
			<?php echo $this->form_create_session['introductory_slots_html']?>
		</div>
	<?php } ?>
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
	<div class="form-group col-md-3 collapse" id="session_delivery_fee_row">
		<label class="font-weight-bold" for="session_delivery_fee">Override Delivery Fee</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">
					<?php echo $this->form_create_session['session_delivery_fee_enable_html']?>
				</div>
			</div>
			<?php echo $this->form_create_session['session_delivery_fee_html']?>
		</div>
	</div>
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

<div class="form-row">
	<div class="form-group col-md-6 collapse show" id="private_session_row">
		<label class="font-weight-bold" for="session_password">Private Session</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">
					Invite Code
				</div>
			</div>
			<?php echo $this->form_create_session['session_password_html']?>
		</div>
	</div>
	<div class="form-group col-md-6 collapse" id="privatePartyHost">
		<label class="font-weight-bold" for="session_details">Host</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<button type="button" data-guestsearch="add_hostess" data-select_button_title="Add Host" data-all_stores_checked="false" data-select_function="addHostess" class="btn btn-primary"><i class="far fa-address-book font-size-medium-small mx-2"></i></button>
			</div>
			<?php echo $this->form_create_session['session_host_html']?>
			<div class="input-group-append">
				<div class="input-group-text">
					<?php echo $this->form_create_session['do_send_pp_notification_html']?>
				</div>
			</div>
		</div>
	</div>
</div>