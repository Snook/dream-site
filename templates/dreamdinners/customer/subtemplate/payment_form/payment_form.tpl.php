
<form method="post" class="needs-validation" novalidate>

	<?php if (isset($this->form_payment['hidden_html'])) echo $this->form_payment['hidden_html'];?>

	<?php include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_credit_card.tpl.php'); ?>


	<?php if (isset($this->allowGuest) && $this->allowGuest) {
		include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_billing_email.tpl.php'); ?>
		<input type="hidden" name="run_as_guest" value="true" />
	<?php  } ?>


	<div class="row mb-2">
		<div class="col text-center">
			<?php $this->tandc_page = 'checkout'; include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_agree.tpl.php'); ?>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<div class="form-row">
				<div class="form-group col-md-12 text-center">
					<?php echo $this->form_payment['complete_order_html']; ?>
					<div class="invalid-feedback form-feedback">Please complete the required information.</div>
				</div>
			</div>
		</div>
	</div>

</form>

