<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
			<a href="/?page=my_meals&amp;tab=nav-past_orders" class="btn btn-primary"><span class="pr-2">&#10094;</span> My Orders</a>
		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
			<h2>Select your new time</h2>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

		</div>
	</div>
</header>

<main class="container-fluid">
	<?php foreach ($this->sessions['sessions'] AS $date => $day) { ?>
		<?php if ($day['info']['has_available_sessions']) { ?>
			<div class="row mb-2 mx-2">
				<div class="offset-lg-3 offset-md-2 col-lg-6 col-md-8 border">
					<div class="row">
						<div class="col-md-5 text-center pt-3 <?php echo ($day['info']['day_has_session_in_cart']) ? ' bg-green font-weight-bold text-white' : 'bg-gray-light'; ?>" data-day="<?php echo $date; ?>">
							<p class="font-weight-bold text-uppercase font-size-medium-small"><?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?></p>
							<p class="font-weight-semi-bold"><?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?></p>
							<p><?php echo $day['info']['session_count_open']; ?> session<?php echo ($day['info']['session_count_open'] != 1) ? 's' : ''; ?> available</p>
						</div>
						<div class="col-md-7 my-auto pt-3">
							<div class="form-row">
								<?php if (!empty($day['sessions'])) { ?>
									<?php foreach ($day['sessions'] AS $id => $session) {
										if($session['session_type_subtype'] == CSession::WALK_IN){continue;}?>
										<div class="form-group col-6 pl-4">
											<?php if (!empty($session['session_title'])) { ?>
												<div class="font-size-small"><?php echo strip_tags($session['session_title']); ?></div>
											<?php } ?>
											<?php if (!empty($this->current_session_id) && $this->current_session_id == $session['id']) { ?>
												<div class="custom-control custom-radio">
													<input type="radio" id="sessionRescheduleRadio-<?php echo $session['id']; ?>" data-day="<?php echo $date; ?>"
														   data-friendly_date_time ="<?php echo CTemplate::dateTimeFormat($session['session_start'], VERBOSE); ?>"
														   data-unix_expiry="<?php echo $session['unix_expiry']; ?>" data-is_private="<?php echo (!empty($session['session_password'])) ? 'true' : 'false'; ?>" name="sessionRescheduleRadio" class="custom-control-input" value="<?php echo $session['id']; ?>" disabled="disabled" />
													<label class="custom-control-label px-2<?php echo (!empty($this->current_session_id) && $this->current_session_id == $session['id']) ? ' bg-green font-weight-bold text-white' : ''; ?>" for="sessionRescheduleRadio-<?php echo $session['id']; ?>"><?php echo CTemplate::dateTimeFormat($session['session_start'], TIME_ONLY); ?> (current) <?php echo (false && !empty($session['session_password'])) ? '<i class="fas fa-key" data-toggle="tooltip" data-placement="top" data-tooltip="Private party, password required"></i>' : ''; ?></label>
												</div>
											<?php } else { ?>
												<div class="custom-control custom-radio">
													<input type="radio" id="sessionRescheduleRadio-<?php echo $session['id']; ?>" data-day="<?php echo $date; ?>"
														   data-friendly_date_time ="<?php echo CTemplate::dateTimeFormat($session['session_start'], VERBOSE); ?>"
														   data-unix_expiry="<?php echo $session['unix_expiry']; ?>" data-is_private="<?php echo (!empty($session['session_password'])) ? 'true' : 'false'; ?>" name="sessionRescheduleRadio" class="custom-control-input" value="<?php echo $session['id']; ?>"<?php echo (!empty($this->cart_info['session_info']['id']) && $this->cart_info['session_info']['id'] == $session['id']) ? ' checked="checked"' : ''; ?> />
													<label class="custom-control-label px-2<?php echo (!empty($this->current_session_id) && $this->current_session_id == $session['id']) ? ' bg-green font-weight-bold text-white' : ''; ?>" for="sessionRescheduleRadio-<?php echo $session['id']; ?>"><?php echo CTemplate::dateTimeFormat($session['session_start'], TIME_ONLY); ?> <?php echo (false && !empty($session['session_password'])) ? '<i class="fas fa-key" data-toggle="tooltip" data-placement="top" data-tooltip="Private party, password required"></i>' : ''; ?></label>
												</div>
											<?php } ?>
										</div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php } ?>
</main>

<div class="modal fade" id="rescheduleSessionDialog" tabindex="-1" role="dialog" aria-labelledby="rescheduleSessionDialogLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="rescheduleSessionDialogLabel">Reschedule Session</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				Are you sure you want to reschedule your <span id="reschedule_from_session"></span> session to <span id="reschedule_to_session"></span>?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="do_reschedule">Reschedule</button>
			</div>
		</div>
	</div>
</div>