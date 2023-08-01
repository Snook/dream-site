<div class="form-row mb-4 mb-md-0" data-email-address-div="<?php echo $referral['referred_user_email']; ?>">
	<div class="form-group col-md-4">
		<input type="text" class="form-control" disabled placeholder="Guest name" id="name[<?php echo $referral_id; ?>]" name="name[<?php echo $referral_id; ?>]" value="<?php echo $referral['referred_user_name']; ?>">
	</div>
	<div class="form-group col-md-8">
		<div class="input-group mx-auto">
			<input type="email" class="form-control" disabled placeholder="name@example.com" id="email[<?php echo $referral_id; ?>]" name="email[<?php echo $referral_id; ?>]" value="<?php echo $referral['referred_user_email']; ?>">
			<div class="input-group-append">
				<div class="input-group-text py-0">
					<?php if (!in_array($referral['referred_user_email'], $this->usersFuturePastEvents['attendingGuestEmails'])) { ?>
						<div class="custom-control custom-checkbox">
							<input class="custom-control-input referred_user_send_email" data-referral_id="<?php echo $referral_id; ?>" id="resend[<?php echo $referral_id; ?>]" name="resend[<?php echo $referral_id; ?>]" type="checkbox">
							<label class="custom-control-label" for="resend[<?php echo $referral_id; ?>]">Resend</label>
						</div>
					<?php } else { ?>
						<span class="font-size-small">Attending</span>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
