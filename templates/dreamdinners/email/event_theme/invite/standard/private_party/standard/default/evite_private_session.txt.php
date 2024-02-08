YOU'RE INVITED TO A PRIVATE PARTY!

<?php echo $this->to_name; ?>,

Join <?php echo $this->session['session_host_informal_name']; ?> at a Private Party to simplify your mealtime and get prepped family-style dinners to cook at home. Dream Dinners will take care of all of the shopping, chopping, and clean up. All you have to do is relax and enjoy some extra time with your friends and family.

This event has limited spots available, so RSVP today!
<?php echo $this->referral_link; ?>


<?php echo $this->message; ?>

--------------------------------
Event Details

HOST: <?php echo $this->session['session_host_informal_name']; ?>
DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>
INVITE CODE: <?php echo $this->session['session_password']; ?>

LOCATION: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>
STORE PHONE: <?php echo $this->session['telephone_day']; ?>

RSVP: <?php echo $this->referral_link; ?>

Thanks,
- <?php echo $this->session['informal_host_name']; ?>