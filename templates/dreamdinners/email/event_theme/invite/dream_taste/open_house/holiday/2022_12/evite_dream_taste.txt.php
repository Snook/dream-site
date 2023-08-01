Celebrate the season with our Christmas Brunch Trial Offer.

For only $<?php echo $this->session['dream_taste_price']; ?>, Dream Dinners has you covered for both your Christmas morning brunch and the week that follows.

During this exclusive pick up event, you will receive a Breakfast Frittata, Seasonal French Toast Bake and Country Potatoes plus three medium dinners from our delicious menu. Your Christmas Brunch comes in our family-size that serves 4-6 people.

We’re excited to introduce you to the Dream Dinners experience and make your Christmas homemade, made easy. Our offer is only for guests of this event, and spaces are limited. Use the link below to reserve your spot today!

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