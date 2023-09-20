Dream Dinners
Session Reminder

Dear <?= $this->firstname ?>,

Thank you for placing your order with Dream Dinners. Your session is scheduled for <?=$this->dateTimeFormat($this->session_start, NORMAL);?>at our <?=$this->store_name?> location.

Don't forget to:
- Bring your cooler to easily transport your meals home.
- Plan to order in-store for your next session. Save time by <?=HTTPS_BASE?>session-menu taking a peek at next month's menu before you arrive and you'll be all set to order when you come in. Plus, you earn bonus PLATEPOINTS each time you sign up in-store.

We are expecting only one person per order. If you wish to bring a guest, please let us know in advance of the session. This will allow us to ensure that we can accommodate the additional person, as well as meet occupancy guidelines as set out by state laws. It is our goal to offer comfortable sessions for all of our guest. Note: Our stores tailor each session to accommodate your order.

---------------------------------------------------
Reschedule and Cancellation Policy
If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee.

---------------------------------------------------

We look forward to seeing you soon. If you'd like to review your order, you can log in now.  <?php echo HTTPS_SERVER; ?>


Enjoy!
 Dream Dinners


Contact your local store: <?=HTTPS_BASE?>locations/<?=$this->store_id?>
View Dream Dinners Policy, Terms and Conditions: <?=HTTPS_BASE?>terms