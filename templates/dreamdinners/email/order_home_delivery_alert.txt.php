Dream Dinners
Customer Home Delivery Alert


Delivery Address
-----------------------------
<?php echo $this->orderInfo['orderAddress']['firstname']; ?> <?php echo $this->orderInfo['orderAddress']['lastname']; ?>
<?php echo $this->orderInfo['orderAddress']['telephone_1']; ?>

<?php echo $this->orderInfo['orderAddress']['address_line1']; ?>
<?php if (!empty($this->orderInfo['orderAddress']['address_line2'])) { ?><?php echo $this->orderInfo['orderAddress']['address_line2']; ?><?php } ?>
<?php echo $this->orderInfo['orderAddress']['city']; ?>, <?php echo $this->orderInfo['orderAddress']['state_id']; ?> <?php echo $this->orderInfo['orderAddress']['postal_code']; ?>

<?php if (!empty($this->orderInfo['orderAddress']['address_note'])) { ?>
Note: <?php echo $this->orderInfo['orderAddress']['address_note']; ?>
<?php } ?>


<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_text_email.tpl.php'); ?>
