Dream Dinners
Payment Declined or Transaction Error

To <?php echo $this->sessionInfo['store_name'] ?> Staff,
The payment for the order for <?php echo $this->customer_name ?> which is scheduled for <?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?> at our <?php echo $this->sessionInfo['store_name']?>
 has been declined or an error occurred during the transaction.
The reason that the transaction failed or was declined : <?php echo $this->declinedPaymentReason?>.

---------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>