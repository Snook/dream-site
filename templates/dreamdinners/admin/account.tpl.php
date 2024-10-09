<?php $this->setScript('foot', SCRIPT_PATH . '/admin/account.min.js'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->setScriptVar('storeSupportsPlatePoints = ' . ($this->platePointsStatus['storeSupportsPlatePoints'] ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('userIsEnrolledInPlatePoints = ' . ($this->platePointsStatus['userIsEnrolled'] ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('userHasHomeStore = ' . ($this->platePointsStatus['userHasHomeStore'] ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('userAtHomeStore = ' . ($this->platePointsStatus['userAtHomeStore'] ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('isCreate = ' . (!empty($this->isCreate) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('isEMailLess = ' . (!empty($this->isEMailLess) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('isPartialAccount = ' . (!empty($this->isPartialAccount) ? 'true' : 'false') . ';'); ?>
<?php $this->assign('page_title', (!empty($this->isCreate)) ? 'Add New Guest' : 'Edit Guest Info'); ?>
<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1>Edit Guest</h1>
			</div>
		</div>

		<form name="customer_create" action="<?php echo HTTPS_SERVER.$_SERVER["REQUEST_URI"];?>" method="post" autocomplete="off" class="needs-validation" novalidate>

			<?php if (isset($this->form_account['store_html'])) { ?>
				<div class="form-row">
					<div class="form-group col-12">
						<label class="font-weight-bold" for="store">Home Store</label>
						<?php echo $this->form_account['store_html']; ?>
					</div>
				</div>
			<?php } ?>

			<div class="row">
				<div class="col-12 bg-gray text-center py-2 mb-3">
					<h2>Account Information</h2>
				</div>
			</div>

			<div class="form-row">
				<?php if ($this->isCreate || (isset($this->isEMailLess) && $this->isEMailLess) || $this->isPartialAccount) { ?>
					<div class="form-group col-md-6">
						<?php if ($this->isCreate) { ?>
							<?php
							CForm::formElement(array(
								CForm::type => CForm::CheckBox,
								CForm::name => 'generateLogin',
								CForm::label => 'No email address? Check this and a login and password will be automatically generated.'
							));
							?>
						<?php } else if (isset($this->isEMailLess) && $this->isEMailLess){ ?>
							<?php
							CForm::formElement(array(
								CForm::type => CForm::CheckBox,
								CForm::name => 'add_email',
								CForm::label => 'Check to add an email address to this account'
							));
							?>
						<?php } ?>
						<?php if (!empty($this->isPartialAccount)) { ?>
							<?php
							CForm::formElement(array(
								CForm::type => CForm::CheckBox,
								CForm::name => 'convertToFull',
								CForm::onClick => 'updateConversionState',
								CForm::label => 'Upgrade to Standard Account'
							), $_REQUEST['upgrade']);
							?>
						<?php } ?>
					</div>
				<?php } ?>

				<div class="form-group col-md-<?php echo ($this->isCreate || (isset($this->isEMailLess) && $this->isEMailLess) || $this->isPartialAccount) ? '6' : '12'; ?>">
					<?php
					if ($this->platePointsStatus['userIsEnrolled']) { // show read only view ?>
						Guest is enrolled in PlatePoints
					<?php } else if (!empty($this->isPartialAccount)) {?>
						Please change the guest account to a standard account prior to enrolling in PlatePoints.
					<?php } else if (!empty($this->user->membershipData) && $this->user->membershipData['enrolled']) {?>
						Guest is enrolled in Meal Prep+
					<?php } ?>
				</div>

				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="firstname">First name</label>
					<?php echo $this->form_account['firstname_html']; ?>
				</div>
				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="lastname">Last name</label>
					<?php echo $this->form_account['lastname_html']; ?>
				</div>
				<div class="form-group col-md-6 <?php echo (!empty($this->isEMailLess) ? 'collapse' : ''); ?>" id="email_main_row">
					<label class="font-weight-bold" for="primary_email">Email</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">@</span>
						</div>
						<?php echo $this->form_account['primary_email_html']; ?>
					</div>
					<div class="mt-1">
						<?php if ($this->isCreate) { ?>
							No email address? <label for="generateLogin" class="btn-link font-weight-bold">Click here</label>
						<?php } else if (!preg_match( '/^unsubscribe.([0-9]*)@example.com/', $this->user->primary_email)) { ?>
							Use <span class="font-weight-bold btn-link cursor-pointer email-unsubscribe" data-email="unsubscribe.<?php echo $this->user->id; ?>@example.com">unsubscribe.<?php echo $this->user->id; ?>@example.com</span> to invalidate this email address.
						<?php } ?>
					</div>
				</div>
				<div class="form-group col-md-6 <?php echo (!empty($this->isEMailLess) ? 'collapse' : ''); ?>" id="email_confirm_row">
					<label class="font-weight-bold" for="primary_email_confirm">Confirm Email</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">@</span>
						</div>
						<?php echo $this->form_account['confirm_email_address_html']; ?>
					</div>
				</div>
			</div>
			<?php if ($this->can['editSecondaryEmail']) { ?>
				<div class="form-row" id="secondary_email_row">
					<div class="form-group col-md-6 <?php echo (!empty($this->isEMailLess) ? 'collapse' : ''); ?>">
						<label class="font-weight-bold" for="secondary_email">Corporate Crate ID</label>
						<?php echo $this->form_account['secondary_email_html']; ?>
					</div>
				</div>
			<?php } ?>
			<div class="form-row" id="main_pw_row">
				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="password">Guest Password</label>
					<?php echo $this->form_account['password_html']; ?>
				</div>
				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="password_confirm">Confirm Password</label>
					<?php echo $this->form_account['password_confirm_html']; ?>
				</div>
			</div>

			<div class="row">
				<div class="col-12 bg-gray text-center py-2 mb-3">
					<h2>Contact Information</h2>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="telephone_1">Primary Telephone</label>
					<?php echo $this->form_account['telephone_1_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="telephone_1_type">Telephone Type</label>
					<div class="pt-2">
						<div class="custom-control custom-radio custom-control-inline">
							<?php echo $this->form_account['telephone_1_type_html']['MOBILE']; ?>
						</div>
						<div class="custom-control custom-radio custom-control-inline">
							<?php echo $this->form_account['telephone_1_type_html']['LAND_LINE']; ?>
						</div>
					</div>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="telephone_1_call_time">Best Time to Call</label>
					<?php echo $this->form_account['telephone_1_call_time_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="telephone_2">Secondary Telephone</label>
					<?php echo $this->form_account['telephone_2_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="telephone_2_type">Telephone Type</label>
					<div class="pt-2">
						<div class="custom-control custom-radio custom-control-inline">
							<?php echo $this->form_account['telephone_2_type_html']['MOBILE']; ?>
						</div>
						<div class="custom-control custom-radio custom-control-inline">
							<?php echo $this->form_account['telephone_2_type_html']['LAND_LINE']; ?>
						</div>
					</div>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="telephone_2_call_time">Best Time to Call</label>
					<?php echo $this->form_account['telephone_2_call_time_html']; ?>
				</div>
			</div>

			<div class="row">
				<div class="col-12 bg-gray text-center py-2 mb-3">
					<h2>Billing Address</h2>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="address_line1">Street Address</label>
					<?php echo $this->form_account['address_line1_html']; ?>
				</div>
				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="address_line2">Unit / Suite</label>
					<?php echo $this->form_account['address_line2_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="city">City</label>
					<?php echo $this->form_account['city_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="state_id">State</label>
					<?php echo $this->form_account['state_id_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="postal_code">Postal Code</label>
					<?php echo $this->form_account['postal_code_html']; ?>
				</div>
			</div>

			<div class="row">
				<div class="col-12 bg-gray text-center py-2 mb-3">
					<h2>Home Delivery Address</h2>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="shipping_address_line1">Street Address</label>
					<?php echo $this->form_account['shipping_address_line1_html']; ?>
				</div>
				<div class="form-group col-md-6">
					<label class="font-weight-bold" for="shipping_address_line2">Unit / Suite</label>
					<?php echo $this->form_account['shipping_address_line2_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="shipping_city">City</label>
					<?php echo $this->form_account['shipping_city_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="shipping_state_id">State</label>
					<?php echo $this->form_account['shipping_state_id_html']; ?>
				</div>
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="shipping_postal_code">Postal Code</label>
					<?php echo $this->form_account['shipping_postal_code_html']; ?>
				</div>
				<div class="form-group col-12">
					<label class="font-weight-bold" for="shipping_address_note">Address Note</label>
					<?php echo $this->form_account['shipping_address_note_html']; ?>
				</div>
			</div>

			<div class="row">
				<div class="col-12 bg-gray text-center py-2 mb-3">
					<h2>Personal Information</h2>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-4">
					<label class="font-weight-bold" for="gender">Gender</label>
					<?php echo $this->form_account['gender_html']; ?>
				</div>
				<?php if (isset($this->getUserData) && $this->getUserData) { ?>
					<div class="form-group col-md-4">
						<label class="font-weight-bold" for="birthday_month">Birth Month</label>
						<?php echo $this->form_account['birthday_month_html']; ?>
					</div>
					<div class="form-group col-md-4">
						<label class="font-weight-bold" for="birthday_year">Birth Year</label>
						<?php echo $this->form_account['birthday_year_html']; ?>
					</div>

				<?php } ?>
			</div>

			<?php if (empty($this->hasReferralSource)) { ?>

				<div class="form-row">
					<div class="form-group col-md-6">
						<?php echo $this->form_account['referral_source_html']; ?>
					</div>
					<div class="form-group col-md-6 collapse" id="referral_source_details_div">
						<?php echo $this->form_account['referral_source_details_html'];?>
					</div>
					<div class="form-group col-md-6 collapse" id="virtual_party_source_details_div">
						<?php echo $this->form_account['virtual_party_source_details_html'];?>
					</div>
					<div class="form-group col-md-6 collapse" id="customer_referral_email_div">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">@</span>
							</div>
							<?php echo $this->form_account['customer_referral_email_html'];?>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<?php echo $this->form_account['referral_source_html']; ?>
				<div id="referral_source_details_div" class="collapse"><?php echo $this->form_account['referral_source_details_html'];?> </div>
				<div id="virtual_party_source_details_div" class="collapse"><?php echo $this->form_account['virtual_party_source_details_html'];?></div>
				<div id="customer_referral_email_div" class="collapse"><?php echo $this->form_account['customer_referral_email_html'];?></div>
			<?php } ?>

			<div class="row my-4">
				<div class="col text-center">
					<?php echo $this->form_account['submit_account_html'];?>
				</div>
				<div class="invalid-feedback form-feedback text-center font-weight-bold font-size-small">Missing required information.</div>
			</div>

		</form>

	</div>


<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>