<?php $this->assign('page_title', 'About Us | Our Food');?>
<?php $this->assign('page_description','Dream Dinners works with Sysco.'); ?>
<?php $this->assign('page_keywords','food, sysco, dream dinners food'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<a href="/about-us" class="btn btn-primary"><span class="pr-2">&#10094;</span> About Us</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Our Food</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<div class="container">
			<div class="row mb-4">
				<div class="col">
                <p class="text-center"><img src="<?php echo IMAGES_PATH; ?>/about_us/our-food-header.jpg" alt="Dream Dinners Food" class="img-fluid" /></p>
					<p>Here at Dream Dinners, we are committed to impacting future generations through the power of the family dinner table, creating nutritious meals made from quality ingredients. Our food values are straightforward; we are dedicated to bringing you and your family food that is simple, honest and delicious.</p>
					<p>We honor this through our relationship with Sysco, the largest foodservice distributor in North America. Sysco provides all Dream Dinners stores with restaurant-quality ingredients and the freshest produce regionally available. Sysco strives to use region-specific local growers for their produce whenever possible, insisting on the same high quality, but with produce grown in the region where it is sold. Sysco also has a code of conduct that requires that all facilities that produce goods for Sysco must provide a safe and healthy work environment for all employees. Sysco has the same commitment to the communities in which it operates and a responsibility for the environment it impacts, and they seek to work with suppliers that share this commitment.</p>
					<p>Through Sysco we can help you assemble fresh to frozen meals for your dinner table. We are always working to add innovative recipes to our menu centered on new flavors, ingredients and ways to enjoy food. Understanding that no two families are the same, we provide customizable features to our meals that can be done at home in order to cater to your family's personal tastes.</p>
					<p>Dream Dinners will truly change the way you feed your family, granting you more time to gather around the table to enjoy your loved ones with the peace of mind of knowing they are eating delicious and nutritious food.</p>
              </div>
			</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>