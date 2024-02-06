Celebrate the season with our Thanksgiving Trial Offer.

For only $<?php echo $this->session['dream_taste_price']; ?>, Dream Dinners has you covered for both your holiday and the week that follows.

During this exclusive pick up event, you will receive our Holiday Roasted Turkey, Holiday Mashed Potatoes, Holiday Savory Stuffing, and Holiday Green Bean Casserole plus three medium dinners from our delicious menu.

Our offer is only for guests of this event, and availability is limited. Use the link below to place your order today!

<?php echo $this->message; ?>

--------------------------------
Event Details

DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

WHERE: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>

STORE PHONE: <?php echo $this->session['telephone_day']; ?>

RSVP Now: <?php echo $this->referral_link; ?>


*Holiday Pick Up Events are valid for new and reacquired guests only. Events are limited to one Trial per a household.