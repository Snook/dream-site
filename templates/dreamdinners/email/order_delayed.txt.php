Dream Dinners
Delayed Payment Order Confirmation

Thank you for placing an order with Dream Dinners. Your Delayed Payment Order was
successfully processed and your reservation has been retained. We look forward to
seeing you.

Order for: <?php echo $this->customer_name ?>
attending <?$this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE)?>
at <?$this->sessionInfo['store_name']; ?>
Confirmation Number: <?= $this->orderInfo['order_confirmation'] ?>
Please keep this number for your records

-------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
