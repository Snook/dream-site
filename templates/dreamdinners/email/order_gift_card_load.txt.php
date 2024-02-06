Thank you for your order. This receipt is your "Proof of Purchase".

<?php if ($this->charged_amount >= 125 && (date('Y-m-d') >= '2021-12-01' && date('Y-m-d') <= '2021-12-25' )) { ?>

	Our Gift to You

	Thank you for gifting Dream Dinners this year. We want to give you something special. Use the coupon code below to get $10 off your next standard order between January and March 2022.

	Code: Gift22

	Happy Holidays from Dream Dinners.

	*$125 Dream Dinners gift card purchase must be made in one transaction. $10 off coupon code will be sent in your order confirmation email. Only one coupon code can be applied per an 2022 order. Coupon code is valid January – March 2022. Coupon code not eligible with other coupons or offers. Coupon not valid for use in conjunction with a Meal Prep Starter Pack or Special Event session type. No cash redemption permitted. Available for use at participating locations.
<?php } ?>


Dream Dinners Gift Card Receipt

Gift Card Details

Amount Loaded on Card: $<?php echo $this->charged_amount?>
Gift Card Number: <?php echo str_repeat('X', (strlen($this->gift_card_number) - 4)) . substr($this->gift_card_number, -4);?>
Date of Purchase: <?php echo date("m/d/Y");?>

Payment Details

Card Type: <?php echo $this->credit_card_type?>
Last 4 digits: <?php echo $this->credit_card_number?>
Amount: $<?php echo $this->charged_amount?>
Date of Purchase: <?php echo $this->date_of_purchase?>
Billing Name: <?php echo $this->billing_name?>
Billing Address: <?php echo $this->billing_address?> <?php echo $this->billing_zip?>
Billing Email: <?php echo $this->billing_email?>

* Gift Card orders that require shipping will take 2-6 business days for processing & shipping.

Note: $2 shipping/service fee added each Traditional Gift Card ordered.

Have questions? To view the complete Gift Card Policies & Terms online or if you
have questions regarding your Gift Card or Customer Service options please visit
<?php echo HTTPS_SERVER ?>/giftcards