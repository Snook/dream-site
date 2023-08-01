Let's Cook Up Some Fun!

<?php echo $this->to_name; ?>,
You're invited to a Meal Prep Workshop at Dream Dinners. Learn the secret to making easy, homemade meals and taste delicious appetizers. You’ll receive three delicious, medium-size meals to enjoy at home with your family for just $<?php echo $this->session['dream_taste_price']; ?>.</p>

There are limited spaces available and this offer is only available for guests of this event. Use the link below to reserve your spot today.

<?php echo $this->message; ?>

--------------------------------
Event Details

DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

WHERE: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>

STORE PHONE: <?php echo $this->session['telephone_day']; ?>

RSVP Now: <?php echo $this->referral_link; ?>


*Meal Prep Workshop sessions are limited to one Meal Prep Workshop order per household per Meal Prep Workshop session.