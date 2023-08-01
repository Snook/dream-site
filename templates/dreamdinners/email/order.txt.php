Dream Dinners
Order Confirmation

Thank you for placing your order. We're excited about the change Dream Dinners is making in your life. Here's to another month of easy, homemade dinners and savoring more moments with those you love.

Your Dream Dinners' order is summarized below.

---------------------------------------------------

Reschedule and Cancellation Policy
If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee.

During inclement weather, please contact your local store to see if your session has been canceled. In the event the store must close, information will be provided on the store's voicemail and every effort will be made to reschedule your session.

Terms & Conditions
By submitting this order and attending your session you are acknowledging agreement to abide by the Terms and Conditions posted on our website. https://dreamdinners.com/main.php?static=terms

---------------------------------------------------
<?php if ($this->sessionInfo['session_type'] == CSession::DELIVERED) { ?>
<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_delivered_text_email.tpl.php'); ?>
<?php } else { ?>
 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
<?php } ?>