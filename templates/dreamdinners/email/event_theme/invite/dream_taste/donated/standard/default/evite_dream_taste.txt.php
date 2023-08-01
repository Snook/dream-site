Let’s Cook Up Some Fun!

<?php echo $this->to_name; ?>,
You’re invited to my free Meal Prep Workshop. We'll enjoy a fun event together and learn the secret to making easy homemade meals at my exclusive, free event. You’ll receive three delicious, medium-size meals that are already prepped and ready to enjoy at home with your family.

There are limited spaces available, and this offer is only available for guests of this event. Use the link below to view the menu items. Contact us to reserve your spot today!

<?php echo $this->message; ?>

--------------------------------
Event Details

HOST:  <?php echo $this->session['session_host_informal_name']; ?>
DATE: <?php echo $this->dateTimeFormat($this->session['session_start'], VERBOSE_DATE_NO_YEAR); ?>
TIME: <?php echo $this->dateTimeFormat($this->session['session_start'], TIME_ONLY); ?>
INVITE CODE: <?php echo  $this->session['session_password']; ?>

LOCATION: <?php echo $this->session['address_line1']; ?> <?php echo (!empty($this->session['address_line2']) ? $this->session['address_line2'] : ''); ?>
<?php echo $this->session['city']; ?>, <?php echo $this->session['state_id']; ?> <?php echo $this->session['postal_code']; ?>
STORE PHONE: <?php echo $this->session['telephone_day']; ?>

View Menu Items: <?php echo $this->referral_link; ?>

Thanks,
- <?php echo $this->session['informal_host_name']; ?>



*Meal Prep Workshop sessions are limited to one Meal Prep Workshop order per household per Meal Prep Workshop session.