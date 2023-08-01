<?php $this->assign('page_title', 'Do It Yourself Dinners');?>
<?php $this->assign('page_description','Prepped by us and homemade by you. Save hours of time and money. Sample our menu today.'); ?>
<?php $this->assign('page_keywords','diy dinners, diy meals, do it yourself dinners, meal prep, prepared dinners, prepared meals, dinner solution, meal assembly, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">

		<section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-meal-prep-bundles">
					<div class="col-md-8 text-center mx-auto">
						<h1 class="my-4 font-weight-bold">Try Dream Dinners Again</h1>
						<h3 class="mb-5">Getting dinner on the table is easy with our chef-created, family friendly recipes prepped in our local assembly kitchens.</h3>
						<!--<h3 class="mb-5">Save $30 on your next order with code*: DISCOVER</h3>-->
						<a href="/main.php?page=locations" class="btn btn-primary mb-4">Get Started</a>
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
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini_beef_tostada_cups_458x344.webp" alt="Mini Beef Tostada Cups" class="img-fluid" />
						</div>
					</div>
					<div class="col-md-6 text-left">
						<h2><b>MEAL PREP DINNERS</b></h2>
						<p>Dream Dinners will change how you meal plan, cook and gather at the table with your family. The benefits of Dream Dinners include:</p>
						<ul>
							<li>Prepped Meals for Your Family</li>
							<li>Less Time Standing in Line at the Grocery Shopping</li>
							<li>Easy Pick Up</li>
							<li>Home Delivery at Select Locations*</li>
							<li>A Fully Stocked Sides &amp; Sweets Freezer</li>
							<li>Less Money Spent on Groceries and Takeout</li>
							<li>Easy Meal Planning</li>
							<li>Less Food Waste</li>
							<li>More Time with Your Family</li>
						</ul>

					</div>
				</div>
			</div>
		</section>
<section>
			<div class="container-fluid my-5 bg-cyan-dark">
				<div class="container">
				<div class="row">
					<div class="col">
						<div class="text-center text-white">
							<h2 class="my-4">Three Convenient Ways We Serve You Locally</h2>
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
									<p>Visit one of our local stores to quickly and easily pick up your assembled meals.</p>
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
									<p>At your local store, assemble and customize your dinners using our easy-to-follow recipes.</p>
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
		<!--<div class="container">
			<div class="row my-5 text-center">
				<p class="mt-4"><i>*Code is valid for use by guests that have not ordered from their Dream Dinners account in over a year and may redeem the coupon code towards their next monthly order. A monthly order consists of at least 12 medium dinners or 6 large dinners (or a combination of both sizes). Code is not valid for use in conjunction with a Fundraiser, Meal Prep Workshop, or any Special Event session type. Code cannot be combined with other offers. Available for use at participating locations. Code can only be redeemed one time on a qualifying account. No cash redemption permitted.</i></p>
		</div>
		</div>-->
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>