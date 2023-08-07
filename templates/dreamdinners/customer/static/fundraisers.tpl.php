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
						<h3 class="mb-4 mt-4">Hosting a fundraiser at your local Dream Dinners is easy and delicious.</h3>
						<p class="mb-4">Let us host an Open House RSVP event for your group! During the event, attendees can taste samples, see a Dream Dinners demo and win prizes. Set up a table to promote your organization. Attendees can donate directly to your cause. If guests place orders for Dream Dinners online, they can pick them up at the event. We will also have a fully stocked freezer for attendees to shop from.</p>

						
						<div class="row mb-4">
							<div class="col">
								<p class="font-weight-bold text-uppercase mb-1">How it works</p>
								<div class="card-group text-center">
									<div class="card">
										<div class="card-header font-weight-bold">Choose a Date & Time</div>
										<i class="dd-icon icon-calendar_add font-size-extra-extra-large text-orange m-4"></i>
										<div class="card-body">
											<p class="card-text">Pick a date and time that works for your organization.</p>
										</div>
									</div>
									<div class="card">
										<div class="card-header font-weight-bold">Promote your event</div>
										<i class="dd-icon icon-email_1 font-size-extra-extra-large text-orange m-4"></i>
										<div class="card-body">
											<p class="card-text">Invite guests to join your Fundraiser event.</p>
										</div>
									</div>
									<div class="card">
										<div class="card-header font-weight-bold">Host your event &nbsp;&nbsp;&nbsp;</div>
										<i class="dd-icon icon-shop-front font-size-extra-extra-large text-orange m-4"></i>
										<div class="card-body">
											<p class="card-text">Have a great time connecting with your community.</p>
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

						<p><a href="main.php?page=locations">Contact your local Dream Dinners store</a> today and start planning how to meet and exceed your fundraising goals.</p>
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>