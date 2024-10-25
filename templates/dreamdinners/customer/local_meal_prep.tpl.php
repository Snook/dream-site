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
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/dinner-reinvented-1400x400.webp" alt="Dinner Reinvented" class="img-fluid">
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
						<div class="col-md-7 mb-3 mt-3 text-right">
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
			<div class="container my-5"id="menu">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-4 mb-4 font-weight-bold font-have-heart-two font-size-extra-extra-large">FEATURED ON THE MENU THIS MONTH</h2>
						</div>
					</div>
				</div>

				<div class="row my-5">
					<div class="col">
						<div class="card-group text-center mb-2">
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/sirloin-fried-rice-under30-orng-featured-menu-item-400x400.webp" alt="Sizzling Sirloin Fried Rice" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Sizzling Sirloin Fried Rice</h5>
								</div>
							</div>
							<div class="card border-0 pr-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-chili-crockpot-orng-featured-menu-item-400x400.webp" alt="Chicken and White Bean Chili" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken and White Bean Chili</h5>
								</div>
							</div>
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/bacon-mac-3-cheese-pan-featured-menu-item-400x400.webp" alt="Bacon Mac N Three Cheese Bake" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Bacon Mac N Three Cheese Bake</h5>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/meatloaf-milano-air-fryer-featured-menu-item-400x400.webp" alt="Meatloaf Milano with Mashed Potatoes" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Meatloaf Milano with Mashed Potatoes</h5>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container-fluid my-5 bg-cyan-dark" id="local">
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
						<div class="col-md-7 text-right mb-3 mt-3">
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
						<div class="col-md-7 text-left">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/orange-asian-chicken-450x300.webp" alt="Orange Chicken" class="img-fluid" />
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
				<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
				<div class="row mt-4">
					<div class="col text-center">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/customer-testimonials-1400x600.webp" alt="customer testimonials" class="img-fluid">
					</div>
				</div>
				<!--<div class="row mt-4">
					<div class="col text-center">
						<p class="font-italic">Offer valid for new customers or customers that have not placed a Dream Dinners order in more than 12 months. Offer cannot be combined with Dinner Dollars, other coupons or offers. No cash value. Offer can be redeemed once per guest and is not transferrable. Offer expires August 31, 2024. Valid only at participating locations.</p>
					</div>
				</div>-->
			</div>
		</section>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>