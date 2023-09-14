Your Dream Dinners party has been scheduled.
We are so excited you have booked your Private Party at Dream Dinners. We look forward to helping you and your friends assembly easy, homemade meals for your families.

Below are the details for your party and a link to invite your friends.

Dream Dinners Party Details:

Your event is scheduled for <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], VERBOSE_DATE);  ?> at <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY);  ?>

Invite Code: <?= $this->session_info['session_password'] ?>

Store Location: <?=$this->session_info['store_name'] ?>

Address:
<?= $this->session_info['address_line1'] ?> <?= $this->session_info['address_line2'] ?>
<?= $this->session_info['city'] ?>, <?= $this->session_info['state_id'] ?>
<?= $this->session_info['postal_code'] ?>


Click the link below to invite your guests.
<?=HTTPS_BASE ?>my-events?sid=<?= $this->session_info['id'] ?>