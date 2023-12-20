
<div class="container">
	<div class="row">
		<ul class="list-inline">
			<li class="list-inline-item mb-2 mr-4"><s>Unpublished Session</s></li>
			<li class="list-inline-item mb-2 mr-4"><img src="<?php echo ADMIN_IMAGES_PATH?>/calendar/session_closed.png"> Closed or Expired</li>
			<li class="list-inline-item mb-2 mr-4"><img src="<?php echo ADMIN_IMAGES_PATH?>/calendar/session_pub.png"> Published Session</li>
			<li class="list-inline-item mb-2 mr-4"><img src="<?php echo ADMIN_IMAGES_PATH?>/calendar/session_saved.png"> Saved Session (never published)</li>
			<?php if ($this->page == 'admin_publish_sessions') { ?>
				<li class="list-inline-item mb-2 mr-4"><img src="<?php echo ADMIN_IMAGES_PATH?>/calendar/session_new.png">&nbsp;New Session (not editable until saved or published)
			<?php } ?>
			<li class="list-inline-item mb-2 mr-4"><i class="dd-icon icon-customize text-orange"></i>/<i class="dd-icon icon-customize text-black"></i> Customization Open/Closed</li>
			<li class="list-inline-item mb-2 mr-4"><font style="color:green;">$</font> Discounted Session</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::STANDARD); ?> Assembly</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::PRIVATE_SESSION); ?> Assembly - Private Party</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::SPECIAL_EVENT); ?> Pick Up</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::DREAM_TASTE); ?> Meal Prep Workshop</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::REMOTE_PICKUP); ?> Community Pick Up</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::REMOTE_PICKUP_PRIVATE); ?> Community Pick Up - Private</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::DELIVERY); ?> Home Delivery</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::DELIVERY_PRIVATE); ?> Home Delivery - Private</li>
			<?php if ($this->DAO_menu->isEnabled_Bundle_Fundraiser()) { ?>
				<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::FUNDRAISER); ?> Fundraiser Event</li>
				<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote('FUNDRAISER_CURBSIDE'); ?> Fundraiser Curbside</li>
			<?php } ?>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::WALK_IN); ?> Walk-In</li>
		</ul>
	</div>
</div>