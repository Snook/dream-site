<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - Fundraisers"); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<a href="/location/<?php echo $this->DAO_store->id; ?>" class="btn btn-primary"><span class="pr-2">&#10094;</span> Store Information</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1><?php echo $this->DAO_store->store_name; ?> Fundraisers</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>

		<div class="row mt-5">
			<div class="col text-center">
				<?php if (!$this->showOrgSpecific) { ?>
					<p>Below is a list of our current fundraisers. If you do not find one you are looking for or would like to schedule a fundraiser for your organization, contact us at <?php echo $this->DAO_store->telephone_day; ?>.</p>
				<?php } ?>
				<?php if ($this->showOrgSpecific) { ?>
					<p class="text-center"><img src="/theme/dreamdinners/images/events_programs/fundraisers.jpg" alt="Fundraising at Dream Dinners" class="img-fluid" /></p>
				<?php } ?>
			</div>
		</div>
	</header>

	<section>
		<div class="container">
			<?php if (!empty($this->fundraiserArray)) { ?>
			<?php foreach ($this->fundraiserArray AS $month) { ?>
				<div class="row mb-4">
					<div class="col">
						<?php foreach ($month['fundraisers'] AS $fundraiser) { ?>
							<div class="row mb-5">
								<div class="col">

									<div class="row">
										<div class="col text-center">
											<h4><?php if ($this->showOrgSpecific) { ?>Help the <?php } ?><?php echo $fundraiser['fundraiser']->fundraiser_name; ?></h4>
											<p><?php echo $fundraiser['fundraiser']->fundraiser_description; ?></p>
											<?php if ($this->showOrgSpecific) { ?>
												<p>At this event, you will get three delicious medium dinners to take home and enjoy with your family. Help a deserving cause, while also learning about how easy dinnertime can be. We will donate $10 from each order to <?php echo $fundraiser['fundraiser']->fundraiser_name; ?>.</p>
											<?php } ?>
										</div>
									</div>

									<div class="row justify-content-center">
										<?php if ($this->showOrgSpecific) { ?>
											<div class="col-12 text-center">
												<h2>Select a time that works for you</h2>
											</div>
										<?php } ?>
										<?php foreach ($fundraiser['sessions'] AS $date => $sessions) { ?>
											<div class="col-md-6 col-lg-4 mb-3">
												<div class="card w-100">
													<div class="card-body">
														<p class="card-title"><?php echo CTemplate::dateTimeFormat(strtotime($date), MONTH_DAY); ?> <span class="font-size-small ml-1 text-muted"><?php echo CTemplate::dateTimeFormat(strtotime($date), FULL_DAY); ?></span></p>
														<?php foreach ($sessions AS $session) { ?>
															<a href="/session/<?php echo $session->id; ?>" class="btn btn-primary w-100 mb-1"><?php echo (($session->session_type_string == 'fundraiser_pick_up') ? 'Pick Up' : 'Assembly'); ?> - <?php echo CTemplate::dateTimeFormat($session->session_start, TIME_ONLY); ?></a>
														<?php } ?>
													</div>
												</div>
											</div>
										<?php } ?>
									</div>

									<div class="row">
										<div class="col text-center text-muted">
											*Don’t know what type of session to choose? At assembly sessions, you will customize and prep your own meals for your family with our easy to follow recipe cards. At a pick up session,  our trained staff preps your meals for you and you would just stop by to pick them up.
										</div>
									</div>

								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php } else { ?>
			<h3 class="text-center">We do not have any fundraiser events currently scheduled. If you have questions or would like to schedule a fundraiser for your organization, contact us at <?php echo $this->DAO_store->telephone_day; ?>.</h3>
		<?php } ?>
	</section>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>