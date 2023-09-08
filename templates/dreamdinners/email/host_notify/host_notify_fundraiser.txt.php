Your Dream Dinners Fundraiser is Scheduled!

You can now invite your friends and family to attend. Below are the details of your event and most importantly, the link for everyone to use to RSVP.

Visit the My Events page in your Dream Dinners account to get access to our online sharing tools.

<?=HTTPS_BASE ?>?page=my_events

If you have any questions or concerns, please contact your store.

Have a great event!

------------------------------------

Your event is scheduled for <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], VERBOSE_DATE);  ?> at <?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY);  ?>

RSVP Link: <?=HTTPS_BASE ?>session/<?= $this->session_info['id'] ?>


Store Location: <?=$this->session_info['store_name'] ?>

Address:
<?= $this->session_info['address_line1'] ?> <?= $this->session_info['address_line2'] ?>
<?= $this->session_info['city'] ?>, <?= $this->session_info['state_id'] ?>
<?= $this->session_info['postal_code'] ?>


My Events
<?=HTTPS_BASE ?>?page=my_events&amp;sid=<?= $this->session_info['id'] ?>