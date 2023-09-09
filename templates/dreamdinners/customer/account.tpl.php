<?php $this->assign('canonical_url', HTTPS_BASE . 'account'); ?>
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
					<a class="btn btn-primary" href="/login">Already a guest? Sign in</a>
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
					<div class="col-md-6 col-xl-4">
						<?php include $this->loadTemplate('customer/subtemplate/account/account_credentials.tpl.php'); ?>
					</div>
					<div class="col-md-6 col-xl-4">
						<?php include $this->loadTemplate('customer/subtemplate/account/account_billing.tpl.php'); ?>
					</div>
					<?php if (CUser::isLoggedIn()) { ?>
						<div class="col-md-6 col-xl-4">
							<?php include $this->loadTemplate('customer/subtemplate/account/account_shipping.tpl.php'); ?>
						</div>
					<?php } ?>
					<div class="col-md-6 col-xl-4">
						<?php include $this->loadTemplate('customer/subtemplate/account/account_contact_details.tpl.php'); ?>
					</div>

					<?php if (isset($this->isCreate) && $this->isCreate && !(isset($this->hide_store_selector) && $this->hide_store_selector)) { ?>
						<div class="col-md-6 col-xl-4">

							<div class="row">
								<div class="col-md-9 col-9">
									<h2 class="text-green-dark text-uppercase font-weight-bold text-left font-size-medium-small mb-4">Your Local Stores</h2>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<?php if (empty($this->home_store_read_only)) { ?>
										<span id="store_search_results">To find your local stores, please enter <a href="#postal_code">postal code</a>.</span>
									<?php } else { ?>
										<?php echo $this->storeName ?>
									<?php } ?>
								</div>
							</div>
						</div>
					<?PHP } ?>

					<div class="col-md-6 col-xl-4">
						<?php include $this->loadTemplate('customer/subtemplate/account/account_demographics.tpl.php'); ?>
					</div>

					<div class="col-md-6 col-xl-4">
						<?php if(!$this->isPreferred ) { ?>
							<?php include $this->loadTemplate('customer/subtemplate/account/account_platepoints.tpl.php'); ?>
						<?php } ?>
					</div>

				</div>

				<?php if(!empty($this->isCreate) && $this->isCreate ) { ?>
					<div class="row mt-4 mb-2">
						<div class="col text-center">
							<?php $this->tandc_page = 'account'; include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_agree.tpl.php'); ?>
						</div>
					</div>
				<?php } ?>

				<div class="row mb-5">
					<div class="col-md-4 offset-md-4 text-center">
						<?php echo $this->form_account['submit_account_html']; ?>
						<div class="invalid-feedback form-feedback">Please complete the required information.</div>
					</div>
				</div>

			</form>

			<?php if (!isset($this->isCreate) || !$this->isCreate) { ?>
				<div class="row mb-4">
					<div class="col text-center">
						<h2>Preferences</h2>
					</div>
				</div>

				<div class="row">

					<?php if (false) { // disable roundup ?>
						<div class="form-group bg-orange col-md-6 col-xl-4 p-3">
							<div class="row">
								<div class="col-12">
									<h2 class="font-weight-bold font-size-medium-small text-uppercase">Fight Against Hunger</h2>
								</div>
								<div class="col-12">
									<p class="font-size-small">Automatically donate to the Dream Dinners Foundation every session by rounding up your order*. You can feed a child for only $20 each month. Please select one of the pre-set values to add to your order each month. You may adjust this donation amount at any time.</p>
									<div class="row mb-2">
										<div class="col">
											<select class="form-control custom-select" data-user_pref="ltd_auto_round_up">
												<option value="0">Select a Round Up Value</option>
												<option value="1" <?php if (!empty($this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value'] == '1') { ?>selected="selected"<?php } ?>>
													Nearest Dollar
												</option>
												<option value="2" <?php if (!empty($this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value'] == '2') { ?>selected="selected"<?php } ?>>
													2 Dollars
												</option>
												<option value="5" <?php if (!empty($this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value'] == '5') { ?>selected="selected"<?php } ?>>
													5 Dollars
												</option>
												<option value="10" <?php if (!empty($this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value'] == '10') { ?>selected="selected"<?php } ?>>
													10 Dollars
												</option>
												<option value="35" <?php if (!empty($this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value'] == '35') { ?>selected="selected"<?php } ?>>
													35 Dollars
												</option>
												<option value="54" <?php if (!empty($this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user->preferences[CUser::LTD_AUTO_ROUND_UP]['value'] == '54') { ?>selected="selected"<?php } ?>>
													54 Dollars
												</option>
											</select>
										</div>
									</div>
									<p class="font-size-small font-italic">*Only at participating locations</p>
								</div>
							</div>
						</div>
					<?php } ?>


					<?php if (defined('ENABLE_EMAIL_PREFERENCE') && ENABLE_EMAIL_PREFERENCE == true) { ?>
						<div class="form-group col-md-6 col-xl-4 bg-green-light p-3">

							<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left mt-3">Email Subscriptions</h2>
							<p class="font-size-small">Subscribe to Dream Dinners Email communications.</p>

							<div class="ml-4">
								<div class="custom-control custom-switch">
									<input class="custom-control-input"  id="email_reminder_session" data-user_pref="email_reminder_session" data-user_pref_value_check="PENDING_OPT_IN" data-user_pref_value_uncheck="PENDING_OPT_OUT" type="checkbox" <?php if ($this->user->preferences[CUser::EMAIL_REMINDER_SESSION]['value'] == 'OPTED_IN' || $this->user->preferences[CUser::EMAIL_REMINDER_SESSION]['value'] == 'PENDING_OPT_IN') {?>checked="checked"<?php } ?> />
									<label class="custom-control-label" for="email_reminder_session"><span class="font-weight-bold">Reminders:</span></label> <span class="font-size-small">Sent 3 days before your store session or tracking updates for Delivered orders.</span>
								</div>
							</div>

							<div class="ml-4">
								<div class="custom-control custom-switch">
									<input class="custom-control-input"  id="email_plate_points" data-user_pref="email_plate_points" data-user_pref_value_check="PENDING_OPT_IN" data-user_pref_value_uncheck="PENDING_OPT_OUT" type="checkbox" <?php if ($this->user->preferences[CUser::EMAIL_PLATE_POINTS]['value'] == 'OPTED_IN' || $this->user->preferences[CUser::EMAIL_PLATE_POINTS]['value'] == 'PENDING_OPT_IN') {?>checked="checked"<?php } ?> />
									<label class="custom-control-label" for="email_plate_points"><span class="font-weight-bold">PLATEPOINTS:</span></label> <span class="font-size-small">Loyalty program notifications, announcements and reminders</span>
								</div>
							</div>

							<div class="ml-4">
								<div class="custom-control custom-switch">
									<input class="custom-control-input"  id="email_offers_and_promos" data-user_pref="email_offers_and_promos" data-user_pref_value_check="PENDING_OPT_IN" data-user_pref_value_uncheck="PENDING_OPT_OUT" type="checkbox" <?php if ($this->user->preferences[CUser::EMAIL_OFFERS_AND_PROMOS]['value'] == 'OPTED_IN' || $this->user->preferences[CUser::EMAIL_OFFERS_AND_PROMOS]['value'] == 'PENDING_OPT_IN') {?>checked="checked"<?php } ?> />
									<label class="custom-control-label" for="email_offers_and_promos"><span class="font-weight-bold">Offers and Promotions:</span></label> <span class="font-size-small">Including contests, coupons and events</span>
								</div>
							</div>

							<div class="ml-4">
								<div class="custom-control custom-switch">
									<input class="custom-control-input"  id="email_surveys" data-user_pref="email_surveys" data-user_pref_value_check="PENDING_OPT_IN" data-user_pref_value_uncheck="PENDING_OPT_OUT" type="checkbox" <?php if ($this->user->preferences[CUser::EMAIL_SURVEYS]['value'] == 'OPTED_IN' || $this->user->preferences[CUser::EMAIL_SURVEYS]['value'] == 'PENDING_OPT_IN') {?>checked="checked"<?php } ?> />
									<label class="custom-control-label" for="email_surveys"><span class="font-weight-bold">Surveys:</span></label> <span class="font-size-small">Your opportunity to give us your feedback about your experience, meals and more.</span>
								</div>
							</div>

						</div>
					<?php } ?>


					<?php if (defined('ENABLE_SMS_PREFERENCE') && ENABLE_SMS_PREFERENCE == true) { ?>
						<div class="form-group col-md-6 col-xl-4 bg-green-dark text-white p-3">
							<?php include $this->loadTemplate('customer/subtemplate/account/account_main_sms_preferences.tpl.php'); ?>
						</div>
					<?php } ?>

					<div class="form-group col-md-6 col-xl-4 bg-green-light">
						<div class="row">
							<div class="col p-4">
								<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left">Terms &amp; conditions</h2>

								<div class="ml-4">
									<div class="custom-control custom-switch">
										<input class="custom-control-input"  id="tc_delayed_payment_agree" data-user_pref="tc_delayed_payment_agree" type="checkbox" <?php if (!empty($this->user->preferences[CUser::TC_DELAYED_PAYMENT_AGREE]['value'])) {?>checked="checked"<?php } ?> />
										<label class="custom-control-label" for="tc_delayed_payment_agree"><span class="font-weight-bold">Delayed payments at your local assembly store: </span></label>
										<span class="font-size-small">When I opt to use the Delayed Payment service to pay for my order, I understand that the balance due for this and future orders will be automatically withdrawn (5) five days prior to my store order date and will transact using the same credit card used to pay for the order deposit. I also understand that delayed payment may not be available for small orders.</span>
									</div>
								</div>

							</div>
						</div>
					</div>

					<div class="form-group col-md-6 col-xl-4 bg-green-dark text-white p-3">

						<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left">Print Outs for your Store Orders						</h2>
						<p class="font-size-small">Please select from the list below, what items you would like to have printed out for you at your session.</p>

						<div class="ml-4">
							<div class="custom-control custom-switch">
								<input class="custom-control-input" id="session_print_freezer_sheet" data-user_pref="session_print_freezer_sheet" type="checkbox" <?php if (!empty($this->user->preferences[CUser::SESSION_PRINT_FREEZER_SHEET]['value'])) {?>checked="checked"<?php } ?> />
								<label class="custom-control-label" for="session_print_freezer_sheet">Freezer sheet</label>
							</div>

							<div class="custom-control custom-switch">
								<input class="custom-control-input"  id="session_print_nutritionals" data-user_pref="session_print_nutritionals" type="checkbox" <?php if (!empty($this->user->preferences[CUser::SESSION_PRINT_NUTRITIONALS]['value'])) {?>checked="checked"<?php } ?> />
								<label class="custom-control-label" for="session_print_nutritionals">Nutritionals</label>
							</div>
						</div>

						<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left mt-3">Account note</h2>

						<p class="font-size-small">Please tell us if there is something we should always know about you for local assembly kitchen orders.</p>

						<textarea id="user_account_note" data-user_pref="user_account_note" class="form-control"><?php if (!empty($this->user->preferences[CUser::USER_ACCOUNT_NOTE]['value'])) { echo htmlentities($this->user->preferences[CUser::USER_ACCOUNT_NOTE]['value']); } ?></textarea>

					</div>

					<?php if ($this->user->homeStoreAllowsMealCustomization()) { ?>
						<div class="form-group col-md-6 col-xl-4 bg-green-light p-3" id="recipe_customization_row">

							<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left mt-3">Meal Customizations</h2>
							<p class="font-size-small"><?php echo OrdersCustomization::RECIPE_DESCRIPTION;?></p>

							<?php foreach($this->user->getMealCustomizationPreferences() as $key => $pref) { ?>
								<div class="ml-4">
									<?php switch ($pref->type ) {
										case 'INPUT': ?>
										<div class="">
											<br>
											<label class="control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description ?>:</span></label>
											<input id="<?php echo $key ?>" type="text" style="width: 300px;display: inline;" data-user_pref="<?php echo $key ?>" size="20" maxlength="15" class="form-control" value="<?php if (!empty($pref->value)) { echo htmlentities($pref->value); } ?>"></input>
										</div>
										<?php break; case 'CHECKBOX': ?>
										<div class="custom-control custom-switch">
											<input class="custom-control-input"  id="<?php echo $key ?>" data-user_pref="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
											<label class="custom-control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description ?></span></label> <span class="font-size-small"><?php if(!empty($pref->information)){ ?><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?>"></span><?php } ?>
										</div>
										<?php break; case 'SPECIAL_REQUEST': ?>
											<?php if (!empty($pref->details)) { ?>
													<div class="custom-control custom-switch">
														<input class="custom-control-input"  id="<?php echo $key ?>" data-user_pref="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
														<label class="custom-control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description ?></span></label> <span class="font-size-small"><?php if(!empty($pref->information)){ ?><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?>"></span><?php } ?>
														<br><span class="font-italic font-size-small font-weight-bold">( <?php echo htmlentities($pref->details);?> )</span>
													</div>
											<?php } ?>
										<?php break; } ?>
								</div>
							<?php } ?>

							<p class="font-size-small font-italic mt-3">*<?php echo OrdersCustomization::RECIPE_LEGAL; ?></p>

						</div>
					<?php } ?>

					<?php if (!empty($this->card_references)) { ?>
						<div class="form-group col-md-6 col-xl-4 bg-green-dark p-3">
							<div class="col">
								<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left">Your Saved Credit Cards</h2>
								<?php
								$numberCCRefs = count($this->card_references);
								foreach($this->card_references as $thisRef) {
									if ($thisRef['stale']) { ?>
										<div class="form-row ml-4" id="row-cc_ref-<?php echo $thisRef['ucf_id']; ?>"  data-num_cc_refs="<?php echo $numberCCRefs; ?>">
											<div class="col">
												<span class="collapse" id="checkout_title-cc_ref-<?php echo $thisRef['ucf_id']; ?>">Stored credit card <?php echo  $thisRef['card_type']; ?> ending in <?php echo $thisRef['cc_number']; ?></span>
												<i id="remove-cc_ref-<?php echo $thisRef['ucf_id']; ?>" class="far fa-trash-alt text-green-dark-extra mr-2"></i>
												<label for="cc_pay_id<?php echo $thisRef['cc_number']; ?>"><img src="<?php echo IMAGES_PATH; ?>/ccicon/<?php echo strtolower($thisRef['card_type']); ?>_icon.gif" data-credit_card_logo="<?php echo $thisRef['card_type']; ?>" class="icon-credit-card dimmed_dd_image"> <?php echo  $thisRef['card_type']; ?> ending in <?php echo  $thisRef['cc_number']; ?></label>
												<br /><p class="text-gray-600 font-size-small mb-1">This saved card cannot be referenced. Please add and save this card again at checkout if you would like to use it.</p>
											</div>
										</div>
									<?php } else { ?>
										<div class="form-row ml-4" id="row-cc_ref-<?php echo $thisRef['ucf_id']; ?>"  data-num_cc_refs="<?php echo $numberCCRefs; ?>">
											<div class="col">
												<span class="collapse" id="checkout_title-cc_ref-<?php echo $thisRef['ucf_id']; ?>">Stored credit card <?php echo  $thisRef['card_type']; ?> ending in <?php echo $thisRef['cc_number']; ?></span>
												<i id="remove-cc_ref-<?php echo $thisRef['ucf_id']; ?>" class="far fa-trash-alt text-green-dark-extra mr-2"></i>
												<label for="cc_pay_id<?php echo $thisRef['cc_number']; ?>"><img src="<?php echo IMAGES_PATH; ?>/ccicon/<?php echo strtolower($thisRef['card_type']); ?>_icon.gif" data-credit_card_logo="<?php echo $thisRef['card_type']; ?>" class="icon-credit-card"> <?php echo  $thisRef['card_type']; ?> ending in <?php echo  $thisRef['cc_number']; ?></label>
											</div>
										</div>
									<?php }}?>
							</div>
						</div>
					<?php } ?>

					<?php if ( CUser::getCurrentUser()->isCCPA_Enabled() || CUser::getCurrentUser()->isAccountDeleteEligible()) { ?>
						<div class="form-group col-md-6 col-xl-4 bg-gray p-3">
							<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left">Personal Information Requests</h2>
							<div class="row">
								<?php if (CUser::getCurrentUser()->isCCPA_Enabled()) { ?>
									<div class="col-lg-6 mb-2">
										<div class="btn btn-primary btn-sm account-request-data">Request my information</div>
										<p class="font-size-small mt-2">Create a support request to receive access to a file containing your information and how it has been collected or shared. You will be notified by email when your file is available to download.</p>
									</div>
								<?php } ?>
								<?php if (CUser::getCurrentUser()->isAccountDeleteEligible()) { ?>
									<div class="col-lg-6 mb-2">
										<div class="btn btn-danger btn-sm account-request-delete <?php echo (CUser::getCurrentUser()->hasPendingActivity()) ? 'disabled' : ''; ?>">Delete my account</div>
										<p class="font-size-small mt-2">Click here to permanently delete your account and remove your data from our system.</p>
										<?php if (CUser::getCurrentUser()->hasPendingActivity()) { ?>
											<p class="font-size-small text-danger">Account not eligible for deletion. Your account cannot have any current sessions scheduled, outstanding balances due or a pending information request.</p>
										<?php } ?>
									</div>
								<?php } ?>
							</div>
							<p class="font-size-small font-italic">We will work to process all verified requests within 45 days pursuant to the CCPA. If we need an extension for up to an additional 45 days in order to process your request, we will provide you with an explanation for the delay. Please view our <a href="/privacy">Privacy Policy</a> for additional information.</p>
						</div>
					<?php }?>

				</div>

			<?php } ?>

		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>