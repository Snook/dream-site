<div class="row">
	<div class="col">
		<?php if ($this->sessionInfo['session_type'] == CSession::DELIVERED) { ?>
			<?php foreach ($this->orderInfo['boxes']['current_boxes'] as $box) { ?>
				<?php include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_box_item.tpl.php'); ?>
			<?php } ?>
		<?php } else { ?>
			<?php
			$counter = 0;
			$lastItem = count($this->menuInfo['itemList']);
			foreach ($this->menuInfo['itemList'] as $id => $itemInfo)
			{
				$counter++;

				if ($this->orderInfo['type_of_order'] == COrders::INTRO || $this->orderInfo['type_of_order'] == COrders::DREAM_TASTE || $this->sessionInfo['session_type'] == COrders::FUNDRAISER)
				{
					include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_bundle.tpl.php');
				}
				else
				{
					include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_menu_item.tpl.php');
				}
			}
			?>
		<?php } ?>
	</div>
</div>

<?php
if (isset($this->menuInfo['promo_item']))
{
	$itemInfo = $this->menuInfo['promo_item'];

	include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_promo.tpl.php');
}
?>

<?php
if (isset($this->menuInfo['free_meal_item']))
{
	$itemInfo = $this->menuInfo['free_meal_item'];

	include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_free_meal.tpl.php');
}
?>

