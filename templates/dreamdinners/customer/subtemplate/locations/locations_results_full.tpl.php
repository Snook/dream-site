<?php if(!empty($this->store_results_array)) { ?>

	<form method="post" class="row">
		<div class="col">
			<h2 class="text-center mb-4">For the best value, visit a local store.</h2>
			<?php if (!empty($this->zip_code)) { ?><p class="text-center">Search results for 50 miles around zip code <?php echo $this->zip_code; ?></p><?php } ?>
			<?php foreach( $this->store_results_array as $szState => $arStores ) { ?>
				<?php $count = 1; foreach( $arStores as $id => $arStore ) { $count++; ?>
					<div class="row pb-4">
						<div class="col-lg-8 mx-auto col-s-12 pl-sm-0">
							<div class="row bg-gray-100 border p-2">
								<div class="col-12 col-sm-7 col-md-6">
									<h3 class="text-uppercase font-weight-bold text-center d-sm-none"><?php echo $arStore["DAO_store"]->store_name; ?></h3>
									<div>
										<iframe
												class="border border-width-2-imp border-gray-400"
												width="100%"
												height="250"
												style="background-repeat: no-repeat; background-size: 100%; background-image:url('<?php echo IMAGES_PATH; ?>/stores/0.webp')"
												src="//www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_APIKEY; ?>&q=<?php echo (!empty($arStore["DAO_store"]->google_place_id)) ? 'place_id:' . $arStore["DAO_store"]->google_place_id : urlencode($arStore["DAO_store"]->address_linear); ?>" allowfullscreen>
										</iframe>
										<a href="#" class="row text-uppercase" target="map_view" data-linear_address="<?php echo $arStore["DAO_store"]->address_linear; ?>">
											<div class="col-1"><i class="fas fa-map-marked-alt"></i></div>
											<div class="col-11"><?php echo $arStore["DAO_store"]->address_html; ?></div>
										</a>
									</div>
								</div>
								<div class="col-12 col-sm-5 col-md-6 mt-4 mt-sm-0">
									<h3 class="text-uppercase font-weight-bold d-none d-sm-block mb-4"><?php echo $arStore["DAO_store"]->store_name; ?></h3>
									<div class="row mb-2">
										<div class="col">
											<?php if ($arStore["DAO_store"]->isComingSoon()) { ?>
												<span class="btn btn-default btn-block btn-select-checked">Coming Soon!</span>
											<?php } else { ?>
												<a href="<?php echo $arStore["DAO_store"]->getPrettyUrl(); ?>/order" rel="nofollow" class="btn btn-primary btn-block btn-spinner">View Menu &amp; Order</a>
											<?php } ?>
										</div>
									</div>

									<div class="row">
										<div class="col">
											<a href="<?php echo $arStore["DAO_store"]->getPrettyUrl(); ?>" class="btn btn-primary btn-block btn-spinner">Store Info &amp; Hours</a>
										</div>
									</div>

									<div class="mt-4">
										<a href="tel:<?php echo $arStore["DAO_store"]->telephone_day; ?>" class="text-decoration-none text-body"><?php echo $arStore["DAO_store"]->telephone_day; ?></a>
									</div>
									<div>
										<?php echo CTemplate::recaptcha_mailHideHtml($arStore["DAO_store"]->email_address, 'Email Store'); ?>
									</div>

									<?php if ($arStore["DAO_store"]->supports_delivery) { ?>
										<div class="mt-1">
											<i class="dd-icon icon-delivery text-green font-size-medium-large align-bottom"></i> <span class="font-italic">Home Delivery Available!</span>
										</div>
									<?php } ?>

									<div class="row mt-4">
										<div class="col">
											<a href="<?php echo $arStore["DAO_store"]->getPrettyUrl(); ?>/calendar" class="btn btn-primary btn-block btn-spinner">View store events</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</form>

<?php } ?>

<?php if (!empty($this->state_has_delivered) || !empty($this->delivered)) { ?>
	<form method="post" class="row">
		<div class="col">
			<h2 class="text-center mb-4">Can't visit a local store? We can ship to your door!</h2>
			<div class="row pb-4">
				<div class="col-lg-8 mx-auto col-s-12 pl-sm-0">
					<div class="row bg-gray-100 border p-2">
						<div class="col-12 col-sm-7 col-md-6">
							<h3 class="text-uppercase font-weight-bold text-center d-sm-none">Shipped</h3>
							<img src="<?php echo IMAGES_PATH; ?>/stores/dc-locations-image.webp" <?php if (empty($this->state_has_delivered)) { ?>data-click_id="select_delivered"<?php } ?> alt="Dream Dinners Delivered" class="img-fluid w-100" />
						</div>
						<div class="col-12 col-sm-5 col-md-6 mt-4 mt-sm-0">
							<h3 class="text-uppercase font-weight-bold d-none d-sm-block">Shipped</h3>
							<div>
								Get ready-to-cook dinners shipped to your front door.
							</div>
							<div class="mt-4">
								No subscription required. Order as you need it.
							</div>
							<?php if (empty($this->state_has_delivered)) { ?>
								<div class="mt-4">
									<button class="btn btn-primary btn-block btn-spinner" id="select_delivered" data-start-delivered-order="<?php echo $this->delivered->zip; ?>">
										View Menu &amp; Order
									</button>
								</div>
							<?php } ?>
							<?php if (!empty($this->state_has_delivered)) { ?>
								<div class="mt-4">
									<form method="post" class="form-inline">
										<div class="input-group">
											<input type="number" class="form-control" id="zip" name="zip" placeholder="Postal code">
											<div class="input-group-append">
												<button type="submit" value="Get Started" class="btn btn-primary">Search</button>
											</div>
										</div>
									</form>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
<?php } ?>

<?php if (empty($this->state_has_delivered) && empty($this->delivered) && empty($this->store_results_array)) { ?>
	<div class="row my-4">
		<div class="col col-lg-8 mx-auto">
			<!--<p><b>Note to Clemmons Guests:</b> We are working through some logistics issues at our distribution shipping center. We will have shipping to your door available soon. We apologize for the inconvenience and will email you as soon as we are able to ship to your location.</p>-->
			<p>Oh no! We do not currently have a Dream Dinners location near you and we have paused shipping to your area. As we continue to grow, we hope to be able to serve you and your family again soon. If you would like to be contacted when Dream Dinners is available in your community, please complete the contact form below.<br /><br />Note: You will be required to verify your email address after submission. Check your inbox for an email from Dream Dinners with the subject line of "Action Required: Confirm Your Sign-Up".</p>
			<p>Want to learn more about owning a Dream Dinners Assembly Kitchen, visit <a href="https://www.dreamdinnersfranchise.com/" target="_blank">DreamDinnersFranchise.com</a>.</p>
			<p>Want meal planning tips and family activities from Dream Dinners?</p>

			<ul>
				<li><a href="https://facebook.com/dreamdinners" target="_blank">Like us on Facebook</a></li>
				<li><a href="https://instagram.com/dreamdinners" target="_blank">Follow us on Instagram</a></li>
				<li><a href="https://blog.dreamdinners.com/" target="_blank">Subscribe to our Blog</a></li>
			</ul>
		</div>
	</div>
<?php } ?>

<?php if (empty($this->store_results_array) && !empty($this->delivered)) {?>
	<div class="row my-4">
		<div class="col col-lg-8 mx-auto">
			<h2 class="text-center mb-4">We do not have a local store within 50 miles of your search.</h2>
			As we continue to grow, we hope to add additional locations in your area.
		</div>
	</div>
<?php } ?>

<?php if (empty($this->state_has_delivered) && !empty($this->store_results_array) && empty($this->delivered)) {?>
	<div class="row my-4">
		<div class="col col-lg-8 mx-auto">
			<h2 class="text-center mb-4">We are not able to ship to your area yet.</h2>
			As we continue to grow, we will be adding additional shipping areas. If you have a local store near you, check to see if home delivery is an option!
		</div>
	</div>
<?php } ?>

<?php if (empty($this->state_has_delivered) && empty($this->store_results_array) && empty($this->delivered)) { ?>

	<div class="row my-4">
		<div class="col col-lg-8 mx-auto">
			<h4>Your Contact Information</h4>
			<p>We will not sell or distribute your information. It will be used solely for the purpose intended. <a href="/privacy" target="_blank">View our Privacy Policy</a> for complete details.</p>
		</div>
	</div>

	<form method="post" action="//oi.vresp.com?fid=2b7c825d75" target="vr_optin_popup" class="row needs-validation" novalidate>
		<div class="col col-lg-8 mx-auto">
			<div class="row mb-2">
				<div class="col-6">
					<?php echo $this->vresp['first_name_html']; ?>
				</div>
				<div class="col-6">
					<?php echo $this->vresp['last_name_html']; ?>
				</div>
			</div>
			<div class="row mb-2">
				<div class="col-4">
					<?php echo $this->vresp['email_address_html']; ?>
				</div>
				<div class="col-4">
					<?php echo $this->vresp['state_html']; ?>
				</div>
				<div class="col-4">
					<?php echo $this->vresp['postalcode_html']; ?>
				</div>
			</div>
			<div class="row mt-4">
				<div class="col col-lg-6 mx-auto">
					<input type="submit" value="Submit" class="btn btn-primary btn-block" />
				</div>
			</div>
		</div>
	</form>

<?php }  ?>