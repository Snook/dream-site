Your Private Home Delivery is Scheduled!

You can now share Dream Dinners with your friends and family by inviting them to join you at your private home delivery event. Below are the details of your event and, most importantly, the link for everyone to use to RSVP.

Visit the My Events page in your Dream Dinners account to get access to our easy to use sharing tools.

<?php echo HTTPS_BASE ?>my-events

If you have any questions or concerns, please contact your store.

Have a great event!

------------------------------------

Your event is scheduled for <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], VERBOSE_DATE); ?> at <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY); ?>

RSVP Link: <?php echo HTTPS_BASE ?>session/<?php echo $this->session_info['id'] ?>

Invite Code: <?php echo $this->session_info['session_password'] ?>

Meals Prepped by: <?php echo $this->session_info['store_name'] ?>

Home Delivery Address:
<?php echo $this->session_info['session_remote_location']->address_line1 . ((!empty($this->session_info['session_remote_location']->address_line2)) ? ' ' . $this->session_info['session_remote_location']->address_line2 : '') . ', ' . $this->session_info['session_remote_location']->city . ', ' . $this->session_info['session_remote_location']->state_id . ' ' .$this->session_info['session_remote_location']->postal_code; ?>


My Events
<?php echo HTTPS_BASE ?>my-events?sid=<?php echo $this->session_info['id'] ?>