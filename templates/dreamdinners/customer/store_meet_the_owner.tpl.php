<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - Meet the owner"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true) . '/meet-the-owner'); ?>
<?php $this->assign('order_process_navigation_page', 'session_menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1>Meet the owner of <?php echo $this->DAO_store->store_name; ?> Dream Dinners location</h1>
				<p class="font-marker">We offer real food, made from scratch, so your life can feel just a little easier.</p>
			</div>
		</div>
		<div class="row">
			<div class="col text-center">
				<ul class="nav justify-content-around justify-content-md-between">
					<li class="nav-item mb-3">
						<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-green-light text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="/<?php echo $this->DAO_store->getPrettyUrl(); ?>">
							<span>Location info</span>
						</a>
					</li>
					<li class="nav-item mb-3">
						<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-cyan-dark text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="/<?php echo $this->DAO_store->getPrettyUrl(); ?>/meet-the-owner">
							<span>Meet the owner</span>
						</a>
					</li>
					<li class="nav-item mb-3">
						<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-orange text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="/<?php echo $this->DAO_store->getPrettyUrl(); ?>/calendar">
							<span>What's New &amp; Store Calendar</span>
						</a>
					</li>
					<li class="nav-item mb-3">
						<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-green-dark text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="/menu/<?php echo $this->DAO_store->id; ?>">
							<span>Order Now</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</header>

	<!-- Header-->
	<section>
		<div class="container mb-5">

			<div class="row">

				<?php if (!empty($this->DAO_store->bio_primary_party_name)) { ?>
					<div class="col-md-6">
						<div class="row">
							<div class="col-12 mb-3">
								<img src="/theme/dreamdinners/images/stores/bio/portrait-<?php echo $this->DAO_store->id; ?>-1.webp" alt="<?php echo $this->DAO_store->bio_primary_party_name; ?> portrait" class="img-fluid w-100">
							</div>
							<div class="col-12">
								<h5 class="font-weight-medium mb-0"><?php echo $this->DAO_store->bio_primary_party_name; ?></h5>
								<h6 class="text-muted"><?php echo $this->DAO_store->bio_primary_party_title; ?></h6>
								<p><?php echo nl2br($this->DAO_store->bio_primary_party_story); ?></p>
							</div>
						</div>
					</div>
				<?php } ?>

				<?php if (!empty($this->DAO_store->bio_secondary_party_name)) { ?>
					<div class="col-md-6">
						<div class="row">
							<div class="col-12 mb-3">
								<img src="/theme/dreamdinners/images/stores/bio/portrait-<?php echo $this->DAO_store->id; ?>-2.webp" alt="Mill Creek" class="img-fluid w-100">
							</div>
							<div class="col-12">
								<h5 class="font-weight-medium mb-0"><?php echo $this->DAO_store->bio_secondary_party_name; ?></h5>
								<h6 class="text-muted"><?php echo $this->DAO_store->bio_secondary_party_title; ?></h6>
								<p><?php echo nl2br($this->DAO_store->bio_secondary_party_story); ?></p>
							</div>
						</div>
					</div>
				<?php } ?>

			</div>

			<?php if (!empty($this->DAO_store->bio_team_description)) { ?>
				<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />

				<div class="row">

					<div class="col">
						<div class="row">
							<div class="col-md-6 mb-3 mb-md-0">
								<img src="/theme/dreamdinners/images/stores/bio/portrait-<?php echo $this->DAO_store->id; ?>-team.webp" alt="<?php echo $this->DAO_store->store_name; ?> team portrait" class="img-fluid w-100">
							</div>
							<div class="col-md-6">
								<h5 class="font-weight-medium mb-0"><?php echo $this->DAO_store->store_name; ?> team members</h5>
								<p><?php echo nl2br($this->DAO_store->bio_team_description); ?></p>
							</div>
						</div>
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