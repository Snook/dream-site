
<div class="container">
	<div class="row">
		<ul class="list-inline">
			<li class="list-inline-item mb-2 mr-4"><s>Unpublished Session</s></li>
			<li class="list-inline-item mb-2 mr-4"><img src="<?php echo ADMIN_IMAGES_PATH?>/calendar/session_closed.png"> Closed or Expired</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::SPECIAL_EVENT); ?> Made for You</li>
			<li class="list-inline-item mb-2 mr-4"><img src="<?php echo ADMIN_IMAGES_PATH?>/calendar/session_pub.png"> Published Session</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::STANDARD); ?> Standard Session</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::DREAM_TASTE); ?> Meal Prep Workshop</li>
			<li class="list-inline-item mb-2 mr-4"><img src="<?php echo ADMIN_IMAGES_PATH?>/calendar/session_saved.png" style="vertical-align:middle;margin-bottom:.25;"> Saved Session (never published)</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::PRIVATE_SESSION); ?> Private Standard Session</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::REMOTE_PICKUP); ?> Community Pick Up</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::REMOTE_PICKUP_PRIVATE); ?> Community Pick Up - Private</li>
			<li class="list-inline-item mb-2 mr-4"><font style="color:green;">$</font> Discounted Session</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::DELIVERY); ?> Home Delivery</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::DELIVERY_PRIVATE); ?> Home Delivery - Private</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::FUNDRAISER); ?> Fundraiser Event</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote('FUNDRAISER_CURBSIDE'); ?> Fundraiser Curbside</li>
			<li class="list-inline-item mb-2 mr-4"><?php echo CCalendar::sessionTypeNote(CSession::WALK_IN); ?> Walk-In</li>
			<li class="list-inline-item mb-2 mr-4"><i class="dd-icon icon-customize text-orange" style="font-size: 65%;"></i>/<i class="dd-icon icon-customize text-black" style="font-size: 65%;"></i>  Customization Open/Closed</li>
		</ul>
	</div>
</div>