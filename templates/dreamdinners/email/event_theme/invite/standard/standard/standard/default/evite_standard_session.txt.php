Join me at my next session!

<?php echo $this->to_name; ?>,
Join me at my Dream Dinners session to customize dinners for your family. They will take care of all of the shopping, chopping, and clean up, so you can assemble a month of meals in about an hour.

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