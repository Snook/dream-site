<?php $this->assign('page_title', 'Holidays at Dream Dinners');?>
<?php $this->assign('page_description','Find our holiday tools and promotions.'); ?>
<?php $this->assign('page_keywords','thanksgiving, christmas, easter, mothers day'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Holidays</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<div class="row my-4">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Holiday-Toolkit.pdf" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/holiday-toolkit-458x344.png" alt="Holiday" class="img-fluid" /></a>
								<div class="card-body">
									<h5 class="card-title">Holiday Toolkit</h5>
									<p class="card-text">Games, activities, food, tips & tricks to fill your holidays with love & laughter.</p>
									<p><a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Holiday-Toolkit.pdf" target="_blank" class="btn btn-primary">Download Toolkit</a></p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Holiday-Placemat.pdf" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/holiday-placemat-458x344.png" alt="Holiday Placemat" class="img-fluid" /></a>
								<div class="card-body">
									<h5 class="card-title">Holiday Placemat</h5>
									<p class="card-text">Download and print a 11x17 fun activity placemat.</p>
									<p><a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Holiday-Placemat.pdf" target="_blank" class="btn btn-primary">Download Placemat</a></p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Holiday-Gift-Tags.pdf" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/holiday-gift-tags-458x344.png" alt="Gift Tags" class="img-fluid" /></a>
								<div class="card-body">
									<h5 class="card-title">Holiday Gift Tags</h5>
									<p class="card-text">Download some fun gift tags and fun gift giving ideas.</p>
									<p><a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Holiday-Gift-Tags.pdf" target="_blank" class="btn btn-primary">Download Gift Tags</a></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>