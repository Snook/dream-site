<?php $this->assign('page_title', 'My Events'); ?>
<?php $this->setScript('foot', '//apis.google.com/js/client.js'); ?>
<?php $this->setScript('foot', '//secure.aadcdn.microsoftonline-p.com/lib/0.2.4/js/msal.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/my_events.min.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/my_events_graph.min.js'); ?>
<?php if (!empty($this->usersFuturePastEvents['manageEvent'])) { ?>
	<?php $this->setScriptVar('manageEventID = ' . $this->usersFuturePastEvents['manageEvent']['id'] . ';'); ?>
	<?php $this->setScriptVar('eventReferrals = ' . $this->usersFuturePastEvents['eventReferralsJS'] . ';'); ?>
	<?php $this->setScriptVar('manageInviteOnly = ' . ((!empty($this->usersFuturePastEvents['manageInviteOnly'])) ? "true" : "false") . ';'); ?>
<?php } ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<?php if (!empty($this->usersFuturePastEvents['manageEvent'])) { ?>
					<a href="/?page=my_events" class="btn btn-primary"><span class="pr-2">&#10094;</span> My Events</a>
				<?php } else { ?>
					<a href="/?page=my_account" class="btn btn-primary"><span class="pr-2">&#10094;</span> My Account</a>
				<?php } ?>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1><?php if (!empty($this->usersFuturePastEvents['manageEvent'])) { ?>Invite Friends<?php } else { ?>My Events<?php } ?></h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main>
		<div class="container">

			<?php if (!empty($this->usersFuturePastEvents['manageEvent'])) { ?>
				<?php include $this->loadTemplate('customer/subtemplate/my_events/my_events_manage_event.tpl.php'); ?>
			<?php } else { ?>
				<?php if (!empty($this->usersFuturePastEvents['upcomingEvents'])) { ?>
					<?php foreach ($this->usersFuturePastEvents['upcomingEvents'] as $id => $event) { ?>
						<?php include $this->loadTemplate('customer/subtemplate/my_events/my_events_upcoming.tpl.php'); ?>
					<?php }?>
				<?php } ?>
			<?php } ?>

			<?php if (empty($this->usersFuturePastEvents['manageEvent'])) { ?>
				<div class="card-group mt-5">
					<div class="card mx-2">
						<div class="card-body bg-green-light text-center py-4">
							<p class="card-text mb-0">Host a Dream Dinners</p>
							<h5 class="card-title">Meal Prep Workshop</h5>
							<p class="font-size-small">Share how easy cooking with Dream Dinners can be and earn rewards. Introduce your friends to Dream Dinners at a private pick up or fun assembly event.</p>
							<p class="font-size-small">Contact your store to schedule your Meal Prep Workshop.</p>
						</div>
					</div>
					<div class="card mx-2">
						<div class="card-body bg-orange text-white text-center py-4">
							<p class="card-text mb-0">Host a Dream Dinners</p>
							<h5 class="card-title">Fundraiser</h5>
							<p class="font-size-small">Raise money for your organization! Attendees will receive meals for their family to enjoy and your organization will receive a $10 donation for each order placed.</p>
							<p class="font-size-small">Contact your store to schedule your fundraiser event.</p>
						</div>
					</div>
					<div class="card mx-2">
						<div class="card-body bg-green-dark text-white text-center py-4">
							<p class="card-text mb-0">Host a Dream Dinners</p>
							<h5 class="card-title">Private Party</h5>
							<p class="font-size-small">Get your friends together for a private pick up or assembly event at Dream Dinners.</>
							<p class="font-size-small">Contact your store to schedule your Private Party.</p>
						</div>
					</div>
				</div>
			<?php } ?>

		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>