<div class="row bg-gray mb-4">
	<div class="col">
		<div class="row mt-2">
			<div class="col-12">
				<h2 id="enroll_in_plate_points_head" class="text-green-dark text-uppercase font-weight-bold text-left font-size-medium-small mb-4 ml-xs-2">
					<?php if (!empty($this->platePointsEnroll)) { ?>Enroll in PlatePoints<?php } else { ?>PlatePoints<?php } ?></h2>
			</div>
		</div>
		<div class="form-row">
			<div class="col-12 col-lg-12">

				<div class="form-row">
					<div class="form-group col-md-4">
						<p>Birthday</p>
					</div>
					<div class="form-group col-md-8">
						<div class="row">
							<div class="col-md-6 pr-md-1 mb-2 mb-md-0">
								<?php echo $this->form_account['birthday_month_html']; ?>
							</div>
							<div class="col-md-6 pl-md-1">
								<?php echo $this->form_account['birthday_year_html']; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row mb-2">
			<div class="col text-center">
				<?php if (CUser::getCurrentUser()->platePointsData['status'] == 'active') { ?>
					You're currently enrolled in the program. View details under My Account.
				<?php } else if (!CUser::getCurrentUser()->platePointsData['userIsOnHold']) { ?>
					<div class="custom-control custom-control-inline custom-checkbox">
						<input type="checkbox" class="custom-control-input" name="enroll_in_plate_points" id="enroll_in_plate_points">
						<label for="enroll_in_plate_points" class="custom-control-label">Enroll me in PlatePoints. I agree to the <a href="/?static=terms#platepoints" target="_blank">program terms.</a></label>
					</div>
				<?php } ?>
			</div>
		</div>

	</div>
</div>