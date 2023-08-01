<?php $this->assign('page_title', 'Make Homemade Dinners');?>
<?php $this->assign('page_description','Make homemade meals for your family at one of our kitchens and cook them in yours. Save hours of time and money. Sample our menu today.'); ?>
<?php $this->assign('page_keywords','homemade dinner, homemade meals, prepared meals, dinner solution, meal assembly, dinner preparation'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">

        <section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-intro-offer">
					<div class="col-md-8 text-center mx-auto">
						<h1 class="my-4 font-weight-bold">Try our Meal Prep Starter Pack</h1>
						<h3 class="mb-5">Prepping meals at Dream Dinners means spending less time in the kitchen, more time doing what you love.</h3>
						<a href="/main.php?page=locations" class="btn btn-primary mb-4">Find a Location</a>
					</div>
				</div>
			</div>
		</section>
        <section>
			<div class="container">
				<div class="row my-4">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/how_it_works/order-meals-online-458x344.jpg" alt="order online" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Step 1</p>
									<h5 class="card-title">Order Online</h5>
									<p class="card-text">View our monthly menu, select a session time and complete your order.</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/how_it_works/assemble-your-meals-458x344.jpg" alt="assemble dinners in store" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Step 2</p>
									<h5 class="card-title">Prep Your Meals</h5>
									<p class="card-text">The chopping and clean up are taken care of, so you can assemble and customize a month of meals at one of our locations.</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/how_it_works/cook-meals-at-home-458x344.jpg" alt="cook dinners at home" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Step 3</p>
									<h5 class="card-title">Cook at Home</h5>
									<p class="card-text">Thaw 3-5 of your meals each week, cook as directed, and enjoy dinner together.</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/how_it_works/share-family-dinner-moments-458x344.jpg" alt="enjoy dinner as a family" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Step 4</p>
									<h5 class="card-title">Share More Moments</h5>
									<p class="card-text">Spend less time in the kitchen and more time doing what you love.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

        <div class="container">
			<div class="row my-5">
				<div class="col-md-6 mb-6">
					<div class="embed-responsive embed-responsive-16by9">
						<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/8L6TMDW4jf4?rel=0&amp;controls=0" allowfullscreen></iframe>
					</div>
				</div>
				<div class="col-md-5 text-center">
					<h2><b>ANSWER THE QUESTION "WHAT'S FOR DINNER?"</b></h2>
					<p>Learn how to meal prep with Dream Dinners. Our Meal Prep Starter Pack will help you get dinner on the table with up to 6 family-style dinners for just $99. That is only $5.50 a serving!</p>
					<p><a href="/main.php?page=session_menu" data-gaq_cat="Intro Offer" data-gaq_action="View Menu" data-gaq_label="Landing Page" class="btn btn-lg btn-primary">View the Menu</a></p>
				</div>
			</div>
          </div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>