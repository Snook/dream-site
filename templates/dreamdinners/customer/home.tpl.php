<?php $this->assign('canonical_url', HTTPS_BASE); ?>
<?php $this->assign('page_title', 'Homemade, Made Easy®'); ?>
<?php $this->assign('page_description', 'Dream Dinners brings easy, prepped dinners to families in the communities we serve. Our delicious meals are prepared with quality ingredients in our local assembly kitchens.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<section>
			<div class="container-fluid">
				<div class="row hero-double">
					<div class="col">
						<img src="<?php echo IMAGES_PATH; ?>/home_content/carne-asada-in-a-snap-header-1920x837.webp" alt="Carne Asada Steak Tacos in a Snap" class="img-fluid">
						<div class="col-12 col-sm-9 col-md-8 col-lg-6 col-xl-4 ml-sm-5 p-4" style="bottom: 1rem; background-color: rgba(255,255,255,0.90)">
							<h1 class="font-marker">Your Local Meal Kit Solution</h1>
							<p class="text-uppercase">Real food made from scratch, so your life can feel a little easier.</p>
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
				</div>
			</div>
		</section>
		<!--<section class="container-fluid">
			<header class="d-none d-sm-block row hero-double position-relative" style="min-height: 70vh;" role="banner">
				<video class="hero-double__img" autoplay="" id="heroVideo" loop="" muted="" poster="<?php echo IMAGES_PATH; ?>/home_content/media/family_dinner2.webp?v=<?php echo JAVASCRIPT_CSS_VERSION; ?>">
					<source src="<?php echo IMAGES_PATH; ?>/home_content/media/family_dinner.mp4?v=<?php echo JAVASCRIPT_CSS_VERSION; ?>" type="video/mp4" />
					<source src="<?php echo IMAGES_PATH; ?>/home_content/media/family_dinner.webm?v=<?php echo JAVASCRIPT_CSS_VERSION; ?>" type="video/webm" />
					<source src="<?php echo IMAGES_PATH; ?>/home_content/media/family_dinner.ogv?v=<?php echo JAVASCRIPT_CSS_VERSION; ?>" type="video/ogv" />
				</video>
				<div class="col-12 col-sm-9 col-md-8 col-lg-6 col-xl-4 ml-sm-5 p-4 position-absolute" style="bottom: 3rem; background-color: rgba(255,255,255,0.90)">
					<h1 class="font-marker">Your Local Meal Kit Solution</h1>
					<p class="text-uppercase">Real food made from scratch, so your life can feel a little easier.</p>
					<form action="/locations" method="post">
						<div class="form-group mx-auto">
							<div class="input-group">
								<!--<div class="input-group-prepend">
									<div class="input-group-text">
									Find a location near you
									</div>
								</div>-->
								<!--<input type="number" class="form-control" id="zip" name="zip" placeholder="Postal code">
								<div class="input-group-append">
									<button type="submit" value="Get Started" class="btn btn-primary">Get started</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</header>

			<header class="d-sm-none row mx-1">
				<div class="col">
					<img class="img-fluid" src="<?php echo IMAGES_PATH; ?>/home_content/media/family_dinner2.webp?v=<?php echo JAVASCRIPT_CSS_VERSION; ?>">
					<h1 class="font-marker">Your Local Meal Kit Solution</h1>
					<p class="text-uppercase">Real food made from scratch, so your life can feel a little easier.</p>
					<form action="/locations" method="post">
						<div class="form-group mx-auto">
							<div class="input-group">
								<!--<div class="input-group-prepend">
									<div class="input-group-text">
									Find a location near you
									</div>
								</div>-->
								<!--<input type="number" class="form-control" id="zip" name="zip" placeholder="Postal code">
								<div class="input-group-append">
									<button type="submit" value="Get Started" class="btn btn-primary">Get started</button>
								</div>
							</div>
						</div>
					</form>
					<div class="border-top mt-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
				</div>
			</header>
		</section>-->

		<!-- Menu Highlights-->
		<section>
			<div class="container my-5">
				<div class="row">
				<div class="border-top mt-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
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
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chimichangas-pan-featured-menu-item-400x400.webp" alt="Oven Baked Chimichangas" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Oven Baked Chimichangas</h5>
								</div>
							</div>
							<div class="card border-0 pr-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/baked-penne-kid-pick-featured-menu-item-400x400.webp" alt="Baked Penne Chicken Alfredo" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Baked Penne Chicken Alfredo</h5>
								</div>
							</div>
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-costoletta-air-fryer-featured-menu-item-400x400.webp" alt="Chicken Costoletta with Almond Green Beans" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Costoletta with Almond Green Beans</h5>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/surf-turf-yakisoba-30min-featured-menu-item-400x400.webp" alt="Surf and Turf Yakisoba Noodle Bowl" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Surf and Turf Yakisoba Noodle Bowl</h5>
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
						<h1 class="font-weight-bold font-marker mt-2">April Fan Favorites Bundle</h1>
						<p class="text-uppercase mb-4">Start your order with our April Fan Favorites bundle. These 4 dinners are our best-sellers on the April menu. Shipping menu items for the bundle may vary.</p>
						<a href="/session-menu" class="btn btn-lg btn-green">Order Now</a>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/apr24-fan-fav-bundle-collage-957x657.webp" alt="Fan Favorites" class="img-fluid">
					</div>

				</div>
			</div>
		</section>
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/apr24-breakfast-bundle-homepage-collage-circles-957x657.webp" alt="Specials" class="img-fluid">
					</div>
					<div class="col-md-6 text-left p-5 my-5">
						<h1 class="font-weight-bold font-marker mt-2">Spring Breakfast Bundle</h1>
						<p class="text-uppercase mb-4">Perfect for Mother's Day, bridal brunches, baby showers or a weekend brunch, our easy breakfast bundle is a tasty treat to brighten up your morning. Available at select locations, while supplies last. Not available for shipping.</p>
						<a href="/session-menu" class="btn btn-lg btn-green">Order Now</a>
					</div>
					

				</div>
			</div>
		</section>
		<!-- How it works
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container my-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-5 mb-4 font-weight-bold">How Dream Dinners Works</h2>
						</div>
					</div>
				</div>

				
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/order-online-circles-550x410.webp" alt="order online" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Choose from our monthly menu of 17 menu items and place your online order.</h5>
										<p class="card-text">Start with as few as three dinners or plan for the entire month. We also have veggies, starches, breakfasts and desserts ready to add to your order in our Sides & Sweets freezer.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1 order-md-2">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/prep-meals-circles-550x410.webp" alt="prep your meals" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Our dinners are prepped fresh and then frozen for your convenience.</h5>
										<p class="card-text">You can choose to assemble in your local store for maximum customization or have our team assemble your dinners for you.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/cook-at-home-circles-550x410.webp" alt="cook at home" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Your dinners are ready for you to cook at home.</h5>
										<p class="card-text">We have options that cook in under 30 minutes and no-mess pan meals that go straight in the oven. Each recipe includes easy-to-follow cooking instructions to make things simple.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1 order-md-2">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/fresh-flavors-circles-550x410.webp" alt="fresh flavors" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Our recipes are developed with the whole family in mind.</h5>
										<p class="card-text">We offer a variety of proteins and flavor profiles to meet everyone’s needs without getting bored. Each month you will find your favorites mixed with some new flavors for you to try. </p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/eat-connect-circles-550x410.webp" alt="eat and connect together" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Our mission is to help you, your family and friends gather around the table over an easy, homemade meal.</h5>
										<p class="card-text">With our ready-to-cook meals, you will spend less time in the kitchen and  more time connecting with the ones you love. </p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row my-2">
					<div class="col text-center">
						<a href="/locations" class="btn btn-lg btn-primary">Get started</a>
					</div>
				</div>
			</div>
		</section> -->

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