<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/fullcalendar/main.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/store.min.js'); ?>
<?php $this->setScriptVar('calendarJS = ' . $this->calendarJS . ';'); ?>
<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - What's new &amp; calendar"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true) . '/calendar'); ?>
<?php $this->assign('order_process_navigation_page', 'session-menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1>What's new at Dream Dinners <?php echo $this->DAO_store->store_name; ?></h1>
				<h3 class="font-marker">We offer real food, made from scratch, so your life can feel a little easier.</h3>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<section>
		<div class="container mb-5">

			<?php if (!empty($this->DAO_store->ActivePromoArray)) { ?>
				<div class="row mb-4">
					<div class="col">
						<h3 id="promotions" class="text-uppercase font-weight-bold text-center">Store promotions</h3>

						<?php $count = 0; foreach ($this->DAO_store->ActivePromoArray AS $DAO_site_message) { $count++; ?>
							<div class="row">
								<div class="col text-center">
									<p class="font-weight-bold text-uppercase"><?php echo $DAO_site_message->title; ?></p>
									<p><?php echo $DAO_site_message->message; ?></p>
									<p class="font-italic text-muted font-size-small">Until <?php echo CTemplate::dateTimeFormat($DAO_site_message->message_end, MONTH_DAY_YEAR); ?></p>
									<?php if ($count != count($this->DAO_store->ActivePromoArray)) { ?>
										<hr />
									<?php } ?>
								</div>
							</div>
						<?php } ?>

					</div>
				</div>

				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />
			<?php } ?>

			<?php if (!$this->DAO_store->isComingSoon()) { ?>
				<div class="row">
					<div class="col text-center">
						<h3 class="text-uppercase font-weight-bold text-center">Store calendar</h3>
						<p>Our calendar represents dates and times available to reserve your order based on how you would like to get your meals. Options may include Pick Up, Home Delivery, You Assemble at Store, Community Pick Up locations and Events. Many stores have open hours to come in shop out of the freezer listed on the Location Info page.</p>
						<?php if ($this->has_meal_customization_sessions){?>
							<p>Times listed below are available for everyone! If you would like Meal Customization, please select a time marked with a <i class="dd-icon icon-customize text-orange font-size-small"></i> so we can have your dinners customized in time for your pick up or delivery. Customization options available at checkout.</p>
						<?php } ?>
					</div>
					<div class="col-12 text-center">
						<h2 id="calendar_month" class="d-block d-md-none"></h2>
					</div>
					<div class="col-12">
						<div id="calendar" data-fullcalendar="calendar"></div>
					</div>
					<div id="calendar_results" class="col"></div>
				</div>

				<div class="row">
					<div class="col">
						<a href="<?php echo $this->DAO_store->getPrettyUrl(); ?>" class="btn btn-primary btn-block">Start your order</a>
					</div>
				</div>
			<?php } ?>

		</div>
	</section>

<?php include $this->loadTemplate('customer/subtemplate/store/store_footer.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>