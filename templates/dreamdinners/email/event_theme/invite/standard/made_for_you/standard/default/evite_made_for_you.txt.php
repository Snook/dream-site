You have been invited to try our Pick Up service

<?php echo $this->to_name; ?>,
Give Dream Dinners a try. Choose a pick up session and we will assemble delicious dinners for your family. You simply pick up your dinners during the selected pick up window.

<?php echo $this->message; ?>

--------------------------------
Details

DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

LOCATION: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>
STORE PHONE: <?php echo $this->session['telephone_day']; ?>

Place your order today!
<?php echo $this->referral_link; ?>

Thanks,
- <?php echo $this->session['informal_host_name']; ?>