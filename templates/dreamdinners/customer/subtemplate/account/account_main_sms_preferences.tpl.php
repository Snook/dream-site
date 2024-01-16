
	<form id="add_mobile_number" class="needs-validation" novalidate>

		<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left mt-3">Text Subscriptions</h2>

		<?php include $this->loadTemplate('customer/subtemplate/account/account_sms_preferences.tpl.php'); ?>

		<div class="collapse text-dark bg-green-light p-3 mt-2"  data-sms_dlog_comp="true" >
			<div class="col">
				<button id="confirm_number_update" name="confirm_number_update" class="btn btn-primary custom-control-inline btn-spinner" value="Confirm Mobile Number Update">Confirm</button>
				<button id="cancel_number_update" name="cancel_number_update" class="btn btn-secondary custom-control-inline toggle-update_mobile_number" value="Cancel Mobile Number Update">Cancel</button>
			</div>
		</div>

	</form>