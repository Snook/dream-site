Your Dream Dinners order has shipped!

Your freshly assembled dinners will be delivered to your home on your selected delivery date. Make sure to place your dinners in your refrigerator as soon as you can if you plan on eating them in the next few days or in the freezer for future use.

Tracking Number: 

Delivery Date: <?php echo CTemplate::dateTimeFormat($this->orderObj->findSession()->session_start, FULL_MONTH_DAY_YEAR); ?>

Shipping Address: 
<?php echo $this->orderObj->orderAddress->address_line1; ?> <?php echo (!empty($this->orderObj->orderAddress->address_line2) ? $this->orderObj->orderAddress->address_line2 : ''); ?> 
<?php echo $this->orderObj->orderAddress->city; ?>, <?php echo $this->orderObj->orderAddress->state_id; ?> <?php echo $this->orderObj->orderAddress->postal_code; ?>


---------------------------

If you have any questions, please contact <?=$this->sessionInfo['store_name'] . "\n";?> at <?=$this->sessionInfo['telephone_day'] . "\n";?>.