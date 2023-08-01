You're Invited!

This summer, the Dream Dinners Foundation is proud to partner with The Leukemia & Lymphoma Society (LLS) for The Dare to Dream Project, supporting pediatric blood cancer patients.

Join us to learn more about The Dare to Dream Project at our Leukemia and Lymphoma Society Open House Event. Enjoy delicious samples, fun giveaways and help us support a great cause.

<?php echo $this->message; ?>

--------------------------------
Event Details

DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

WHERE: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>

STORE PHONE: <?php echo $this->session['telephone_day']; ?>

RSVP Now: <?php echo $this->referral_link; ?>