Dream Dinners
Pick Up Order Reminder

Dear <?php echo $this->DAO_user->firstname ?>,

It's almost time to pick up your meals. We're looking forward to seeing you when it's convenient during your pick up window on <?php echo $this->DAO_session->sessionStartDateTime()->format("F j, Y - g:i A"); ?> to <?php echo $this->DAO_session->sessionEndDateTime()->format("g:i A"); ?> at our <?php echo $this->DAO_store->store_name; ?> location.


 Tips
 - Add on a few of our delicious sides, breakfast and sweets <?php echo HTTPS_BASE; ?>freezer from our Sides and Sweets Freezer.
 - Place your next order online. <?php echo $this->DAO_store->getMenuURL(); ?>
 - Please arrive during your designated pick up window.
 - Bring your cooler.

---------------------------------------------------

Not feeling well?
If you are experiencing a fever or other illness symptoms within 24 hours of your pick up or assembly session, please call to reschedule your visit.

Reschedule and Cancellation Policy
If you need to reschedule or cancel your order, contact us six days prior to your order date. Cancellations with six or more days’ notice will receive a full refund. Cancellations within five or fewer days’ notice will be subject to a 25% restocking fee.

---------------------------------------------------

We look forward to seeing you soon.

Enjoy!
 Dream Dinners


Contact your local store: <?php echo HTTPS_BASE; ?>location/<?php echo $this->DAO_store->id;?>
View Dream Dinners Policy, Terms and Conditions: <?php echo HTTPS_BASE; ?>terms