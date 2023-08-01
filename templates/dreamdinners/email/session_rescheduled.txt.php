Dream Dinners
Order Rescheduled

Dear <?= $this->customer_name ?>,

Your order for a session scheduled for <?=$this->dateTimeFormat($this->origSessionInfo['session_start'], NORMAL);?> at our <?=$this->sessionInfo['city']?> location has been rescheduled.

The new session time is <?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?>.

If you have any questions or concerns regarding this order please contact us. The details of your order are listed below.
Thank you

---------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
