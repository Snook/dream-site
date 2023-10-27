<?php $this->assign('page_title', 'Orginal Meal Kit Company');?>
<?php $this->assign('page_description','As life gets busier, choose the Original Meal Kit made for families. Our tried and true recipes your family will love are prepped and ready to cook at home.'); ?>
<?php $this->assign('page_keywords','prepped meals, prepped dinners, homemade dinner, homemade meals, prepared meals, dinner solution, meal assembly, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<section>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 text-left p-5 my-5">
						<h1 class="font-weight-bold mt-2">Parent’s Choice, Kid Approved</h1>
						<p class="text-uppercase mb-4">As life gets busier, choose dinners your family will love. Our tried and true freezer meals are prepped and ready to cook at home. No chopping, shopping or messy clean-up.</p>
						<a href="/locations" class="btn btn-lg btn-green">Find a Location</a>
					</div>
					<div class="col-md-6 p-0">
						<figure>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/nov23-menu-collage-957x657.webp" alt="Italiano Chicken and Orzo adn Buffalo Ranch Chicken Sliders and Pork Marsala" class="img-fluid">
						</figure>
					</div>
				</div>
			</div>
		</section>
		<section>
		<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-5 mb-6">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mom-making-pot-pies-pan-450x300.webp" alt="Making pot pies" class="img-fluid" />
						</div>
					</div>
					<div class="col-md-7 text-left">
					  <h2><strong>As Your Personal Kitchen Assistant, Dream Dinners Provides:</strong></h2>
						<br>&nbsp;
                      <ul>
                        <li>Chef-created, family-friendly recipes without having to plan and grocery shop.</li>
                        <li>Ready-to-cook frozen dinners leaving you prepared and ready to enjoy summer.</li>
                        <li>Easy to follow instructions making both cooking and clean up simple and stress-free.</li>
                      </ul>
					</div>
				</div>
			  </div>
		</section>
		 <section>
		 <div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container">
				<div class="row">
					<div class="col my-3">
						<div class="text-center">
							<h2 class="my-3">Kid Pick Menu Recommendations</h2>
						</div>
					</div>
				</div>

				<div class="row my-4">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini-turkey-hand-pies-circle-458x344.webp" alt="Mini Turkey and Vegetable Hand Pies" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Mini Turkey and Vegetable Hand Pies</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/cod-fish-n-chips-circle-458x344.webp" alt="Cod Fish n Chips" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Cod Fish n Chips</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mexican-lasagna-circle-458x344.webp" alt="Mexican Beef Tortilla Bake" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Mexican Beef Tortilla Bake</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken_pizzaiola_family_circle_458x344.webp" alt="Chicken Pizzaiola" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Chicken Pizzaiola</p>
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
		</section>
		<!-- Ways We Can Serve You-->
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container my-5">
				<div class="row">
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
									<h5 class="card-title">Local Store Assembly</h5>
									<p class="card-text">Visit a location near you and assemble your own meals in our local prep kitchen.</p>
								</div>
							</div>
							<!--<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/home_content/shipped-to-your-door-384x352.webp" alt="Shipped to your door" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">National Shipping</h5>
									<p class="card-text">No location near you? We can ship via FedEx ready-to-cook meals to your home. No subscription required.</p>
								</div>
							</div>-->
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