Dream Dinners
Customer Coupon Code Used Alert

-----------------------------
Name: <?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?> <?php echo HTTPS_SERVER; ?>/main.php?page=admin_user_details&id=<?php echo $this->user->id; ?>
Email:&nbsp;<?php echo $this->customer_primary_email; ?>

Coupon Used
<?php echo $this->coupon_details->coupon_code; ?> - <?php echo $this->coupon_details->coupon_code_title; ?>


<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
