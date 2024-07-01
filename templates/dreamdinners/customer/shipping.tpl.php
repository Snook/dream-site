<?php $this->setScript('foot', SCRIPT_PATH . '/customer/locations.min.js'); ?>
<?php $this->assign('page_title', 'Shipping at Dream Dinners');?>
<?php $this->assign('page_description','Real food made from scratch shipped to your door.'); ?>
<?php $this->assign('page_keywords','shipped prepared meals, prepared dinners shipped, shipping homemade meals, homemade meals shippeded, shipped prepped meals, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>


	<!--<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">

			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-center">

			</div>
		</div>
	</header>-->

	<main role="main">

		<!--<section>
			<div class="container-fluid">
				<div class="row mb-5">
					<div class="col text-center">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/crispy-shrimp-green-beans-in-a-snap-header-1920x600.webp" alt="Family making and eating a meal together" class="img-fluid" />
					</div>
				</div>
			</div>
		</section>-->
		<section>
			<div class="container">
					<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
					  <ol class="carousel-indicators">
						<li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
						<li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
						<li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
						<li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
					  </ol>
					  <div class="carousel-inner">
						<div class="carousel-item active">
						  <a href="/local-meal-prep"><img class="d-block w-100" src="<?php echo IMAGES_PATH; ?>/landing_pages/new-guest-july24-free-shipping-1400x600.webp?auto=yes" alt="New Customer Exclusive Offer"></a>
						</div>
						<div class="carousel-item">
						  <a href="/locations"><img class="d-block w-100" src="<?php echo IMAGES_PATH; ?>/landing_pages/existing-guest-july24-shipping-1400x600.webp?auto=yes" alt="Summer Shipping"></a>
						</div>
						<div class="carousel-item">
						  <img class="d-block w-100" src="<?php echo IMAGES_PATH; ?>/landing_pages/summer-family-shipping-in-a-snap-collage-1400x600.webp?auto=yes" alt="Dinner in a Snap">
						</div>
						<div class="carousel-item">
						  <img class="d-block w-100" src="<?php echo IMAGES_PATH; ?>/landing_pages/travel-may24-1400x600.webp?auto=yes" alt="Travel with Dream Dinners">
						</div>
					  </div>
					  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
						<span class="carousel-control-prev-icon" aria-hidden="true"></span>
						<span class="sr-only">Previous</span>
					  </a>
					  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
						<span class="carousel-control-next-icon" aria-hidden="true"></span>
						<span class="sr-only">Next</span>
					  </a>
					</div>
			</div>
		</section>
		<section>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 text-left p-5 my-5">
						<h1 class="font-weight-bold font-have-heart-two font-size-extra-large mt-2">Get Dream Dinners Shipped to Your Door</h1>
						<p class="text-uppercase mb-4">Real food made from scratch, so your life can feel a little easier.</p>
						<p>Four easy, homemade meals prepped and ready to cook, most in less than 30 minutes. Order when you want, no subscription necessary. Enter your zip code to see if we can deliver to you. Great for travel or a gift for new baby, new home, birthday and more!</p>
						<form action="/locations" method="post" class="form-shipping-search needs-validation" novalidate>
							<div class="form-group mx-auto">
								<div class="input-group">
									<div class="input-group-append">
										<span class="input-group-text">Zip code</span>
									</div>
									<input type="number" class="form-control form-shipping-search-zip" required>
									<div class="input-group-append">
										<button type="submit" value="Get Started" class="btn btn-primary">View menu</button>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/jul24-shipping-collage-957x657.webp" alt="Shipping Menu" class="img-fluid">
					</div>

				</div>
			</div>
		</section>
		<!-- customer data -->
		<section class="bg-cyan-dark">
			<div class="container my-5 mp-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-5 mb-4 font-weight-bold text-center text-white">Our Customers Love Us</h2>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<div class="card-group text-center text-white my-5 mp-5">
							<div class="card border-0 bg-cyan-dark">
								<div class="card-body mb-2">
									<i class="dd-icon icon-star font-size-extra-extra-large text-white m-5"></i>
									<h3 class="font-weight-bold font-size-large mt-3">4.6<br>VALUE RATING</h3>
								</div>
							</div>
							<div class="card border-0 bg-cyan-dark">
								<div class="card-body mb-2">
									<i class="dd-icon icon-star font-size-extra-extra-large text-white mb-5"></i>
									<h2 class="font-weight-bold font-size-large mt-3">4.9<br>QUALITY RATING</h2>
								</div>
							</div>
							<div class="card border-0 bg-cyan-dark">
								<div class="card-body mb-2">
									<i class="dd-icon icon-cart font-size-extra-extra-large text-white mb-5"></i>
									<h3 class="font-weight-bold font-size-large mt-3">100%<br>WILL ORDER AGAIN!</h3>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section>
			<div class="container-fluid my-5">
				<div class="col-sm-12 text-center"><h2 class="mb-1 mt-4 font-weight-bold">Order In a Snap</h2></div>
				<div class="row">
					<div class="col-md-6 text-center p-5 my-2">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/family-grilling-600x350.webp" alt="Family eating outside" class="img-fluid">
						<h3 class="font-weight-bold my-2">Grill Box</h3>
						<p class="mb-4">The perfect easy meal plan for summer including Sweet Cider BBQ Chicken with Oven Roasted Broccoli, Terracotta Chicken with Pita & Hummus, Campfire Grilled Pork Chops with Sweet Potato Fries, and Al Pastor Pork Street Tacos.</p>
					</div>
					<div class="col-md-6 text-center p-5 my-2">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/new-baby-600x350.webp" alt="Couple with new baby and moving boxes" class="img-fluid">
						<h3 class="font-weight-bold my-2">New Baby Gift Box</h3>
						<p class="mb-4">Congratulate your friends and family on their new addition with easy, quick dinners including Chicken Paella, Chicken Yakitori with Jasmine Rice, Campfire Grilled Pork Chops with Sweet Potato Fries, and Sweet Cider BBQ Chicken with Oven Roasted Broccoli.</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-center p-5 my-2">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/family-camping-600x350.webp" alt="Family camping" class="img-fluid">
						<h3 class="font-weight-bold my-2">Vacation Ready Box</h3>
						<p class="mb-4">Let us take care of your vacation meal planning with delicious, simple dinners including Terracotta Chicken with Pita & Hummus, Summer Grilled BBQ Chicken Pizza, Campfire Grilled Pork Chops with Sweet Potato Fries, and Steak & Chicken Fajitas. Plus, get a sweet morning treat with six of our Breakfast Scones.</p>
					</div>
					<div class="col-md-6 text-center p-5 my-2">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/woman-broken-leg-600x350.webp" alt="Woman with broken leg on couch" class="img-fluid">
						<h3 class="font-weight-bold my-2">Get Well Box</h3>
						<p class="mb-4">Send your love when your friends and family aren’t feeling their best with easy, wholesome dinners including Sweet Cider BBQ Chicken with Oven Roasted Broccoli, Terracotta Chicken with Pita & Hummus, Chicken Yakitori with Jasmine Rice, and Steak & Chicken Fajitas. Plus, six of our delicious Hope’s Cookies to help put a smile on their face.</p>
					</div>
				</div>
			</div>
		</section>
		<section class="bg-green-light">
			<div class="container my-5 mp-5">
				<div class="row">
					<div class="col offset-md-4 col-md-6 mx-lg-auto mt-3 mb-3 text-center">
					<h2 class="font-weight-bold font-have-heart-two font-size-extra-large mt-2">Ready to start cooking?</h2>
						<p class="text-uppercase mb-3">Enter your zip code to see the menu.</p>
						<form action="/locations" method="post" class="form-shipping-search needs-validation" novalidate>
							<div class="form-group mx-auto">
								<div class="input-group">
									<div class="input-group-append">
										<span class="input-group-text">Zip code</span>
									</div>
									<input type="number" class="form-control form-shipping-search-zip" required>
									<div class="input-group-append">
										<button type="submit" value="Get Started" class="btn btn-primary">View menu</button>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</section>				
		<section>
		<!--<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>-->
			<div class="container mb-5">
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
									<i class="dd-icon icon-table_setting font-size-extra-extra-large text-green m-4"></i>
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body">
										<h5 class="card-title">Convenient</h5>
										<p class="card-text">Our meals are fully prepped and ready to cook for more convenience and options.</p>
									</div>
								</div>
								<div class="card border-0 mx-1 text-center">
									<i class="dd-icon icon-calendar_add font-size-extra-extra-large text-green m-4"></i>
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body">
										<h5 class="card-title">Absolutely No Commitment</h5>
										<p class="card-text">We won't charge your card monthly. No hassle of pausing or canceling a subscription. Simply order your meals and place another order when you're ready.</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row my-2 mt-5">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1 text-center">
									<i class="dd-icon icon-eat_together font-size-extra-extra-large text-green m-4"></i>
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body">
										<h5 class="card-title">Over 20 Years’ Experience</h5>
										<p class="card-text">We aren't a fad. Dream Dinners has been around for 22 years with tried and true recipes our customers love. Unlike other meal kits, when you choose Dream Dinners, you choose tradition.</p>
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

					<div class="row my-5">
						<div class="col text-center">
							<a href="/how-it-works" class="btn btn-primary">Learn More</a>
							<p>&nbsp;</p>
						</div>
					</div>
				</div>
			</div>
		</section>


		<!--<div class="border-bottom border-green-dark mb-3 mx-5"></div>
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 mb-6 text-left">
						<h3 class="my-4">OUR GUESTS LOVE US</h3>
						<p>Hear how our guest Kellie uses our prepared dinners to make dinnertime easier for her family.</p>

						<p><a href="/session-menu" data-gaq_cat="original" data-gaq_action="Order Now" data-gaq_label="Landing Page" class="btn btn-lg btn-primary">Order Now</a></p>
					</div>
					<div class="col-md-6 text-right">
						<div class="embed-responsive embed-responsive-16by9">
							<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/IBMWQ1Z5sDw?rel=0&amp;controls=0" allowfullscreen></iframe>
						</div>
					</div>
				</div>
			</div>
		</section>-->
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>