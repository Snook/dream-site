<div class="row mb-4 bg-gray-light p-2">
	<div class="col-md-3 text-left pl-md-0">
		<?php if ($this->usersFuturePastEvents['manageEvent']['session_type_true'] == CSession::REMOTE_PICKUP_PRIVATE) { ?>
			<img src="<?php echo IMAGES_PATH; ?>/my_events/my-events-remote_pickup_private.jpg" alt="community pick up" class="img-fluid" />
		<?php } else { ?>
			<img src="<?php echo IMAGES_PATH; ?>/stores/<?php echo $this->usersFuturePastEvents['manageEvent']['store_id']; ?>.webp" alt="<?php echo $this->usersFuturePastEvents['manageEvent']['store_name']; ?> Street View" class="img-fluid" />
		<?php } ?>
	</div>
	<div class="col-md-3 text-center text-md-left pt-2">
		<h3 class="font-weight-bold text-uppercase"><?php echo $this->usersFuturePastEvents['manageEvent']['session_type_title_public']; ?></h3>
		<p class="text-uppercase font-size-small mb-5"><?php echo CTemplate::dateTimeFormat($this->usersFuturePastEvents['manageEvent']['session_start'], FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($this->usersFuturePastEvents['manageEvent']['session_start'], MONTH_DAY); ?> at <?php echo CTemplate::dateTimeFormat($this->usersFuturePastEvents['manageEvent']['session_start'], TIME_ONLY); ?></p>
	</div>
	<div class="col-12 col-md-6">
		<div class="row justify-content-end">
			<?php if ($this->usersFuturePastEvents['manageEvent']['session_type'] != CSession::STANDARD) { ?>
				<div class="col-12 col-md-4 offset-md-4 mt-2 mt-md-0">
					<a class="btn btn-primary btn-block py-4" target="_blank" href="/main.php?page=print&amp;<?php echo strtolower($this->usersFuturePastEvents['manageEvent']['session_type_true']); ?>_event_pdf=<?php echo $this->usersFuturePastEvents['manageEvent']['id']; ?>">
						<i class="dd-icon icon-print font-size-extra-large"></i>
						<div>Print Invite</div>
					</a>
				</div>
			<?php } ?>
			<div class="col-12 col-md-4 mt-2 mt-md-0">
				<button class="btn btn-primary btn-block py-4"
						data-share-social="facebook,twitter"
						data-share-title="<?php echo $this->usersFuturePastEvents['manageEvent']['session_type_text']; ?>"
						data-share-text="You're invited to a Dream Dinners <?php echo $this->usersFuturePastEvents['manageEvent']['session_type_title_public']; ?> session on <?php echo CTemplate::dateTimeFormat($this->usersFuturePastEvents['manageEvent']['session_start'], VERBOSE_DATE_NO_YEAR);?> at <?php echo CTemplate::dateTimeFormat($this->usersFuturePastEvents['manageEvent']['session_start'], TIME_ONLY);?> to get easy, homemade dinners for your family."
						data-share-url="<?php echo HTTPS_BASE . 'session/' . $this->usersFuturePastEvents['manageEvent']['id']; ?>-<?php echo CUser::getCurrentUser()->id;?>">
					<i class="dd-icon icon-share2 font-size-extra-large"></i>
					<div>Share</div>
				</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12 col-md-4 bg-gray-light p-4 mb-4 mb-md-0">
		<div class="form-row">
			<h2 class="text-green-dark text-uppercase font-weight-bold text-left font-size-medium-small mb-4 ml-xs-2">Session Details</h2>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<label for="exampleFormControlTextarea1">Personal message</label>
				<textarea class="form-control" id="session_message" rows="3"><?php echo $this->usersFuturePastEvents['manageEvent']['session_host_message']; ?></textarea>
			</div>
		</div>
		<?php if (!$this->usersFuturePastEvents['manageInviteOnly']) { ?>
			<div class="form-row">
				<div class="form-group col-md-12">
					<label for="exampleFormControlInput1">Event host</label>
					<input type="text" class="form-control" id="session_host" placeholder="Host name" value="<?php echo $this->usersFuturePastEvents['manageEvent']['session_host_informal_name']; ?>">
				</div>
			</div>
			<div class="row mt-4">
				<div class="col">
					<button class="btn btn-primary btn-block btn-spinner save-event-details disabled">Save details</button>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="col-12 col-md-8">
		<?php if (!empty($this->usersFuturePastEvents['manageEvent']['bookings']) || !empty($this->usersFuturePastEvents['manageEvent']['session_rsvp'])) { ?>
			<div class="row mb-4">
				<div class="col">
					<h2 class="text-green-dark text-uppercase font-weight-bold text-left font-size-medium-small mb-4 ml-xs-2">Guests attending</h2>
					<div class="row">
						<?php foreach ($this->usersFuturePastEvents['guestsAttending'] AS $booking) { ?>
							<div class="col-md-4 mb-md-3">
								<div class="card">
									<div class="card-body bg-gray-light">
										<p class="card-text text-center<?php echo (CUser::getCurrentUser()->id == $booking['user_id']) ? ' font-weight-bold' : ''; ?>"><?php echo $booking['firstname']; ?> <?php echo $booking['lastname']; ?></p>
									</div>
								</div>
							</div>
						<?php } ?>
						<?php if ($this->usersFuturePastEvents['manageInviteOnly'] && !empty($this->usersFuturePastEvents['remainingTotalAttending'])) { ?>
							<div class="col-md-4 mb-md-3">
								<div class="card">
									<div class="card-body bg-gray-light">
										<p class="card-text text-center font-italic"><?php echo $this->usersFuturePastEvents['remainingTotalAttending'];?> other guest<?php echo ($this->usersFuturePastEvents['remainingTotalAttending'] == 1) ? '' : 's'; ?></p>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<div class="row">
			<div class="col-6">
				<h2 class="text-green-dark text-uppercase font-weight-bold text-left font-size-medium-small mb-4 ml-xs-2">Invite guests</h2>
			</div>
			<div class="col-6 text-right">
				<div class="dropdown">
					<button class="btn btn-primary btn-sm mb-4 mb-md-0 import_contacts dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Import Contacts
					</button>
					<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
						<a class="dropdown-item nav-link import_contacts_google" href="#"><i class="dd-icon icon-google mr-2"></i>Google</a>
						<?php if (!$this->isIE11 && !$this->isMobileSafari) { ?>
						<a class="dropdown-item nav-link import_contacts_outlook" href="#"><i class="dd-icon icon-microsoftoutlook mr-2"></i>Outlook.com</a>
						<?php } ?>
						<a class="dropdown-item nav-link import_contacts_previous" href="#"><i class="dd-icon icon-email mr-2"></i>Previous Contacts</a>
					</div>
				</div>

			</div>
		</div>
		<div class="row">
			<div class="col event-referrals-div">
				<?php include $this->loadTemplate('customer/subtemplate/my_events/my_events_referrals.tpl.php'); ?>
			</div>
		</div>
		<div class="row mt-4">
			<div class="col">
				<button class="btn btn-primary btn-block btn-spinner send-event-emails disabled">Send <span id="number_of_emails"></span> email invite<span id="number_of_emails_plural">s</span></button>
			</div>
		</div>
		<div class="row mt-4">
			<div class="col">
				<p class="font-italic font-size-small text-muted">Note: Contacts added to this event platform will only be sent the invitations you choose to send. Dream Dinners will not sell or market to, any of your contacts added to the event platform.</p>
			</div>
		</div>

	</div>

</div>

