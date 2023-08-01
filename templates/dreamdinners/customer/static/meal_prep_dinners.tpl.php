<?php $this->assign('page_title', 'Try Our Meal Prep Dinners');?>
<?php $this->assign('page_description','Prepped by us and homemade by you. Save hours of time and money. Sample our menu today.'); ?>
<?php $this->assign('page_keywords','meal prep dinners, prepped meals, meal prep, prepared dinners, prepared meals, dinner solution, meal assembly, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">

		<section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-meal-prep-bundles">
					<div class="col-md-6 text-center mx-auto">
						<h1 class="my-4 font-weight-bold">Try Our Meal Prep Starter Pack</h1>
						<h3 class="mb-5">Try our ready-to-cook meals with our Meal Prep Starter Pack! Pick up at our store or have us deliver directly to your home.</h3>
						<a href="/main.php?page=locations" class="btn btn-primary mb-4">Find a Location</a>
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
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini_chicken_pot_pies_458x344.webp" alt="Mini Chicken Pot Pies" class="img-fluid" />
						</div>
					</div>
					<div class="col-md-6 text-left">
						<h2><b>Meal Prep Made Easy</b></h2>
						<p>Dream Dinners will change how you meal plan, cook and gather at the table with your family. The benefits of Dream Dinners include:</p>
						<ul>
							<li>Home Delivery or Quick, Easy Pick Up</li>
							<li>Less Time Grocery Shopping</li>
							<li>Less Money Spent on Groceries, Grab n’ Go Dinners and Takeout</li>
							<li>Easy Meal Planning and Prep</li>
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
								<p class="card-text">We do all the shopping, chopping, assembly, and clean up so that you can get a month of meals locally made at one of our locations.</p>
								<a href="/main.php?static=how_it_works" class="btn btn-primary">Learn More</a>
							</div>
						</div>
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-order_online font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">Browse Menu</h5>
								<p class="card-text">Each month, our menu features 17 recipes for your family to enjoy. Our recipes include a variety of protein options and flavor profiles to experience at home.</p>
								<a href="/main.php?page=browse_menu" class="btn btn-primary">Browse Menus</a>
							</div>
						</div>
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-shop-front font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">Book Session</h5>
								<p class="card-text">Choose a Home Delivery window for our home delivery service or a Pick Up time to pick up your meals curbside at one of our locations.</p>
								<a href="/main.php?page=locations" class="btn btn-primary">Find a Location</a>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	<div class="container">
			<div class="row my-5 text-center">
				<p class="mt-4"><i>*Our Meal Prep Starter Pack is only available to new Dream Dinners guests and existing guests that have not attended a Dream Dinners session in over a year.</i></p>
		</div>
		</div>
		
	<!-- Virtual Section -->
	<!--<section>
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-6 bg-cyan-dark text-white text-right p-5">
						<h2 class="text-uppercase font-weight-bold">WANT TO LEARN MORE ABOUT MAKING DINNERTIME EASIER?</h2>
						<p class="text-uppercase mb-4">Get comfy and find out how to simplify meals in your home with Dream Dinners.</p>
						<a href="/main.php?static=national_virtual_party" class="btn btn-gray-light">Learn More</a>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/home_content/virtual-party-957x675.jpg" alt="Watching a Virtual Meal Prep Party" class="img-fluid">
				</div>
			</div>
		</section>-->
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>