You're Invited!

<?php echo $this->to_name; ?>,
Join me for a Friends Night Out. This event gives you the chance to see how easy it is to experience homemade meals with your family.

Everyone who RSVP's will receive one free ready-to-cook meal* to take home and enjoy.

In addition to the FREE dinner, you will get to enjoy time with your friends and try some delicious appetizers. This event has limited spots available, so RSVP today!

*Every guest receives a free medium dinner which serves 2-3 people.

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