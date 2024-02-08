Dream Dinners
Order Rescheduled

Dear <?php echo $this->customer_name ?>,

Your order scheduled for <?php echo $this->dateTimeFormat($this->origSessionInfo['session_start'], NORMAL);?> has been rescheduled.

The new pick up time is at <b><?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?>.

If you have any questions or concerns regarding this order please contact us. The details of your order are listed below.
Thank you


---------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>