Dream Dinners Gift Card Receipt

Thank you for your order. This receipt is your "Proof of Purchase".

<?php if ($this->charged_amount >= 125 && (date('Y-m-d') >= '2021-12-01' && date('Y-m-d') <= '2021-12-25' )) { ?>

Our Gift to You 

Thank you for gifting Dream Dinners this year. We want to give you something special. Use the coupon code below to get $10 off your next standard order between January and March 2022.

Code: Gift22

Happy Holidays from Dream Dinners.

*$125 Dream Dinners gift card purchase must be made in one transaction. $10 off coupon code will be sent in your order confirmation email. Only one coupon code can be applied per an 2022 order. Coupon code is valid January – March 2022. Coupon code not eligible with other coupons or offers. Coupon not valid for use in conjunction with a Meal Prep Starter Pack or Special Event session type. No cash redemption permitted. Available for use at participating locations.
<?php } ?>

---------------------------------------

Gift Cards Purchased:
<?php foreach($this->cards_purchased as $id => $thisCard) { ?>

Card Type: <?php if ($thisCard['media_type'] == 'PHYSICAL') { ?>
Traditional card (shipped to address)
<?php } else { ?>
eGift card (sent via email)
<?php } ?>
Card Design: <?=$thisCard['design_title']?>
Amount: $<?=$thisCard['card_amount']?>
To: <?=$thisCard['to_name']?>
From:<?=$thisCard['from_name']?>
Message: <?=$thisCard['message_text']?>
<?php if ($thisCard['media_type'] == 'VIRTUAL') { ?>
Recipient Email Address: <?=$thisCard['recipient_email']?>
Resend eGift Card Email: <?=HTTPS_BASE?>main.php?page=resend_egift&oid=<?=$thisCard['confirm_id']?>
<?php } else { ?>
Recipient Name: <?=$thisCard['ship_to_name']?>
Recipient Address:
<?=$thisCard['shipping_address_1']?>
<?php if (!empty($thisCard['shipping_address_2'])) { ?>
<?=$thisCard['shipping_address_2']?>
<?php } ?>
<?=$thisCard['shipping_city']?>, <?=$thisCard['shipping_state']?> <?=$thisCard['shipping_zip']?>
<?php } } ?>

Payment Details
Card Type: <?= $this->payment_card_type?>
Last 4 digits: <?= $this->credit_card_number?>
Amount: $<?= $this->charged_amount?>
Date of Purchase: <?= $this->date_of_purchase?>
Billing Name: <?= $this->billing_name?>
Billing Address: <?= $this->billing_address?> <?= $this->billing_zip?>
Billing Email: <?=$this->billing_email?>

*Gift Card orders that require shipping will take 2-6 business days for processing & shipping.

Note: $2 shipping/service fee added each Traditional Gift Card ordered.

Have questions? To view the complete Gift Card Policies & Terms online or
if you have questions regarding your Gift Card or Customer Service options
please visit <?=HTTPS_SERVER?>/main.php?static=giftcards
