Your Dream Dinners order has shipped!

Your freshly assembled dinners will be delivered to your home on your selected delivery date. Make sure to place your dinners in your refrigerator as soon as you can if you plan on eating them in the next few days or in the freezer for future use.

Tracking Number:

Delivery Date: <?php echo $this->DAO_orders->DAO_session->sessionStartDateTime()->format("F j, Y"); ?>

Tracking number: <?php echo $this->DAO_orders->DAO_orders_shipping->tracking_number; ?>

Shipping Address:

<?php echo $this->DAO_orders->DAO_orders_address->generateAddressWithBreaks(); ?>


---------------------------

If you have any questions, please contact <?php echo $this->DAO_orders->DAO_store->store_name; ?> at <?php echo $this->DAO_orders->DAO_store->telephone_day; ?>.