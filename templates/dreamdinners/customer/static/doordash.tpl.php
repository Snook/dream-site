<?php $this->assign('page_title', 'DoorDash');?>
<?php $this->assign('page_description','Dream Dinners brings easy, prepared meals to families in your community. Our delicious dinners are prepped with quality ingredients in our local assembly kitchens.'); ?>
<?php $this->assign('page_keywords','prepared meals, prepared dinners, homemade dinner, homemade meals, prepped meals, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">

        <section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-local-meal-prep">
					<div class="col-md-5 text-center mx-auto py-2" style="background: rgba(255, 255, 255, 0.8)">
						<h1 class="my-4 font-weight-bold">Thanks for ordering Dream Dinners on DoorDash.</h1>
						<h3 class="mb-5">Are you ready to have more delicious, prepped dinners for days you want to cook at home? Order now to get more variety, value pricing, and exclusive access to extras in our Sides & Sweets Freezer.</h3>
						<a href="/main.php?page=locations" class="btn btn-primary mb-4 start-intro-offer">Find a Location</a>
					</div>
				</div>
			</div>
		</section>

		<!-- Menu Recommendations
        <section>
			<div class="container">
				<div class="row">
					<div class="col my-3">
						<div class="text-center">
							<h2 class="my-3">November Menu Recommendations</h2>
							<div class="border-bottom border-red mb-3 mx-5"></div>
						</div>
					</div>
				</div>
				<div class="row my-4">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/southern-shrimp-and-grits-circle-458x344.webp" alt="Southern Shrimp and Grits" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Southern Shrimp and Grits</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini-chicken-pot-pies-circle-458x344.webp" alt="Mini Chicken Pot Pies" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Mini Chicken Pot Pies</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/terracotta-chicken-circle-458x344.webp" alt="Terracotta Chicken with Pita and Hummus" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Terracotta Chicken with Pita and Hummus</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini-beef-tostada-cups-circle-458x344.webp" alt="Mini Beef Tostada Cups" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Mini Beef Tostada Cups</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row my-4">
					<div class="col text-center mb-5">
						<a href="/main.php?page=session_menu" class="btn btn-lg btn-primary">Get Cooking</a>
					</div>
				</div>
			</div>
		</section> -->

		<!-- The Dream Dinners Difference -->
		<section>
			<div class="container my-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-5 mb-4 font-weight-bold">The Dream Dinners Difference</h2>
							<div class="border-bottom border-green-dark mb-3 mx-5"></div>
						</div>
					</div>
				</div>

				<!-- 4 Steps -->
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
					<div class="col">
						<div class="card-group">
							<div class="card border-0 mx-1 text-center">
								<i class="dd-icon icon-eat_together font-size-extra-extra-large text-green m-4"></i>
							</div>
							<div class="card border-0 mx-1 text-left">
								<div class="card-body">
									<h5 class="card-title">Family Friendly</h5>
									<p class="card-text">From our founders, to our store-owners, family is at the forefront of everything we do. Our mission is to make gathering around the dinner table a cornerstone of daily life.</p>
								</div>
							</div>
							<div class="card border-0 mx-1 text-center">
								<i class="dd-icon icon-order_online font-size-extra-extra-large text-green m-4"></i>
							</div>
							<div class="card border-0 mx-1 text-left">
								<div class="card-body">
									<h5 class="card-title">Affordable and Convenient</h5>
									<p class="card-text">At an average of $7.50/serving, we are much more affordable than other meal kits and take-out. We are also more convenient, offering more monthly recipes. You can visit us in-store to assemble your meals amongst friends, pick up from your local assembly kitchen, or have our meals delivered right to your home.</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row my-2 mt-5">
					<div class="col">
						<div class="card-group">
							<div class="card border-0 mx-1 text-center">
								<i class="dd-icon icon-chef font-size-extra-extra-large text-green m-4"></i>
							</div>
							<div class="card border-0 mx-1 text-left">
								<div class="card-body">
									<h5 class="card-title">Chef-Curated Recipes</h5>
									<p class="card-text">With Dream Dinners, you get access to innovative recipes and a variety of flavors on the menu every month.</p>
								</div>
							</div>

							<div class="card border-0 mx-1 text-center">
								<i class="dd-icon icon-heart font-size-extra-extra-large text-green m-4"></i>
							</div>
							<div class="card border-0 mx-1 text-left">
								<div class="card-body">
									<h5 class="card-title">More Time for Things that Matter</h5>
									<p class="card-text">We do all of the shopping, chopping, prepping, and clean up. Most of our meals cook in less than 30 minutes, leaving you more time to do the things you love.</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row my-4">
					<div class="col text-center">
						<a href="/main.php?static=how_it_works" class="btn btn-primary">Learn More</a>
					</div>
				</div>
			</div>
		</section>



		<!-- Three ways to serve -->
		<section>
			<div class="container-fluid my-5 bg-cyan-dark">
				<div class="container">
					<div class="row">
						<div class="col">
							<div class="text-center text-white">
								<h2 class="my-4">Three Convenient Ways to Serve You</h2>
								<div class="border-bottom border-cyan mb-3 mx-5"></div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="card-group text-center text-white">

								<div class="card bg-cyan-dark border-0">
									<i class="dd-icon icon-cooler font-size-extra-extra-large text-white m-4"></i>
									<div class="card-body">
										<h5 class="card-title">Store Pick Up</h5>
										<p>Visit one of our local stores to quickly and easily pick up your prepared meals.</p>
									</div>
								</div>
								<div class="card bg-cyan-dark border-0">
									<i class="dd-icon icon-delivery font-size-extra-extra-large text-white m-4"></i>
									<div class="card-body">
										<h5 class="card-title">Home Delivery</h5>
										<p>For ultimate convenience, get your fully-prepped meals delivered to your door.</p>
									</div>
								</div>
								<div class="card bg-cyan-dark border-0">
									<i class="dd-icon icon-measuring_cup font-size-extra-extra-large text-white m-4"></i>
									<div class="card-body">
										<h5 class="card-title">Store Assembly</h5>
										<p>At your local store, assemble and customize your prepared dinners using our easy-to-follow recipes.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row my-4 pb-4">
						<div class="col text-center text-white">
							<p><a href="/main.php?page=locations" class="btn btn-cyan text-white">Find a Location</a></p>
							<p><em>*Availability varies by location. Check your local stores calendar to find available ordering options.</em></p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- Testimonials with Video -->
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 mb-6 text-left">
							<h3 class="my-4">OUR GUESTS LOVE US</h3>
						<p>Hear how our guest Kellie uses our prepared dinners to make dinnertime easier for her family.</p>

						<p><a href="/main.php?page=session_menu" data-gaq_cat="original" data-gaq_action="Order Now" data-gaq_label="Landing Page" class="btn btn-lg btn-primary">Order Now</a></p>
					</div>
					<div class="col-md-6 text-right">
						<div class="embed-responsive embed-responsive-16by9">
						<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/IBMWQ1Z5sDw?rel=0&amp;controls=0" allowfullscreen></iframe>
					</div>
				</div>
			  </div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>