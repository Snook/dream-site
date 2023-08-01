Dream Dinners
Session Reminder

Dear <?= $this->firstname ?>,

Thank you for placing your Made for You order with Dream Dinners. Your pick up time is scheduled
 for <?=$this->dateTimeFormat($this->session_start, NORMAL);?> to <?= date("g:i A", strtotime($this->session_end))?>
 at our <?=$this->store_name?> location.

Don't forget to:
- Bring your cooler to easily transport your meals home.
- Plan to order in-store for your next session. Save time by <?=HTTPS_BASE?>main.php?page=print&menu=<?=$this->next_menu_id?>&store=<?=$this->store_id?> taking a peek at next month's menu before you arrive and you'll be all set to order when you come in. Plus, you earn bonus PLATEPOINTS each time you sign up in-store.

All your fully assembled meals will be waiting for you when you arrive. Because we
want to have the time to assist you with your order, we ask that you arrive on time.
----------------------------------------------
Reschedule and Cancellation Policy
If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee.

----------------------------------------------

We look forward to seeing you soon. If you'd like to review your order, you can log in now.  https://dreamdinners.com


Enjoy!
 Dream Dinners


Contact your local store: <?=HTTPS_BASE?>main.php?page=locations&store_id=<?=$this->store_id?>
View Dream Dinners Policy, Terms and Conditions: <?=HTTPS_BASE?>main.php?static=terms
