<?php $this->setPreload(IMAGES_PATH . "/stores/store-landing.jpg", "image"); ?>
<?php $this->assign('page_title', htmlspecialchars($this->DAO_store->store_name) . " - Meet the owner"); ?>
<?php $this->assign('canonical_url', $this->DAO_store->getPrettyUrl(true) . '/meet-the-owner'); ?>
<?php $this->assign('order_process_navigation_page', 'session-menu'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row mb-3">
			<div class="col text-center">
				<h1>Meet the owner of the <?php echo $this->DAO_store->store_name; ?> Dream Dinners Location</h1>
				<h3 class="font-have-heart-two">We offer real food, made from scratch, so your life can feel a little easier.</h3>
			</div>
		</div>
		<?php include $this->loadTemplate('customer/subtemplate/store/store_navigation.tpl.php'); ?>
	</header>

	<section>
		<div class="container mb-5">

			<div class="row">

				<?php if (($this->DAO_store->hasBioPrimary())) { ?>
					<div class="col-md-6 mx-auto">
						<div class="row">
							<div class="col-12 mb-3">
								<img src="/theme/dreamdinners/images/stores/bio/portrait-<?php echo $this->DAO_store->id; ?>-1.webp" alt="<?php echo $this->DAO_store->bio_primary_party_name; ?>" class="img-fluid w-100">
							</div>
							<div class="col-12">
								<h5 class="font-weight-medium mb-0"><?php echo $this->DAO_store->bio_primary_party_name; ?></h5>
								<h6 class="text-muted"><?php echo $this->DAO_store->bio_primary_party_title; ?></h6>
								<p><?php echo nl2br($this->DAO_store->bio_primary_party_story); ?></p>
							</div>
						</div>
					</div>
				<?php } ?>

				<?php if ($this->DAO_store->hasBioSecondary()) { ?>
					<div class="col-md-6 mx-auto">
						<div class="row">
							<div class="col-12 mb-3">
								<img src="/theme/dreamdinners/images/stores/bio/portrait-<?php echo $this->DAO_store->id; ?>-2.webp" alt="<?php echo $this->DAO_store->bio_secondary_party_name; ?>" class="img-fluid w-100">
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

			<?php if ($this->DAO_store->hasBioTeam()) { ?>

				<?php if ($this->DAO_store->hasBioPrimary() || $this->DAO_store->hasBioSecondary()) { // show hr only if there are owner bios ?>
					<hr class="border-green-light border-width-3-5-imp my-5 border-top-style-dotted" />
				<?php } ?>

				<div class="row">

					<div class="col">
						<div class="row">
							<div class="col-md-6 mb-3 mb-md-0">
								<img src="/theme/dreamdinners/images/stores/bio/portrait-<?php echo $this->DAO_store->id; ?>-team.webp" alt="<?php echo $this->DAO_store->store_name; ?> Team" class="img-fluid w-100">
							</div>
							<div class="col-md-6">
								<h5 class="font-weight-medium mb-0">Your <?php echo $this->DAO_store->store_name; ?> Team</h5>
								<p><?php echo nl2br($this->DAO_store->bio_team_description); ?></p>
							</div>
						</div>
					</div>

				</div>
			<?php } ?>

		</div>
	</section>

<?php include $this->loadTemplate('customer/subtemplate/store/store_footer.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>