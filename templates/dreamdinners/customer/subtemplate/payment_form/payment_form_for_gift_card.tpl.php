<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<form id="gift_card_checkout" method="post" action="main.php?page=checkout_gift_card" class="needs-validation" novalidate>
	<?php if (isset($this->form_payment['hidden_html'])) echo $this->form_payment['hidden_html'];?>
	<input type="hidden" name="run_as_guest" value="true" />

	<?php include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_credit_card.tpl.php'); ?>

	<?php include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_billing_email.tpl.php'); ?>

	<div class="row mt-5">
		<div class="col text-center">
			<button id="checkout_submit" name="checkout_submit" class="btn btn-primary g-recaptcha"
                    data-sitekey="<?php echo GOOGLE_CAPTCHA_SITE_KEY?>" data-callback='submit_gift_card_purchase'>Submit Order</button>
		</div>
	</div>
</form>