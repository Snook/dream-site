Your Dream Dinners party has been scheduled.
We are so excited you have booked your Private Party at Dream Dinners. We look forward to helping you and your friends assembly easy, homemade meals for your families.

Below are the details for your party and a link to invite your friends.

Dream Dinners Party Details:

Your event is scheduled for <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], VERBOSE_DATE); ?> at <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY); ?>

Invite Code: <?php echo $this->session_info['session_password'] ?>

Store Location: <?php echo $this->session_info['store_name'] ?>

Address:
<?php echo $this->session_info['address_line1'] ?> <?php echo $this->session_info['address_line2'] ?>
<?php echo $this->session_info['city'] ?>, <?php echo $this->session_info['state_id'] ?>
<?php echo $this->session_info['postal_code'] ?>


Click the link below to invite your guests.
<?php echo HTTPS_BASE ?>my-events?sid=<?php echo $this->session_info['id'] ?>