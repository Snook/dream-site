<div class="card col-11 col-lg-5 no-gutters m-md-2 text-center">
	<div class="row mt-4">
		<div class="col">
			<i class="dd-icon icon-session-type-<?php echo $session['session_type_string']; ?> font-size-extra-extra-large text-color-session-type-<?php echo $session['session_type_string']; ?>"></i>
		</div>
	</div>
	<h3 class="card-title my-3"><?php echo $session['session_type_title_public']; ?></h3>
	<h6 class="card-subtitle mb-2 text-muted">
		<?php if ($session['session_type_string'] == 'remote_pickup_private') { ?>
			<div>Location: <?php echo $session['session_remote_location']->location_title; ?></div>
		<?php } ?>
		<?php if (!empty($session['session_host_informal_name'])) { ?>
			<div>Hosted by <?php echo $session['session_host_informal_name']; ?></div>
		<?php } ?>
		<?php if ($session['session_type'] == CSession::FUNDRAISER) { ?>
			<div>Benefits <?php echo $session['fundraiser_name']; ?></div>
		<?php } ?>
	</h6>
	<div class="card-body font-size-small">
		<?php if (!empty($session['session_details'])) { ?>
			<?php echo strip_tags($session['session_details']); ?>
		<?php } else { ?>
			<?php
			switch ($session['session_type_string'])
			{
				case 'fundraiser':
				case 'fundraiser_curbside':
					echo 'Join us at a fundraiser event for ' . $session['fundraiser_name'] . ' and support a great cause.';
					break;
				case 'private_party':
					echo 'Experience what Dream Dinners is all about at a Private Party' . ((!empty($session['session_host_informal_name'])) ? ' hosted by ' . $session['session_host_informal_name'] : '') . '.';
					break;
				case 'friends_night_out':
					echo 'Experience what Dream Dinners is all about at a Friends Night Out Party hosted by ' . $session['session_host_informal_name'] . '.';
					break;
				case 'meal_prep_workshop':
					echo 'Join us at an exclusive Meal Prep Workshop event to learn more about Dream Dinners hosted by ' . $session['session_host_informal_name'] . '.';
					break;
				case 'meal_prep_workshop_curbside':
					echo 'Join us at an exclusive Meal Prep Workshop event hosted by ' . $session['session_host_informal_name'] . ' to learn about how easy dinnertime can be.';
					break;
				case 'open_house':
					if ($session['session_type_fadmin_acronym'] == 'OHRN')
					{
						echo 'New to Dream Dinners? Join us for a special event at our store. Click to RSVP or for  questions, please call ' . $this->cart_info['store_info']['telephone_day'];
						break;
					}
					else if ($session['session_type_fadmin_acronym'] == 'OHRA')
					{
						echo 'Join us for a special event at our store. Click to RSVP or for  questions, please call ' . $this->cart_info['store_info']['telephone_day'];
						break;
					}
					else
					{
						echo 'New to Dream Dinners? Come and see what Dream Dinners is all about and assemble 3 dinners for your family. Questions, please call ' . $this->cart_info['store_info']['telephone_day'];
						break;
					}
				case 'open_house_curbside':
					echo 'New to Dream Dinners? Come and see what Dream Dinners is all about and take home 3 dinners for your family. Questions, please call ' . $this->cart_info['store_info']['telephone_day'];
					break;
				case 'holiday_pick_up_event':
					echo 'Give Dream Dinners a try with our complete holiday dinner pick up event. Questions, please call ' . $this->cart_info['store_info']['telephone_day'];
					break;
				case 'remote_pickup_private':
					echo 'This is a special pick up event hosted by ' . $session['session_host_informal_name'] . '. Order your perfectly prepped dinners from our menu and we will assemble them for your family at our local assembly kitchen. You simply pick up your dinners at ' . $session['session_remote_location']->location_title . ' community pick up location.';
					break;
			}
			?>
		<?php } ?>
	</div>
	<div class="card-subtitle font-weight-bold text-green my-3"><?php echo CTemplate::dateTimeFormat($session['session_start'], VERBOSE); ?></div>
	<?php if (!empty($this->cart_info['session_info']) && $this->cart_info['session_info']['id'] == $session['id']) { ?>
		<a href="/main.php?page=session_menu" class="btn btn-primary btn-block px-5 mb-4">Currently Selected Session</a>
	<?php } else { ?>
		<div class="input-group mb-4">
			<?php if (!empty($session['session_password'])) { ?>
				<input type="text" class="form-control" data-event_session="<?php echo $session['id']; ?>" placeholder="Invite Code" aria-label="Invite Code" aria-describedby="session-button-<?php echo $session['id']; ?>" />
				<div class="input-group-append">
					<button class="btn btn-primary btn-block px-5 btn-spinner add_event_session" data-session="<?php echo $session['id']; ?>" data-unix_expiry="<?php echo $session['unix_expiry']; ?>" data-is_private="<?php echo (!empty($session['session_password'])) ? 'true' : 'false'; ?>" type="button" id="session-button-<?php echo $session['id']; ?>">Submit</button>
				</div>
				<div class="invalid-feedback text-left">
					Invalid code
				</div>
			<?php } else { ?>
				<button class="btn btn-primary btn-block px-5 btn-spinner add_event_session" data-session="<?php echo $session['id']; ?>" data-unix_expiry="<?php echo $session['unix_expiry']; ?>" data-is_private="<?php echo (!empty($session['session_password'])) ? 'true' : 'false'; ?>" type="button" id="session-button-<?php echo $session['id']; ?>">Join Event</button>
			<?php } ?>
		</div>
	<?php } ?>
</div>