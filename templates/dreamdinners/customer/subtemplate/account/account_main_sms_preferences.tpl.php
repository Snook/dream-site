
	<form id="add_mobile_number" class="needs-validation" novalidate>

		<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left mt-3">Text Subscriptions</h2>

		<div class="row mb-3">

			<?php if ($this->user->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] == 'UNANSWERED') { ?>

				<div class="col-12">
					<button id="add_update_mobile_number" class="btn btn-primary toggle-update_mobile_number" value="Add Mobile Number" data-op="add">Add Mobile Number</button>
				</div>
				<div class="col-12 pt-2">
					<span id="current_mobile_target"></span>
				</div>

			<?php } else if (isset($this->sms_special_case) && $this->sms_special_case == 'pending_second_step') { ?>

				<div class="col-12">
					<button  id="add_update_mobile_number" class="btn btn-primary  toggle-update_mobile_number" value="Update Mobile Number">Update or Remove Mobile Number</button>
				</div>
				<div class="col-12 pt-2">
					<span id="current_mobile_target">Current Mobile number: <?php echo CTemplate::telephoneFormat($this->user->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] );?></span>
				</div>
				<div  class="col-12 font-size-small p-2">Your account is currently pending. Please reset your account by clicking the button above. This will send you an opt-out message. You can then opt-in by providing your mobile number and selecting your preferences.</div>

			<?php } else { ?>
				<div class="col-12">
					<button  id="add_update_mobile_number" class="btn btn-primary toggle-update_mobile_number" value="Update Mobile Number">Update or Remove Mobile Number</button>
				</div>
				<div class="col-12 pt-2">
					<span id="current_mobile_target">Current Mobile number: <?php echo CTemplate::telephoneFormat($this->user->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] );?></span>
				</div>
			<?php } ?>

		</div>

		<div id="add_or_change_SMS_number_div" data-sms_dlog_comp="true" class="collapse text-dark bg-green-light p-3 mb-2">
			<?php if ($this->user->preferences['TEXT_MESSAGE_TARGET_NUMBER']['value'] == 'UNANSWERED')  { ?>
				<h4>Select a Number or Add a New One</h4>
			<?php } else { ?>
				<h4>Update or Remove Mobile Number</h4>
			<?php } ?>

			<div class="row">
				<div class="col">
					<?php if (!empty($this->user->telephone_1)) { ?>
						<div class="form-row mb-2">
							<div class="col">
								<div class="custom-control custom-radio">
									<input class="custom-control-input" type="radio" id="add_method_primary" value="primary" name="add_method" required />
									<label class="custom-control-label" for="add_method_primary">Use Primary Number: <?php echo $this->user->telephone_1; ?></label>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php if (!empty($this->user->telephone_2)) { ?>
						<div class="form-row mb-2">
							<div class="col">
								<div class="custom-control custom-radio">
									<input class="custom-control-input" type="radio" id="add_method_secondary" value="secondary" name="add_method" required />
									<label class="custom-control-label" for="add_method_secondary">Use Secondary Number:  <?php echo $this->user->telephone_2; ?></label>
								</div>
							</div>
						</div>
					<?php } ?>

					<div class="form-inline mb-2 mb-lg-0">
						<div class="custom-control custom-radio mb-lg-2">
							<input class="custom-control-input" type="radio" id="add_method_new" value="new" name="add_method" required />
							<label class="custom-control-label" for="add_method_new">Provide New Number</label>
						</div>
						<div id="new_mobile_number_div" class="ml-4 ml-lg-2 collapse">
							<input type="tel" name="new mobile_number" id="new_mobile_number" class="form-control form-control-sm telephone" placeholder="*Mobile Telephone" data-telephone="true" maxlength="18" size="18" value="<?php echo ($this->user->preferences['TEXT_MESSAGE_TARGET_NUMBER']['value'] != 'UNANSWERED') ? $this->user->preferences['TEXT_MESSAGE_TARGET_NUMBER']['value'] : ''; ?>" />
						</div>
					</div>

					<div class="form-row <?php echo ($this->user->preferences['TEXT_MESSAGE_TARGET_NUMBER']['value'] == 'UNANSWERED') ? 'collapse' : ''; ?>" id="remove_mobile_number_div">
						<div class="col">
							<div  class="custom-control custom-radio">
								<input class="custom-control-input" type="radio" id="add_method_delete" value="delete" name="add_method" required />
								<label class="custom-control-label" for="add_method_delete">Remove Current Number: <?php echo $this->user->preferences['TEXT_MESSAGE_TARGET_NUMBER']['value']; ?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php include $this->loadTemplate('customer/subtemplate/account/account_sms_preferences.tpl.php'); ?>

		<div class="collapse text-dark bg-green-light p-3 mt-2"  data-sms_dlog_comp="true" >
			<div class="col">
				<button id="confirm_number_update" name="confirm_number_update" class="btn btn-primary custom-control-inline btn-spinner" value="Confirm Mobile Number Update">Confirm</button>
				<button id="cancel_number_update" name="cancel_number_update" class="btn btn-secondary custom-control-inline toggle-update_mobile_number" value="Cancel Mobile Number Update">Cancel</button>
			</div>
		</div>

		<p class="font-italic font-size-small mt-3">*Up to 12 Msgs/Month. To opt out simply turn off a preference or Reply <span class="font-weight-bold">STOP</span> to cancel, <span class="font-weight-bold">HELP</span> for help to 73328. Msg &amp; data rates may apply. <a href="/?static=terms">View SMS Terms</a> | <a href="/?static=privacy">View SMS Privacy Policy</a></p>

	</form>