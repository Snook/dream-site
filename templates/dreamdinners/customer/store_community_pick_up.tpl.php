<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - Community Pick Up locations"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true) . '/community-pick-up'); ?>
<?php $this->assign('order_process_navigation_page', 'session-menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1><?php echo $this->DAO_store->store_name; ?> Dream Dinners Community Pick Up locations</h1>
				<h3 class="font-marker">We offer real food, made from scratch, so your life can feel a little easier.</h3>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<section>
		<div class="container mb-5">

			<?php if (!empty($this->locationArray)) { ?>
				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted">

				<div class="row mb-3">
					<div class="col">
						<h3 class="text-uppercase font-weight-bold text-center">Communities served by <?php echo $this->DAO_store->store_name; ?></h3>
					</div>
				</div>

				<div class="row">

					<?php foreach ($this->locationArray AS $location) { ?>
						<div id="<?php echo $location['DAO_store_pickup_location']->generateAnchor(); ?>" class="col-lg-6 col-xl-4 mb-5 mx-auto">

							<div class="row mb-4">
								<div class="text-center col">
									<h3 class="text-uppercase font-weight-bold"><?php echo $location['DAO_store_pickup_location']->location_title; ?></h3>
									<p><?php echo $location['DAO_store_pickup_location']->generateAddressHTML(); ?></p>
								</div>
							</div>

							<div class="row mb-3">
								<div class="col">
									<iframe
											class="border border-width-2-imp border-gray-500"
											width="100%"
											height="250"
											src="//www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_APIKEY; ?>&q=<?php echo urlencode($location['DAO_store_pickup_location']->generateAddressLinear()); ?>" allowfullscreen>
									</iframe>
								</div>
							</div>

							<div class="row">
								<div class="col">
									<h3 class="text-uppercase font-weight-bold text-center">Available pick up times</h3>
								</div>
							</div>
							<div class="row">
								<?php if (empty($location["sessionArray"])) { ?>
									<div class="col">
										<p class="text-center">No community pick times currently available for this location. Please contact us at <?php echo $this->DAO_store->telephone_day; ?> for questions.</p>
									</div>
								<?php } else { ?>
									<?php foreach ($location["sessionArray"] AS $DAO_session) { ?>
										<div class="col-12">
											<a href="/session/<?php echo $DAO_session->id; ?>" class="btn btn-primary btn-block mb-2" rel="nofollow">
												<div><?php echo $DAO_session->sessionStartDateTime()->format('l F j'); ?></div>
												<div><?php echo $DAO_session->sessionStartDateTime()->format('g:i A'); ?> - <?php echo $DAO_session->sessionEndDateTime()->format('g:i A'); ?></div>
											</a>
										</div>
									<?php } ?>
								<?php } ?>
							</div>

						</div>
					<?php } ?>

				</div>
			<?php } else { ?>
				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted">

				<div class="row">
					<div class="col text-center">We do not have any community pick up dates available. Please contact the store for questions.</div>
				</div>
			<?php } ?>

		</div>
	</section>


<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>