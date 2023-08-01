<?php $this->assign('canonical_url', HTTPS_BASE . 'main.php?page=account_signup'); ?>
<?php $this->setScript('foot', '//maps.googleapis.com/maps/api/js?v=3&amp;key=' . GOOGLE_APIKEY); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/locations.min.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/account.min.js'); ?>
<?php $this->setScriptVar('is_create = ' . ($this->isCreate ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('sms_special_case = "' . $this->sms_special_case . '";'); ?>
<?php $this->setScriptVar('scroll = "' . $this->scroll . '";'); ?>

<?php $this->assign('page_title', 'Account');?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<?php if (!CUser::isLoggedIn()) { ?>
					<a class="btn btn-primary" href="/main.php?page=login">Already a guest? Sign in</a>
				<?php } ?>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Account</h1>
			</div>
		</div>
	</header>

	<main>
		<div class="container-fluid">

			<?php if(!empty($this->form_account['id']) && !$this->form_account['primary_email'] ) { ?>
				<div class="alert alert-primary" role="alert">
					This account has been created at a Dream Dinners store without an email address. To use our website, you will need to have a valid email address.
					Please update your account details to continue using the site, your email address will then be used as your account log in.
				</div>
			<?php } ?>

			<form id="customer_create" name="customer_create" action="<?php echo HTTPS_BASE . $_SERVER["REQUEST_URI"];?>" method="post" class="needs-validation" novalidate>
				<?php if (isset($this->form_account['hidden_html'])) echo $this->form_account['hidden_html'];?>

				<div class="row">
					<div class="col-md-4 offset-md-4 text-center">
						<?php include $this->loadTemplate('customer/subtemplate/account/account_credentials.tpl.php'); ?>

						<div class="form-row">
							<div class="form-group col-md-6">
								<?php echo $this->form_account['telephone_1_html']; ?>
							</div>
							<div class="form-group col-md-6">
								<?php echo $this->form_account['telephone_1_call_time_html']; ?>
							</div>
						</div>
						<div class="form-row text-center text-md-left pl-2 mb-4">
							<div class="col">
								<div class="custom-control-inline">
									<?php echo $this->form_account['telephone_1_type_html']['MOBILE']; ?>
								</div>
								<div class="custom-control-inline">
									<?php echo $this->form_account['telephone_1_type_html']['LAND_LINE']; ?>
								</div>
							</div>
						</div>

						<?php include $this->loadTemplate('customer/subtemplate/account/account_demographics_simple.tpl.php'); ?>

					</div>
				</div>
				<div class="row mt-4 mb-2">
					<div class="col text-center">
						<?php $this->tandc_page = 'account'; include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_agree.tpl.php'); ?>
					</div>
				</div>

				<div class="row mb-5">
					<div class="col-md-4 offset-md-4 text-center">
						<?php echo $this->form_account['submit_account_html']; ?>
						<div class="invalid-feedback form-feedback">Please complete the required information.</div>
					</div>
				</div>

			</form>

		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>