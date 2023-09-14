<?php $this->assign('no_cache', true); ?>
<?php $this->assign('page_title', 'Gift');?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>
	<main role="main">
		<!-- Header -->
		<section>
				<div class="container-fluid my-5">
					<div class="row hero-double">
						<div class="col-md-6 text-left p-5 my-5">
							<h1 class="font-weight-bold mt-2">Gift Dinners to Friends and Family</h1>
							<p class="text-uppercase mb-4">Show you care by gifting your family and friends both near and far with easy, delicious meals. The perfect gift for any occasion like welcoming a new baby, a birthday, housewarming, or sympathy. Take something off their plate by having dinner ready to cook and delivered to their door.</p>
							<!--<a href="/gift-card-order" class="btn btn-lg btn-green">Purchase Gift Card</a>-->
						</div>
						<div class="hero-double__right col-md-6 p-0">
							<figure>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/gift-landing-page-header-957x657.webp" alt="Dream Dinners Lifestyle Session" class="img-fluid">
							</figure>
						</div>
					</div>
				</div>
			</section>

			<!-- Three ways to gift-->
			<section>
			<div class="container">
				<div class="row my-5 text-center">
                	<div class="col mb-2">
						<h2 class="font-weight-bold"><strong>Three Ways to Gift</strong></h2>
					</div>
				</div>
				<div class="row my-3">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chelsee-hood-458x344.webp" alt="Dream Dinners Home Delivery" class="img-fluid" />
								<div class="card-body">
									<h5 class="card-title">Delivered to their Door</h5>
									<p class="card-text">Choose home delivery from one of our local stores to have delicious dinners delivered to their home. *Available within 20 miles of most locations.</p>
									<p><a href="/locations" class="btn btn-primary">Check Your Local Store</a></p>
								</div>
							</div>
							<div class="card border-0 mx-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/dinner-for-a-night-458x344.webp" alt="Dinner for a Night" class="img-fluid" />
								<div class="card-body">
									<h5 class="card-title">Dinner for a Night</h5>
									<p class="card-text">Add an extra meal or two to your monthly order to gift. Want to gift them more? Place a home delivery order from one of our assembly kitchens.</p>
									<p><a href="/locations" class="btn btn-primary">Contact Your Local Store</a></p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<a href="/gift-card-order"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/dream-dinners-gift-card-458x344.webp" alt="Dream Dinner Gift Cards" class="img-fluid" /></a>
								<div class="card-body">
									<h5 class="card-title">Gift Card</h5>
									<p class="card-text">Not sure what meals they would enjoy. A Dream Dinners gift card is the perfect solution.</p>
									<p><a href="/gift-card-order" class="btn btn-primary">Purchase Gift Card</a></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>
<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>