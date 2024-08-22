<?php $this->assign('page_title', 'Submission Complete');?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h2>Submission Complete</h2>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main class="container">

		<div class="row">
			<div class="col">
				<p class="alert alert-green"><?php echo $this->submission_message; ?></p>
			</div>
		</div>

		<div class="row my-2">
			<div class="col text-center">
				<a href="/locations" class="btn btn-lg btn-primary">Get started</a>
			</div>
		</div>

		<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

		<div class="row no-gutters">
			<div class="col-12 col-lg-6">
				<div class="card-group text-center">
					<div class="card border-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/order-online-no-text-circles-550x410.webp" alt="order online" class="img-fluid" />
						<div class="card-body">
							<h5 class="card-title my-0">1. Order Online</h5>
							<p class="card-text">View our monthly menu, select a time and complete your order.</p>
						</div>
					</div>
					<div class="card border-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/prep-meals-no-text-circles-550x410.webp" alt="prep your meals" class="img-fluid" />
						<div class="card-body">
							<h5 class="card-title my-0">2. Meals are Prepped</h5>
							<p class="card-text">The shopping, chopping, prep and clean up are taken care of, so you can enjoy meals at home.</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-lg-6">
				<div class="card-group text-center">
					<div class="card border-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/cook-at-home-no-text-circles-550x410.webp" alt="cook at home" class="img-fluid" />
						<div class="card-body">
							<h5 class="card-title my-0">3. Cook at Home</h5>
							<p class="card-text">Thaw your meals each week, cook as directed, and enjoy dinner together.</p>
						</div>
					</div>
					<div class="card border-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/eat-connect-no-text-circles-550x410.webp" alt="eat and connect together" class="img-fluid" />
						<div class="card-body">
							<h5 class="card-title my-0">4. Eat and Connect</h5>
							<p class="card-text">Spend less time in the kitchen and more time doing what you love.</p>
						</div>
					</div>
				</div>
			</div>
		</div>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>