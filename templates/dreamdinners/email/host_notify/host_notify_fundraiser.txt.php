Your Dream Dinners Fundraiser is Scheduled!

You can now invite your friends and family to attend. Below are the details of your event and most importantly, the link for everyone to use to RSVP.

Visit the My Events page in your Dream Dinners account to get access to our online sharing tools.

<?php echo HTTPS_BASE ?>my-events

If you have any questions or concerns, please contact your store.

Have a great event!

------------------------------------

Your event is scheduled for <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], VERBOSE_DATE); ?> at <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY); ?>

RSVP Link: <?php echo HTTPS_BASE ?>session/<?php echo $this->session_info['id'] ?>


Store Location: <?php echo $this->session_info['store_name'] ?>

Address:
<?php echo $this->session_info['address_line1'] ?> <?php echo $this->session_info['address_line2'] ?>
<?php echo $this->session_info['city'] ?>, <?php echo $this->session_info['state_id'] ?>
<?php echo $this->session_info['postal_code'] ?>


My Events
<?php echo HTTPS_BASE ?>my-events?sid=<?php echo $this->session_info['id'] ?>