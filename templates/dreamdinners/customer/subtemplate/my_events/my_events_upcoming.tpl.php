<div class="row mb-4">
	<div class="col-md-2 d-md-none text-center">
		<h3 class="font-weight-bold text-uppercase"><?php echo $event['session_type_title_public']; ?></h3>
		<p class="text-uppercase"><?php echo CTemplate::dateTimeFormat($event['session_start'], FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($event['session_start'], MONTH_DAY); ?> at <?php echo CTemplate::dateTimeFormat($event['session_start'], TIME_ONLY); ?></p>
	</div>
	<div class="col-md-2 text-left pl-md-0">
		<img src="<?php echo IMAGES_PATH; ?>/my_events/my-events-<?php echo $event['session_type_string']; ?>.jpg" alt="<?php echo $event['session_type_title_public']; ?>" class="img-fluid" />
	</div>
	<div class="col-md-3 d-none d-md-block">
		<h3 class="font-weight-bold font-medium-small text-uppercase"><?php echo $event['session_type_title_public']; ?></h3>
		<p class="text-uppercase font-size-small mb-5"><?php echo CTemplate::dateTimeFormat($event['session_start'], FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($event['session_start'], MONTH_DAY); ?> at <?php echo CTemplate::dateTimeFormat($event['session_start'], TIME_ONLY); ?></p>
	</div>
	<div class="col-12 col-md-7 pr-md-0">
		<div class="row justify-content-end">
			<div class="col-12 col-md-4 mt-2 mt-md-0">
				<a class="btn btn-primary btn-block py-4" href="/?page=my_events&amp;sid=<?php echo $event['id']; ?>">
					<i class="dd-icon icon-email_1 font-size-extra-extra-large"></i>
					<div>Invite Friends</div>
				</a>
			</div>
			<?php if ($event['session_type'] != CSession::STANDARD && $event['session_type'] != CSession::MADE_FOR_YOU) { ?>
			<div class="col-12 col-md-4 mt-2 mt-md-0">
				<a class="btn btn-primary btn-block py-4" target="_blank" href="/?page=print&amp;<?php echo strtolower($event['session_type_true']); ?>_event_pdf=<?php echo $event['id']; ?>">
					<i class="dd-icon icon-print font-size-extra-extra-large"></i>
					<div>Print Invite</div>
				</a>
			</div>
			<?php } ?>
			<div class="col-12 col-md-4 mt-2 mt-md-0">
				<button class="btn btn-primary btn-block py-4"
						data-share-social="facebook,twitter"
						data-share-title="<?php echo $event['session_type_text']; ?>"
						data-share-text="You're invited to a Dream Dinners <?php echo $event['session_type_title_public']; ?> session on <?php echo CTemplate::dateTimeFormat($event['session_start'], VERBOSE_DATE_NO_YEAR);?> at <?php echo CTemplate::dateTimeFormat($event['session_start'], TIME_ONLY);?> to cook easy, homemade dinners for your family."
						data-share-url="<?php echo HTTPS_BASE . 'session/' . $event['id']; ?>">
					<i class="dd-icon icon-share2 font-size-extra-extra-large"></i>
					<div>Share</div>
				</button>
			</div>
		</div>
	</div>
</div>