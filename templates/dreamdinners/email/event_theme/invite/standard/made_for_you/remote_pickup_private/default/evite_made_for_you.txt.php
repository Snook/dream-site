You’re Invited to a Community Pick Up Event

Join me at this special pick up event to fill your freezer with delicious, easy meals for your family. You will choose your perfectly prepped dinners from Dream Dinners monthly menu, then the Dream Dinners team will assemble your dinners at their local assembly kitchen. You simply pick up your dinners at the community pick up location at <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?> on <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>.

Spend less time in the kitchen and more time savoring moments together.

<?php echo $this->message; ?>

--------------------------------
Details

DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

LOCATION: Hosted by <?php echo $this->session['session_host_firstname']; ?>
		<?php echo $this->sessionInfo['session_remote_location']->address_line1 . ((!empty($this->sessionInfo['session_remote_location']->address_line2)) ? ' ' . $this->sessionInfo['session_remote_location']->address_line2 : '') . ', ' . $this->sessionInfo['session_remote_location']->city . ', ' . $this->sessionInfo['session_remote_location']->state_id . ' ' .$this->sessionInfo['session_remote_location']->postal_code; ?>

For questions about your order contact the store at: <?php echo $this->session['telephone_day']; ?>

Place your monthly order today!
<?php echo $this->referral_link; ?>

New to Dream Dinners? Try the Meal Prep Starter Pack for only $99.
<?php echo $this->referral_link_starter; ?>


Thanks,
- <?php echo $this->session['informal_host_name']; ?>