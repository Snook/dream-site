<ul id="session_selection">

	<?php foreach ($this->sessions AS $date => $day) { ?>

		<li class="day <?php echo ($day['info']['is_past']) ? 'is_past' : ''; ?>"
			id="day_<?php echo $date; ?>"
			data-unix_expiry="<?php echo (strtotime($date . ' 23:59:59')); ?>"
			data-date="<?php echo $date; ?>"
			data-menu_id="<?php echo $day['info']['menu_id']; ?>">
			<span class="name"><?php echo date('l', strtotime($date)); ?></span>
			<span class="date"><?php echo CTemplate::dateTimeFormat($date, MONTH_DAY_YEAR); ?></span>
			<div class="clear"></div>
		</li>

		<?php if (!empty($day['sessions'])) { ?>
			<?php foreach ($day['sessions'] AS $id => $session) { ?>

				<li class="session <?php echo ($session['is_past']) ? 'is_past' : ''; ?>"
					id="session-<?php echo $session['id']; ?>"
					data-unix_expiry="<?php echo $session['unix_expiry']; ?>"
					data-session_id="<?php echo $session['id']; ?>"
					data-session_date="<?php echo CTemplate::dateTimeFormat($session['session_start'], YEAR_MONTH_DAY); ?>"
					data-menu_id="<?php echo $session['menu_id']; ?>">
					<span class="type"><span class="note_calendar note_<?php echo $session['session_type_string']; ?> <?php echo (!empty($session['is_past']) ? 'note_closed' : ''); ?>" data-tooltip="<?php echo $session['session_type_title']; ?><?php if (!empty($session['session_host_firstname'])) { ?> hosted by <?php echo $session['session_host_firstname']; ?> <?php echo $session['session_host_lastname']; ?><?php } ?>"><?php echo $session['session_type_fadmin_acronym']; ?></span></span>

					<span class="time <?php echo ($session['session_publish_state'] == CSession::CLOSED) ? 'closed' : ''; ?>" <?php echo ($session['session_publish_state'] == CSession::CLOSED) ? 'data-tooltip="Closed"' : ''; ?>>
						<?php if($session['session_type_subtype'] == CSession::WALK_IN){
							echo 'Walk-In';
						}else{
							echo CTemplate::dateTimeFormat($session['session_start'], TIME_ONLY);
						}?>
					</span>

					<?php if($session['additional_orders'] > 0 ){ ?>
						<span class="attending" data-tooltip="Booked Guests/Additional Orders"><?php echo $session['booked_count'] + $session['num_rsvps']; ?>/<?php echo $session['additional_orders']; ?></span>
					<?php }else{ ?>
						<span class="attending" data-tooltip="Booked Guests"><?php echo $session['booked_count'] + $session['num_rsvps']; ?></span>
					<?php } ?>

					<?php if($session['session_type_subtype'] != CSession::WALK_IN){?>
						<span class="remaining" data-tooltip="<?php echo ($session['remaining_slots'] >= 0) ? 'Remaining' : 'Overbooked'; ?> Standard / Intro"><?php echo $session['remaining_slots']; ?>/<?php echo ($session['remaining_intro_slots'] > 0) ? $session['remaining_intro_slots'] : 0; ?></span>
					<?php } ?>
					<div class="clear"></div>
				</li>

			<?php } ?>
		<?php } else { ?>

			<li class="session <?php echo ($day['info']['is_past']) ? 'is_past' : ''; ?>"
				data-unix_expiry="<?php echo (strtotime($date . ' 23:59:59')); ?>"
				data-date="<?php echo $date; ?>" data-menu_id="<?php echo $day['info']['menu']['id']; ?>"
				data-selected_day="<?php echo CTemplate::dateTimeFormat($date, VERBOSE_DATE); ?>">
				<div class="no_sessions">No Sessions</div>
			</li>

		<?php } ?>

	<?php  } ?>

</ul>
