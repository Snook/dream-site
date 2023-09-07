<?php $this->assign('page_title', 'Get Our Meal Prep Dinners Delivered');?>
<?php $this->assign('page_description','Prepped by us and homemade by you. Save hours of time and money. Get dinners delivered to your door.'); ?>
<?php $this->assign('page_keywords','meal prep dinners, prepped meals, meal prep, prepared dinners delivered, meal prep delivery, delivered prepared meals'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">

		<section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-meal-prep-bundles">
					<div class="col-md-6 text-center mx-auto">
						<h1 class="my-4 font-weight-bold">Dream Dinners To Your Door</h1>
						<h3 class="mb-5">Dream Dinners just got even easier! With our  Home Delivery Service, our ServSafe certified team will prep and assemble your dinners and now also drop them off at your door. Stay home and stay healthy while we bring a month's worth of prepped meals to your door.</h3>
						<a href="/?page=locations" class="btn btn-primary mb-4">Find a Location</a>
					</div>
				</div>
			</div>
		</section>
		<!-- body-->
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 mb-5">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chelsee-hood-458x344.webp" alt="Chelsee Hood" class="img-fluid" />
						</div>
					</div>
					<div class="col-md-6 text-left">
						<h2><b>Dream Dinners Home Delivery</b></h2>
						<p>Dream Dinners has changed how you meal plan, cook and gather at the table with your family. Now with our new delivery service, we can also change the way you get your Dream Dinners order every month.</p>
						<p>The benefits of Dream Dinners Home Delivery include:</p>
						<ul>
							<li>Home Delivery to Your Door or Quick, Easy Store Pick Up</li>
							<li>Less Time Spent at the Grocery Store</li>
							<li>A Fully Stocked Sides &amp; Sweets Freezer</li>
							<li>Prepped Meals for Your Family</li>
							<li>Less Money Spent on Groceries and Takeout</li>
							<li>Easy Meal Planning</li>
							<li>Less Food Waste</li>
							<li>More Time with Your Family</li>
						</ul>
					</div>
				</div>
			</div>
		</section>
		<!-- 3 icon box-->
		<div class="container">
			<div class="row my-4">
				<div class="col">
					<div class="card-group text-center">
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-measuring_cup font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">How It Works</h5>
								<p class="card-text">In our professional kitchen, we do all the shopping, chopping, and prep and then drop off a month of meals at your door.</p>
								<a href="/?static=how_it_works" class="btn btn-primary">Learn More</a>
							</div>
						</div>
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-order_online font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">Browse Menu</h5>
								<p class="card-text">Each month, our menu features 17 recipes for your family to enjoy. Our recipes include a variety of protein options and flavor profiles to experience at home.</p>
								<a href="/?page=browse_menu" class="btn btn-primary">Browse Menus</a>
							</div>
						</div>
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-delivery font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">Choose a Delivery Time</h5>
								<p class="card-text">When you place your order, you will choose a Home Delivery time that works for your schedule.</p>
								<a href="/?page=locations" class="btn btn-primary">Find a Location</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="row my-5 text-center">
				<p class="mt-4"><i>*Delivery fee applies to orders delivered within 20 miles of the store. Orders may be canceled for any distance beyond 20 miles. If the store can accommodate an additional fee may be added to your order before delivery.</i></p>
			</div>
		</div>

		<!-- Virtual Section -->
		<!--<section>
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-6 bg-cyan-dark text-white text-right p-5">
						<h2 class="text-uppercase font-weight-bold">WANT TO LEARN MORE ABOUT MAKING DINNERTIME EASIER?</h2>
						<p class="text-uppercase mb-4">Get comfy and find out how to simplify meals in your home with Dream Dinners.</p>
						<a href="/?static=national_virtual_party" class="btn btn-gray-light">Learn More</a>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/home_content/virtual-party-957x675.jpg" alt="Watching a Virtual Meal Prep Party" class="img-fluid">
				</div>
			</div>
		</section>-->
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>