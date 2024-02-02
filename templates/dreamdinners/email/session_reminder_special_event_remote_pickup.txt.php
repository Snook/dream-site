Dream Dinners
Community Pick Up Order Reminder

Dear <?= $this->firstname ?>,

It's almost time to pick up your meals. We're looking forward to seeing you at the community pick up location during your pick up window on <?=$this->dateTimeFormat($this->session_start, NORMAL);?> to <?= date("g:i A", strtotime($this->session_end))?> at our <?=$this->store_name?> location.

Community Pick Up Location:
<?php echo $this->sessionInfo['session_title']; ?>
<?php echo $this->sessionInfo['session_remote_location']->address_line1 . ((!empty($this->sessionInfo['session_remote_location']->address_line2)) ? ' ' . $this->sessionInfo['session_remote_location']->address_line2 : '') . ', ' . $this->sessionInfo['session_remote_location']->city . ', ' . $this->sessionInfo['session_remote_location']->state_id . ' ' .$this->sessionInfo['session_remote_location']->postal_code; ?>

What to Expect
  - We will have your dinners ready when you arrive. Bring your cooler to take them home.
  - Add on a few of our delicious sides, breakfast and sweets <?=HTTPS_BASE?>freezer from our Sides and Sweets Freezer.
  - Place your next order to reserve your preferred community pick up spot. <?=HTTPS_BASE?>session-menu


---------------------------------------------------

Not feeling well?
If you are experiencing a fever or other illness symptoms within 24 hours of your pick up or assembly session, please call to reschedule your visit.

Reschedule and Cancelation Policy
If you need to reschedule or cancel your order, contact us six days prior to your order date. Cancelations with six or more days’ notice will receive a full refund. Cancelations within five or fewer days’ notice will be subject to a 25% restocking fee.

---------------------------------------------------

We look forward to seeing you soon.

Enjoy!
 Dream Dinners


Contact your local store: <?=HTTPS_BASE?>locations/<?=$this->store_id?>
View Dream Dinners Policy, Terms and Conditions: <?=HTTPS_BASE?>terms