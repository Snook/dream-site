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
								<a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Back-to-School-Toolkit.pdf" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/back-to-school-toolkit-458x344.png" alt="Back to School" class="img-fluid" /></a>
								<div class="card-body">
								<h5 class="card-title">Back to School Toolkit</h5>
									<p class="card-text">Filled with activities, tips and tricks to keep the kiddos entertained.</p>
									<p><a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Back-to-School-Toolkit.pdf" target="_blank" class="btn btn-primary">Download Toolkit</a></p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Back-to-School-Placemat.pdf" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/back-to-school-placemat-458x344.png" alt="Back to School Placemat" class="img-fluid" /></a>								<div class="card-body">
									<h5 class="card-title">Back to School Placemat</h5>
									<p class="card-text">Download and print a 11x17 fun activity placemat.</p>
									<p><a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Back-to-School-Placemat.pdf" target="_blank" class="btn btn-primary">Download Placemat</a></p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Back-to-School-Note-Cards.pdf" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/back-to-school-note-cards-458x344.png" alt="Back to School notes" class="img-fluid" /></a>
								<div class="card-body">
									<h5 class="card-title">Back to School Notes</h5>
									<p class="card-text">Add one of these sweet notes their lunchbox to brighten their day.</p>
									<p><a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Back-to-School-Note-Cards.pdf" target="_blank" class="btn btn-primary">Download Notes</a></p>
								</div>
							</div>
							<!--<div class="card border-0 mx-1">
								<a href="/main.php?page=gift_card_order" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/landing_pages/gift-cards-458x344.png" alt="Dream Dinner Gift Cards" class="img-fluid" /></a>
								<div class="card-body">
									<h5 class="card-title">Dream Dinners Gift Cards</h5>
									<p class="card-text">Looking for the perfect gift?<br/>Bring joy with ready to cook meals.</p>
									<p><a href="/main.php?page=gift_card_order" class="btn btn-primary">Buy a Gift Card</a></p>
								</div>
							</div>-->
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>