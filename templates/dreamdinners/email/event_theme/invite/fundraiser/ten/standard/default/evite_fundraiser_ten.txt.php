You're Invited!

Help <?php echo $this->session['fundraiser_name']; ?> reach our fundraising goal.

Join us to assemble three delicious meals to take home and enjoy with your family for just $60. During this exclusive event, you will enjoy samples, learn more about how easy family dinners can be, and help a deserving cause. $10 from each purchase is automatically donated back to our organization.

Attendance is by invitation only, and space is limited. RSVP today to reserve your space.

Learn more about how Dream Dinners works here.
<?php echo HTTPS_SERVER; ?>/how-it-works

<?php echo $this->message; ?>

--------------------------------
Event Details

ORGANIZATION:  <?php echo $this->session['fundraiser_name']; ?>
DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

LOCATION: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>
STORE PHONE: <?php echo $this->session['telephone_day']; ?>

RSVP: <?php echo $this->referral_link; ?>

Thanks,
- <?php echo $this->session['informal_host_name']; ?>

--------------------------------

*Fundraising sessions are limited to one fundraising order per household per fundraising organization.