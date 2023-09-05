<?php $this->setScript('foot', '//maps.googleapis.com/maps/api/js?key=' . GOOGLE_APIKEY); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/fullcalendar/main.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/store.min.js'); ?>
<?php $this->setScriptVar('calendarJS = ' . $this->calendarJS . ';'); ?>
<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->store_info['store_name'])); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true) . '/meet-the-owner'); ?>
<?php $this->assign('order_process_navigation_page', 'session_menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1><?php echo $this->store_info['store_name']; ?> Meet the owner</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<!-- Header-->
	<section>
		<div class="container mb-5">
			<div class="row no-gutters">
				<div class="col-12 col-lg-6">
					<div class="card-group text-center">
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/order-online-no-text-circles-550x410.webp" alt="order online" class="img-fluid" />
							<div class="card-body">
								<h5 class="card-title">1. Order Online</h5>
								<p class="card-text">View our monthly menu, select a time and complete your order.</p>
							</div>
						</div>
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/prep-meals-no-text-circles-550x410.webp" alt="prep your meals" class="img-fluid" />
							<div class="card-body">
								<h5 class="card-title">2. Meals are Prepped</h5>
								<p class="card-text">The shopping, chopping, prep and clean up are taken care of, so you can enjoy meals at home.</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 col-lg-6">
					<div class="card-group text-center">
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/cook-at-home-no-text-circles-550x410.webp" alt="cook at home" class="img-fluid" />
							<div class="card-body">
								<h5 class="card-title">3. Cook at Home</h5>
								<p class="card-text">Thaw your meals each week, cook as directed, and enjoy dinner together.</p>
							</div>
						</div>
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/eat-connect-no-text-circles-550x410.webp" alt="eat and connect together" class="img-fluid" />
							<div class="card-body">
								<h5 class="card-title">4. Eat and Connect</h5>
								<p class="card-text">Spend less time in the kitchen and more time doing what you love.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--
			<?php if (empty($this->store_info['coming_soon'])) { ?>
				<div class="row justify-content-center mt-3">
					<div class="col-12 col-md-3 font-weight-bold text-center text-md-right align-self-center mb-2">
						<div>Place your order</div>
					</div>
					<?php if (!empty($this->calendar['info']['session_type'][CSession::ALL_STANDARD])) { ?>
						<?php foreach ($this->sessionArray['sessions'][CSession::ALL_STANDARD] AS $mid => $sessionInfo) { ?>
							<?php if (!empty($sessionInfo['session_info']['session_count'])) { ?>
								<div class="col-12 col-md-auto">
									<a href="/menu/<?php echo $this->store_info['id']; ?>-<?php echo $sessionInfo['menu_info']['menu_name_abbr']; ?>" class="btn btn-primary w-100 mb-2">
										<i class="dd-icon icon-<?php echo strtolower($sessionInfo['menu_info']['menu_month']); ?> font-size-large align-middle"></i>
										<span class="px-4"><?php echo $sessionInfo['menu_info']['menu_month']; ?></span>
									</a>
								</div>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>-->

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

			<div class="row">
				<div class="col-lg-8">
					<div class="row mb-3">
						<div class="col-sm-6 col-lg-4">
							<img src="/theme/dreamdinners/images/stores/<?php echo $this->store_info['id']; ?>.webp" alt="Mill Creek" class="img-fluid w-100">
						</div>

						<div class="col-sm-6 col-lg-4 text-center text-sm-left">
							<h3 class="text-uppercase font-weight-bold">
								<?php echo $this->store_info['store_name']; ?>
							</h3>
							<div>
								<p>
									<?php echo $this->store_info['address_line1']; ?><br />
									<?php echo (!empty($this->store_info['address_line2'])) ? $this->store_info['address_line2'] . '<br />' : ''; ?>
									<?php echo $this->store_info['city'] ?>, <?php echo $this->store_info['state_id']; ?> <?php echo $this->store_info['postal_code']; ?>
								</p>
								<div class="d-md-none"><a href="tel:<?php echo $this->store_info['telephone_day']; ?>"><?php echo $this->store_info['telephone_day']; ?></a></div>
								<div class="d-none d-md-block"><?php echo $this->store_info['telephone_day']; ?></div>
							</div>
							<?php echo (!empty($this->store_info['telephone_sms'])) ? 'Text us at '.$this->store_info['telephone_sms'] . '<br />' : ''; ?>
							<?php echo (!empty($this->store_info['email_address'])) ? CTemplate::recaptcha_mailHideHtml($this->store_info['email_address'], 'Email Store') . '<br />' : ''; ?>

						</div>
						<div class="col-lg-4 text-center text-lg-left text-lg-right mt-2 mt-lg-0">
							<a href="https://instagram.com/<?php echo (!empty($this->store_info['social_instagram'])) ? $this->store_info['social_instagram'] : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-instagram"></i></a>
							<a href="https://twitter.com/<?php echo (!empty($this->store_info['social_twitter'])) ? $this->store_info['social_twitter'] : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-twitter"></i></a>
							<a href="https://facebook.com/<?php echo (!empty($this->store_info['social_facebook'])) ? $this->store_info['social_facebook'] : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-facebook-f"></i></a>
							<a href="https://pinterest.com/dreamdinners" class="text-decoration-hover-none font-size-large text-green-light" target="_blank"><i class="fab fa-pinterest"></i></a>
							<?php if (!empty($this->calendar['info']['session_type']['DELIVERY'])) { ?>
								<div class="mt-1">
									<i class="dd-icon icon-delivery text-green font-size-medium-large align-bottom"></i> <span class="font-italic">Home Delivery Available!</span>
								</div>
							<?php } ?>
							<div class="mt-1">
								<span class="font-weight-bold text-black text-uppercase">New lower minimums!</span>
							</div>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="row">
						<div class="col">
							<iframe
									class="border border-width-2-imp border-gray-500"
									width="100%"
									height="250"
									src="//www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_APIKEY; ?>&q=<?php echo (!empty($this->store_info['google_place_id'])) ? 'place_id:' . $this->store_info['google_place_id'] : urlencode($this->store_info['linear_address']); ?>" allowfullscreen>
							</iframe>
						</div>
					</div>
				</div>

			</div>

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

			<!-- Menu Highlights-->
			<div class="row mb-2">
				<div class="col">
					<div class="text-center">
						<h2 class="font-weight-bold">Meals Made In A Snap</h2>
					</div>
				</div>
			</div>
			<div class="row mb-2 no-gutters">
				<div class="col-12 col-lg-6">
					<div class="card-group text-center mb-2">
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini-pot-pies-featured-kid-pick-400x400.webp" alt="Mini Chicken Pot Pies" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Mini Chicken Pot Pies</h5>
								</div>
							</div>
							<div class="card border-0 pr-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-enchiladas-featured-pan-400x400.webp" alt="Chicken Enchiladas" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Enchiladas</h5>
								</div>
							</div>

						</div>
				</div>
				<div class="col-12 col-lg-6">
					<div class="card-group text-center">
						<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/carne-asada-tacos-featured-under-30-400x400.webp" alt="Carne Asada Steak Tacos" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Carne Asada Steak Tacos</h5>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-tikka-masala-featured-instant-pot-400x400.webp" alt="Chicken Tikka Masala" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Tikka Masala over Jasmine Rice</h5>
								</div>
							</div>
					</div>
				</div>
			</div>
			<!--<div class="row">
				<div class="col text-center">
					<a href="/menu/<?php echo $this->store_info['id']; ?>-apr" class="btn btn-lg btn-primary">View Menu Options</a>
				</div>
			</div>-->

			<?php if (empty($this->store_info['coming_soon'])) { ?>
				<!--order buttons -->
				<div class="row no-gutters">
					<div class="card-deck justify-content-center">

						<div class="col-12 col-md-6 col-lg-4">
							<div class="card m-0<?php if (empty($this->calendar['info']['session_type'][CSession::ALL_STANDARD])) { ?> disabled<?php } ?>">
								<div class="card-body text-center">
									<div class="row mt-2">
										<div class="col">
											<i class="dd-icon icon-cooler font-size-extra-extra-large text-green"></i>
										</div>
									</div>
									<h5 class="card-title font-size-medium">Place an Order</h5>
									<p class="card-text mb-2">
										Get local pricing for prepped dinners on our monthly menu. Schedule your pick up, in-store assembly or home delivery* time slot after you select your meals.
									</p>
								</div>
								<div class="card-footer border-0 p-0">
									<?php if (empty($this->calendar['info']['session_type'][CSession::ALL_STANDARD])) { ?>
										<div class="card-footer border-0 p-0 bg-gray-600">
											<p class="card-text text-white py-2 text-center">No dates are available at this time</p>
										</div>
									<?php } else { ?>
										<?php foreach ($this->sessionArray['sessions'][CSession::ALL_STANDARD] AS $mid => $sessionInfo) { ?>
											<?php if (!empty($sessionInfo['session_info']['session_count'])) { ?>
												<a href="/menu/<?php echo $this->store_info['id']; ?>-<?php echo $sessionInfo['menu_info']['menu_name_abbr']; ?>" class="btn btn-primary btn-block btn-spinner" id="select_session-standard-<?php echo $mid; ?>" name="session_menu" type="submit" value="<?php echo $mid; ?>">
													<i class="dd-icon icon-<?php echo strtolower($sessionInfo['menu_info']['menu_month']); ?> float-left font-size-medium-small mt-1"></i>
													<?php echo $sessionInfo['menu_info']['menu_month']; ?> Menu
												</a>
											<?php } ?>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</div>

						<?php if ($this->canOrderIntro) { ?>
							<div class="col-12 col-md-6 col-lg-4">
								<div class="card m-0<?php if (empty($this->calendar['info']['session_type'][CSession::INTRO])) { ?> disabled<?php } ?>">
									<div class="card-body text-center">
										<div class="row mt-2">
											<div class="col">
												<i class="dd-icon icon-tablespoon font-size-extra-extra-large text-green"></i>
											</div>
										</div>
										<h5 class="card-title font-size-medium">Meal Prep Starter Pack</h5>
										<p class="card-text mb-2">
											New to Dream Dinners? Try our ready-to-cook meals with our Meal Prep Starter Pack. Choose store pick up, in-store assembly or have us deliver directly to your home*.
										</p>
									</div>
									<div class="card-footer border-0 p-0">
										<?php if (empty($this->calendar['info']['session_type'][CSession::INTRO])) { ?>
											<div class="card-footer border-0 p-0 bg-gray-600">
												<p class="card-text text-white py-2 text-center">No dates are available at this time</p>
											</div>
										<?php } else { ?>
											<?php foreach ($this->sessionArray['sessions'][CSession::INTRO] AS $mid => $sessionInfo) { ?>
												<?php if (!empty($sessionInfo['session_info']['session_count'])) { ?>
													<a href="/menu/<?php echo $this->store_info['id']; ?>-<?php echo $sessionInfo['menu_info']['menu_name_abbr']; ?>-starter" class="btn btn-primary btn-block btn-spinner" id="select_session-intro-<?php echo $mid; ?>" name="menu" type="submit" value="<?php echo $mid; ?>"<?php echo (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS) ? ' data-toggle="tooltip" title="This button currently enabled for all users for testing purposes."' : ''; ?>>
														<?php echo $sessionInfo['menu_info']['menu_month']; ?> Menu
													</a>
												<?php } ?>
											<?php } ?>
										<?php } ?>
									</div>
								</div>
							</div>
						<?php } ?>

						<?php if (!empty($this->calendar['info']['session_type'][CSession::EVENT])) { ?>
							<div class="col-12 col-md-6 col-lg-4" id="events">
								<div class="card m-0">
									<div class="card-body text-center">
										<div class="row mt-2">
											<div class="col">
												<i class="dd-icon icon-group font-size-extra-extra-large text-green"></i>
											</div>
										</div>
										<h5 class="card-title font-size-medium">Special Events</h5>
										<p class="card-text mb-2">
											Have you been invited to a Dream Dinners Meal Prep Workshop, Fundraiser, Private Party, or Friends Night Out? Find your event to sign up here.
										</p>
									</div>
									<div class="card-footer border-0 p-0">
										<?php if (empty($this->calendar['info']['session_type'][CSession::EVENT])) { ?>
											<div class="card-footer border-0 p-0 bg-gray-600">
												<p class="card-text text-white py-2 text-center">No events are available at this time</p>
											</div>
										<?php } else { ?>
											<?php foreach ($this->sessionArray['sessions'][CSession::EVENT] AS $mid => $sessionInfo) { ?>
												<?php if (!empty($sessionInfo['session_info']['session_count'])) { ?>
													<a href="/menu/<?php echo $this->store_info['id']; ?>-<?php echo $sessionInfo['menu_info']['menu_name_abbr']; ?>-events" class="btn btn-primary btn-block btn-spinner" id="select_session-event-<?php echo $mid; ?>" name="session_menu" type="submit" value="<?php echo $mid; ?>">
														<?php echo $sessionInfo['menu_info']['menu_month']; ?> Menu
													</a>
												<?php } ?>
											<?php } ?>
										<?php } ?>
									</div>
								</div>
							</div>
						<?php } ?>

					</div>
				</div>
			<?php } ?>

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

			<!-- About and directions -->
			<div class="row mb-3">
				<?php if (!empty($this->store_info['store_description'])) { ?>
					<div class="col-auto col-md-6 mb-3 mb-md-auto">
						<h3 class="text-uppercase font-weight-bold text-center text-md-left">
							About our store
						</h3>
						<div class="location-about">
							<?php echo nl2br($this->store_info['store_description']); ?>
						</div>
					</div>
				<?php } ?>
				<?php if (!empty($this->store_info['address_directions'])) { ?>
					<div class="col-auto col-md-6">
						<h3 class="text-uppercase font-weight-bold text-center text-md-left">
							Store directions
						</h3>
						<div class="location-about">
							<?php echo nl2br($this->store_info['address_directions']); ?>
						</div>
					</div>
				<?php } ?>
			</div>

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

			<!-- Tabs -->
			<?php if ($this->storePromos || $this->storeOHEvents) { ?>
				<div class="row mb-4">
					<div class="col">
						<ul class="nav nav-pills nav-justified mb-4">
							<?php if ($this->storePromos) { ?>
								<li class="nav-item">
									<a class="nav-link text-uppercase font-weight-bold active" id="promos-tab" data-urlpush="false" data-toggle="tab" data-target="#promos" href="/main.php?page=store&amp;id=<?php echo $this->store_info['id']; ?>&amp;tab=promos" role="tab" aria-controls="promos" aria-selected="true">Store promotions</a>
								</li>
							<?php } ?>
							<?php if ($this->storeOHEvents) { ?>
								<li class="nav-item">
									<a class="nav-link text-uppercase font-weight-bold <?php if (!$this->storePromos) { ?>active<?php } ?>" id="events-tab" data-urlpush="false" data-toggle="tab" data-target="#events" href="/main.php?page=store&amp;id=<?php echo $this->store_info['id']; ?>&amp;tab=events" role="tab" aria-controls="events" aria-selected="false">Store events</a>
								</li>
							<?php } ?>
						</ul>

						<div class="tab-content" id="storeDetailsContent">
							<?php if ($this->storePromos) { ?>
								<div class="tab-pane fade show active" id="promos" role="tabpanel" aria-labelledby="promos-tab">
									<?php $count = 0; foreach ($this->storePromos AS $storePromo) { $count++; ?>
										<div class="row">
											<div class="col text-center">
												<p class="font-weight-bold text-uppercase"><?php echo $storePromo->title; ?></p>
												<p><?php echo $storePromo->message; ?></p>
												<p class="font-italic text-muted font-size-small">Until <?php echo CTemplate::dateTimeFormat($storePromo->message_end, MONTH_DAY_YEAR); ?></p>
												<?php if ($count != count($this->storePromos)) { ?>
													<hr />
												<?php } ?>
											</div>
										</div>
									<?php } ?>
								</div>
							<?php } ?>
							<?php if ($this->storeOHEvents) { ?>
								<div class="tab-pane fade <?php if (!$this->storePromos) { ?>show active<?php } ?>" id="events" role="tabpanel" aria-labelledby="events-tab">
									<div class="row mb-3">
										<div class="col-lg-4 mb-2">
											<img src="<?php echo IMAGES_PATH; ?>/landing_pages/open-house-meal-prep-540x360.jpg" alt="Open House Meal Prep" class="img-fluid" />
										</div>
										<div class="col-lg-8">
											<p class="font-weight-bold text-uppercase">Try our meal prep experience at an open house</p>
											<p>Dream Dinners will change how you meal plan, cook and gather at the table with your family. Join us in our meal prep kitchen to receive three medium family-style, ready-to-cook meals for just $60.</p>
											<div class="row">
												<?php foreach ($this->storeOHEvents AS $storeEvent) { ?>
													<div class="col-lg-6 mb-2">
														<a href="/session/<?php echo $storeEvent['extendedProps']['id']; ?>" class="btn btn-primary btn-block">
															<div><?php echo CTemplate::dateTimeFormat($storeEvent['start'], FULL_DAY); ?></div>
															<div><?php echo CTemplate::dateTimeFormat($storeEvent['start'], FULL_MONTH_DAY_YEAR); ?> at <?php echo CTemplate::dateTimeFormat($storeEvent['start'], TIME_ONLY); ?></div>
														</a>
													</div>
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
							<?php } ?>
						</div>
					</div>
				</div>

				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

			<?php } ?>

			<?php if (empty($this->store_info['coming_soon'])) { ?>

				<div class="row">
					<div class="col text-center">
						<h3 class="text-uppercase font-weight-bold text-center">
							Store Calendar
						</h3>
						<p>Our calendar represents dates and times available to reserve your order based on how you would like to get your meals. Options may include Pick Up, Home Delivery,You Assemble at Store, Community Pick Up locations and Events. Many stores have open hours to come in shop out of the freezer not listed here. Contact the store for any questions.</p>
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
						<a href="/main.php?page=session_menu" class="btn btn-primary btn-block">Start your order</a>
					</div>
				</div>
			<?php } ?>

		</div>
	</section>

	<section>
		<div class="container-fluid my-5">
			<div class="row mb-5">
				<div class="col-md-6 p-5 text-center mx-auto bg-gray">
					<h2 class="mb-4 font-weight-bold">Job Opportunities</h2>
					<p>
						Dream Dinners is an innovative concept in meal preparation that eliminates the stress of dealing with dinner – We remove menu planning, shopping & prep-work from the equation, leaving more quality time for families.
						We are looking for amazing team members to help us change more lives and bring Homemade, Made Easy meals into the community.
					</p>
					<?php if (!empty($this->store_info['job_positions_available'])) { ?>
						<h4 class="text-uppercase font-weight-bold">Available Positions</h4>
						<p>Our store is hiring for the following positions</p>
						<div class="row">
							<?php foreach ($this->store_info['job_positions_available'] AS $job) { ?>
								<div class="col-12 mb-md-3">
									<div class="card">
										<div class="card-body bg-gray-light">
											<p class="card-text text-center"><?php echo $job['title']; ?></p>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
						<p class="mt-3">To apply for one of the positions above, please send a resume to <a href="mailto:<?php echo $this->store_info['email_address']; ?>"><?php echo $this->store_info['email_address']; ?></a></p>
					<?php } else { ?>
						<p>Join our Dream Dinners team. Feel free to submit your resume to <a href="mailto:<?php echo $this->store_info['email_address']; ?>"><?php echo $this->store_info['email_address']; ?></a>.</p>
					<?php } ?>
				</div>
				<div class="col-md-6 bg-green text-white text-center p-5 mx-auto">
					<h2>At Dream Dinners, <span class="font-weight-bold">our mission</span> is to make gathering around the family table a cornerstone of daily life. </h2>
				</div>
			</div>
		</div>
	</section>


<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>