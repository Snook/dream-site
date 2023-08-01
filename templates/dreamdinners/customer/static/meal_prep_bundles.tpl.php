<?php $this->assign('page_title', 'Meal Prep Dinners');?>
<?php $this->assign('page_description','Make homemade meals for your family at one of our kitchens and cook them in yours. Save hours of time and money. Sample our menu today.'); ?>
<?php $this->assign('page_keywords','meal prep, prepared dinners, homemade dinner, homemade meals, prepared meals, dinner solution, meal assembly, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">

		<section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-meal-prep-bundles">
					<div class="col-md-6 text-center mx-auto">
						<h1 class="my-4 font-weight-bold">Try our Meal Prep Starter Pack</h1>
						<h3 class="mb-5">Prepping meals at Dream Dinners means spending less time in the kitchen, more time doing what you love</h3>
						<a href="/main.php?page=locations" class="btn btn-primary mb-4">Find a Location</a>
					</div>
				</div>
			</div>
		</section>
<!-- body
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 mb-5">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/events_programs/weeknight-solutions-dec19-menu-collage-675x380.jpg" alt="december weeknight dinners" class="img-fluid" />
						</div>
					</div>
					<div class="col-md-6 text-left">
						<h2><b>Busy Weeknight Solutions</b></h2>
						<p>These dinners are quick and easy to serve in between holiday concerts and wrapping gifts.</p>
						<ul>
							<li>Cashew Chicken with Noodles</li>
							<li>Central Park Garlic Chicken</li>
							<li>Pecan Crusted Pork Chops with Buttery Peas with Bacon </li>
						</ul>
					</div>
				</div>
			</div>

			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 text-left">
						<h2><b>Great Holiday Meals for Gatherings</b></h2>
						<p>Serve these dishes as appetizers or share with friends and family.</p>
						<ul>
							<li>Chicken Marsala with Mashed Potatoes</li>
							<li>Italian Stuffed Shells</li>
							<li>Coconut Shrimp with Tropical Chili Sauce</li>
						</ul>
					</div>
					<div class="col-md-6 mb-5">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/events_programs/holiday-meals-menu-collage-675x380.jpg" alt="december holiday dinners" class="img-fluid" />
						</div>
					</div>
				</div>
			</div>
		</section>-->

		<div class="container">
			<div class="row my-4">
				<div class="col">
					<div class="card-group text-center">
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-measuring_cup font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">How It Works</h5>
								<p class="card-text">In our locations, we do all the shopping, chopping and clean up so that you can prep a month of meals in about an hour, at one of our locations.</p>
								<a href="/main.php?static=how_it_works" class="btn btn-primary">Learn More</a>
							</div>
						</div>
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-order_online font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">Browse Menu</h5>
								<p class="card-text">Each month, our full menu features over 16 recipes for your family to enjoy. Our recipes include a variety of protein options and flavor profiles to experience at home.</p>
								<a href="/main.php?page=browse_menu" class="btn btn-primary">Browse Menus</a>
							</div>
						</div>
						<div class="card border-0 mx-1">
							<i class="dd-icon icon-shop-front font-size-extra-extra-large text-green m-4"></i>
							<div class="card-body">
								<h5 class="card-title">Book Session</h5>
								<p class="card-text">At one of our locations, you will choose a session time where our helpful team members will walk you through the assembly experience and show you how easy meal prep can be.</p>
								<a href="/main.php?page=locations" class="btn btn-primary start-intro-offer">Find a Location</a>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		</section>


	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>