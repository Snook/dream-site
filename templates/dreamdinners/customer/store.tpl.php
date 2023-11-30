<?php $this->setScript('foot', '//maps.googleapis.com/maps/api/js?key=' . GOOGLE_APIKEY); ?>
<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - Location info"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true)); ?>
<?php $this->assign('order_process_navigation_page', 'session-menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1><?php echo $this->DAO_store->store_name; ?> Meal Prep Store</h1>
				<img src="<?php echo IMAGES_PATH; ?>/landing_pages/in-a-snap-chicken-tenders-1920x600.webp" alt="Dinner in a Snap" class="img-fluid" />
				<h3 class="font-marker mt-2">We offer real food, made from scratch, so your life can feel a little easier.</h3>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<section>
		<div class="container mb-5">

			<div class="row mb-3">
				<div class="col-12 col-lg-6 col-xl-5">
					<img src="/theme/dreamdinners/images/stores/<?php echo $this->DAO_store->id; ?>.webp" alt="<?php echo $this->DAO_store->store_name; ?>" class="img-fluid w-100">
				</div>
				<div class="text-center text-lg-left col-12 mt-3 mt-lg-0 col-lg-6 col-xl-3">
					<?php if ($this->DAO_store->hasPublicAddress()) { ?>
						<p><?php echo$this->DAO_store->address_html; ?></p>
					<?php } ?>
					<div class="d-md-none"><a href="tel:<?php echo $this->DAO_store->telephone_day; ?>"><?php echo $this->DAO_store->telephone_day; ?></a></div>
					<div class="d-none d-md-block"><?php echo $this->DAO_store->telephone_day; ?></div>
					<?php echo (!empty($this->DAO_store->telephone_sms)) ? 'Text us at '.$this->DAO_store->telephone_sms . '<br />' : ''; ?>
					<?php echo (!empty($this->DAO_store->email_address)) ? CTemplate::recaptcha_mailHideHtml($this->DAO_store->email_address, 'Email Store') . '<br />' : ''; ?>

					<a href="https://instagram.com/<?php echo (!empty($this->DAO_store->social_instagram)) ? $this->DAO_store->social_instagram : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-instagram"></i></a>
					<!--<a href="https://twitter.com/<?php echo (!empty($this->DAO_store->social_twitter)) ? $this->DAO_store->social_twitter : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-twitter"></i></a>-->
					<a href="https://facebook.com/<?php echo (!empty($this->DAO_store->social_facebook)) ? $this->DAO_store->social_facebook : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-facebook-f"></i></a>
					<a href="https://pinterest.com/dreamdinners" class="text-decoration-hover-none font-size-large text-green-light" target="_blank"><i class="fab fa-pinterest"></i></a>

					<?php if (!empty($this->calendar['info']['session_type']['DELIVERY'])) { ?>
						<div class="mt-1">
							<i class="dd-icon icon-delivery text-green font-size-medium-large align-bottom"></i> <span class="font-italic">Home Delivery Available!</span>
						</div>
					<?php } ?>
				</div>
				<?php if ($this->DAO_store->hasPublicAddress()) { ?>
					<div class="col-12 mt-3 col-xl-4 mt-xl-0">
						<?php if (!empty($this->DAO_store->address_directions)) { ?>
							<h3 class="text-uppercase font-weight-bold text-center text-md-left">
								Store directions
							</h3>
							<div class="location-about">
								<?php echo nl2br($this->DAO_store->address_directions); ?>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			</div>

			<?php if ($this->DAO_store->hasPublicAddress()) { ?>
				<div class="row mb-3">
					<div class="col">
						<iframe
								class="border border-width-2-imp border-gray-500"
								width="100%"
								height="250"
								src="//www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_APIKEY; ?>&q=<?php echo (!empty($this->DAO_store->google_place_id)) ? 'place_id:' . $this->DAO_store->google_place_id : urlencode($this->DAO_store->address_linear); ?>" allowfullscreen>
						</iframe>
					</div>
				</div>
			<?php } ?>

			<div class="row mb-3">
				<?php if (!empty($this->DAO_store->bio_store_hours) || !empty($this->DAO_store->bio_store_holiday_hours)) { ?>
					<?php if (!empty($this->DAO_store->bio_store_hours)) { ?>
						<div class="col-12 col-md-6 mb-2 mb-md-0">
							<h3 class="text-uppercase font-weight-bold text-center text-md-left">
								Store hours
							</h3>
							<div>
								<?php echo nl2br($this->DAO_store->bio_store_hours); ?>
							</div>
						</div>
					<?php } ?>
					<?php if (!empty($this->DAO_store->bio_store_holiday_hours)) { ?>
						<div class="col-12 col-md-6 mb-2 mb-md-0">
							<h3 class="text-uppercase font-weight-bold text-center text-md-left">
								Holiday hours
							</h3>
							<div>
								<?php echo nl2br($this->DAO_store->bio_store_holiday_hours); ?>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if (!empty($this->DAO_store->store_description)) { ?>
					<div class="col-12">
						<h3 class="text-uppercase font-weight-bold text-center text-md-left">
							About our store
						</h3>
						<div>
							<?php echo nl2br($this->DAO_store->store_description); ?>
						</div>
					</div>
				<?php } ?>
			</div>

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

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
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-tacos-featured-kid-pick-400x400.webp" alt="Chicken Soft Tacos" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Soft Tacos</h5>
								</div>
							</div>
							<div class="card border-0 pr-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/manicotti-featured-pan-meal-400x400.webp" alt="Cheese Lovers Manicotti" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Cheese Lover’s Manicotti</h5>
								</div>
							</div>
					</div>
				</div>
				<div class="col-12 col-lg-6">
					<div class="card-group text-center">
						<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-yakitori-featured-30min-less-400x400.webp" alt="Chicken Yakitori" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Yakitori</h5>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/pulled-pork-featured-crock-pot-400x400.webp" alt="Pulled Pork BBQ Sandwiches" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Pulled Pork BBQ Sandwiches</h5>
								</div>
							</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<a href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/order" class="btn btn-primary btn-block">Start your order</a>
				</div>
			</div>
			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

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

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>