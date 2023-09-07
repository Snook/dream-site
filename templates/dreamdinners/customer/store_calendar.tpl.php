<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - What's new &amp; calendar"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true) . '/calendar'); ?>
<?php $this->assign('order_process_navigation_page', 'session_menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1>What's new at <?php echo $this->DAO_store->store_name; ?> Dream Dinners location</h1>
				<p class="font-marker">We offer real food, made from scratch, so your life can feel just a little easier.</p>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<!-- Header-->
	<section>
		<div class="container mb-5">

			<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

			<!-- Tabs -->
			<?php if ($this->storePromos || $this->storeOHEvents) { ?>
				<div class="row mb-4">
					<div class="col">
						<ul class="nav nav-pills nav-justified mb-4">
							<?php if ($this->storePromos) { ?>
								<li class="nav-item">
									<a class="nav-link text-uppercase font-weight-bold active" id="promos-tab" data-urlpush="false" data-toggle="tab" data-target="#promos" href="/?page=store&amp;id=<?php echo $this->DAO_store->id; ?>&amp;tab=promos" role="tab" aria-controls="promos" aria-selected="true">Store promotions</a>
								</li>
							<?php } ?>
							<?php if ($this->storeOHEvents) { ?>
								<li class="nav-item">
									<a class="nav-link text-uppercase font-weight-bold <?php if (!$this->storePromos) { ?>active<?php } ?>" id="events-tab" data-urlpush="false" data-toggle="tab" data-target="#events" href="/?page=store&amp;id=<?php echo $this->DAO_store->id; ?>&amp;tab=events" role="tab" aria-controls="events" aria-selected="false">Store events</a>
								</li>
							<?php } ?>
						</ul>

						<div class="tab-content" id="storeDetailsContent">
							<?php if ($this->storePromos) { ?>
								<div class="tab-pane fade show active" id="promos" role="tabpanel" aria-labelledby="promos-tab">
									<?php $count = 0; foreach ($this->storePromos AS $storePromo) { $count++; ?>
										<div class="row">
											<div class="col text-center">
												<p class="font-weight-bold text-uppercase"><?php echo $storePromo->title; ?></p>
												<p><?php echo $storePromo->message; ?></p>
												<p class="font-italic text-muted font-size-small">Until <?php echo CTemplate::dateTimeFormat($storePromo->message_end, MONTH_DAY_YEAR); ?></p>
												<?php if ($count != count($this->storePromos)) { ?>
													<hr />
												<?php } ?>
											</div>
										</div>
									<?php } ?>
								</div>
							<?php } ?>
							<?php if ($this->storeOHEvents) { ?>
								<div class="tab-pane fade <?php if (!$this->storePromos) { ?>show active<?php } ?>" id="events" role="tabpanel" aria-labelledby="events-tab">
									<div class="row mb-3">
										<div class="col-lg-4 mb-2">
											<img src="<?php echo IMAGES_PATH; ?>/landing_pages/open-house-meal-prep-540x360.jpg" alt="Open House Meal Prep" class="img-fluid" />
										</div>
										<div class="col-lg-8">
											<p class="font-weight-bold text-uppercase">Try our meal prep experience at an open house</p>
											<p>Dream Dinners will change how you meal plan, cook and gather at the table with your family. Join us in our meal prep kitchen to receive three medium family-style, ready-to-cook meals for just $60.</p>
											<div class="row">
												<?php foreach ($this->storeOHEvents AS $storeEvent) { ?>
													<div class="col-lg-6 mb-2">
														<a href="/session/<?php echo $storeEvent['extendedProps']['id']; ?>" class="btn btn-primary btn-block">
															<div><?php echo CTemplate::dateTimeFormat($storeEvent['start'], FULL_DAY); ?></div>
															<div><?php echo CTemplate::dateTimeFormat($storeEvent['start'], FULL_MONTH_DAY_YEAR); ?> at <?php echo CTemplate::dateTimeFormat($storeEvent['start'], TIME_ONLY); ?></div>
														</a>
													</div>
												<?php } ?>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col">
											<p class="font-italic text-muted font-size-small">*Our Open House events are only available to new Dream Dinners guests or guests that have not attended a Dream Dinners session in over a year.</p>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>

				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

			<?php } ?>

		</div>
	</section>


<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>