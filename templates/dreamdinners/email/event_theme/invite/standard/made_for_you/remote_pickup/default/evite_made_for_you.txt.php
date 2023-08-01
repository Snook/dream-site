You’re Invited to Our Community Pick Up Event

At this community pick up event, we will bring your Dream Dinners to you at your local pick up location. You will choose your perfectly prepped dinners from Dream Dinners monthly menu, then the Dream Dinners team will assemble your dinners at their local assembly kitchen. You simply pick up your dinners at the community pick up location below at <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?> on <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>.

<?php echo $this->message; ?>

--------------------------------
Details

DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

LOCATION: <?php echo $this->sessionInfo['session_title']; ?>
		<?php echo $this->sessionInfo['session_remote_location']->address_line1 . ((!empty($this->sessionInfo['session_remote_location']->address_line2)) ? ' ' . $this->sessionInfo['session_remote_location']->address_line2 : '') . ', ' . $this->sessionInfo['session_remote_location']->city . ', ' . $this->sessionInfo['session_remote_location']->state_id . ' ' .$this->sessionInfo['session_remote_location']->postal_code; ?>

For questions contact the store at: <?php echo $this->session['telephone_day']; ?>

Place your monthly order today!
<?php echo $this->referral_link; ?>

New to Dream Dinners? Try the Meal Prep Starter Pack for only $99.
<?php echo $this->referral_link; ?>


Thanks,
- <?php echo $this->session['informal_host_name']; ?>