<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/clipboard/clipboard.min.js'); ?>
<?php $this->assign('page_title', 'Dream Dinners');?>
<?php $this->assign('page_description','Dream Dinners helps you get easy homemade meals on the table. '); ?>
<?php $this->assign('page_keywords','dream dinners, local meal prep, local meal prep company, local meal kit, local meal prep service'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<section>
			<div class="container">
				<!--<img src="<?php echo IMAGES_PATH; ?>/landing_pages/family-dinner-generational-cheers-1400x500.webp" alt="Family celebrating over a meal" class="img-fluid" />-->
				<div class="row my-5">
					<div class="col mb-4 text-center">
						<h1 class="font-weight-bold font-have-heart-two font-size-extra-large mt-2">It was great to meet you!</h1>
						<h3>Let us help you check easy, homemade meals off your to-do list.</h3>
					</div>
				</div>
			</div>
		</section>
		<!-- How it works -->
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container my-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-5 mb-4 font-weight-bold">Want to know more about Dream Dinners?<br>Here is how it works.</h2>
						</div>
					</div>
				</div>

				<!--  -->
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/order-online-circles-550x410.webp" alt="order online" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Choose from our monthly menu of 17 menu items and place your online order.</h5>
										<p class="card-text">Start with as few as three dinners or plan for the entire month. We also have veggies, starches, breakfasts and desserts ready to add to your order in our Sides & Sweets freezer.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1 order-md-2">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/prep-meals-circles-550x410.webp" alt="prep your meals" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Our dinners are prepped fresh and then frozen for your convenience.</h5>
										<p class="card-text">You can choose to assemble in your local store for maximum customization or have our team assemble your dinners for you.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/cook-at-home-circles-550x410.webp" alt="cook at home" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Your dinners are ready for you to cook at home.</h5>
										<p class="card-text">We have options that cook in under 30 minutes and no-mess pan meals that go straight in the oven. Each recipe includes easy-to-follow cooking instructions to make things simple.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1 order-md-2">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/fresh-flavors-circles-550x410.webp" alt="fresh flavors" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Our recipes are developed with the whole family in mind.</h5>
										<p class="card-text">We offer a variety of proteins and flavor profiles to meet everyone’s needs without getting bored. Each month you will find your favorites mixed with some new flavors for you to try. </p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 mx-1">
									<img src="<?php echo IMAGES_PATH; ?>/landing_pages/eat-connect-circles-550x410.webp" alt="eat and connect together" class="img-fluid" />
								</div>
								<div class="card border-0 mx-1 text-left">
									<div class="card-body py-5 px-5">
										<h5 class="card-title pt-5">Our mission is to help you, your family and friends gather around the table over an easy, homemade meal.</h5>
										<p class="card-text">With our ready-to-cook meals, you will spend less time in the kitchen and  more time connecting with the ones you love. </p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row my-2">
					<div class="col text-center">
						<a href="/locations" class="btn btn-lg btn-primary">Get started</a>
					</div>
				</div>
			</div>
		</section>

	

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>