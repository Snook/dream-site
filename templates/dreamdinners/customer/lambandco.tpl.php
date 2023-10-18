<?php $this->assign('page_title', 'Lamb and Company Sisters');?>
<?php $this->assign('page_description',''); ?>
<?php $this->assign('page_keywords','prepped meals, prepped dinners, homemade dinner, homemade meals, prepared meals, dinner solution, meal assembly, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<section>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 text-left p-5 my-5">
						<h1 class="font-weight-bold mt-2">As seen on HGTV’s Unsellable Houses, Lyndsay and Leslie are busy running a business, filming a tv show and spending time with their families.</h1>
						<p class="text-uppercase mb-1">Have you wondered how they do it all? They have Dream Dinners to help!</p>
						<p class="mb-4">Dream Dinners is here to make their life a little less crazy and their summertime a little easier with our ready-to-cook freezer meals, and we want to help you to. Spend less time in the kitchen and more time in the sun having fun!</p>
						<a href="/locations" class="btn btn-lg btn-green">Find a Location</a>
					</div>
					<div class="col-md-6 p-0">
						<figure>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/lamb-and-co-sisters-at-dream-dinners-957x657.webp" alt="Lindsey and Leslie at Dream Dinners" class="img-fluid">
						</figure> 
					</div>

				</div>
			</div>
		</section>
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-5 mb-6">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/grilled_chicken_caesar_sandwiches_family_458x344.webp" alt="Grilled Chicken Caesar Sandwiches" class="img-fluid" />
						</div>
					</div>
					<div class="col-md-7 text-left">
					  <h2><strong>As Your Personal Kitchen Assistant, Dream Dinners Provides:</strong></h2>
						<br>&nbsp;
                      <ul>
                        <li>Chef-created, family-friendly recipes without having to plan and grocery shop. </li>
                        <li>Ready-to-cook frozen dinners leaving you prepared and ready to enjoy summer.</li>
                        <li>Easy to follow instructions making both cooking and clean up simple and stress-free.</li>
                      </ul>
					</div>
				</div>
			  </div>
		</section>
		 <!--<section>
			<div class="container">
				<div class="row">
					<div class="col my-3">
						<div class="text-center" style="border-top: #b9bf33 dotted 5px !important;">
							<h2 class="my-3">July Menu Recommendations</h2>
						</div>
					</div>
				</div>
				<div class="row my-4">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/southwest-mac-n-cheese-circle-458x344.webp" alt="Southwest Mac n Cheese" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Southwest Mac n Cheese</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/turkey-florentine-burger-circle-458x344.webp" alt="Turkey Florentine Burger" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Turkey Florentine Burger</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/southwest-mac-n-cheese-circle-458x344.webp" alt="Southwest Mac n Cheese" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Southwest Mac n Cheese</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/backyard-bbq-chicken-circle-458x344.webp" alt="Backyard BBQ Chicken" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Backyard BBQ Chicken</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
				<div class="row my-4">
					<div class="col text-center">
						<a href="/locations" class="btn btn-lg btn-primary">Get Cooking</a>
					</div>
				</div>
			</div>
		</section>-->
		<!-- Ways We Can Serve You-->
		<section>
			<!--<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>-->
			<div class="container my-5">
				<div class="row" style="border-top: #b9bf33 dotted 5px !important;">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-4 mb-4 font-weight-bold">WAYS WE CAN SERVE YOU</h2>
						</div>
					</div>
				</div>
				<div class="row my-5">
					<div class="col">
						<div class="card-group text-center mb-5">
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/home_content/pick-up-your-meals-384x352.webp" alt="Pick Up Your Meals" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Local Store Pick Up</h5>
									<p class="card-text">Find a location near you to pick up your ready-to-cook meals prepped by our team.</p>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/home_content/home-delivery-384x352.webp" alt="Home Delivery" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Local Delivery</h5>
									<p class="card-text">Pick a location near you and we will deliver your ready-to-cook meals directly to your home.</p>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/home_content/assemble-your-meals-384x352.webp" alt="Assemble Your Meals" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Local In-store Assembly</h5>
									<p class="card-text">Visit a location near you and assemble your own meals in our local prep kitchen.</p>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/home_content/shipped-to-your-door-384x352.webp" alt="Shipped to your door" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">National Shipping</h5>
									<p class="card-text">No location near you? We can ship ready-to-cook meals to your home. No subscription required.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 mb-6 text-left">
							<h3 class="my-4">OUR GUESTS LOVE US</h3>
						<p>Hear what our guest Ashley and her daughters have to say about how Dream Dinners has made dinnertime easier at her house.</p>
						
						<p><a href="/locations" data-gaq_cat="original" data-gaq_action="Order Now" data-gaq_label="Landing Page" class="btn btn-lg btn-primary">Order Now</a></p>
					</div>
					<div class="col-md-6 text-right">
						<div class="embed-responsive embed-responsive-16by9">
						<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/iSh8gXjsT6w?rel=0&amp;controls=0" allowfullscreen></iframe>
					</div>
				</div>
			  </div>
			</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>