Dream Dinners
Order Confirmation

Thank you for placing an order with Dream Dinners. Your delayed payment was successfully processed. We look forward to seeing you at the community pick up location.

Your fully prepped dinners will be waiting when you arrive. Please bring a cooler or box to transport your dinners home. It is important that you arrive during your scheduled time.

Community Pick Up Location:
<?php echo $this->sessionInfo['session_title']; ?>
<?php echo $this->sessionInfo['session_remote_location']->address_line1 . ((!empty($this->sessionInfo['session_remote_location']->address_line2)) ? ' ' . $this->sessionInfo['session_remote_location']->address_line2 : '') . ', ' . $this->sessionInfo['session_remote_location']->city . ', ' . $this->sessionInfo['session_remote_location']->state_id . ' ' .$this->sessionInfo['session_remote_location']->postal_code; ?>

If you have questions about your order please contact the store.

-----------------------------------------

Reschedule and Cancellation Policy
If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee.

During inclement weather, please contact your local store to see if your session has been canceled. In the event the store must close, information will be provided on the store's voicemail and every effort will be made to reschedule your session.


<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
