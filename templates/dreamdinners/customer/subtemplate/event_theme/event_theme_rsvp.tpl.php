<div class="card-deck">

	<div class="col-lg-6 p-0">
		<div class="card m-2">
			<div class="card-header font-weight-bold text-center rsvp_only_form_header">
				<div class="rsvp_only_form_complete<?php if (!$this->has_rsvp) { ?> collapse<?php } ?>">Thank you for your RSVP!</div>
				<div class="rsvp_only_form_incomplete<?php if ($this->has_rsvp) { ?> collapse<?php } ?>">RSVP Today</div>
			</div>
			<div class="card-body">
				<div class="rsvp_only_form_complete<?php if (!$this->has_rsvp) { ?> collapse<?php } ?>">
					<p>We have you registered and can't wait to meet you! Look for event details in your inbox. See you soon.</p>

					<a class="btn btn-primary btn-sm" href="/account">Complete your profile</a>
				</div>
				<form id="rsvp_only_form" name="rsvp_only_form" class="rsvp_only_form_incomplete needs-validation<?php if ($this->has_rsvp) { ?> collapse<?php } ?>" novalidate>
					<div class="form-row">
						<div class="form-group col-md-6">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">First</span>
								</div>
								<input type="text" name="rsvp_firstname" id="rsvp_firstname" class="cform_input form-control" placeholder="*First Name" required="required" />
							</div>
						</div>
						<div class="form-group col-md-6">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">Last</span>
								</div>
								<input type="text" name="rsvp_lastname" id="rsvp_lastname" class="cform_input form-control" placeholder="*Last Name" required="required" />
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">Email</span>
								</div>
								<input type="email" name="rsvp_primary_email_login" id="rsvp_primary_email_login" class="cform_input form-control" placeholder="*Email" required="required" />
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">Password</span>
								</div>
								<input type="password" name="rsvp_password_login" id="rsvp_password_login" class="cform_input form-control" placeholder="*Password" required="required" />
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">Phone</span>
								</div>
								<input type="tel" name="rsvp_telephone_1" id="rsvp_telephone_1" class="cform_input form-control telephone" placeholder="*Primary Telephone" required="required" />
							</div>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col">
							<div class="custom-control custom-checkbox">
								<input type="checkbox" name="rsvp_sms_opt_in" id="rsvp_sms_opt_in" class="custom-control-input">
								<label class="custom-control-label font-size-small" for="rsvp_sms_opt_in">Opt-in to receive text messages. Message and data rates may apply.</label>
							</div>
						</div>
					</div>

				</form>
			</div>
		</div>
		<div class="card-footer">
			<?php if (!empty($this->session['dream_taste_can_rsvp_upgrade'])) { ?>
				<button class="btn btn-primary btn-block rsvp_upgrade_btn rsvp_only_form_complete<?php if (!$this->has_rsvp) { ?> collapse<?php } ?>">
					Add 3 additional medium meals for just $<?php echo $this->session['dream_taste_price']; ?>!
				</button>
			<?php } ?>
			<button class="btn btn-primary btn-block btn-spinner mt-0 rsvp_submit_btn rsvp_only_form_incomplete<?php if ($this->has_rsvp) { ?> collapse<?php } ?>">
				RSVP Today
			</button>
		</div>
	</div>
</div>

<?php if (!empty($this->session['dream_taste_can_rsvp_upgrade'])) { ?>
	<div class="col-lg-6 p-0">
		<div class="card m-2">
			<div class="card-header font-weight-bold text-center">
				RSVP &amp; Upgrade
			</div>
			<div class="card-body text-center">
				<i class="dd-icon icon-group font-size-extra-large text-green"></i>
				<p class="card-text">We are extending an opportunity for you to receive a one-time special offer. Upgrade your order to receive <span class="font-weight-bold">three additional medium-size meals</span> for only $<?php echo $this->session['dream_taste_price']; ?>. There is no obligation. Choose your extra meals today!</p>
			</div>
			<div class="card-footer">
				<button class="btn btn-primary btn-block rsvp_upgrade_btn">
					View menu items
				</button>
			</div>
		</div>
	</div>
<?php } ?>

</div>