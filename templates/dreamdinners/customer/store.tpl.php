<?php $this->setScript('foot', '//maps.googleapis.com/maps/api/js?key=' . GOOGLE_APIKEY); ?>
<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - Location info"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true)); ?>
<?php $this->assign('order_process_navigation_page', 'session_menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1><?php echo $this->DAO_store->store_name; ?> Meal Prep Store</h1>
				<p class="font-marker">We offer real food, made from scratch, so your life can feel just a little easier.</p>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<section>
		<div class="container mb-5">

			<div class="row mb-3">
				<div class="col-12 col-lg-6 col-xl-5">
					<img src="/theme/dreamdinners/images/stores/<?php echo $this->DAO_store->id; ?>.webp" alt="Mill Creek" class="img-fluid w-100">
				</div>
				<div class="text-center text-lg-left col-12 mt-3 mt-lg-0 col-lg-6 col-xl-3">
					<p><?php echo$this->DAO_store->address_html; ?></p>
					<div class="d-md-none"><a href="tel:<?php echo $this->DAO_store->telephone_day; ?>"><?php echo $this->DAO_store->telephone_day; ?></a></div>
					<div class="d-none d-md-block"><?php echo $this->DAO_store->telephone_day; ?></div>
					<?php echo (!empty($this->DAO_store->telephone_sms)) ? 'Text us at '.$this->DAO_store->telephone_sms . '<br />' : ''; ?>
					<?php echo (!empty($this->DAO_store->email_address)) ? CTemplate::recaptcha_mailHideHtml($this->DAO_store->email_address, 'Email Store') . '<br />' : ''; ?>

					<a href="https://instagram.com/<?php echo (!empty($this->DAO_store->social_instagram)) ? $this->DAO_store->social_instagram : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-instagram"></i></a>
					<a href="https://twitter.com/<?php echo (!empty($this->DAO_store->social_twitter)) ? $this->DAO_store->social_twitter : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-twitter"></i></a>
					<a href="https://facebook.com/<?php echo (!empty($this->DAO_store->social_facebook)) ? $this->DAO_store->social_facebook : 'dreamdinners'; ?>" class="text-decoration-hover-none font-size-large text-green-light mr-3" target="_blank"><i class="fab fa-facebook-f"></i></a>
					<a href="https://pinterest.com/dreamdinners" class="text-decoration-hover-none font-size-large text-green-light" target="_blank"><i class="fab fa-pinterest"></i></a>

					<?php if (!empty($this->calendar['info']['session_type']['DELIVERY'])) { ?>
						<div class="mt-1">
							<i class="dd-icon icon-delivery text-green font-size-medium-large align-bottom"></i> <span class="font-italic">Home Delivery Available!</span>
						</div>
					<?php } ?>
				</div>
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
			</div>

			<div class="row mb-3">
				<div class="col">
					<iframe
							class="border border-width-2-imp border-gray-500"
							width="100%"
							height="250"
							src="//www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_APIKEY; ?>&q=<?php echo (!empty($this->DAO_store->google_place_id)) ? 'place_id:' . $this->DAO_store->google_place_id : urlencode($this->DAO_store->linear_address); ?>" allowfullscreen>
					</iframe>
				</div>
			</div>

			<div class="row mb-3">
				<?php if (!empty($this->DAO_store->bio_store_hours)) { ?>
					<div class="col">
						<h3 class="text-uppercase font-weight-bold text-center text-md-left">
							Store hours
						</h3>
						<div>
							<?php echo nl2br($this->DAO_store->bio_store_hours); ?>
						</div>
					</div>
				<?php } ?>
				<?php if (!empty($this->DAO_store->store_description)) { ?>
					<div class="col">
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
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini-pot-pies-featured-kid-pick-400x400.webp" alt="Mini Chicken Pot Pies" class="img-fluid">
							<div class="card-body">
								<h5 class="card-title my-0">Mini Chicken Pot Pies</h5>
							</div>
						</div>
						<div class="card border-0 pr-2">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-enchiladas-featured-pan-400x400.webp" alt="Chicken Enchiladas" class="img-fluid">
							<div class="card-body">
								<h5 class="card-title my-0">Chicken Enchiladas</h5>
							</div>
						</div>

					</div>
				</div>
				<div class="col-12 col-lg-6">
					<div class="card-group text-center">
						<div class="card border-0 pr-4">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/carne-asada-tacos-featured-under-30-400x400.webp" alt="Carne Asada Steak Tacos" class="img-fluid">
							<div class="card-body">
								<h5 class="card-title my-0">Carne Asada Steak Tacos</h5>
							</div>
						</div>
						<div class="card border-0">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-tikka-masala-featured-instant-pot-400x400.webp" alt="Chicken Tikka Masala" class="img-fluid">
							<div class="card-body">
								<h5 class="card-title my-0">Chicken Tikka Masala over Jasmine Rice</h5>
							</div>
						</div>
					</div>
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

	<section>
		<div class="container-fluid my-5">
			<div class="row mb-5">
				<div class="col-md-6 p-5 text-center mx-auto bg-gray">
					<h2 class="mb-4 font-weight-bold">Job Opportunities</h2>
					<p>
						Dream Dinners is an innovative concept in meal preparation that eliminates the stress of dealing with dinner – We remove menu planning, shopping & prep-work from the equation, leaving more quality time for families.
						We are looking for amazing team members to help us change more lives and bring Homemade, Made Easy meals into the community.
					</p>
					<?php if (!empty($this->DAO_store->job_positions_available)) { ?>
						<h4 class="text-uppercase font-weight-bold">Available Positions</h4>
						<p>Our store is hiring for the following positions</p>
						<div class="row">
							<?php foreach ($this->DAO_store->job_positions_available AS $job) { ?>
								<div class="col-12 mb-md-3">
									<div class="card">
										<div class="card-body bg-gray-light">
											<p class="card-text text-center"><?php echo $job['title']; ?></p>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
						<p class="mt-3">To apply for one of the positions above, please send a resume to <a href="mailto:<?php echo $this->DAO_store->email_address; ?>"><?php echo $this->DAO_store->email_address; ?></a></p>
					<?php } else { ?>
						<p>Join our Dream Dinners team. Feel free to submit your resume to <a href="mailto:<?php echo $this->DAO_store->email_address; ?>"><?php echo $this->DAO_store->email_address; ?></a>.</p>
					<?php } ?>
				</div>
				<div class="col-md-6 bg-green text-white text-center p-5 mx-auto">
					<h2>At Dream Dinners, <span class="font-weight-bold">our mission</span> is to make gathering around the family table a cornerstone of daily life. </h2>
				</div>
			</div>
		</div>
	</section>


<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>