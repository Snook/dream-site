<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/fullcalendar/main.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/store.min.js'); ?>
<?php $this->setScriptVar('calendarJS = ' . $this->calendarJS . ';'); ?>
<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - What's new &amp; calendar"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true) . '/calendar'); ?>
<?php $this->assign('order_process_navigation_page', 'session-menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1>What's new at Dream Dinners <?php echo $this->DAO_store->store_name; ?></h1>
				<h3 class="font-marker">We offer real food, made from scratch, so your life can feel a little easier.</h3>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<section>
		<div class="container mb-5">

			<?php if (!empty($this->DAO_store->ActivePromoArray)) { ?>
				<div class="row mb-4">
					<div class="col">
						<h3 id="promotions" class="text-uppercase font-weight-bold text-center">Store promotions</h3>

						<?php $count = 0; foreach ($this->DAO_store->ActivePromoArray AS $DAO_site_message) { $count++; ?>
							<div class="row">
								<div class="col text-center">
									<p class="font-weight-bold text-uppercase"><?php echo $DAO_site_message->title; ?></p>
									<p><?php echo $DAO_site_message->message; ?></p>
									<p class="font-italic text-muted font-size-small">Until <?php echo CTemplate::dateTimeFormat($DAO_site_message->message_end, MONTH_DAY_YEAR); ?></p>
									<?php if ($count != count($this->DAO_store->ActivePromoArray)) { ?>
										<hr />
									<?php } ?>
								</div>
							</div>
						<?php } ?>

					</div>
				</div>

				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />
			<?php } ?>

			<?php if (!$this->DAO_store->isComingSoon()) { ?>
				<?php if (!empty($this->calendar['info']['session_type'][CSession::EVENT])) { ?>
					<div class="row mb-4">
						<div class="col">
							<h3 id="events" class="text-uppercase font-weight-bold text-center">Store events</h3>

							<div class="row mb-3">
								<div class="col-lg-4 mb-2">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/open-house-meal-prep-540x360.jpg" alt="Open House Meal Prep" class="img-fluid" />
								</div>
								<div class="col-lg-8">
									<p>Have you been invited to a Dream Dinners Meal Prep Workshop, Fundraiser, Private Party, or Friends Night Out? Find your event to sign up here.</p>
									<div class="row">
										<?php foreach ($this->sessionArray['sessions'][CSession::INTRO] AS $mid => $sessionInfo) { ?>
											<?php if (!empty($sessionInfo['session_info']['session_count'])) { ?>
												<div class="col-lg-6 mb-2">
													<a href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/order/<?php echo $sessionInfo['menu_info']['menu_name_abbr']; ?>/starter" class="btn btn-primary btn-block btn-spinner" <?php echo (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS) ? ' data-toggle="tooltip" title="This button currently enabled for all users for testing purposes."' : ''; ?>>
														<?php echo $sessionInfo['menu_info']['menu_month']; ?>
													</a>
												</div>
											<?php } ?>
										<?php } ?>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<p class="font-italic text-muted font-size-small">*Our Open House events are only available to new Dream Dinners guests or guests that have not attended a Dream Dinners session in over a year.</p>
								</div>
							</div>
						</div>
					</div>

					<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

					<div class="row">
						<div class="col text-center">
							<h3 class="text-uppercase font-weight-bold text-center">Store calendar</h3>
							<p>Our calendar represents dates and times available to reserve your order based on how you would like to get your meals. Options may include Pick Up, Home Delivery, Assemble at Store, Community Pick Up locations and Events. Many stores have open hours to come in shop out of the freezer listed on the Location Info page.</p>
							<?php if ($this->has_meal_customization_sessions){?>
								<p>Times listed below are available for everyone! If you would like Meal Customization, please select a time marked with a <i class="dd-icon icon-customize text-orange font-size-small"></i> so we can have your dinners customized in time for your pick up or delivery. Customization options available at checkout.</p>
							<?php } ?>
						</div>
						<div class="col-12 text-center">
							<h2 id="calendar_month" class="d-block d-md-none"></h2>
						</div>
						<div class="col-12">
							<div id="calendar" data-fullcalendar="calendar"></div>
						</div>
						<div id="calendar_results" class="col"></div>
					</div>

					<div class="row">
						<div class="col">
							<a href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/order" rel="nofollow" class="btn btn-primary btn-block">Start your order</a>
						</div>
					</div>
				<?php } ?>
			<?php } ?>

		</div>
	</section>

<?php include $this->loadTemplate('customer/subtemplate/store/store_footer.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>