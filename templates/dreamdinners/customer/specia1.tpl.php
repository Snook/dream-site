<?php CBrowserSession::nofollow(); ?>
<?php $this->assign('page_title', 'This or That');?>
<?php $this->assign('page_description','Recipe of what you voted on.'); ?>
<?php $this->assign('page_keywords','recipe, voting, this or that'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-12 text-center">
				<h1>Creamy Dijon Vinaigrette</h1>
			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container-fluid my-5 bg-cyan-dark">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-6 mb-3 mt-5">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/creamy-dijon-vinaigrette-475x450.webp" alt="Creamy Dijon Vinaigrette" class="img-fluid" />
							</div>
						</div>
						<div class="col-md-6 text-center mt-5">
						  <img src="<?php echo IMAGES_PATH; ?>/landing_pages/great-pick-text.webp" alt="Great Pick" class="img-fluid" />
						  <h2 class="font-weight-bold text-white">Download your Creamy Dijon Vinaigrette Recipe here.</h2>
						  <a href="https://dreamdinners.com/web_resources/media/Dream-Dinners-Creamy-Dijon-Vinaigrette-Recipe.pdf" target="_blank" class="btn btn-lg btn-green mb-5">Download Recipe</a>
						</div>
						
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container-fluid my-5">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-6 text-center mt-4">
						  <h2 class="font-weight-bold font-have-heart-two font-size-extra-extra-large"><strong>Pairs Well With</strong></h2>
							<h3>Backyard BBQ Chicken with Bacon Ranch Green Beans</h3>
							<p>Perfect to cook on the grill. Tender grilled chicken breasts covered in BBQ sauce and served with Bacon Ranch Green Beans.</p>
						  <a href="/session_menu" class="btn btn-lg btn-primary btn-cyan-dark">ORDER NOW</a>
						</div>
						<div class="col-md-6 text-left">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/backyard-bbq-chicken-family-top-circle-458x344.webp" alt="Backyard BBQ Chicken" class="img-fluid" />
							</div>
						</div>
					</div>	
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>