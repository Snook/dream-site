Let’s Cook Up Some Fun!

<?php echo $this->to_name; ?>,
You're invited to my Meal Prep Workshop. Learn my secret to making easy homemade meals at my exclusive pick up event. You’ll receive three delicious, medium-size meals already prepped and ready to enjoy at home with your family for just $<?php echo $this->session['dream_taste_price']; ?>.

There are limited spaces available, and this offer is only available for guests of this event. Use the link below to reserve your spot today!

Learn more about Dream Dinners.
<?php echo HTTPS_SERVER; ?>/how-it-works

<?php echo $this->message; ?>

--------------------------------
Event Details

HOST: <?php echo $this->session['session_host_informal_name']; ?>
PICK UP DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>
INVITE CODE: <?php echo $this->session['session_password']; ?>

LOCATION: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>
STORE PHONE: <?php echo $this->session['telephone_day']; ?>

RSVP: <?php echo $this->referral_link; ?>

Thanks,
- <?php echo $this->session['informal_host_name']; ?>



*Meal Prep Workshop sessions are limited to one Meal Prep Workshop order per household per Meal Prep Workshop session.