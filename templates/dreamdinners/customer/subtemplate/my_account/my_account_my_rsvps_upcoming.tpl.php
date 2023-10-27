<div class="row mb-2">
	<div class="col-12 col-xl-4 pt-2 pt-xl-3 text-center text-xl-left">
		<h3 class="font-weight-bold font-size-medium text-uppercase"><?php echo $event['session_type_title_public']; ?></h3>
		<p class="mb-0 text-uppercase"><?php echo CTemplate::dateTimeFormat($event['session_start'], FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($event['session_start'], MONTH_DAY); ?> at <?php echo CTemplate::dateTimeFormat($event['session_start'], TIME_ONLY); ?></p>
		<p class="mb-0 font-size-small">Dream Dinners <?php echo $event['store_name']; ?></p>
		<p class="mb-0 font-size-small"><?php echo $event['address_line1']; ?><?php echo (!empty($event['address_line2'])) ? ', ' . $event['address_line2'] : ''; ?>, <?php echo $event['city'];?>, <?php echo $event['state_id']; ?></p>
		<p class="mb-0 font-size-small"><?php echo $event['telephone_day']; ?> - <?php echo $event['email_address'];?></p>
	</div>
	<div class="col-12 col-xl-8">
		<div class="row justify-content-end">
			<?php if(!$event['is_past']){ ?>
			<div class="col-12 col-md-4 mt-2 mt-xl-0">
				<a class="btn btn-primary btn-block" href="<?php echo HTTPS_BASE . 'session/' . $event['id']; ?>">
					<div>View Event</div>
				</a>
			</div>
			<div class="col-12 col-md-4 mt-2 mt-xl-0">
				<button class="btn btn-primary btn-block cancel_session_rsvp"
						data-user_id="<?php echo $this->user->id; ?>"
						data-session_id="<?php echo $event['id']; ?>">
					<div>Cancel RSVP</div>
				</button>
			</div>
			<?php } ?>
		</div>
	</div>
</div>