<?php $this->assign('page_title', 'Share The Love at Dream Dinners');?>
<?php $this->assign('page_description','Prepped by us and homemade by you. Learn how to save hours of time and money and give back in the process.'); ?>
<?php $this->assign('page_keywords','share the love, give meals, meal prep, meal prep workshop, learn how to meal prep, prepared meals, meal assembly, open house event, dream taste event'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">

		<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>SHARE THE LOVE THIS FEBRUARY</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>
<!-- body-->
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 mb-3">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/open-house-meal-prep-540x360.jpg" alt="Open House Meal Prep" class="img-fluid" />
						</div>
					</div>
					<div class="col-md-6 text-left">
						<h2><b>Give Meal Prep a try this month and give back.</b></h2>
						<p>Join us in our meal prep kitchen to customize up to 6 family-style dinners with our Meal Prep Starter Pack.</p>
						<p><b>Plus, for every Meal Prep Starter Pack purchased in February, we will feed a person in need through the Dream Dinners Foundation.</b></p>
						<a href="/main.php?page=session_menu" class="btn btn-primary btn-block mb-3 start-intro-offer">Find a Location &amp; View Menu</a>
					</div>
				</div>
			</div>
		
		</section>
		
<!-- 3 box-->
		<section>
			<div class="container">
				<div class="row my-4">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<a href="/main.php?page=session_menu" class="start-intro-offer"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/parmesan-herb-crusted-chicken-458x344.jpg" alt="Parmesan Herb Crusted Chicken" class="img-fluid" /></a>
							</div>
							<div class="card border-0 mx-1">
								<a href="/main.php?page=session_menu" class="start-intro-offer"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-shrimp-carbonara-458x344.jpg" alt="Chicken and Shrimp Carbonara" class="img-fluid" /></a>
							</div>
							<div class="card border-0 mx-1">
								<a href="/main.php?page=session_menu" class="start-intro-offer"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini-turkey-meatloaves-458x344.jpg" alt="MINI TURKEY MEATLOAVES WITH BACON RANCH GREEN BEANS" class="img-fluid" /></a>
							</div>
							<div class="card border-0 mx-1">
								<a href="/main.php?page=session_menu" class="start-intro-offer"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/thai-peanut-chicken-458x344.jpg" alt="THAI PEANUT CHICKEN WITH JASMINE RICE" class="img-fluid" /></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		
	<div class="container">
			<div class="row my-5 text-center">
				<p class="mt-4"><i>*At participating locations. Meal Prep Starter Pack is valid for new guests or guests who have not attended a Dream Dinners session in over a year.</i></p>
		</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>