<?php if(!empty($this->store_results_array)) { ?>

	<form method="post" class="row">
		<div class="col">
			<h2 class="text-center mb-4">For the best value, visit a local store.</h2>
			<?php if (!empty($this->zip_code)) { ?><p class="text-center">Search results for 30 miles around zip code <?php echo $this->zip_code; ?></p><?php } ?>
			<?php foreach($this->store_results_array as $szState => $storArray ) { ?>
				<?php $count = 1; foreach($storArray as $id => $store ) { $count++; ?>
					<div class="row pb-4">
						<div class="col-lg-8 mx-auto col-s-12 pl-sm-0">
							<div class="row bg-gray-100 border p-2">
								<div class="col-12 col-sm-7 col-md-6">
									<?php if ($store["type"] == 'COMMUNITY_PICK_UP') { ?>
										<h3 class="text-uppercase font-weight-bold text-center d-sm-none">Community Pick Up location</h3>
									<?php } else { ?>
										<h3 class="text-uppercase font-weight-bold text-center d-sm-none"><?php echo $store["DAO_store"]->store_name; ?> Store</h3>
									<?php } ?>
									<?php if ($store["DAO_store"]->hasAvailableSessionType(array('PICK_UP', 'HOME_DELIVERY', 'ASSEMBLY')) && $store["type"] != 'COMMUNITY_PICK_UP') { ?>
										<div class="row d-sm-none mb-3">
											<div class="col-12 text-center text-uppercase font-weight-bold mb-2">Available services</div>
											<?php if ($store["DAO_store"]->hasAvailableSessionType('PICK_UP')) { ?>
												<div class="col text-center">
													<i class="dd-icon icon-store-front text-green font-size-medium-large align-bottom"></i>
													<div class="font-italic">Store Pick Up</div>
												</div>
											<?php } ?>
											<?php if ($store["DAO_store"]->hasAvailableSessionType('HOME_DELIVERY')) { ?>
												<div class="col text-center">
													<i class="dd-icon icon-delivery text-green font-size-medium-large align-bottom"></i>
													<div class="font-italic">Home Delivery</div>
													<?php if ($store["DAO_store"]->showDeliveryRadius()) { ?>
														<div class="font-size-small text-muted">Available up to <?php echo $store["DAO_store"]->delivery_radius; ?> miles from the store.</div>
													<?php } ?>
												</div>
											<?php } ?>
											<?php if ($store["DAO_store"]->hasAvailableSessionType('ASSEMBLY')) { ?>
												<div class="col text-center">
													<i class="dd-icon icon-measuring_cup text-green font-size-medium-large align-bottom"></i>
													<div class="font-italic">Assemble In Store</div>
												</div>
											<?php } ?>
										</div>
									<?php } ?>
									<div>
										<?php if ($store["DAO_store"]->hasPublicAddress()) { ?>
											<iframe
													class="border border-width-2-imp border-gray-400"
													width="100%"
													height="250"
													style="background-repeat: no-repeat; background-size: 100%; background-image:url('<?php echo IMAGES_PATH; ?>/stores/0.webp')"
												<?php if ($store["type"] == 'COMMUNITY_PICK_UP') { ?>
													src="//www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_APIKEY; ?>&q=<?php echo urlencode($store["DAO_store_pickup_location"]->generateAddressLinear()); ?>"
												<?php } else { ?>
													src="//www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_APIKEY; ?>&q=<?php echo (!empty($store["DAO_store"]->google_place_id)) ? 'place_id:' . $store["DAO_store"]->google_place_id : urlencode($store["DAO_store"]->generateAddressLinear()); ?>"
												<?php } ?>
													allowfullscreen>
											</iframe>
											<a href="#" class="row text-uppercase" target="map_view" data-linear_address="<?php echo $store["DAO_store"]->generateAddressLinear(); ?>">
												<div class="col-1"><i class="fas fa-map-marked-alt"></i></div>
												<?php if ($store["type"] == 'COMMUNITY_PICK_UP') { ?>
													<div class="col-11"><?php echo $store["DAO_store_pickup_location"]->generateAddressHTML(); ?></div>
												<?php } else { ?>
													<div class="col-11"><?php echo $store["DAO_store"]->generateAddressHTML(); ?></div>
												<?php } ?>
											</a>
										<?php } else { ?>
											<img src="<?php echo IMAGES_PATH; ?>/stores/<?php echo $store["DAO_store"]->id; ?>.webp" class="img-fluid border border-width-2-imp border-gray-400" alt="<?php echo $store["DAO_store"]->store_name; ?>"/>
										<?php } ?>
									</div>
								</div>
								<div class="col-12 col-sm-5 col-md-6 mt-4 mt-sm-0">
									<?php if ($store["type"] == 'COMMUNITY_PICK_UP') { ?>
										<h3 class="text-uppercase font-weight-bold d-none d-sm-block">Community Pick Up location</h3>
										<div class="text-uppercase mb-2"><?php echo $store["DAO_store_pickup_location"]->location_title; ?></div>
										<div class="font-size-small mb-4">Provided by the <?php echo $store["DAO_store_pickup_location"]->DAO_store->store_name; ?> store</div>
									<?php } else { ?>
										<h3 class="text-uppercase font-weight-bold d-none d-sm-block mb-3"><?php echo $store["DAO_store"]->store_name; ?> Store</h3>
									<?php } ?>
									<?php if ($store["DAO_store"]->hasAvailableSessionType(array('PICK_UP', 'HOME_DELIVERY', 'ASSEMBLY')) && $store["type"] != 'COMMUNITY_PICK_UP') { ?>
										<div class="row d-none d-sm-flex mb-3">
											<div class="col-12 text-center text-uppercase font-weight-bold mb-2">Available services</div>
											<?php if ($store["DAO_store"]->hasAvailableSessionType('PICK_UP')) { ?>
												<div class="col text-center">
													<i class="dd-icon icon-store-front text-green font-size-medium-large align-bottom"></i>
													<div class="font-italic">Store Pick Up</div>
												</div>
											<?php } ?>
											<?php if ($store["DAO_store"]->hasAvailableSessionType('HOME_DELIVERY')) { ?>
												<div class="col text-center">
													<i class="dd-icon icon-delivery text-green font-size-medium-large align-bottom"></i>
													<div class="font-italic">Home Delivery</div>
													<?php if ($store["DAO_store"]->showDeliveryRadius()) { ?>
														<div class="font-size-small text-muted">Available up to <?php echo $store["DAO_store"]->delivery_radius; ?> miles from the store.</div>
													<?php } ?>
												</div>
											<?php } ?>
											<?php if ($store["DAO_store"]->hasAvailableSessionType('ASSEMBLY')) { ?>
												<div class="col text-center">
													<i class="dd-icon icon-measuring_cup text-green font-size-medium-large align-bottom"></i>
													<div class="font-italic">Assemble In Store</div>
												</div>
											<?php } ?>
										</div>
									<?php } ?>
									<div class="row mb-2">
										<div class="col">
											<?php if ($store["DAO_store"]->isComingSoon()) { ?>
												<span class="btn btn-default w-100 btn-select-checked">Coming Soon!</span>
											<?php } else if (!$store["DAO_store"]->hasAvailableCustomerMenu()) { ?>
												<span class="btn btn-primary w-100 disabled">Menu not available</span>
											<?php } else { ?>
												<?php if ($store["type"] == 'COMMUNITY_PICK_UP') { ?>
													<a href="<?php echo $store["DAO_store"]->getPrettyUrl(); ?>/community-pick-up#<?php echo $store["DAO_store_pickup_location"]->generateAnchor(); ?>" rel="nofollow" class="btn btn-primary w-100 btn-spinner">View Pick Up Times</a>
												<?php } else { ?>
													<a href="<?php echo $store["DAO_store"]->getPrettyUrl(); ?>/order" rel="nofollow" class="btn btn-primary w-100 btn-spinner">View Menu &amp; Order</a>
												<?php } ?>
											<?php } ?>
										</div>
									</div>

									<?php if ($store["type"] != 'COMMUNITY_PICK_UP') { ?>
										<div class="row">
											<div class="col">
												<a href="<?php echo $store["DAO_store"]->getPrettyUrl(); ?>" class="btn btn-primary w-100 btn-spinner">Store Info &amp; Hours</a>
											</div>
										</div>
									<?php } ?>

									<div class="mt-4">
										<a href="tel:<?php echo $store["DAO_store"]->telephone_day; ?>" class="text-decoration-none text-body"><?php echo $store["DAO_store"]->telephone_day; ?></a>
									</div>
									<div>
										<?php echo CTemplate::recaptcha_mailHideHtml($store["DAO_store"]->email_address, 'Email Store'); ?>
									</div>

									<?php if ($store["type"] != 'COMMUNITY_PICK_UP') { ?>
										<div class="row mt-4">
											<div class="col">
												<a href="<?php echo $store["DAO_store"]->getPrettyUrl(); ?>/calendar" class="btn btn-primary w-100 btn-spinner">View store events</a>
											</div>
										</div>
									<?php } ?>
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
							<img src="<?php echo IMAGES_PATH; ?>/stores/dc-locations-image.webp" <?php if (empty($this->state_has_delivered)) { ?>data-click_id="select_delivered"<?php } ?> alt="Dream Dinners" class="img-fluid w-100" />
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
								<?php if ($this->shipping_has_inventory) { ?>
									<div class="mt-4">
										<button class="btn btn-primary w-100 btn-spinner" id="select_delivered" data-start-delivered-order="<?php echo $this->delivered->zip; ?>">
											View Menu &amp; Order
										</button>
									</div>
								<?php } else { ?>
									<div class="mt-4">
										<div class="alert alert-warning">
											We can ship to you, but our fridge is currently empty. We are busy prepping our new menu. Check back soon for a new selection of meals.
										</div>
									</div>
								<?php } ?>
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
			<p>Oh no! We do not currently have a Dream Dinners location near you or we are not currently shipping to your area. As we continue to grow, we hope to be able to serve you and your family again soon.</p>
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
			<h2 class="text-center mb-4">We do not have a local store within 30 miles of your search.</h2>
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