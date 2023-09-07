<?php $this->assign('page_title', 'Garmin Meal Assembly at Dream Dinners Store');?>
<?php $this->assign('no_navigation', true); ?>
<?php $this->assign('no_footer', true); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
		<section>
			<div class="container">
				<div class="row mt-2 mb-1">
					<div class="col">
						<a href="https://garmin.dreamdinners.com/">&lt; Deliver to door</a>
					</div>
				</div>
				<div class="row mb-5">
					<div class="col text-center">
						<img src="<?php echo IMAGES_PATH; ?>/corporate_crate/dream-dinners-garmin-logo.png" alt="Dream Dinners and Garmin" class="img-fluid" border="0" />
					</div>
				</div>
				<div class="row mb-5">
					<div class="col font-weight-bold text-center bg-green-dark text-white pt-3">
						<p>New to Dream Dinners?</p>
						<p>Customize up to six family dinners for only $84.95 with our Meal Prep Starter Pack!</p>
					</div>
				</div>
				<div class="row mb-5">
					<div class="col text-center">
						<img src="<?php echo IMAGES_PATH; ?>/corporate_crate/store-intro-offer-header1000x250.jpg" alt="Dream Dinners" class="img-fluid" border="0" />
						<p class="font-size-large text-green-dark mt-4">Give Assembling In Our Store A Try</p>
						<p class="font-size-medium">Select a State to See Available Locations</p>
						<p>
							<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#multiCollapseKansas" aria-expanded="false" aria-controls="multiCollapseKansas">Kansas</button> |
							<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#multiCollapseOregon" aria-expanded="false" aria-controls="multiCollapseOregon">Oregon</button>
						</p>
						<div id="accordion-GL">
							<div class="row">
								<div class="col">
									<div class="collapse multi-collapse" id="multiCollapseKansas" data-parent="#accordion-GL">
										<div class="card card-body">
											<?php include $this->loadTemplate('customer/static/subtemplate/garmin/garmin_kansas.tpl.php'); ?>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<div class="collapse multi-collapse" id="multiCollapseOregon" data-parent="#accordion-GL">
										<div class="card card-body">
											<?php include $this->loadTemplate('customer/static/subtemplate/garmin/garmin_oregon.tpl.php'); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row mb-5">
					<div class="col text-right">
						<a href="/locations">View other locations &gt;</a>
					</div>
				</div>
				<div class="row mb-5">
					<div class="col">
						<p class="font-italic">*<span class="font-weight-bold">Use your Garmin corporate email address to place your first order in order to access all our corporate offerings.</span> After your initial order is complete, you can log in to your account on DreamDinners.com and change your email address if you would prefer to receive future communication at another email address.</p>
					</div>
				</div>
			</div>
		</section>
	</main>


<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>