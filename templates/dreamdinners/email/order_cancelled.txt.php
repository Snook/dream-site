Dream Dinners
Order Canceled

Dear <?php echo $this->customer_name ?>,
Your order for a session scheduled for <?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?> at our <?php echo $this->sessionInfo['city']?> location has been canceled.

If you have any questions or concerns regarding this order please contact us. The details of the canceled order are listed below.
Thank you

---------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>