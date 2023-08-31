<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/clipboard/clipboard.min.js'); ?>
<?php $this->assign('page_title','My Account Summary'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>My Account</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main class="container-fluid">

		<div class="row">

			<div class="col-md-6 mb-2">

				<div class="row mb-2">
					<div class="col-4 text-center">
						<img src="<?php echo IMAGES_PATH; ?>/style/platepoints/placeholder_avatar.png" alt="Profile picture" class="img-fluid">
					</div>
					<div class="col-8">
						<h2 class="font-weight-bold text-uppercase font-size-medium"><?php echo CUser::getCurrentUser()->firstname . " " . CUser::getCurrentUser()->lastname; ?></h2>

						<?php if (CUser::getCurrentUser()->membershipData['status'] == CUser::MEMBERSHIP_STATUS_CURRENT) { ?>

							<?php include $this->loadTemplate('customer/subtemplate/my_account/my_account_membership_status.tpl.php'); ?>

						<?php } else if (CUser::getCurrentUser()->platePointsData['status'] == 'active') { ?>
							<div class="row mb-2">
								<div class="col">
									<p class="text-uppercase">Points <?php echo number_format(200 - intVal(CUser::getCurrentUser()->platePointsData['points_until_next_credit'])); ?> of 200</p>
									<div class="font-size-small">Points pending: <?php echo number_format(CUser::getCurrentUser()->platePointsData['pending_points']); ?></div>
									<div class="font-size-small">Total Dinner Dollars: $<?php echo CUser::getCurrentUser()->platePointsData['available_credit']; ?></div>
									<div class="font-size-small">
										<?php foreach ($this->userCredits['credit']['available_pp_credits'] AS $id => $credit) {?>
											&nbsp;&rtrif;$<?php echo $credit['dollar_value']; ?> expire on <?php echo CTemplate::dateTimeFormat($credit['expiration_date'], FULL_MONTH_DAY_YEAR); ?><br>
										<?php } ?>
									</div>
								</div>
							</div>
						<?php } ?>

					</div>

				</div>

				<?php if (!$this->isPreferred) { ?>
					<?php if (CUser::getCurrentUser()->platePointsData['status'] == 'active') { ?>

						<?php //include $this->loadTemplate('customer/subtemplate/my_account/my_account_platepoints_status.tpl.php'); ?>

					<?php } else if (false){//!CUser::getCurrentUser()->platePointsData['userIsOnHold']) { ?>

						<div class="row mb-4">
							<div class="col text-center py-2 bg-image-platepoints">
								<h3 class="font-weight-bold mb-2 mt-4">Join PlatePoints, Our Rewards Program</h3>
								<p>Accrue PlatePoints as you meal prep with Dream Dinners. Apply your earned rewards at checkout.</p>
								<a class="btn btn-secondary mt-3 mb-4" href="/main.php?page=account&amp;pp_enroll=1">Enroll today</a>
								<p><i>*Ability to spend rewards is available at participating locations.</i></p>
							</div>
						</div>

					<?php } ?>
				<?php } ?>

			</div>

			<div class="col-md-6 mb-2">
				<div class="row">
					<div class="col p-4 bg-gray">
						<h5 class="font-weight-bold text-uppercase">Tell your friends</h5>
						<p><!--Share Dream Dinners and receive referral rewards for each NEW friend that completes an order.--> Dream Dinners is easier than ever! Share your personal referral link to introduce your friends and family to Dream Dinners. You get 10 Dinner Dollars for every referral and they get a free dinner on us*!</p>
						<div class="input-group mb-3">
							<div class="input-group-prepend">
								<span class="input-group-text">Your link</span>
							</div>
							<input type="text" id="my_share_pp_link" class="form-control" aria-label="Your referral link" value="<?php echo HTTPS_BASE; ?>share/<?php echo CUser::getCurrentUser()->id; ?>" readonly />
							<div class="input-group-append">
								<button class="input-group-text btn-clip" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard"  data-clipboard-target="#my_share_pp_link" ><i class="fas fa-clipboard-list"></i></button>
							</div>
							<div class="input-group-append">
								<a class="input-group-text" data-toggle="tooltip" data-placement="top" title="Download QR code" href="<?php echo HTTPS_BASE; ?>ddproc.php?processor=qr_code&amp;op=referral&amp;d=1&amp;s=10&amp;id=<?php echo CUser::getCurrentUser()->id; ?>" ><i class="fas fa-qrcode"></i></a>
							</div>
						</div>

						<button class="btn btn-cyan btn-block mb-4"
								data-share-social="facebook,twitter"
								data-share-title="Dream Dinners"
								data-share-text="Dream Dinners is my solution to making homemade dinners for the family."
								data-share-url="<?php echo HTTPS_BASE; ?>share/<?php echo CUser::getCurrentUser()->id; ?>">
							<i class="dd-icon icon-share2 mr-2"></i> Share
						</button>

						<div class="row pt-2">
							<div class="col-md-4 col-xl-2 text-center">
								<a href="<?php echo HTTPS_BASE; ?>ddproc.php?processor=qr_code&amp;op=referral&amp;s=10&amp;id=<?php echo CUser::getCurrentUser()->id; ?>" target="_blank">
									<img class="img-fluid" src="<?php echo HTTPS_BASE; ?>ddproc.php?processor=qr_code&amp;op=referral&amp;s=5&amp;id=<?php echo CUser::getCurrentUser()->id; ?>"></a>
								<p class="font-weight-light font-size-small">(<a href="<?php echo HTTPS_BASE; ?>ddproc.php?processor=qr_code&amp;op=referral&amp;d=1&amp;s=10&amp;id=<?php echo CUser::getCurrentUser()->id; ?>">Download</a>)</p>
							</div>
							<div class="col-md-8 col-xl-10">
								<p class="font-weight-bold">Your Referral QR Code</p>
								<p class="mb-0">Save and print this code to share. Anyone can easily scan and order to help you earn rewards even faster!</p>
								<p class="p-1 text-muted font-italic font-size-small">*Free dinner available to new guests with code EASIER May 1- August 31, 2023. Rewards and free dinner available at participating store locations.</p>
							</div>
						</div>

					</div>
				</div>

			</div>

			<?php if (!empty($this->future_orders)) { ?>
				<div class="col-xl-6 mb-2 bg-gray-light">
					<div class="col-12 pt-4 text-center">
						<h5 class="font-weight-bold text-uppercase">Upcoming orders</h5>
					</div>
					<?php foreach ($this->future_orders as $type => $orders) { ?>
						<?php if(!empty($orders)){ ?>
							<div class="row pt-2">
								<div class="col-12 text-left">
									<h5 class="font-weight-bold text-uppercase"><?php  echo $type ?></h5>
								</div>
								<div class="col-12">
									<?php foreach ($orders AS $order) { ?>
										<?php include $this->loadTemplate('customer/subtemplate/my_account/my_account_sessions_upcoming.tpl.php'); ?>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>
			<div class="col-md-6 mb-2">
				<?php include $this->loadTemplateIfElse('customer/subtemplate/my_account/monthly/' . $this->monthlyDirectory . '/my_account_monthly.tpl.php', 'customer/subtemplate/my_account/monthly/default/my_account_monthly.tpl.php'); ?>

				<div class="row mb-3">
					<div class="col p-0">
						<a class="btn btn-primary btn-lg btn-block" href="/main.php?page=session_menu" id="my_account_start">Start order</a>
					</div>
				</div>
				<?php if (!$this->is_delivered_only) { ?>
					<div class="row mb-2">
						<?php foreach ($this->printMenus AS $calendar) { ?>
							<div class="col-6">
								<a class="btn btn-green-dark-extra btn-sm btn-block" href="/main.php?page=print&amp;store=<?php echo $this->user->home_store_id; ?>&amp;menu=<?php echo $calendar['id']; ?>" target="_blank"><i class="dd-icon icon-print mr-2"></i> Print <?php echo $calendar['menu_name']; ?> Store Menu</a>
							</div>
						<?php } ?>
					</div>
				<?php } ?>

			</div>



			<?php if (!$this->is_delivered_only) { ?>
				<div class="col-xl-6 mb-2">
					<div class="row bg-image-foundation py-2">
						<!--
					<div class="col-lg-6 py-2 py-xl-5 text-center">
						<i class="dd-icon icon-steam_heart font-size-extra-large text-white"></i>
						<h5 class="font-weight-semi-bold text-uppercase text-white">You have rounded up $<?php echo CTemplate::moneyFormat(CUser::getCurrentUser()->LTD_Round_UP_Lifetime_total); ?> for the Dream Dinners Foundation</h5>
					</div>
					-->
						<div class="col-lg-12 py-2 py-xl-5 text-center text-white">
							<i class="d-none d-lg-block dd-icon icon-steam_heart font-size-extra-large mb-2"></i>
							<h5 class="font-weight-semi-bold text-uppercase">You have donated $<?php echo CTemplate::moneyFormat(CUser::getCurrentUser()->LTD_MOTM_Lifetime_total); ?> by purchasing the Dream Dinners Foundation meal of the month</h5>
							<p class="font-size-small font-italic">*Available at participating store locations.</p>
						</div>
					</div>
				</div>
			<?php } ?>




			<?php if (!empty($this->past_orders)) { ?>
				<div class="col-xl-6 mb-2 bg-gray">
					<div class="col-12 pt-4 text-center">
						<h5 class="font-weight-bold text-uppercase">Previous orders</h5>
					</div>
					<?php foreach ($this->past_orders as $type => $orders) { ?>
						<?php if (!empty($orders)) { ?>
							<div class="row pt-2">
								<div class="col-12 text-left">
									<h5 class="font-weight-bold text-uppercase"><?php  echo $type ?></h5>
								</div>
								<div class="col-12">
									<?php foreach ($orders AS $order) { ?>
										<?php include $this->loadTemplate('customer/subtemplate/my_account/my_account_sessions_past.tpl.php'); ?>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
					<div class="row pb-2">
						<div class="col-6">
							<a class="btn btn-primary btn-block" href="/main.php?page=my_meals">Rate My Meals</a>
						</div>
						<div class="col-6">
							<a class="btn btn-gray-dark btn-block" href="/main.php?page=my_meals&amp;tab=nav-past_orders">Order history</a>
						</div>
					</div>
				</div>
			<?php } ?>
			<!--text message banner-->
			<div class="col-xl-6 mb-2 bg-cyan-dark py-2">
				<div class="col-12 pt-4 text-center">
					<i class="d-none d-lg-block dd-icon icon-mobile font-size-extra-large text-white mb-2"></i>
					<h5 class="font-weight-bold text-uppercase text-white">OPT INTO DREAM DINNERS EMAIl &amp; TEXT MESSAGING</h5>
					<p class="text-white">Get important Dream Dinners info in the palm of your hand like a reminder about your next order. Plus exciting offers, announcements and weekly thaw reminders.</p>
					<a class="btn btn-gray-dark btn-block mb-3" href="/main.php?page=account">Edit Preferences to Opt In</a>
				</div>
			</div>
			<?php if (!$this->is_delivered_only) { ?>
				<div class="col-xl-6 mb-2 bg-gray-light">
					<?php if (!empty($this->usersFuturePastEvents['upcomingEvents'])) { ?>
						<div class="row pt-2">
							<div class="col-md-12">
								<h5 class="font-weight-bold text-uppercase">My events</h5>
								<?php foreach ($this->usersFuturePastEvents['upcomingEvents'] as $id => $event) { ?>
									<?php include $this->loadTemplate('customer/subtemplate/my_account/my_account_my_events_upcoming.tpl.php'); ?>
								<?php } ?>
							</div>
						</div>
						<div class="row">
							<div class="col my-2">
								<a class="btn btn-primary btn-block" href="/main.php?page=my_events">My events</a>
							</div>
						</div>
					<?php } else { ?>
						<div class="row h-100">
							<div class="col bg-green-dark text-white py-4 text-center">
								<h5 class="font-weight-bold text-uppercase">Host a Dream Dinners Event</h5>
								<p>Want to introduce your friends to Dream Dinners with a party? Looking to raise money for a local nonprofit with a fundraiser? At Dream Dinners, we have several different events to meet your needs.</p>
								<p class="text-uppercase">Contact your local store</p>
								<p><?php echo $this->store['telephone_day']; ?></p>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if ($this->userTestRecipes) { ?>
				<div class="col-xl-6 mb-2 bg-gray py-2">
					<div class="col-12">
						<h5 class="font-weight-bold text-uppercase">My test recipes</h5>
					</div>
					<div class="col">
						<?php include $this->loadTemplate('customer/subtemplate/my_surveys/my_surveys_list.tpl.php'); ?>
					</div>
				</div>
			<?php } ?>

			<?php if ($this->userCredits) { ?>
				<div class="col-xl-6 mb-2 bg-gray-light">
					<div class="row py-2">
						<div class="col-12">
							<h5 class="font-weight-bold text-uppercase">My credits</h5>
						</div>
						<?php if (!empty($this->userCredits['credit']['available_pp_credits'])) { ?>
							<div class="col-md-6 mb-2">
								<p class="font-weight-bold text-green-dark">PLATEPOINTS Dinner Dollars</p>
								<ul class="list-group">
									<?php foreach ($this->userCredits['credit']['available_pp_credits'] AS $id => $credit) {
										// DD EXP DATE DISPLAY 8 - adjustment made upstream
										?>
										<li class="list-group-item">$<?php echo $credit['dollar_value']; ?>, expires <?php echo CTemplate::dateTimeFormat($credit['expiration_date'], FULL_MONTH_DAY_YEAR); ?></li>
									<?php } ?>
								</ul>
								<div class="font-italic font-size-small text-muted">PLATEPOINTS Dinner Dollars can only be used at participating stores and on items above your standard order, Sides &amp; Sweets freezer items and service fees.</div>
								<?php if (empty($this->store['supports_plate_points'])) { ?>
									<div class="font-italic font-size-small text-muted">Your independently-owned Home Store is not currently participating in the PLATEPOINTS program. You will be able to earn PLATEPOINTS and participate in non-spend activities, but Dinner Dollars earned and gifts cannot be redeemed at a non-participating store or on a Delivered order.</div>
								<?php } ?>
							</div>
						<?php } ?>

						<?php if (!empty($this->userCredits['credit']['refstoreCredits'])) { ?>
							<div class="col-md-6 mb-2">
								<p class="font-weight-bold text-green-dark">Referral Credits</p>
								<ul class="list-group">
									<?php foreach ($this->userCredits['credit']['refstoreCredits'] as $credit) { ?>
										<li class="list-group-item">$<?php echo $credit['amount']; ?> at <?php echo $credit['store_name']; ?>, awarded <?php echo $this->dateTimeFormat($credit['timestamp_created']); ?>.</li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>

						<?php if (!empty($this->userCredits['credit']['directstoreCredits'])) { ?>
							<div class="col-md-6 mb-2">
								<p class="font-weight-bold text-green-dark">Direct Store Credits</p>
								<ul class="list-group">
									<?php foreach ($this->userCredits['credit']['directstoreCredits'] as $credit) { ?>
										<li class="list-group-item">$<?php echo $credit['amount']; ?> at <?php echo $credit['store_name']; ?>, awarded <?php echo $this->dateTimeFormat($credit['timestamp_created']); ?>.</li>
									<?php } ?>
								</ul>
								<div class="font-italic font-size-small text-muted">Store credits can only be used at the store listed next to the credit.</div>
							</div>
						<?php } ?>

						<?php if (!empty($this->userCredits['credit']['storeCredits'])) { ?>
							<div class="col-md-6 mb-2">
								<p class="font-weight-bold text-green-dark">Gift Card Store Credits</p>
								<ul class="list-group">
									<?php foreach ($this->userCredits['credit']['storeCredits'] as $credit) { ?>
										<li class="list-group-item">$<?php echo $credit['amount']; ?> at <?php echo $credit['store_name']; ?>, awarded <?php echo $this->dateTimeFormat($credit['timestamp_created']); ?>.</li>
									<?php } ?>
								</ul>
								<div class="font-italic font-size-small text-muted">Store credits can only be used at the store listed next to the credit.</div>
							</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>

			<?php if (CUser::getCurrentUser()->platePointsData['status'] == 'active') { ?>
				<div class="col-xl-6 mb-2 bg-gray">
					<div class="row py-2">
						<div class="col-12">
							<h5 class="font-weight-bold text-uppercase">PLATEPOINTS activity</h5>
						</div>
						<div class="col-12">
							<ul class="list-group text-center overflow-auto height-12">
								<?php foreach ($this->platepoints_history AS $thisEvent) { ?>
									<?php if (!empty($thisEvent['meta_array']) && !empty($thisEvent['meta_array']['comments'])) { ?>
										<li class="list-group-item font-weight-bold bg-green-light p-1"><?php echo CTemplate::dateTimeFormat($thisEvent['timestamp_created'], NORMAL); ?></li>
										<li class="list-group-item p-2"><?php echo $thisEvent['meta_array']['comments']; ?></li>
									<?php } ?>
								<?php } ?>
							</ul>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if (CUser::getCurrentUser()->isCCPA_Enabled()) { ?>
				<div class="col-xl-6 mb-2 bg-gray-light">
					<div class="row pt-2">
						<div class="col-lg-12 py-2 py-xl-5 text-center">
							<h5 class="font-weight-bold text-uppercase">Ways to exercise your rights under the California Consumer Privacy Act</h5>
							<p>California residents can request details about personal information collected and shared <a href="/main.php?page=account">by submitting a request</a>.</p>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>

		<div class="row">
			<div class="col">
				<div class="row">
					<div class="col-sm-6 col-xl-2 mt-2">
						<h5 class="font-weight-bold text-uppercase text-body">Your details</h5>
						<div class="text-uppercase mb-2"><?php echo $this->user->firstname . " " . $this->user->lastname; ?></div>
						<div class="text-uppercase"><?php echo $this->user_address['address_line1']; ?> <?php echo $this->user_address['address_line2']; ?></div>
						<div class="text-uppercase"><?php echo $this->user_address['city']; ?>, <?php echo $this->user_address['state_id']; ?> <?php echo $this->user_address['postal_code']; ?></div>
					</div>
					<div class="col-sm-6 col-xl-3 mt-2">
						<h5 class="font-weight-bold text-uppercase text-body">Contact</h5>
						<div class="mb-2 text-break"><?php echo $this->user->primary_email; ?></div>
						<div><?php echo $this->user->telephone_1; ?></div>
					</div>
					<div class="col-sm-6 col-xl-4 mt-2">
						<h5 class="font-weight-bold text-uppercase text-body">Preferences</h5>
						<ul>
							<li>Print freezer sheet - <?php echo (!empty(CUser::getCurrentUser()->preferences[CUser::SESSION_PRINT_FREEZER_SHEET]['value'])) ? 'Yes' : 'No'; ?></li>
							<li>Print nutritionals - <?php echo (!empty(CUser::getCurrentUser()->preferences[CUser::SESSION_PRINT_NUTRITIONALS]['value'])) ? 'Yes' : 'No'; ?></li>
							<li>Account note - <?php echo (!empty(CUser::getCurrentUser()->preferences[CUser::USER_ACCOUNT_NOTE]['value'])) ? CUser::getCurrentUser()->preferences[CUser::USER_ACCOUNT_NOTE]['value'] : 'Not set'; ?></li>
							<li>Text Messages - <?php echo (!empty(CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'])
									&& strpos(CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'], "PENDING ") === false
									&& CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] != CUser::UNANSWERED) ? CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] : 'Not set'; ?></li>
							<li>Email Subscription - <?php echo (CUser::getCurrentUser()->preferences[CUser::EMAIL_SURVEYS]['value'] == CUser::OPTED_IN
									|| CUser::getCurrentUser()->preferences[CUser::EMAIL_OFFERS_AND_PROMOS]['value'] == CUser::OPTED_IN
									|| CUser::getCurrentUser()->preferences[CUser::EMAIL_PLATE_POINTS]['value'] == CUser::OPTED_IN
									|| CUser::getCurrentUser()->preferences[CUser::EMAIL_REMINDER_SESSION]['value'] == CUser::OPTED_IN) ? 'Subscribed' : 'Unsubscribed'; ?></li>
						</ul>
					</div>
					<div class="col-sm-6 col-xl-3 mt-2">
						<h5 class="font-weight-bold text-uppercase text-body">Home store</h5>
						<?php if (!empty($this->store['city'] )) { ?>
							<div><?php echo $this->store['address_line1']; ?> <?php echo $this->store['address_line2']; ?></div>
							<div><?php echo $this->store['city']; ?>, <?php echo $this->store['state_id']; ?> <?php echo $this->store['postal_code']; ?></div>
							<div><?php echo $this->store['telephone_day']; ?></div>
							<div class="row mt-2">
								<div class="col">
									<a class="btn btn-primary btn-sm mb-2" href="/main.php?page=store&amp;id=<?php echo $this->store['id']?>">Store details</a>
									<a class="btn btn-primary btn-sm mb-2 text-break" href="mailto:<?php echo $this->store['email_address']; ?>"><?php echo $this->store['email_address']; ?></a>
								</div>
							</div>
						<?php } else { ?>
							<div>No Home Store Selected</div>
						<?php } ?>
					</div>
				</div>
				<div class="row mb-2">
					<div class="col">
						<a class="btn btn-primary btn-block" href="/main.php?page=account">Edit Settings</a>
					</div>
				</div>
			</div>
		</div>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>