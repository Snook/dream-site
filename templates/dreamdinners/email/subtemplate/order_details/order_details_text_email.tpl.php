Dream Dinners Order Summary
---------------------------
Order for: <?php echo $this->customer_name ?>
Date and time: <? $this->sessionTypeDateTimeFormat($this->sessionInfo['session_start'], $this->sessionInfo['session_type_subtype'], VERBOSE)?>
Location: <?$this->sessionInfo['store_name']; ?>
Confirmation Number: <?= $this->orderInfo['order_confirmation'] ?>
Please keep this number for your records

<?php if (!empty($this->membership) && $this->membership['status'] == CUser::MEMBERSHIP_STATUS_CURRENT) { ?>
Meal Prep+ Membership
---------------------
This is <?php echo $this->membership['display_strings']['order_position']; ?> membership orders.
My last membership month is <?php echo $this->membership['display_strings']['completion_month']; ?>.
My membership savings to date is $<?php echo CTemplate::moneyFormat($this->membership['display_strings']['total_savings']); ?>.
<?php } else if (!empty($this->plate_points) && $this->plate_points['status'] == 'active') { ?>
PlatePoints
--------------------
<?php if (!$this->isEmptyFloat($this->plate_points['points_this_order'])) { ?>
Points earned this order:  <?php echo number_format($this->plate_points['points_this_order']); ?>
<?php  } ?>
<?php if (!empty($this->plate_points['available_credit'])) { ?>
Available Dinner Dollars:  $<?php echo CTemplate::moneyFormat($this->plate_points['available_credit']); ?>
 <?php } ?>
<?php if (!$this->isEmptyFloat($this->plate_points['next_expiring_credit_amount'])) { ?>
Next Dinner Dollar Expiration:  $<?php echo number_format($this->plate_points['next_expiring_credit_amount']) ?> on <?php echo $this->plate_points['next_expiring_credit_date'] ?>
<?php } ?>
<?php } else { ?>
Rate My Meals
---------------------
Did you know that your ratings contribute to the fate of our menu items? Make sure to actively rate your meals, especially if you want to see the ones you love come back!
Rate your meals now > <?=HTTPS_BASE ?>main.php?page=my_meals
<?php } ?>

---------------------------
Not feeling well?
If you are experiencing a fever or other illness symptoms within 24 hours of your pick up or assembly session, please call <?=$this->sessionInfo['telephone_day'] . "\n";?> to reschedule your visit.

<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_meal_customization_fee'] ) ) { ?>
Meal Customization
<?php echo OrdersCustomization::RECIPE_LEGAL;
} ?>

Reschedule and Cancellation Policy
If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee. During inclement weather, please contact your local store to see if your session has been canceled. In the event the store must close, information will be provided on the store's voicemail and every effort will be made to reschedule your session.

Policies and Terms
By participating in the Dream Dinners program, you agree to the Policy and Terms
https://dreamdinners.com/main.php?static=terms


Order Totals
---------------------------
Total Item Count			<?= $this->orderInfo['menu_items_total_count'] . "\n" ?>
<?php if( !$this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) ) { ?>
Misc Food ( <?=$this->orderInfo['misc_food_subtotal_desc']?> ): $<?= $this->moneyFormat($this->orderInfo['misc_food_subtotal']) ?><?php } ?>
  <?php if( !$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal']) ) { ?>
Misc Non-Food ( <?=$this->orderInfo['misc_nonfood_subtotal_desc']?> ): $<?= $this->moneyFormat($this->orderInfo['misc_nonfood_subtotal']) ?><?php } ?>


