Dream Dinners
Customer Shipping Order Alert

Name:&nbsp;<?php echo $this->customer_name; ?>
Email:&nbsp;<?php echo $this->customer_primary_email; ?>
Order Confirmation: <?php echo $this->orderInfo['order_confirmation']; ?>

Order Date: <?php echo CTemplate::dateTimeFormat($this->orderInfo['timestamp_created'], NORMAL, $this->orderInfo["store_id"], CONCISE) ?>
Carrier Pick Up Date: <?php echo CTemplate::dateTimeFormat($this->orderInfo['orderShipping']['ship_date'], VERBOSE_DATE, $this->orderInfo["store_id"], CONCISE) ?>
Requested Delivery Date: <?php echo $this->sessionInfo['session_start_dtf_verbose_date']; ?>


Delivery Address
-----------------------------
<?php echo $this->orderInfo['orderAddress']['firstname']; ?> <?php echo $this->orderInfo['orderAddress']['lastname']; ?>

<?php echo $this->orderInfo['orderAddress']['address_line1']; ?>
<?php if (!empty($this->orderInfo['orderAddress']['address_line2'])) { ?><?php echo $this->orderInfo['orderAddress']['address_line2']; ?><?php } ?>
<?php echo $this->orderInfo['orderAddress']['city']; ?>, <?php echo $this->orderInfo['orderAddress']['state_id']; ?> <?php echo $this->orderInfo['orderAddress']['postal_code']; ?>

<?php if (!empty($this->orderInfo['orderAddress']['address_note'])) { ?>
Note: <?php echo $this->orderInfo['orderAddress']['address_note']; ?>
<?php } ?>

This shipment includes the following items
-----------------------------
<?php foreach ($this->orderInfo['boxes']['itemList'] as $item) {
	if( $item['qty'] > 0 ){?>
	- <?php echo $item['qty'] ?> <?php echo CMenuItem::translatePricingType($item['pricing_type'], true) ?> <?php echo $item['display_title'] ?>
<?php }}?>