<?php if ($this->user['preferences']['TEXT_MESSAGE_TARGET_NUMBER']['value'] == 'UNANSWERED')  { ?>
	<h4>Select a Number or Add a New One</h4>
<?php } else { ?>
	<h4>Update or Remove Mobile Number</h4>
<?php } ?>

<div class="row">
	<div class="col">
		<form id="add_mobile_number" class="needs-validation" novalidate>
			<?php if (!empty($this->user['telephone_1'])) { ?>
				<div class="form-row mb-2">
					<div class="col">
						<div class="custom-control custom-radio">
							<input class="custom-control-input" type="radio" id="add_method_primary" value="primary" name="add_method" required />
							<label class="custom-control-label" for="add_method_primary">Use Primary Number: <?php echo $this->user['telephone_1']; ?></label>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if (!empty($this->user['telephone_2'])) { ?>
				<div class="form-row mb-2">
					<div class="col">
						<div class="custom-control custom-radio">
							<input class="custom-control-input" type="radio" id="add_method_secondary" value="secondary" name="add_method" required />
							<label class="custom-control-label" for="add_method_secondary">Use Secondary Number:  <?php echo $this->user['telephone_2']; ?></label>
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
					<input type="tel" name="new mobile_number" id="new_mobile_number" class="form-control form-control-sm telephone" placeholder="*Mobile Telephone" data-telephone="true" maxlength="18" size="18" value="<?php echo ($this->user['preferences']['TEXT_MESSAGE_TARGET_NUMBER']['value'] != 'UNANSWERED') ? $this->user['preferences']['TEXT_MESSAGE_TARGET_NUMBER']['value'] : ''; ?>" />
				</div>
			</div>

			<?php if ($this->user['preferences']['TEXT_MESSAGE_TARGET_NUMBER']['value'] != 'UNANSWERED')  { ?>
				<div class="form-row" id="remove_mobile_number_div">
					<div class="col">
						<div  class="custom-control custom-radio">
							<input class="custom-control-input" type="radio" id="add_method_delete" value="delete" name="add_method" required />
							<label class="custom-control-label" for="add_method_delete">Remove Current Number: <?php echo $this->user['preferences']['TEXT_MESSAGE_TARGET_NUMBER']['value']; ?></label>
						</div>
					</div>
				</div>
			<?php } ?>

			<div class="row mt-4">
				<div class="col">
					<button id="confirm_number_update" name="confirm_number_update" class="btn btn-primary custom-control-inline btn-spinner" value="Confirm Mobile Number Update">Confirm</button>
					<button id="cancel_number_update" name="cancel_number_update" class="btn btn-secondary custom-control-inline toggle-update_mobile_number" value="Cancel Mobile Number Update">Cancel</button>
				</div>
			</div>

		</form>
	</div>
</div>