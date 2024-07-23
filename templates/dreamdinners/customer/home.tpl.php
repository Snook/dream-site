<?php $this->assign('canonical_url', HTTPS_BASE); ?>
<?php $this->assign('page_title', 'Homemade, Made Easy®'); ?>
<?php $this->assign('page_description', 'Dream Dinners brings easy, prepped dinners to families in the communities we serve. Our delicious meals are prepared with quality ingredients in our local assembly kitchens.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<!--<section>
			<div class="container-fluid">
				<div class="row">
					<div class="col text-center" style="max-height: 25rem; overflow: hidden;">
						<img src="<?php echo IMAGES_PATH; ?>/home_content/carne-asada-in-a-snap-header-1920x600.webp" alt="Carne Asada Steak Tacos in a Snap" class="img-fluid">
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
						  <a href="/local-meal-prep"><img class="d-block w-100" src="<?php echo IMAGES_PATH; ?>/landing_pages/new-guest-july24-free-shipping-dollars-off-store-1400x600.webp?auto=yes" alt="New Customer Exclusive Offer"></a>
						</div>
						<div class="carousel-item">
						  <a href="/session-menu"><img class="d-block w-100" src="<?php echo IMAGES_PATH; ?>/landing_pages/existing-guest-june24-delivery-1400x600.webp?auto=yes" alt="Summer home delivery"></a>
						</div>
						<div class="carousel-item">
						  <a href="/locations"><img class="d-block w-100" src="<?php echo IMAGES_PATH; ?>/landing_pages/existing-guest-july24-shipping-1400x600.webp?auto=yes" alt="Summer shipping"></a>
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
			<div class="container my-5">
				<div class="col-lg-10 mx-lg-auto">
					<h1 class="font-weight-bold font-have-heart-two font-size-extra-extra-large text-center">Real food made from scratch, so your life can feel a little easier</h1>
					<p class="text-uppercase">We do all of the shopping, chopping, prepping, and clean up. Most of our meals cook in less than 30 minutes, leaving you more time to do the things you love. <br>Find your local options to get started.</p>
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
									<button type="submit" value="Get Started" class="btn btn-primary">Get started</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</section>
<!-- Menu Highlights-->
		<section>
		<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container my-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-4 mb-4 font-weight-bold">Meals Made In A Snap</h2>
						</div>
					</div>
				</div>

				<div class="row my-5">
					<div class="col">
						<div class="card-group text-center mb-2">
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/pubstyle-chicken-30min-teal-featured-400x400.webp" alt="Pub Style Chicken with Mashed Potatoes" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Pub Style Chicken with Mashed Potatoes</h5>
								</div>
							</div>
							<div class="card border-0 pr-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/arroz-con-pollo-featured-pan-meal-teal-400x400.webp" alt="Arroz Con Pollo" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Arroz Con Pollo</h5>
								</div>
							</div>
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/italiano-chicken-orzo-featured-instant-pot-400x400.webp" alt="Italiano Chicken and Orzo" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Italiano Chicken and Orzo</h5>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/creamy-buffalo-chicken-sandwich-featured-new-teal-400x400.webp" alt="Creamy Buffalo Chicken Sandwiches" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Creamy Buffalo Chicken Sandwiches</h5>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div>
					<div class="col">
						<div class="text-center">
							<a href="/locations" class="btn btn-lg btn-primary">See Your Local Menu Options</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Content Selector-->
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container">
				<h2 class="text-center font-weight-bold my-5">What Dinner Solution are You Looking For?</h2>

				<div class="row mb-5 justify-content-center">

					<div class="col-12 col-lg-5 col-xl-4 mb-3 mb-lg-0">

						<ul class="nav justify-content-around justify-content-md-between">
							<li class="nav-item mb-3">
								<a class="m-auto nav-link text-uppercase text-nowrap font-weight-bold rounded-circle bg-green-light text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" id="convenient-tab" data-urlpush="false" data-toggle="tab" data-target="#convenient" href="/?tab=convenient" role="tab" aria-controls="convenient" aria-selected="true">
									<!--<i class="dd-icon icon-plate position-absolute text-green-light" style="font-size: 10rem;"></i>-->
									<span class="position-absolute">Convenience</span>
								</a>
							</li>
							<li class="nav-item mb-3">
								<a class="m-auto nav-link text-uppercase text-nowrap font-weight-bold rounded-circle bg-cyan-dark text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" id="flexible-tab" data-urlpush="false" data-toggle="tab" data-target="#flexible" href="/?tab=flexible" role="tab" aria-controls="flexible" aria-selected="false">
									<!--<i class="dd-icon icon-plate position-absolute text-cyan" style="font-size: 10rem;"></i>-->
									<span class="position-absolute">Flexibility</span>
								</a>
							</li>
							<li class="nav-item mb-3">
								<a class="m-auto nav-link text-uppercase text-nowrap font-weight-bold rounded-circle bg-orange text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" id="quality-tab" data-urlpush="false" data-toggle="tab" data-target="#quality" href="/?tab=quality" role="tab" aria-controls="quality" aria-selected="false">
									<!--<i class="dd-icon icon-plate position-absolute text-cyan-light" style="font-size: 10rem;"></i>-->
									<span class="position-absolute">Quality</span>
								</a>
							</li>
							<li class="nav-item mb-3">
								<a class="m-auto nav-link text-uppercase text-nowrap font-weight-bold rounded-circle bg-green-dark text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" id="easy-tab" data-urlpush="false" data-toggle="tab" data-target="#easy" href="/?tab=easy" role="tab" aria-controls="easy" aria-selected="false">
									<!--<i class="dd-icon icon-plate position-absolute text-orange" style="font-size: 10rem;"></i>-->
									<span class="position-absolute">Easy</span>
								</a>
							</li>
						</ul>
					</div>

					<div class="col-12 col-lg-7 col-xl-8">

						<div class="tab-content" id="">
							<div class="tab-pane fade show active" id="convenient" role="tabpanel" aria-labelledby="convenient-tab">
								<h2><strong>Convenience for Your Busy Schedule</strong></h2>
								<p>Prepped dinners ready to cook in your fridge and freezer make those crazy weeknights a breeze.</p>
								<ul>
									<li>We take care of the meal Planning, shopping and chopping to save you time. Just cook and enjoy.</li>
									<li>We offer local pick up, in-store assembly or home delivery, so getting your meals is as easy as cooking them.</li>
									<li>Breakfast, sides and more are always available in our Sides &amp; Sweets Freezer making us a one-stop shop.</li>
								</ul>
								<!--<div class="col text-center">
									<a href="/convenience" class="btn btn-primary btn-block">Learn More</a>
								</div>-->
							</div>
							<!-- end first tab -->

							<div class="tab-pane fade" id="flexible" role="tabpanel" aria-labelledby="flexible-tab">
								<h2><strong>Flexibility for Whatever Life Throws Your Way</strong></h2>
								<p>Meal planning doesn’t mean you can’t switch up your plans with frozen meals ready to go when you need them.</p>
								<ul>
									<li>Start by ordering just 3 dinners, then come back for more as often as you need.</li>
									<li>Cook-from-frozen and quick thaw dinners mean you always have a homemade meal ready to go.</li>
									<li>Choose from a mix of flavor profiles and proteins, so each dinner is a new adventure</li>
								</ul>
								<!--<div class="col text-center">
									<a href="/flexibility" class="btn btn-primary btn-block">Learn More</a>
								</div>-->
							</div>
							<!-- end second tab -->

							<div class="tab-pane fade" id="quality" role="tabpanel" aria-labelledby="quality-tab">
								<h2><strong>Quality without the Waste</strong></h2>
								<p>Delicious, quality dinners at home without the added waste of meal kits or the grocery store.</p>
								<ul>
									<li>Made with Restaurant-quality ingredients that are frozen for convenience.</li>
									<li>Prepped with only what you need – don’t get stuck with a whole jar of turmeric or veggies that go bad.</li>
									<li>No extra packaging like coolers and ice packs that you find in other meal kits.*</li>
								</ul>
								<!--<div class="col text-center">
									<a href="/quality" class="btn btn-primary btn-block">Learn More</a>
								</div>-->
								<p class="font-italic text-center pt-2">*Does not include our shipped product.</p>
							</div>
							<!-- end third tab -->

							<div class="tab-pane fade" id="easy" role="tabpanel" aria-labelledby="easy-tab">
								<h2><strong>Easy Dinners for the Whole Family</strong></h2>
								<p>Quick and easy dinners designed with families in mind, so you don’t have to worry about "What’s for Dinner".</p>
								<ul>
									<li>Most of our menu every month cooks in under 30 minutes saving you time each night.</li>
									<li>Customize your meals in our store during prep or at home while cooking for those picky eaters.</li>
									<li>Easy-to-follow cooking instructions and no-mess pan meals means you have a solution for whatever the day may bring.</li>
								</ul>
								<!--<div class="col text-center">
									<a href="/ease" class="btn btn-primary btn-block">Learn More</a>
								</div>-->
							</div>
							<!-- end fourth tab -->
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 text-left p-5 my-5">
						<h2 class="font-weight-bold mt-2">August Fan Favorites</h2>
						<p class="text-uppercase mb-4">Start your order with our August Fan Favorites Bundle. These 4 dinners are the perfect easy weeknight meal plan. Bundle includes Pub Style Chicken with Mashed Potatoes, Chateau Chicken with Almond Butter Sauce and Orzo, Cashew Chicken and Noodles, and Old Fashioned Meatloaf. Shipping menu items may vary.</p>
						<a href="/session-menu" class="btn btn-lg btn-green">Order Now</a>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/aug24-fan-fav-bundle-collage-957x657.webp" alt="Grill Bundle" class="img-fluid">
					</div>

				</div>
			</div>
		</section>
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/aug24-back-to-school-bundle-collage-circles-957x657.webp" alt="Specials" class="img-fluid">
					</div>
					<div class="col-md-6 text-left p-5 my-5">
						<h2 class="font-weight-bold mt-2">Back to School Bundle</h2>
						<p class="text-uppercase mb-4">Get ready to head back to school with these quick and easy kid picks. Our bundle includes BBQ Chip Chicken Tenders with Crispy Shoestring Fries, Italian Stuffed Shells and Chicken Carbonara Bake. Available at select locations, while supplies last. Not available for shipping.</p>
						<a href="/session-menu" class="btn btn-lg btn-green">Order Now</a>
					</div>


				</div>
			</div>
		</section>
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 text-left p-5 my-5">
						<h2 class="font-weight-bold mt-2">Share Easy Dinners with Your Friends</h2>
						<p class="text-uppercase mb-4">You can earn rewards when you share Dream Dinners with your friends and family. They get the convenience of ready-to-cook dinners and you earn rewards.</p>
						<a href="/share" class="btn btn-lg btn-green">Learn How</a>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/friends-dinner-together-957x657.webp" alt="Friends Dinner" class="img-fluid">
					</div>

				</div>
			</div>
		</section>

		<!-- Instagram -->
		<section>
			<div class="container-fluid my-5">
				<div class="row px-2 px-lg-5 py-5 bg-light">
					<div class="col-lg-8 offset-lg-2 text-center">
						<h2 class="text-center"><strong>Our Guests Love to Get Cooking </strong></h2>
						<p>Tag us @DreamDinners to be featured on our website.
						<div class="row my-2">
							<div class="col">
								<div class="card-group text-center my-5 mp-5">
									<div class="card border-0 bg-light pr-2">
										<div class="card-body">
											<img class="img-fluid mb-0" src="<?php echo IMAGES_PATH; ?>/home_content/chelsea-hood-instagram-images-400x400.webp" alt="@chelsea_hood">
											<p class="card-text mb-3">@chelsea_hood</p>
										</div>
									</div>
									<div class="card border-0 bg-light pr-2">
										<div class="card-body">
											<img class="img-fluid mb-0" src="<?php echo IMAGES_PATH; ?>/home_content/itstashhaynes-instagram-images-400x400.webp" alt="@itstashahaynes">
											<p class="card-text mb-3">@itstashahaynes</p>
										</div>
									</div>
									<div class="card border-0 bg-light pr-2">
										<div class="card-body">
											<img class="img-fluid mb-0" src="<?php echo IMAGES_PATH; ?>/home_content/sprucingupmamahood-instagram-images-400x400.webp" alt="@sprucingupmamahood">
											<p class="card-text mb-3">@sprucingupmamahood</p>
										</div>
									</div>
									<div class="card border-0 bg-light">
										<div class="card-body">
											<img class="img-fluid mb-0" src="<?php echo IMAGES_PATH; ?>/home_content/consistently-curious-instagram-images-400x400.webp" alt="@consistently_curious">
											<p class="card-text mb-3">@consistently_curious</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row my-2">
							<div class="col text-center">
								<a href="/locations" class="btn btn-primary btn-block">GET COOKING</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>


	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>