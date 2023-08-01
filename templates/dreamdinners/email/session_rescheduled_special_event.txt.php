Dream Dinners
Order Rescheduled

Dear <?= $this->customer_name ?>,

Your order scheduled for pick up on <?=$this->dateTimeFormat($this->origSessionInfo['session_start'], NORMAL);?> at our <?=$this->sessionInfo['store_name']?>
in <?=$this->sessionInfo['city']?> has been rescheduled.

The new pick up time is <b><?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?>.

If you have any questions or concerns regarding this order please contact us. The details of your order are listed below.
Thank you

---------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
