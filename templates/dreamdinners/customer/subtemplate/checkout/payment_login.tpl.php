<div class="row">
	<div class="col">
		<h2>Sign in</h2>
	</div>
</div>

<div class="row">
	<div class="col-10 col-md-12 col-xl-8 offset-1 offset-md-0 offset-xl-2">

		<form id="checkout_login_form" method="POST" action="">
			<input type="hidden" name="back" value="main.php?page=checkout" />
			<div class="row">
				<div class="col">
					<div class="form-group has-danger">
						<div class="input-group">
							<?php echo $this->form_login['primary_email_login_html']; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="form-group">
						<div class="input-group">
							<?php echo $this->form_login['password_login_html']; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="custom-control custom-control-inline custom-checkbox">
						<?php echo $this->form_login['remember_login_html']; ?>
					</div>
					<a class="btn btn-link float-right text-transform-inherit" data-toggle="collapse" href="#" data-target="#forgotPassDiv">Forgot Password?</a>
				</div>
			</div>
			<div class="row pt-1">
				<div class="col">
					<?php echo $this->form_login['submit_login_html']; ?>
				</div>
			</div>
		</form>

		<div id="forgotPassDiv" class="row my-4 collapse">
			<form method="POST">
				<div class="col-lg-12 text-center">
					<h2 class="meal-title">Forgot password</h2>
				</div>
				<div class="col">
					<div class="row">
						<div class="col">
							<p>Please enter your primary email address below and an email will be sent to you containing a link that will allow you to reset your password.</p>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="form-group">
								<div class="input-group">
									<input class="form-control" type="text" name="forgot_primary_email" placeholder="*Email" required="required" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<input class="btn btn-primary btn-block" type="submit" name="forgotPassword" value="Request Password Reset" />
						</div>
					</div>
				</div>
			</form>
		</div>

	</div>
</div>

<?php if ($this->isGiftCardOnlyOrder) { ?>
	<div class="row">
		<div class="col">
			<button class="btn btn-primary" onclick="run_as_guest();">Checkout as Guest</button>
		</div>
	</div>
<?php } ?>

<div class="row mt-5">
	<div class="col">
		<h2>Create account</h2>
	</div>
</div>

<form id="customer_create" name="customer_create" action="" method="post" class="needs-validation" novalidate>
	<input type="hidden" name="back" value="main.php?page=checkout" />
	<?php if (isset($this->form_account['hidden_html'])) echo $this->form_account['hidden_html'];?>

	<div class="row">
		<div class="col-xl-6 mb-4">
			<?php include $this->loadTemplate('customer/subtemplate/account/account_credentials.tpl.php'); ?>
		</div>
		<div class="col-xl-6 mb-4">
			<?php include $this->loadTemplate('customer/subtemplate/account/account_billing.tpl.php'); ?>
		</div>
		<div class="col-xl-12 mb-4">
			<?php include $this->loadTemplate('customer/subtemplate/account/account_contact_details.tpl.php'); ?>
		</div>
		<div class="col-xl-12 mb-4">
			<div class="form-row">
				<div class="col-12">
					<?php echo $this->form_account['referral_source_html']; ?>
				</div>
				<div class="col-12">
					<div id="referral_source_details_div" class="collapse">
						<?php echo $this->form_account['referral_source_details_html'];?>
					</div>
					<div id="virtual_party_source_details_div" class="collapse">
						<?php echo $this->form_account['virtual_party_source_details_html'];?>
					</div>
					<div id="customer_referral_email_div" class="collapse">
						<div class="input-group">
							<?php echo $this->form_account['customer_referral_email_html'];?>
							<div class="input-group-append">
								<div id="customer_referral_result" class="input-group-text">
									@
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>


		</div>
	</div>

	<div class="row mb-2">
		<div class="col text-center">
			<?php $this->tandc_page = 'account'; include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_agree.tpl.php'); ?>
		</div>
	</div>

	<div class="row">
		<div class="col text-center">
			<?php echo $this->form_account['submit_account_html']; ?>
			<div class="invalid-feedback form-feedback">Please complete the required information.</div>
		</div>
	</div>

</form>