<?php if ( !$this->isEmptyFloat($this->orderInfo['user_preferred_discount_total'] )) { ?>
Preferred Discount			<?= $this->moneyFormat($this->orderInfo['user_preferred_discount_total']) . "\n"  ?><?php } ?>
<?php if ( !$this->isEmptyFloat($this->orderInfo['direct_order_discount'] )) { ?>
Direct Order Discount			$<?=$this->moneyFormat($this->orderInfo['direct_order_discount'])  . "\n" ?><?php } ?>
<?php if ( $this->orderInfo['promo_code_discount_total'] ) { ?>
Promotional Code Discount		$<?=$this->moneyFormat($this->orderInfo['promo_code_discount_total'])  . "\n" ?><?php } ?>
<?php if( !$this->isEmptyFloat( $this->orderInfo['volume_discount_total'] ) ) { ?>
Volume Reward:		-<?= $this->moneyFormat($this->orderInfo['volume_discount_total']) ?><?php } ?>
<?php if( !$this->isEmptyFloat( $this->orderInfo['points_discount_total'] ) ) { ?>
PlatePoints Dinner Dollars: -<?= $this->moneyFormat($this->orderInfo['points_discount_total']) ?><?php } ?>
<?php if( !$this->isEmptyFloat( $this->orderInfo['dream_rewards_discount'] ) ) { ?>
Dream Rewards Discount: -<?= $this->moneyFormat( $this->orderInfo['dream_rewards_discount'] ) ?><?php } ?>
<?php if( !$this->isEmptyFloat( $this->orderInfo['promo_code_discount_total'] ) ) { ?>
Promotional Code Discount: -<?= $this->moneyFormat( $this->orderInfo['promo_code_discount_total'] ) ?><?php } ?>
<?php if( !$this->isEmptyFloat( $this->orderInfo['coupon_code_discount_total'] ) ) { ?>
Promo Code Discount (<?=$this->orderInfo['coupon_title']?>): -<?= $this->moneyFormat( $this->orderInfo['coupon_code_discount_total'] ) ?><?php } ?>
<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_fee'] ) ||  $this->orderInfo['service_fee_description'] == "Free Assembly Promo" ) { ?>
Service Fees:			<?= $this->moneyFormat( $this->orderInfo['subtotal_service_fee'] ) ?><?php } ?>
Bag Fees: <?php
	if (!$this->isEmptyFloat($this->orderInfo['subtotal_bag_fee'])) {
		echo '		$'.$this->moneyFormat($this->orderInfo['subtotal_bag_fee']);
	}else{
		if ($this->orderInfo['opted_to_bring_bags']) {
			echo '		I will bring my own Bags';
		}
	} ?>
<?php if( $this->orderInfo['opted_to_customize_recipes']  == 1 ) { ?>
Customization Fee:<?= $this->moneyFormat( $this->orderInfo['subtotal_meal_customization_fee'] ) ?>
<?=  $this->meal_customization_string ?><?php } ?>
<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_delivery_fee'] )) { ?>
Delivery Fee:			<?= $this->moneyFormat( $this->orderInfo['subtotal_delivery_fee'] ) ?><?php } ?>

Item Subtotal		$<?= $this->moneyFormat($this->orderInfo['subtotal_all_items'])  . "\n" ?>
Tax Subtotal		$<?= $this->moneyFormat($this->orderInfo['subtotal_all_taxes'])  . "\n" ?>
Total				$<?= $this->moneyFormat($this->orderInfo['grand_total'])  . "\n" ?>
<?php
   if (empty($this->orderInfo['bundle_id'])) {
   // older olders
   if (isset($this->orderInfo['average_per_serving_cost']) && $this->orderInfo['average_per_serving_cost'] > 0 && !$this->isEmptyFloat($this->orderInfo['average_per_serving_cost']) ) { ?>
Average Cost Per Serving: <?= $this->moneyFormat($this->orderInfo['average_per_serving_cost']) ?>
<?php } else if (!empty($this->orderInfo['servings_total_count']) && $this->orderInfo['servings_total_count'] > 0) {
  	$basisAdjustment = COrders::getBasisAdjustment($this->menuInfo);
?>
Avg Cost Per Serving for Dinners: $<?= $this->moneyFormat( COrders::averageCostPerServing($this->orderInfo, false, $basisAdjustment) ) ?>
<?php } } ?>
Order Type:			<?= $this->orderInfo['order_type']  . "\n" ?>

