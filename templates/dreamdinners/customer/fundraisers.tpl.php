<?php $this->assign('page_title', 'Fundraisers');?>
<?php $this->assign('page_description','Meal prep fundraisers are a great way to earn money for your organization.'); ?>
<?php $this->assign('page_keywords','fundraisers, raise money for school, raise money, community'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Fundraisers</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<div class="row mb-5">
					<div class="col">
						<p class="text-center"><img src="<?php echo IMAGES_PATH; ?>/events_programs/fundraisers.jpg" alt="Fundraising at Dream Dinners" class="img-fluid" /></p>
						<h3 class="mb-4">Fundraising with Dream Dinners</h3>
						<p>Our pick up and in-store fundraisers make it easy and safe for families to support your cause and take home dinner. Everyone wins!</p>
						
						<!--<p>And each year in March, we host our March Matchness event. Dream Dinners will award the top 10 March fundraisers with a donation match of up to $500! You really can't lose. Contact your store to  schedule your March fundraiser today!</p>

						<p><i>*See terms for contest details.</i></p>-->

						<p class="font-weight-bold text-uppercase mb-1">What we do</p>
						<p>Hosting a fundraiser at your local Dream Dinners is easy and delicious. Each fundraiser attendee receives three medium meals for just $60 and your organization gets $10 for each purchase.</p>

						<div class="row mb-4">
							<div class="col">
								<p class="font-weight-bold text-uppercase mb-1">How it works</p>
								<div class="card-group text-center">
									<div class="card">
										<div class="card-header font-weight-bold">Choose a Date & Time</div>
										<i class="dd-icon icon-event font-size-extra-extra-large text-orange m-4"></i>
										<div class="card-body">
											<p class="card-text">Pick a date and time that works for your organization.</p>
										</div>
									</div>
									<div class="card">
										<div class="card-header font-weight-bold">Promote your event</div>
										<i class="dd-icon icon-print font-size-extra-extra-large text-orange m-4"></i>
										<div class="card-body">
											<p class="card-text">Invite guests to join your Meal Prep Fundraiser event.</p>
										</div>
									</div>
									<div class="card">
										<div class="card-header font-weight-bold">Host your event &nbsp;&nbsp;&nbsp;</div>
										<i class="dd-icon icon-email_1 font-size-extra-extra-large text-orange m-4"></i>
										<div class="card-body">
											<p class="card-text">Earn $10 for every meal prep order placed.</p>
										</div>
									</div>
									<div class="card">
										<div class="card-header font-weight-bold">Exceed your goal</div>
										<i class="dd-icon icon-session-type-fundraiser font-size-extra-extra-large text-orange m-4"></i>
										<div class="card-body">
											<p class="card-text">Earn funds to help your organization.</p>
										</div>
									</div>
								</div>
							</div>
						</div>

						<p><a href="/locations">Contact your local Dream Dinners store</a> today and start planning how to meet and exceed your fundraising goals.</p>
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>