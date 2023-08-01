<div class="row mb-2" data-row_session_types="<?php echo $day['info']['session_types_comma']; ?>">
	<div class="col border">
		<div class="row">
			<div class="col-md-4 text-center pt-3 <?php echo ($day['info']['day_has_session_in_cart']) ? ' bg-green font-weight-bold text-white' : 'bg-gray-light'; ?>" data-day="<?php echo $date; ?>">
				<p class="font-weight-bold text-uppercase font-size-medium-small"><?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?></p>
				<p class="font-weight-semi-bold"><?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?></p>
			</div>
			<div class="col-md-8 my-auto pt-3">
				<div class="form-row">
					<?php foreach ($day['sessions'] AS $id => $session) { ?>
						<div class="form-group col-6 pl-4" data-filter_session_type="<?php echo $session['session_type'] . (!empty($session['session_type_subtype']) ? '-' . $session['session_type_subtype'] : ''); ?>">
							<?php if ($session['session_type'] == CSession::STANDARD) { ?>
								<div class="font-size-small">You assemble at Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?></div>
								<?php if (!empty($session['session_title'])) { ?>
									<div class="font-size-small"><?php echo strip_tags($session['session_title']); ?></div>
								<?php } ?>
							<?php } else if ($session['session_type'] == CSession::MADE_FOR_YOU && empty($session['session_type_subtype'])) { ?>
								<div class="font-size-small">You pick up at Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?></div>
								<?php if (!empty($session['session_title'])) { ?>
									<div class="font-size-small"><?php echo strip_tags($session['session_title']); ?></div>
								<?php } ?>
							<?php } else if ($session['session_type'] == CSession::MADE_FOR_YOU && $session['session_type_subtype'] == CSession::DELIVERY) { ?>
								<div class="font-size-small">Home Delivery - Delivery fee may apply</div>
								<?php if (!empty($session['session_title'])) { ?>
									<div class="font-size-small"><?php echo strip_tags($session['session_title']); ?></div>
								<?php } ?>
							<?php } else if ($session['session_type'] == CSession::MADE_FOR_YOU && $session['session_type_subtype'] == CSession::REMOTE_PICKUP) { ?>
								<div class="font-size-small">You pick up at <span class="font-size-small"><?php echo strip_tags($session['session_title']); ?></span> - <span class="font-size-small"><?php echo $session['session_remote_location']->address_line1; ?><?php echo (!empty($session['session_remote_location']->address_line2)) ? ' ' . $session['session_remote_location']->address_line2 : ''; ?> <?php echo $session['session_remote_location']->city; ?>, <?php echo $session['session_remote_location']->state_id; ?></span></div>
							<?php } ?>
							<div class="custom-control custom-radio">
								<input type="radio" id="sessionRadio-<?php echo $session['id']; ?>" data-day="<?php echo $date; ?>" data-unix_expiry="<?php echo $session['unix_expiry']; ?>" data-is_private="<?php echo (!empty($session['session_password'])) ? 'true' : 'false'; ?>" name="sessionRadio" class="custom-control-input" value="<?php echo $session['id']; ?>"<?php echo (!empty($this->cart_info['session_info']['id']) && $this->cart_info['session_info']['id'] == $session['id']) ? ' checked="checked"' : ''; ?> />
								<label class="custom-control-label px-2<?php echo (!empty($this->cart_info['session_info']['id']) && $this->cart_info['session_info']['id'] == $session['id']) ? ' bg-green font-weight-bold text-white' : ''; ?>" for="sessionRadio-<?php echo $session['id']; ?>"><?php echo CTemplate::dateTimeFormat($session['session_start'], TIME_ONLY); ?> <?php echo (false && !empty($session['session_password'])) ? '<i class="fas fa-key" data-toggle="tooltip" data-placement="top" data-tooltip="Private party, password required"></i>' : ''; ?></label>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>