<div class="row">
	<div class="col-lg-9 mt-4 mt-lg-0 text-right order-lg-2">
		<?php if (!$this->isEmptyFloat($this->orderInfo['misc_food_subtotal'])) { ?>
			<div class="row">
				<div class="col-9">Misc Food (<?php echo $this->orderInfo['misc_food_subtotal_desc']; ?>)</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['misc_food_subtotal']); ?></div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal'])) { ?>
			<div class="row">
				<div class="col-9">Misc Non-Food (<?php echo $this->orderInfo['misc_nonfood_subtotal_desc']; ?>)</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['misc_nonfood_subtotal']); ?></div>
			</div>
		<?php } ?>
		<div class="row">
			<div class="col-9"><?php echo ($this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? "Subtotal" : "Discounted Subtotal"); ?></div>
			<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_menu_items'] + $this->orderInfo['misc_food_subtotal'] + $this->orderInfo['subtotal_products']  - $this->orderInfo['subtotal_menu_item_mark_down'] + $this->orderInfo['subtotal_home_store_markup'] - ($this->isEmptyFloat($this->orderInfo['bundle_discount']) ? 0 : $this->orderInfo['bundle_discount']) - ($this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? 0 : $this->orderInfo['family_savings_discount'])); ?></div>
		</div>
		<?php if (!$this->isEmptyFloat($this->orderInfo['volume_discount_total'])) { ?>
			<div class="row">
				<div class="col-9">Volume Reward</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['volume_discount_total']); ?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['promo_code_discount_total'])) { ?>
			<div class="row">
				<div class="col-9">Promotion Discount</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['promo_code_discount_total']); ?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['coupon_code_discount_total'])) { ?>
			<div class="row">
				<div class="col-9">Promo (<?php echo ((!empty($this->coupon_title)) ? $this->coupon_title : $this->orderInfo['coupon_title']);?>)</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['coupon_code_discount_total']); ?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['direct_order_discount'])) { ?>
			<div class="row">
				<div class="col-9">Direct Order Discount</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['direct_order_discount']);?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['membership_discount'])) { ?>
			<div class="row">
				<div class="col-9">Meal Prep+ Discount</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['membership_discount']);?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['points_discount_total'])) { ?>
			<div class="row">
				<div class="col-9">Dinner Dollars</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['points_discount_total']);?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['session_discount_total'])) { ?>
			<div class="row">
				<div class="col-9">Session Discount</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['session_discount_total']);?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['user_preferred_discount_total'])) { ?>
			<div class="row">
				<div class="col-9">Preferred Discount</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['user_preferred_discount_total']);?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['dream_rewards_discount'])) { ?>
			<div class="row">
				<div class="col-9">Dream Rewards Savings</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['dream_rewards_discount']);?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['family_savings_discount'])) { ?>
			<div class="row">
				<div class="col-9">Total Family Savings</div>
				<div class="col-3">(<?php echo $this->moneyFormat($this->orderInfo['family_savings_discount']);?>)</div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_delivery_fee'])) { ?>
			<div class="row">
				<div class="col-9">Delivery Fee</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_delivery_fee']);?></div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_service_fee']) || $this->orderInfo['service_fee_description'] == "Free Assembly Promo") { ?>
			<div class="row">
				<div class="col-9">Service Fees</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_service_fee']);?></div>
			</div>
		<?php } ?>
		<?php if ($this->orderInfo['opted_to_customize_recipes'] == 1) { ?>
			<div class="row">
				<div class="col-9">Customization Fee</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_meal_customization_fee']);?></div>
			</div>
			<div class="row">
				<div class="col-9 mt-1 mb-1 font-size-small"><?php echo $this->meal_customization_string;?></div>
				<div class="col-3"></div>
			</div>
		<?php } ?>
        <?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_bag_fee'])) { ?>
            <div class="row">
                <div class="col-9">Bag Fees</div>
                <div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_bag_fee']);?></div>
            </div>
        <?php }else{ ?>
			<?php if ($this->orderInfo['opted_to_bring_bags']) { ?>
				<div class="row">
					<div class="col-9">Bag Fees</div>
					<div class="col-3">I will bring my own</div>
				</div>
			<?php } ?>
		<?php } ?>
		<div class="row">
			<div class="col-9">Food Tax</div>
			<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_food_sales_taxes']);?></div>
		</div>
		<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_sales_taxes'])) { ?>
			<div class="row">
				<div class="col-9">Non-Food Tax</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_sales_taxes']);?></div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_service_tax'])) { ?>
			<div class="row">
				<div class="col-9">Service Tax</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_service_tax']);?></div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_delivery_tax'])) { ?>
			<div class="row">
				<div class="col-9">Delivery Fee Tax</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_delivery_tax']);?></div>
			</div>
		<?php } ?>
		<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_bag_fee_tax'])) { ?>
			<div class="row">
				<div class="col-9">Bag Fee Tax</div>
				<div class="col-3"><?php echo $this->moneyFormat($this->orderInfo['subtotal_bag_fee_tax']);?></div>
			</div>
		<?php } ?>
		<div class="row border-top border-green-dark">
			<div class="col-9 font-weight-bold text-uppercase">Menu order total</div>
			<div class="col-3 font-weight-bold">$<?php echo $this->orderInfo['grand_total']; ?></div>
		</div>
		<?php if($this->menuInfo['servings_total_count_display'] > 0){?>
			<div class="row">
				<div class="col-9">Average Cost per Serving (<?php echo $this->menuInfo['servings_total_count_display']; ?> servings)</div>
				<div class="col-3"><?php echo $this->menuInfo['cost_per_serving']; ?></div>
			</div>
		<?php }?>
	</div>
	<div class="col-lg-3 mt-4 mt-lg-0 order-lg-1">

		<div class="d-print-none">
			<?php if ($this->sessionInfo['session_type'] != CSession::DELIVERED) { ?>
				<a href="/?page=print&amp;order=<?php echo $this->orderInfo['id']; ?>&amp;freezer=true" class="btn btn-lg btn-primary m-1 d-block" target="_blank">Freezer Sheet</a>
			<?php } ?>
			<a href="/?page=print&amp;order=<?php echo $this->orderInfo['id']; ?>&amp;nutrition=true" class="btn btn-lg btn-primary m-1 d-block" target="_blank">Nutritionals</a>
			<?php if($this->store_allows_meal_customization) { ?>
				<a href="/?page=account&amp;view=recipe_customization_row" class="btn btn-lg btn-cyan m-1 d-block" target="_blank">Meal Customizations</a>
			<?php } ?>
		</div>

	</div>
</div>