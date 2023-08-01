Dream Dinners
Payment Declined

Dear <?= $this->customer_name ?>,

Your payment for an order scheduled for <?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?> at our <?=$this->sessionInfo['store_name']?>
 has been declined.

The reason we are told that the payment was declined is: <?=$this->declinedPaymentReason?>

Please contact us to update your payment information. We are happy to help solve this over the phone or in the store. The details of the order are listed below.

Thank you

---------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
