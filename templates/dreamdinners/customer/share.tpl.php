<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/clipboard/clipboard.min.js'); ?>
<?php $this->assign('page_title', 'Share Dream Dinners');?>
<?php $this->assign('page_description','Real food made from scratch, so your life can feel just a little easier. '); ?>
<?php $this->assign('page_keywords','refer dream dinners, share dream dinners, local meal prep, local meal prep company, local meal kit, local meal prep service'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<section>
			<div class="container">
				<img src="<?php echo IMAGES_PATH; ?>/landing_pages/family-dinner-generational-cheers-1400x500.webp" alt="Family celebrating over a meal" class="img-fluid" />
				<div class="row my-5">
					<div class="col mb-4 text-center">
						<h1 class="font-weight-bold font-marker font-size-extra-large mt-2">Dinner is Now Easier Than Ever</h1>
						<h3>Let us help you check easy, homemade meals off your to-do list.</h3>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-7 text-left">
						<h3>New Guest Exclusive:<br><strong>Buy 3 Dinners, Get 1 Free*</strong></h3>
						<p>Place an order for 3 or more dinners, get a free medium Cashew Chicken and Noodles as our gift to you. </p>
						<p>Use this code at checkout: <span class="font-weight-bold font-marker font-size-large">SHARE</span></p>
						<p class="my-3"><a href="/locations" class="btn btn-lg btn-green">ORDER NOW</a></p>
					</div>
					<div class="col-md-5 mb-6">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/cashew-chicken-with-noodles-circle-458x344.webp" alt="Cashew Chicken and Noodles" class="img-fluid mb-3" />
						</div>
					</div>
				</div>
			</div>
		</section>
		<!--<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container my-5">
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 py-4 px-4 mx-1 text-left">
									<div class="card-body text-center">
										<img src="<?php echo IMAGES_PATH; ?>/landing_pages/charity-hand-heart-orange-150x159.png" alt="Donation" class="img-fluid" />
									</div>
									<div class="card-body">
										<h4 class="card-title">Get a Meal & Give Hope </h4>
										<p class="card-text">This summer for every new customer our guests refer, the Dream Dinners Foundation will donate $5 to support lifesaving blood cancer research for children. </p>
									</div>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="card-group">
								<div class="card border-0 py-4 px-4 mx-1 text-left">
									<div class="card-body text-center">
										<img src="<?php echo IMAGES_PATH; ?>/landing_pages/piggybank-green-150x159.png" alt="Piggybank" class="img-fluid" />
									</div>
									<div class="card-body">
										<h4 class="card-title">Give a Dinner, Earn Rewards</h4>
										<p class="card-text">Share your personal referral link to introduce your friends and family to Dream Dinners. You get 10 Dinner Dollars for every referral and they get a free dinner on us.</p>
										<?php if (!CUser::isLoggedIn()) { ?>
											<p class="card-text"><a href="/my-account">Log in to My Account</a> to get your referral link.</p>
										<?php } else { ?>
											<div class="input-group mb-3">
												<div class="input-group-prepend">
													<span class="input-group-text">Your link</span>
												</div>
												<input type="text" id="my_share_pp_link" class="form-control" aria-label="Your referral link" value="<?php echo HTTPS_BASE; ?>share/<?php echo CUser::getCurrentUser()->id; ?>">
												<div class="input-group-append">
													<button class="input-group-text btn-clip" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard"  data-clipboard-target="#my_share_pp_link" ><i class="fas fa-clipboard-list"></i></button>
												</div>
												<div class="input-group-append">
													<a class="input-group-text" data-toggle="tooltip" data-placement="top" title="Download QR code" href="<?php echo HTTPS_BASE; ?>processor?processor=qr_code&amp;op=referral&amp;d=1&amp;s=10&amp;id=<?php echo CUser::getCurrentUser()->id; ?>" ><i class="fas fa-qrcode"></i></a>
												</div>
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>-->

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

		<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
		<!-- Menu Highlights-->
		<section>
			<div class="container my-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-4 mb-4 font-weight-bold">Meals Made In A Snap</h2>
						</div>
					</div>
				</div>
				<div class="row my-5">
					<div class="col">
						<div class="card-group text-center mb-2">
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mini-pot-pies-featured-kid-pick-400x400.webp" alt="Mini Chicken Pot Pies" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Mini Chicken Pot Pies</h5>
								</div>
							</div>
							<div class="card border-0 pr-2">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-enchiladas-featured-pan-400x400.webp" alt="Chicken Enchiladas" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Enchiladas</h5>
								</div>
							</div>
							<div class="card border-0 pr-4">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/carne-asada-tacos-featured-under-30-400x400.webp" alt="Carne Asada Steak Tacos" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Carne Asada Steak Tacos</h5>
								</div>
							</div>
							<div class="card border-0">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-tikka-masala-featured-instant-pot-400x400.webp" alt="Chicken Tikka Masala" class="img-fluid">
								<div class="card-body">
									<h5 class="card-title">Chicken Tikka Masala over Jasmine Rice</h5>
								</div>
							</div>

						</div>
					</div>
				</div>
				<div>
					<div class="col">
						<div class="text-center">
							<a href="/locations" class="btn btn-lg btn-primary">See Your Local Menu Options</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
		<!-- Testimonials -->
		<section>
			<div class="container-fluid my-5">
				<div class="row px-2 px-lg-5 py-5">
					<div class="col-lg-8 offset-lg-2 text-center">
						<h2 class="text-center"><strong>Hear what our guests have to say...</strong></h2>
						<div class="row my-4">
							<div class="col">
								<div class="card-group text-center">
									<div class="card border-0 mx-1">
										<div class="embed-responsive embed-responsive-16by9">
											<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/IBMWQ1Z5sDw?rel=0&amp;controls=0" allowfullscreen></iframe>
										</div>
										<div class="card-body">
											<h5 class="card-title">Kelly T.<br>Encinitas, CA</h5>
										</div>
									</div>
									<div class="card border-0 mx-1">
										<div class="embed-responsive embed-responsive-16by9">
											<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/MrsU9fG2w3I?rel=0&amp;controls=0" allowfullscreen></iframe>
										</div>
										<div class="card-body">
											<h5 class="card-title">Brittnie  B.<br>Missouri City, TX</h5>
										</div>
									</div>
									<div class="card border-0 mx-1">
										<div class="embed-responsive embed-responsive-16by9">
											<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/iSh8gXjsT6w?rel=0&amp;controls=0" allowfullscreen></iframe>
										</div>
										<div class="card-body">
											<h5 class="card-title">Ashley J.<br>Tucson, AZ</h5>
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="container">
			<div class="row my-5 text-center">
				<p class="mt-4"><i>Fine Print: The free dinner voucher is for new guests who have never been to Dream Dinners. The voucher has no cash value and is not for sale; it is good for one redemption per a household, and recipient must be at least 18 years old. Not valid combined with any other offers or promotions. Code is only valid at participating locations.</i></p>
			</div>
		</div>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>