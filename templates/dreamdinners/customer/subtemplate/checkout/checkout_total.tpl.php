<div class="row pt-3 pb-2 mb-3 bg-gray-light">
	<div class="col-md-12">
		<h2 class="text-uppercase font-weight-semi-bold font-size-medium text-left">Total</h2>

		<!-- Totals -->
		<?php if ($this->foodState != 'noFood') { if ($this->foodState == 'adequateFood') { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<?php if($this->cart_info['order_info']['servings_total_count'] > 0){?>
					<p>
						<?php echo $this->cart_info['cart_info_array']['dinners_total_count']; ?> Dinners <?php if (!empty($this->cart_info['cart_info_array']['num_sides']))  { ?>
							 + <?php echo $this->cart_info['cart_info_array']['num_sides']; ?> Sides and Sweets
						<?php } ?>
						<span class="font-italic">(<?php echo $this->cart_info['order_info']['servings_total_count']; ?> Servings /
						Avg. $<span id="checkout-cost_per_serving"><?php echo CTemplate::moneyFormat($this->cart_info["orderObj"]->getAvgCostPerServing()); ?></span> per serving)</span>
					</p>
					<?php }else{?>
						<?php if($this->cart_info['cart_info_array']['num_sides'] > 0){?>
							<?php echo $this->cart_info['cart_info_array']['num_sides']; ?> Sides and Sweets
						<?php }?>
					<?php }?>

				</div>
				<div class="col-md-6 col-4 text-right">
					<p>$<span id="checkout_total-food"><?php echo CTemplate::moneyFormat($this->cart_info["orderObj"]->getFoodTotal()); ?></span></p>
				</div>
			</div>
		<?php } else if ($this->foodState != 'adequateFood' ) { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<p>
						Servings (<?php echo $this->cart_info['order_info']['servings_total_count']; ?>)
					</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p><s>$<span id="checkout_total-food">0.00</span></s></p>
				</div>
			</div>
		<?php } } ?>


		<?php if( $this->allow_assembly_fee) { ?>
			<?php if( !$this->isEmptyFloat( $this->cart_info['order_info']['subtotal_service_fee'] ) && $this->foodState == 'adequateFood' ) { ?>
				<div class="row">
					<div class="col-md-6 col-8 text-left">
						<p>Assembly Fee</p>
					</div>
					<div class="col-md-6 col-4 text-right">
						<p>$<span id="checkout_total-service_fee"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['subtotal_service_fee']);?></span></p>
					</div>
				</div>
			<?php } else if ($this->cart_info['cart_info_array']['navigation_type'] == CTemplate::INTRO && $this->cart_info['session_info']['session_type'] == CSession::MADE_FOR_YOU) { ?>
				<div class="row">
					<div class="col-md-6 col-8 text-left">
						<p>Assembly Fee <span  class="font-italic">(a $<?php echo CTemplate::moneyFormat($this->defaultAssemblyFee); ?> value)</span></p>
					</div>
					<div class="col-md-6 col-4 text-right">
						<p>$0.00</p>
					</div>
				</div>
			<?php } ?>
		<?php } else { ?>
			<?php if( !$this->isEmptyFloat( $this->cart_info['order_info']['subtotal_service_fee'] ) && $this->foodState == 'adequateFood' ) { ?>
				<div class="row">
					<div class="col-md-6 col-8 text-left">
						<p>Service Fee</p>
					</div>
					<div class="col-md-6 col-4 text-right">
						<p>$<span id="checkout_total-service_fee"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['subtotal_service_fee']);?></span></p>
					</div>
				</div>
			<?php } ?>
		<?php } ?>

		<div class="row" id="customization-fee-row" style="<?php echo (($this->has_meal_customization_selected == true ) ? '': 'display:none;'  )?>">
			<div class="col-md-6 col-8 text-left">
				<p>Customization Fee</p>
			</div>
			<div class="col-md-6 col-4 text-right">
				<p>$<span id="checkout_total-customization_fee"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['subtotal_meal_customization_fee']);?></span></p>
			</div>
		</div>

		<?php if(!empty($this->cart_info['order_info']['subtotal_delivery_fee']) && $this->foodState == 'adequateFood' ) { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<p>Delivery Fee</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p>$<span id="checkout_total-delivery_fee"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['subtotal_delivery_fee']);?></span></p>
                </div>
            </div>
        <?php } ?>

		<?php if( !$this->isEmptyFloat( $this->cart_info['order_info']['volume_discount_total'] ) && $this->foodState == 'adequateFood' ) { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<p>Volume Discount</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p>-$<span id="checkout_total-volume_discount"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['volume_discount_total']);?></span></p>
				</div>
			</div>
		<?php } ?>

		<?php if( !$this->isEmptyFloat( $this->cart_info['order_info']['user_preferred_discount_total'] )  && $this->foodState == 'adequateFood') { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<p>Preferred Discount</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p>-$<span id="checkout_total-preferred_discount"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['user_preferred_discount_total']);?></span></p>
				</div>
			</div>
		<?php } ?>

		<?php if( !$this->isEmptyFloat( $this->cart_info['order_info']['membership_discount'] )  && $this->foodState == 'adequateFood') { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<p>Meal Prep+ Discount</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p>-$<span id="checkout_total-membership_discount"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['membership_discount']);?></span></p>
				</div>
			</div>
		<?php } ?>


		<?php if( !$this->isEmptyFloat( $this->cart_info['order_info']['dream_rewards_discount'] ) && $this->foodState == 'adequateFood') { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<p>Dream Rewards Discount</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p>-$<span id="checkout_total-dream_rewards_discount"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['dream_rewards_discount']);?></span></p>
				</div>
			</div>
		<?php } ?>

		<?php if( !$this->isEmptyFloat( $this->cart_info['order_info']['session_discount_total'] ) && $this->foodState == 'adequateFood') { ?>
			<div class="row">
				<div class="col-md-6 col-8 text-left">
					<p>Session Discount</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p>-$<span id="checkout_total-session_discount_total"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['session_discount_total']);?></span></p>
				</div>
			</div>
		<?php } ?>

		<?php if( isset($this->has_PP_widget) && $this->has_PP_widget) { ?>
			<div id="row-discount_plate_points" class="row <?php echo ($this->isEmptyFloat($this->cart_info['order_info']['points_discount_total']) ? 'collapse' : '')?>">
				<div class="col-md-6 col-8 text-left">
					<p><?php if (!$this->isEditDeliveredOrder){ ?><i id="remove-dinner_dollars" class="fas fa-trash-alt mr-2"><?php } ?></i>Dinner Dollars Discount</p>
				</div>
				<div class="col-md-6 col-4 text-right">
					<p>-$<span id="checkout_total-points_discount_total"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['points_discount_total']);?></span></p>
				</div>
			</div>
		<?php } ?>

		<div id="row-coupon" class="row <?php echo ((!empty($this->cart_info['coupon']) && $this->foodState == 'adequateFood') ? '' : 'collapse') ?>">
			<div class="col-md-6 col-8 text-left">
				<p>
				<?php if($this->hide_remove_gift_card){?><?php }else{?>
					<i id="remove-coupon" class="fas fa-trash-alt mr-2"></i><?php } ?><span id="checkout_title-coupon">Coupon (<span style="font-size:8pt;" id="checkout_title-coupon_code"><?php echo(!empty($this->cart_info['coupon']) ? $this->cart_info['coupon']['coupon_code_short_title'] : "") ?></span>)</span>
				</p>

			</div>
			<div class="col-md-6 col-4 text-right">
				<p>-$<span id="checkout_total-coupon"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['coupon_code_discount_total']);?></span></p>
			</div>
		</div>

		<?php if ($this->supports_bag_fee) { ?>
		<div id="row-bag_fee" class="row">
			<div class="col-md-10 col-8 text-left">
				<p>Reusable Bag Fee <span id="checkout_total-bag_fee_info"><?php echo $this->bagFeeItemizationNote;?></span></p>
			</div>
			<div class="col-md-2 col-4 text-right">
				<p>$<span id="checkout_total-bag_fee"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['subtotal_bag_fee']); ?></span></p>
			</div>
		</div>
		<?php  } ?>

		<div class="row">
			<div class="col-md-6 col-8 text-left">
				<p>Tax</p>
			</div>
			<div class="col-md-6 col-4 text-right">
				<p>$<span id="checkout_total-tax"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['subtotal_all_taxes']); ?></span></p>
			</div>
		</div>

		<div class="row">
			<div class="col mt-2 text-right">
				<span class="pt-2 border-top border-green border-width-1-imp">Subtotal <span id="sum_checkout_total-subtotal"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['grand_total']);?></span></span>
			</div>
		</div>

		<div class="row collapse" id="ltd_round_up_div">
			<div class="col mt-2 text-right">
				<span class="pt-2">Round Up Donation <span id="checkout_total-roundup_donation">0.00</span></span>
			</div>
		</div>

		<!-- Payments -->
		<?php if (CUser::isLoggedIn()) { ?>
			<h2 class="text-uppercase font-weight-semi-bold font-size-medium text-left">Payment</h2>

			<div id="row-credits" class="row">
				<?php if (isset($this->countSelectedCredits) && $this->countSelectedCredits > 0 && !$this->isGiftCardOnlyOrder)  { ?>
					<div class="col-md-6 col-8 text-left">
						<p>Store Credits</p>
					</div>
					<div class="col-md-6 col-4 text-right">
						<p>-$<span id="checkout_total_payment-credits"><?php echo CTemplate::moneyFormat($this->total_store_credit)?></span></p>
					</div>
				<?php  } ?>
			</div>

			<div id="giftcard_container">

				<?php
				if (!empty($this->cart_info['payment_info'] ) ) {
					foreach ($this->cart_info['payment_info'] as $id => $paymentInfo) {
						if ($paymentInfo['payment_type'] == 'gift_card' && empty($paymentInfo['paymentData']['last_result'])) {
							$this->assign('paymentID', $id);
							$this->assign('paymentInfo', $paymentInfo);
							include $this->loadTemplate('customer/subtemplate/checkout/checkout_total_gift_card_payment_row.tpl.php');
						} } } ?>
			</div>

			<div id="creditcard_container" class="row">

				<?php
				if (false) { // credit card is not added to cart currently
					//if (!empty($this->cart_info['payment_info'] )) {
					foreach ($this->cart_info['payment_info'] as $id => $paymentInfo) {
						if ($paymentInfo['payment_type'] == 'credit_card') {
							$this->assign('paymentID', $id);
							$this->assign('paymentInfo', $paymentInfo);
							include $this->loadTemplate('customer/subtemplate/checkout/checkout_total_credit_card_payment_row.tpl.php');
						} } } ?>
			</div>

			<div class="row">
				<div class="col-md-7 col-7 text-left">
					<?php if($this->isEditDeliveredOrder && $this->delta_has_new_total){ ?>
					<p>New Total</p>
					<?php }else if($this->isEditDeliveredOrder && !$this->delta_has_new_total){ ?>
					<p>Total</p>
					<?php }else{ ?>
					<p>Balance Due</p>
					<?php }?>
				</div>
				<div class="col-md-5 col-5 text-right">
					<?php if($this->isEditDeliveredOrder){ ?>
						<input type="hidden" id="isEditDeliveredOrder" value="true"/>
						<input type="hidden" id="original_order_cost" value="<?php echo $this->delta_original_order_cost; ?>"/>
						<p class="">
					<?php } else { ?>
						<p class="font-weight-semi-bold">
					<?php } ?>
						$<span id="credit_card_amount">0.00</span></p>
				</div>
			</div>
			<?php include $this->loadTemplate('customer/subtemplate/checkout/checkout_total_edit_order_row.tpl.php');?>

		<?php } ?>

		<?php if ($this->cart_info['session_info']['session_type_subtype'] == CSession::DELIVERY) { ?>
			<div class="col">
				<span class="text-muted font-size-small">*Delivery fee applies to orders delivered within 20 miles of the store. Orders may be canceled for any distance beyond 20 miles. If the store can accommodate an additional fee may be added to your order before delivery. <?php if (!empty($this->StoreCurrent->telephone_day) ){ ?>Contact the store at <?php echo $this->StoreCurrent->telephone_day ?> for additional information.<?php } ?></span>
			</div>
		<?php } ?>

		<?php  if (isset($this->cart_info['order_info']['bundle_discount']) && $this->cart_info['order_info']['bundle_discount'] > 0) { ?>
			<div class="row pt2">
				<div class="col-12">
					<p>You saved $<?php  echo CTemplate::moneyFormat($this->cart_info['order_info']['bundle_discount']); ?></p>
				</div>
			</div>
		<?php }  ?>

	</div>
</div>