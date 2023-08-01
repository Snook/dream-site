Celebrate with our Summer Celebrations Trial Offer

For only $<?php echo $this->session['dream_taste_price']; ?>, Dream Dinners has you covered for both your summer celebration and the week that follows.  

During this exclusive pick up event, you will get a backyard BBQ bundle including our Herb Crusted Steaks, Chipotle Maple Corn, and a Peach Crisp plus three medium dinners from our delicious menu. Your backyard BBQ comes in our family-size that serves 4 - 6 people.

We’re excited to introduce you to the Dream Dinners experience and make your summer homemade, made easy. Our offer is only for guests of this event, and spaces are limited. Use the link below to reserve your spot today!

<?php echo $this->message; ?>

--------------------------------
Event Details

PICK UP DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>

WHERE: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>

STORE PHONE: <?php echo $this->session['telephone_day']; ?>

ORDER NOW: <?php echo $this->referral_link; ?>


*Trial Events are valid for new and reacquired guests only. Events are limited to one Trial Event order per household.