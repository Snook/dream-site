<?php $this->assign('page_title', 'Sitemap');?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Dream Dinners Sitemap</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">
			</div>
		</div>
	</header>

	<main>
		<div class="container">

			<ul>
				<li><a href="/">Dream Dinners Home Page</a> - Get prepped meals for your family from our local assembly kitchens. Find how we can serve you. </li>
			</ul>

			<h4>How It Works</h4>

			<ul>
				<li><a href="/how-it-works">How It Works</a> - Learn Dream Dinners works.</li>
			</ul>

			<h4>View Menu</h4>

			<nav class="row mb-4">
				<div class="col nav nav-pills nav-fill pr-0" id="dishDetails" role="tablist">
					<?php $count = 0;  foreach ($this->activeMenus AS $menu) { $count++; ?>
						<a class="nav-item nav-link col-<?php echo floor(12 / count($this->activeMenus)); ?> text-uppercase font-weight-bold<?php if ($count == 1) { echo ' active'; } ?>" id="m-<?php echo $menu['id']; ?>-tab" data-urlpush="true" data-toggle="tab" data-target="#m-<?php echo $menu['id']; ?>" href="/browse-menu&amp;tab=m-<?php echo $menu['id']; ?>" role="tab" aria-controls="<?php echo $menu['id']; ?>" aria-selected="<?php echo ($count == 1) ? 'true' : 'false'; ?>"><?php echo $menu['month']; ?></a>
					<?php } ?>
				</div>
			</nav>

			<div class="tab-content mb-4" id="dishDetailsContent">
				<?php $count = 0; foreach ($this->activeMenus AS $menu) { $count++; ?>
					<div class="tab-pane fade<?php if ($count == 1) { echo ' show active'; } ?>" id="m-<?php echo $menu['id']; ?>" role="tabpanel" aria-labelledby="m-<?php echo $menu['id']; ?>-tab">
						<ul class="list-group">
							<?php foreach ($menu['menu_items']['menu_items'] AS $menu_item) {  ?>
								<li class="list-group-item">
									<a href="/item?recipe=<?php echo $menu_item['recipe_id']; ?>"><?php echo $menu_item['menu_item_name']; ?></a>
								</li>
							<?php } ?>
						</ul>
					</div>
				<?php } ?>
			</div>

			<h4>Stores</h4>

			<ul>
				<?php foreach ($this->currentStores AS $state_id => $stores) { ?>
					<li><a href="/locations?state=<?php echo $state_id; ?>"><?php echo CStatesAndProvinces::GetName($state_id); ?></a></li>
					<ul>
						<?php foreach ($stores as $DAO_store) { ?>
							<li><a href="<?php echo $DAO_store->getPrettyUrl(); ?>"><?php echo $DAO_store->store_name; ?></a><?php if ($DAO_store->isComingSoon()) {?> <span class="font-italic text-muted">- Coming Soon!</span><?php } ?></li>
						<?php } ?>
					</ul>
				<?php } ?>
			</ul>

			<h4>Our Services</h4>

			<ul>
				<li><a href="/locations">Locations</a> - Find a meal assembly kitchen near you.</li>
				<li><a href="/delivered">Delivered</a> - Dinners shipped to your door.</li>

			<h4>PlatePoints</h4>

			<ul>
				<li><a href="/platepoints">PlatePoints</a> - Our rewards program.</li>
			</ul>

			<h4>Recipe Resources</h4>

			<ul>
				<li><a href="/recipe-resources">Cooking Instructions and Nutritionals</a> - Search  for cooking instructions and nutritionals.</li>
			</ul>

			<h4>Gift Cards</h4>

			<ul>
				<li><a href="/gift-card-order">Gift Cards</a> - Buy gift cards or check a balance.</li>
			</ul>

			<h4>About Us</h4>

			<ul>
				<li><a href="/about-us">About Us</a> - Dream Dinners, we're about families.</li>
				<li><a href="/job-opportunities">Careers</a> - Contact your local store.</li>
				<li><a href="/contact-us">Contact Us</a> - Contact our home office.</li>
				<li><a href="/promotions">Promotions</a> - Current information on our store promotions and offerings.</li>
				<li><a href="/fundraisers">Fundraisers</a> - Current information on our fundraiser programs.</li>
			</ul>

			<h4>Customer Service</h4>

			<ul>
				<li><a href="/account">Create an account</a> - Sign up for a Dream Dinners account.</li>
				<li><a href="/login">Log In</a> - Log in to your Dream Dinners account.</li>
			</ul>

		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>