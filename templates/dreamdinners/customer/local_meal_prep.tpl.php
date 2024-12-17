<?php $this->assign('page_title', 'Local Meal Prep');?>
<?php $this->assign('page_description','Dream Dinners provides delicious, local meal prep to help families in your community gather around the dinner table. '); ?>
<?php $this->assign('page_keywords','local meal prep, local meal prep company, local meal kit, local meal prep service'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<section>
			<div class="container-fluid my-5">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-5 text-center mt-5">
						  <h2 class="font-weight-bold font-have-heart-two font-size-extra-extra-large"><strong>MEALS IN A SNAP</strong></h2>
						  <p>Our ready-to-cook meals are fully prepped with simple step-by-step instructions. Choose from options like grill, air fryer or instant pot that cook in under 30 minutes and fit into your busy life.</p>
						  <form action="/locations" method="post">
						<div class="form-group mx-auto">
							<div class="input-group">
								<!--<div class="input-group-prepend">
									<div class="input-group-text">
									Find a location near you
									</div>
								</div>-->
								<input type="number" class="form-control" id="zip" name="zip" placeholder="Postal code">
								<div class="input-group-append">
									<button type="submit" value="Get Started" class="btn btn-primary">View Local Menu</button>
								</div>
							</div>
						</div>
					</form>
						</div>
						<div class="col-md-7 mb-3 mt-3 text-right">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/air-fryer-mom-cooking-stovetop-2-images.webp" alt="Air Fryer Chicken Tenders and mom cooking on the stove" class="img-fluid" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
		<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container-fluid my-5">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-7 text-left">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/Salisbury_Meatballs_with_Mushroom_Gravy_458x344.webp" alt="Salisbury Meatballs" class="img-fluid" />
							</div>
						</div>
						<div class="col-md-5 text-center mt-4">
						  <h2 class="font-weight-bold font-have-heart-two font-size-extra-extra-large"><strong>DELICIOUS RECIPES</strong></h2>
						  <p>Our monthly menu has a variety of tasty meals to fit your family’s needs. We assemble your meals just for you from fresh ingredients, then freeze them for optimal freshness. This means they are ready to cook and enjoy whenever you need them.</p>
						  <a href="/browse-menu" class="btn btn-lg btn-primary btn-cyan-dark">VIEW MENU</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container-fluid my-5 bg-cyan-dark" id="local">
				<div class="container">
					<div class="row my-5">
						<div class="col-md-5 text-center mt-5">
						  <h2 class="font-weight-bold font-have-heart-two font-size-extra-extra-large"><strong>LOCALLY OWNED</strong></h2>
						  <p>Our small business owners are here to serve you. Whether your meals are shipped, delivered locally or picked up at our community kitchen, they are ready to support you.</p>
						  <form action="/locations" method="post">
						<div class="form-group mx-auto">
							<div class="input-group">
								<!--<div class="input-group-prepend">
									<div class="input-group-text">
									Find a location near you
									</div>
								</div>-->
								<input type="number" class="form-control" id="zip" name="zip" placeholder="Postal code">
								<div class="input-group-append">
									<button type="submit" value="Get Started" class="btn btn-primary">Find Local Delivery Options</button>
								</div>
							</div>
						</div>
					</form>
						</div>
						<div class="col-md-7 text-right mb-3 mt-3">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/locally-owned-instore-2-images.webp" alt="Local Store" class="img-fluid" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		
		

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>