Special Instructions/Requests:	<?php if ($this->orderInfo['order_user_notes'] != NULL) {
	            echo $this->orderInfo['order_user_notes'] . "\n" ;
	        }
			else echo "\tnone"  . "\n" ;?>


<?php
	if ( isset($this->paymentInfo) ) {
		  	echo "\nPayment Information\n";
		  	echo "-------------------\n";
			$counter = 0;
			foreach ( $this->paymentInfo as $arrItem ) {
				if (is_array($arrItem)) {
					if ($arrItem['payment_type'] === CPayment::CC) {
						$isDeposit = isset($arrItem['deposit']) ? "  (Deposit) " : '' ;
						echo $arrItem['credit_card_type']['other'] . $isDeposit . "\nLast 4 digits: " .  substr($arrItem['payment_number']['other'], strlen($arrItem['payment_number']['other'])-4, strlen($arrItem['payment_number']['other']));
					}
					else if ($arrItem['payment_type'] === CPayment::STORE_CREDIT)
						echo $arrItem['payment_info']['other'] . "\nLast 4 digits: " .
						 (isset($arrItem['payment_number']['other']) ? (substr($arrItem['payment_number']['other'], strlen($arrItem['payment_number']['other'])-4, strlen($arrItem['payment_number']['other']))) : '');
					else if ($arrItem['payment_type'] === CPayment::GIFT_CERT)
						echo $arrItem['payment_info']['other'] . "\nGift Type: " .  $arrItem['gift_cert_type']['other'];
					else
						echo $arrItem['payment_info']['other'];
					echo "\t$" . $arrItem['total']['other'];
					$counter++;
				}
			}

			echo "\n";
		}
?>

<?php if ( isset($this->menuInfo) ) { ?>
Order Itemization
-----------------
Entrees
Qty		Item Price 	Dinner
<?php foreach ( $this->menuInfo as $id => $item ) { if ( is_numeric($id) && $item['qty'] ) { ?>
<?=$item['qty']?>		<?=$item['price'] ?>		<?=$item['display_title'] ?>
<?php }
} }?>

<?php if ( isset($this->sessionInfo) ) { ?>
<?php echo ($this->sessionInfo['session_type'] == "SPECIAL_EVENT" ? "Pickup Details" : "Session Details");?>
---------------
Time: <?=$this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE) . "\n";?>
Location: <?=$this->sessionInfo['store_name'] . "\n";?>
Address: <?=$this->sessionInfo['address_line1']?>
		<?=!empty($this->sessionInfo['address_line2']) ? $this->sessionInfo['address_line2'] . "<br />" : ""?>
		<?=$this->sessionInfo['city']?> <?=$this->sessionInfo['state_id']?> <?=$this->sessionInfo['postal_code']?>
Phone
       (Day): <?=$this->sessionInfo['telephone_day'] . "\n";?>
   (Evening): <?=$this->sessionInfo['telephone_evening'] . "\n";?>
		Fax: <?=$this->sessionInfo['fax'] . "\n";?>
<?php } ?>


---------------------------------------------------------------
Allergens: In compliance with the "Food Allergen Labeling and Consumer Protection Act of 2004" please note that Dream Dinners' facilities may contain Dairy, Eggs, Crustacean Shellfish, Fish, Tree Nuts, Peanuts, Wheat, Soybeans and Sesame which account for most known allergens. Although Dream Dinners' store staff take appropriate safety measures, guests should be aware that cross contamination can occur among food products in store kitchens and at stations. The standard ingredients are available upon request from your local store; however, ingredient substitutions can be made at the store level due to regional availability. If guests feel that there may be a chance of allergens in any recipe, especially due to pre-made ingredients, they need to call the store to ask for specific nutritional information.