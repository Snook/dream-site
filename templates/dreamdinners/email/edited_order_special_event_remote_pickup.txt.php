Dream Dinners
Order Confirmation | Order Updated

Your order was updated on <?php echo CTemplate::dateTimeFormat($this->orderInfo['timestamp_updated'], VERBOSE)?>.


--------------------------------------------------

Reschedule and Cancellation Policy
If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee.

During inclement weather, please contact your local store to see if your session has been canceled. In the event the store must close, information will be provided on the store's voicemail and every effort will be made to reschedule your session.

Click to view the full Terms and Conditions.
<?php echo HTTPS_SERVER; ?>/terms

--------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>