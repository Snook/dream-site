<?php $this->assign('og_title', ((is_array($this->view_badge)) ? $this->view_badge['title'] . ' - PLATEPOINTS Badge' : 'PLATEPOINTS')); ?>
<?php $this->assign('og_url', ((is_array($this->view_badge)) ? HTTPS_BASE . 'platepoints?badge=' . $this->view_badge['level'] : HTTPS_BASE . 'platepoints')); ?>
<?php $this->assign('og_image', ((is_array($this->view_badge)) ? HTTPS_BASE . RELATIVE_IMAGES_PATH . '/style/platepoints/badge-' . $this->view_badge['image'] . '-119x119.png': '')); ?>
<?php $this->assign('og_description', false); ?>
<?php $this->assign('canonical_url', ((is_array($this->view_badge)) ? HTTPS_BASE . 'platepoints?badge=' . $this->view_badge['level'] : HTTPS_BASE . 'platepoints')); ?>
<?php $this->assign('page_title', ((is_array($this->view_badge)) ? $this->view_badge['title'] . ' - PLATEPOINTS Badge' : 'PLATEPOINTS')); ?>
<?php $this->assign('page_description', false); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>PLATEPOINTS</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">
			</div>
		</div>
	</header>

	<main class="container">
		<div class="row mb-5">
			<div class="col text-center my-1">
				<h2 class="font-weight-bold mb-2 mt-5" style="color: #f07722;">Reward Yourself!</h2>
				<h3>We dreamed up a new way to earn points.</h3>
			</div>
		</div>

		<div class="row my-1">
			<div class="col">
				<div class="card-group text-center mb-2">
					<div class="card border-0">
						<div class="card-body">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-1point.png" alt="PlatePoints 1 Point">
							<p style="margin-top: 10px;">$1 = 1 PlatePoint</p>
						</div>
					</div>
					<div class="card border-0">
						<div class="card-body">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-arrow-green-down-icon-60x250.png" alt="PlatePoints Green Arrow">
						</div>
					</div>
					<div class="card border-0">
						<div class="card-body">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-5point.png" alt="PlatePoints 5 Points">
							<p style="margin-top: 10px;">200 PlatePoints = 5 Dinner Dollars to spend on any menu item</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row mb-5 bg-cyan-extra-light" style="border-radius: 10px;">
			<div class="col py-3">
				<div class="card-group text-center mt-2 mb-2">
					<div class="card border-0 bg-cyan-extra-light">
						<div class="card-body">
							<h4>How to earn more PlatePoints:</h4>
							<div class="card-group text-center">
								<div class="card border-0 bg-cyan-extra-light">
									<div class="card-body">
										<p style="font-size: 1rem;"><span style="color: #f07722; font-weight: bold;">Double</span> PlatePoints when you place your next order*.</p>
									</div>
								</div>
								<div class="card border-0 bg-cyan-extra-light">
									<div class="card-body">
										<p style="font-size: 1rem;">Rate your meals online and earn 5 PlatePoints.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--<div class="card border-0 bg-cyan-light">
						<div class="card-body">
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-arrow-orange-down-icon-60x175.png" alt="Platepoints Orange Arrow">
						</div>
					</div>
					<div class="card border-0 bg-cyan-extra-light">
						<div class="card-body">
							<h4>How to earn more Dinner Dollars:</h4>
							<div class="card-group text-center">
								<div class="card border-0 bg-cyan-extra-light">
									<div class="card-body">
										<p style="font-size: 1rem;">Refer a friend and receive 10 Dinner Dollars.</p>
									</div>
								</div>

							</div>
						</div>
					</div>-->
				</div>

				<div class="row">
					<div class="col text-center my-1">
						<h2 class="font-weight-bold mb-2" style="color: #f07722;">Earn & Redeem</h2>
					</div>
				</div>
				<div class="col">
					<div class="card-group text-center">
						<div class="card border-0 bg-cyan-extra-light">
							<div class="card-body">

							</div>
						</div>
						<div class="card border-0 bg-cyan-extra-light">
							<div class="card-body">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-step-1-circle-icon.png" alt="Step 1 order online">
							</div>
						</div>
						<div class="card border-0">
							<div class="card-body bg-cyan-extra-light">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-step-2-circle-icon.png" alt="Step 2 Accure PlatePoints">
							</div>
						</div>
						<div class="card border-0">
							<div class="card-body bg-cyan-extra-light">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-step-3-circle-icon.png" alt="Step 3 Apply earned Dinner Dollars at checkout">
							</div>
						</div>
						<div class="card border-0 bg-cyan-extra-light">
							<div class="card-body">

							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col text-center mt-5">
						<h2 class="font-weight-bold mb-2" style="color: #f07722;">Enroll Today!</h2>
						<p>New to Dream Dinners? You can enroll when you create your account.</p>
					</div>
				</div>
				<div class="col mb-4">
					<div class="card-group text-center">
						<div class="card border-0 bg-cyan-extra-light">
							<div class="card-body">

							</div>
						</div>
						<div class="card border-0 bg-cyan-extra-light">
							<div class="card-body">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-enroll-step-1-circle-icon.png" alt="Step 1 login and navigate to Edit Account">
							</div>
						</div>
						<div class="card border-0">
							<div class="card-body bg-cyan-extra-light">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-enroll-step-2-circle-icon.png" alt="Step 2 Check the Enroll box under PlatePoints">
							</div>
						</div>
						<div class="card border-0">
							<div class="card-body bg-cyan-extra-light">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/platepoints-enroll-step-3-circle-icon.png" alt="Step 3 Click the Update Account button to save">
							</div>
						</div>
						<div class="card border-0 bg-cyan-extra-light">
							<div class="card-body">

							</div>
						</div>
					</div>
				</div>
					<div class="col text-center my-5">
						<a href="/login" class="btn btn-lg btn-primary">Login & Enroll</a>
					</div>
			</div>
		</div>



		<div class="row">
			<div class="col">
				<p class="font-italic text-muted font-size-small">*Double points are earned when you place a qualifying order before, during or within 7 days of your current months visit. All Dinner Dollars expire within 45 days. Apply to an order within the 45-day window to redeem. Dinner Dollars cannot be used to discount shipping or home delivery fees. Dinner Dollars may be redeemed at participating locations. Dinner Dollars are non-transferrable. Preferred guests are not eligible to enroll in the PlatePoints program. Dinner Dollars cannot be combined with other offers.</p>
			</div>
		</div>
	</main>
<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>