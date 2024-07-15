<div class="row mb-2">
	<div class="col-12 col-xl-4 pt-2 pt-xl-3 text-center text-xl-left">
		<h3 class="font-weight-bold font-size-medium text-uppercase"><?php echo $event['session_type_title_public']; ?></h3>
		<p class="text-uppercase"><?php echo CTemplate::dateTimeFormat($event['session_start'], FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($event['session_start'], MONTH_DAY); ?> at <?php echo CTemplate::dateTimeFormat($event['session_start'], TIME_ONLY); ?></p>
	</div>
	<div class="col-12 col-xl-8">
		<div class="row justify-content-end">
			<div class="col-12 col-md-4 mt-2 mt-xl-0">
				<a class="btn btn-primary btn-block" href="/my-events?sid=<?php echo $event['id']; ?>">
					<i class="dd-icon icon-email_1 font-size-extra-large"></i>
					<div>Invite Friends</div>
				</a>
			</div>
			<?php if ($event['session_type'] == CSession::DREAM_TASTE) { ?>
			<div class="col-12 col-md-4 mt-2 mt-xl-0">
				<a class="btn btn-primary btn-block" target="_blank" href="/print?<?php echo strtolower($event['session_type_true']); ?>_event_pdf=<?php echo $event['id']; ?>">
					<i class="dd-icon icon-print font-size-extra-large"></i>
					<div>Print Invite</div>
				</a>
			</div>
			<?php } ?>
			<div class="col-12 col-md-4 mt-2 mt-xl-0">
				<button class="btn btn-primary btn-block"
						data-share-social="facebook,twitter"
						data-share-title="<?php echo $event['session_type_text']; ?>"
						data-share-text="You're invited to a Dream Dinners <?php echo $event['session_type_title_public']; ?> session on <?php echo CTemplate::dateTimeFormat($event['session_start'], VERBOSE_DATE_NO_YEAR);?> at <?php echo CTemplate::dateTimeFormat($event['session_start'], TIME_ONLY);?> to assemble easy, homemade dinners for your family."
						data-share-url="<?php echo HTTPS_BASE . 'session/' . $event['id']; ?>">
					<i class="dd-icon icon-share2 font-size-extra-large"></i>
					<div>Share</div>
				</button>
			</div>
		</div>
	</div>
</div>