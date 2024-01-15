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

			<div class="row">
				<div class="col">
					<h3 class="text-uppercase font-weight-bold text-center">Communities served by <?php echo $this->DAO_store->store_name; ?></h3>
				</div>
			</div>
			<div class="row">
				<?php foreach ($this->locationArray AS $location) { ?>
					<div class="col m-auto text-center">
						<a href="#<?php echo $location['DAO_store_pickup_location']->generateAnchor(); ?>" class="btn btn-link mb-2"><?php echo $location['DAO_store_pickup_location']->city; ?></a>
					</div>
				<?php } ?>
			</div>

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted">

			<div class="row">

				<?php foreach ($this->locationArray AS $location) { ?>
					<div id="<?php echo $location['DAO_store_pickup_location']->generateAnchor(); ?>" class="col-lg-6 col-xl-4 mb-5 mx-auto">

						<div class="row mb-4">
							<div class="text-center col">
								<h3 class="text-uppercase font-weight-bold"><?php echo $location['DAO_store_pickup_location']->city; ?></h3>
								<p><?php echo $location['DAO_store_pickup_location']->generateAddressHTML(); ?></p>
								<?php echo $location['DAO_store_pickup_location']->location_title; ?>
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
									<p class="text-center">No community pick up times currently available for the <?php echo $location['DAO_store_pickup_location']->city; ?> location.</p>
								</div>
							<?php } else { ?>
								<?php foreach ($location["sessionArray"] AS $DAO_session) { ?>
									<div class="col-12">
										<a href="/session/<?php echo $DAO_session->id; ?>" class="btn btn-primary btn-block mb-2" rel="nofollow"><?php echo CTemplate::dateTimeFormat($DAO_session->session_start, VERBOSE); ?></a>
									</div>
								<?php } ?>
							<?php } ?>
						</div>

					</div>
				<?php } ?>

			</div>

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted">

			<div class="row no-gutters">
				<div class="col-12 col-lg-6">
					<div class="card-group text-center">
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/order-online-no-text-circles-550x410.webp" alt="order online" class="img-fluid" />
							<div class="card-body">
								<h5 class="card-title my-0">1. Order Online</h5>
								<p class="card-text">View our monthly menu, select a time and complete your order.</p>
							</div>
						</div>
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/prep-meals-no-text-circles-550x410.webp" alt="prep your meals" class="img-fluid" />
							<div class="card-body">
								<h5 class="card-title my-0">2. Meals are Prepped</h5>
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
								<h5 class="card-title my-0">3. Cook at Home</h5>
								<p class="card-text">Thaw your meals each week, cook as directed, and enjoy dinner together.</p>
							</div>
						</div>
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/eat-connect-no-text-circles-550x410.webp" alt="eat and connect together" class="img-fluid" />
							<div class="card-body">
								<h5 class="card-title my-0">4. Eat and Connect</h5>
								<p class="card-text">Spend less time in the kitchen and more time doing what you love.</p>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</section>

<?php include $this->loadTemplate('customer/subtemplate/store/store_footer.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>