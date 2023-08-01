Dream Dinners
Order Rescheduled

Dear <?= $this->customer_name ?>,

Your Home Delivery order scheduled for delivery on <?=$this->dateTimeFormat($this->origSessionInfo['session_start'], NORMAL);?> has been rescheduled.

The new 2 hour delivery window starts at <b><?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?>.

If you have any questions or concerns regarding this order please contact us. The details of your order are listed below.
Thank you

---------------------------------------------------

*Delivery fees are non-refundable once charged to your order. If the driver arrives and no one is available to accept the delivery, the driver will leave your order at your front door and take a photo before they leave. If the driver cannot leave your order for any reason, they may have to return your order back to the store. This will incur an additional return delivery fee and a possible restocking fee for your order.

---------------------------------------------------

 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
