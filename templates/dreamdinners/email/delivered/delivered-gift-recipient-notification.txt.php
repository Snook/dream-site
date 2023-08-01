You have been gifted Dream Dinners!

Your four delicious family-style dinners will arrive on your doorstep prepped and ready to cook. Once they arrive, place your dinners directly in your refrigerator to enjoy over the next few days or freezer to save for future use. Then prepare based on the easy to follow, step-by-step cooking instructions.

From: <?php echo $this->orderObj->getUser()->firstname; ?> <?php echo $this->orderObj->getUser()->lastname; ?>

Expected Delivery Date: <?php echo CTemplate::dateTimeFormat($this->orderObj->findSession()->session_start, FULL_MONTH_DAY_YEAR); ?>

Shipping To: <?php echo $this->orderObj->orderAddress->firstname; ?> <?php echo $this->orderObj->orderAddress->lastname; ?>, <?php echo $this->orderObj->orderAddress->address_line1; ?> <?php echo (!empty($this->orderObj->orderAddress->address_line2) ? $this->orderObj->orderAddress->address_line2 : ''); ?> <?php echo $this->orderObj->orderAddress->city; ?>, <?php echo $this->orderObj->orderAddress->state_id; ?> <?php echo $this->orderObj->orderAddress->postal_code; ?>


---------------------------

If you have any questions, please contact us via email at support@dreamdinners.com or call us Monday-Friday 9am-5pm PT at 360-804-2020. Use the following order confirmation number to help you <?php echo $this->orderObj->order_confirmation; ?>.