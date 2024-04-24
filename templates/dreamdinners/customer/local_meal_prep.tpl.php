<?php $this->assign('page_title', 'Local Meal Prep');?>
<?php $this->assign('page_description','Dream Dinners provides delicious, local meal prep to help families in your community gather around the dinner table. '); ?>
<?php $this->assign('page_keywords','local meal prep, local meal prep company, local meal kit, local meal prep service'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
	<section>
		<!--<section>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 text-left p-5 my-5">
						<h1 class="font-weight-bold font-have-heart-two mt-2">No idea what to cook this week?</h1>
						<p class="text-uppercase mb-4">No problem.<br>We planned and prepped your meals for you!</p>
						<a href="/locations" class="btn btn-lg btn-green">Order Now</a>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/may24-local-meal-prep-collage-957x657.webp" alt="May Menu" class="img-fluid">
					</div>

				</div>
			</div>
		</section>-->
			<div class="container-fluid">
				<div class="row">
					<div class="col text-center">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/dinner-reinvented-enchiladas-1400x600.webp" alt="Dinner Reinvented" class="img-fluid">
					</div>
				</div>
			</div>
		</section>
		<!-- offer -->
		<section class="bg-cyan-dark">
			<div class="container mp-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-5 mb-1 font-weight-bold text-center text-white">Exclusive May New Customer Offer</h2>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/white-swash-320x12.webp" alt="swash">
							<h3 class="mt-1 mb-5 font-weight-bold text-center text-white">Try Us Today with One of the Following Offers</h3>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container my-5">
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 py-4 px-4 mx-1 text-center">
									<div class="card-body text-center">
										<img src="<?php echo IMAGES_PATH; ?>/landing_pages/dream-dinners-delivered-458x344.webp" alt="Shipped to your door" class="img-fluid mb-3" />
									</div>
									<div class="card-body">
										<h3 class="font-weight-bold font-have-heart-two mt-2 font-size-extra-extra-large">FREE SHIPPING</h3>
										<p>$14.99 Value </p>
										<p>Use code: <span class="font-weight-bold font-have-heart-two font-size-extra-large">SHIPONUS</span></p>
										<!--<p class="my-3"><a href="/locations" class="btn btn-lg btn-green">ORDER NOW</a></p>-->
									</div>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="card-group">
								<div class="card border-0 py-4 px-4 mx-1 text-center">
									<div class="card-body text-center">
										<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chelsee-hood-458x344.webp" alt="Dream Dinners Home Delivery" class="img-fluid mb-3" />
									</div>
									<div class="card-body">
										<h3 class="font-weight-bold font-have-heart-two mt-2 font-size-extra-extra-large">FREE DELIVERY</h3>
										<p>Approx. $20 Value</p>
										<p>Use code: <span class="font-weight-bold font-have-heart-two font-size-extra-large">DELIVERFREE</span></p>
										<!--<p class="my-3"><a href="/locations" class="btn btn-lg btn-green">ORDER NOW</a></p>-->
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section>
			<div class="container-fluid my-5 bg-green-light">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-5 text-center mt-5">
						  <h2 class="font-weight-bold font-have-heart-two font-size-extra-extra-large"><strong>MEALS IN A SNAP</strong></h2>
						  <p>Our ready-to-cook meals are fully prepped with simple step-by-step instructions. Choose from options like grill, air fryer or instant pot that cook in under 30 minutes and fit into your busy life.</p>
						  <form action="/locations" method="post">
						<div class="form-group mx-auto">
							<div class="input-group">
								<!--<div class="input-group-prepend">
									<div class="input-group-text">
									Find a location near you
									</div>
								</div>-->
								<input type="number" class="form-control" id="zip" name="zip" placeholder="Postal code">
								<div class="input-group-append">
									<button type="submit" value="Get Started" class="btn btn-primary">View Local Menu</button>
								</div>
							</div>
						</div>
					</form>
						</div>
						<div class="col-md-7 mb-6">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/air-fryer-mom-cooking-stovetop-2-images.webp" alt="Air Fryer Chicken Tenders and mom cooking on the stove" class="img-fluid" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Menu Highlights-->
		<section>
			<div class="container my-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-4 mb-4 font-weight-bold font-have-heart-two font-size-extra-extra-large">ON THE MENU THIS MONTH</h2>
						</div>
					</div>
				</div>

				<div class="row my-5">
					<div class="col">
						<div class="card-group text-center mb-2">
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/sizzling-sirloin-fried-rice-featured-under-30min-400x400.webp" alt="Sizzling Sirloin Fried Rice" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Sizzling Sirloin Fried Rice</h5>
								</div>
							</div>
							<div class="card border-0 pr-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-enchiladas-featured-pan-400x400.webp" alt="Chicken Enchiladas" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Enchiladas</h5>
								</div>
							</div>
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/maple-bourbon-chicken-featured-instant-pot-400x400.webp" alt="Maple Bourbon BBQ Chicken with Bacon" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Maple Bourbon BBQ Chicken with Bacon &amp; Bacon Ranch Green Beans</h5>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/salsa-ranch-chicken-featured-kid-400x400.webp" alt="Crispy Salsa Ranch Chicken with Mexican Street Corn" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Crispy Salsa Ranch Chicken with Mexican Street Corn</h5>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container-fluid my-5 bg-cyan-dark">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-5 text-center mt-5">
						  <h2 class="font-weight-bold font-have-heart-two font-size-extra-extra-large"><strong>LOCALLY OWNED</strong></h2>
						  <p>Our small business owners are here to serve you. Whether your meals are shipped, delivered locally or picked up at our community kitchen, they are ready to support you.</p>
						  <form action="/locations" method="post">
						<div class="form-group mx-auto">
							<div class="input-group">
								<!--<div class="input-group-prepend">
									<div class="input-group-text">
									Find a location near you
									</div>
								</div>-->
								<input type="number" class="form-control" id="zip" name="zip" placeholder="Postal code">
								<div class="input-group-append">
									<button type="submit" value="Get Started" class="btn btn-primary">Find Local Delivery Options</button>
								</div>
							</div>
						</div>
					</form>
						</div>
						<div class="col-md-7">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/locally-owned-instore-2-images.webp" alt="Local Store" class="img-fluid" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container-fluid my-5">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-7">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/1044_slow_roasted_carolina_bbq_pork_tenderloin_458x344.webp" alt="Slow Roasted Carolina BBQ Pork Tenderloin" class="img-fluid" />
							</div>
						</div>
						<div class="col-md-5 text-center mt-4">
						  <h2 class="font-weight-bold font-have-heart-two font-size-extra-extra-large"><strong>DELICIOUS RECIPES</strong></h2>
						  <p>Our monthly menu has a variety of tasty meals to fit your family’s needs. We assemble your meals just for you from fresh ingredients, then freeze them for optimal freshness. This means they are ready to cook and enjoy whenever you need them.</p>
						  <a href="/browse-menu" class="btn btn-lg btn-primary btn-cyan-dark">VIEW MENU</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Testimonials -->
		<section>
			<div class="container-fluid">
				<div class="row mt-4">
					<div class="col text-center">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/customer-testimonials-1400x600.webp" alt="customer testimonials" class="img-fluid">
					</div>
				</div>
			</div>
		</section>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>