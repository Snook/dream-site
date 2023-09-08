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
				<h1>What's new at <?php echo $this->DAO_store->store_name; ?> Dream Dinners location</h1>
				<p class="font-marker">We offer real food, made from scratch, so your life can feel just a little easier.</p>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<section>
		<div class="container mb-5">

			<?php if (!$this->DAO_store->isComingSoon()) { ?>
				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

				<div class="row">
					<div class="col text-center">
						<h3 class="text-uppercase font-weight-bold text-center">
							Store Calendar
						</h3>
						<p>Our calendar represents dates and times available to reserve your order based on how you would like to get your meals. Options may include Pick Up, Home Delivery,You Assemble at Store, Community Pick Up locations and Events. Many stores have open hours to come in shop out of the freezer not listed here. Contact the store for any questions.</p>
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
						<a href="/session-menu" class="btn btn-primary btn-block">Start your order</a>
					</div>
				</div>
			<?php } ?>

		</div>
	</section>

	<section>
		<div class="container-fluid my-5">
			<div class="row mb-5">
				<div class="col-md-6 p-5 text-center mx-auto bg-gray">
					<h2 class="mb-4 font-weight-bold">Job Opportunities</h2>
					<p>
						Dream Dinners is an innovative concept in meal preparation that eliminates the stress of dealing with dinner – We remove menu planning, shopping & prep-work from the equation, leaving more quality time for families.
						We are looking for amazing team members to help us change more lives and bring Homemade, Made Easy meals into the community.
					</p>
					<?php if (!empty($this->DAO_store->job_positions_available)) { ?>
						<h4 class="text-uppercase font-weight-bold">Available Positions</h4>
						<p>Our store is hiring for the following positions</p>
						<div class="row">
							<?php foreach ($this->DAO_store->job_positions_available AS $job) { ?>
								<div class="col-12 mb-md-3">
									<div class="card">
										<div class="card-body bg-gray-light">
											<p class="card-text text-center"><?php echo $job['title']; ?></p>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
						<p class="mt-3">To apply for one of the positions above, please send a resume to <a href="mailto:<?php echo $this->DAO_store->email_address; ?>"><?php echo $this->DAO_store->email_address; ?></a></p>
					<?php } else { ?>
						<p>Join our Dream Dinners team. Feel free to submit your resume to <a href="mailto:<?php echo $this->DAO_store->email_address; ?>"><?php echo $this->DAO_store->email_address; ?></a>.</p>
					<?php } ?>
				</div>
				<div class="col-md-6 bg-green text-white text-center p-5 mx-auto">
					<h2>At Dream Dinners, <span class="font-weight-bold">our mission</span> is to make gathering around the family table a cornerstone of daily life. </h2>
				</div>
			</div>
		</div>
	</section>


<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>