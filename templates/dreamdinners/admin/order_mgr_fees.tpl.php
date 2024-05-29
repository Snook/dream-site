<div class="tab-content ">
	<div class="row mt-2">
		<div class="col-8">
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">Service Fee</div>
				</div>
				<?php echo $this->form_direct_order['service_fee_description_html'] ?>
			</div>
		</div>
		<div class="col-4">
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">$</div>
				</div>
				<?php echo $this->form_direct_order['subtotal_service_fee_html'] ?>
			</div>
		</div>
	</div>
	<?php if (isset($this->show_misc_food_field) && $this->show_misc_food_field) { ?>
		<div class="row mt-2">
			<div class="col-8">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">Misc Food</div>
					</div>
					<?php echo $this->form_direct_order['misc_food_subtotal_desc_html'] ?>
				</div>
			</div>
			<div class="col-4">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?php echo $this->form_direct_order['misc_food_subtotal_html'] ?>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if (isset($this->show_misc_non_food_field) && $this->show_misc_non_food_field) { ?>
		<div class="row mt-2">
			<div class="col-8">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">Misc Non-Food</div>
					</div>
					<?php echo $this->form_direct_order['misc_nonfood_subtotal_desc_html'] ?>
				</div>
			</div>
			<div class="col-4">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?php echo $this->form_direct_order['misc_nonfood_subtotal_html'] ?>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($this->orderOrStoreSupportDelivery) { ?>
		<div class="row mt-2">
			<div class="offset-8 col-4">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">Delivery Fee $</div>
					</div>
					<?php echo $this->form_direct_order['subtotal_delivery_fee_html'] ?>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($this->orderOrStoreSupportDeliveryTip) { ?>
		<div class="row mt-2">
			<div class="offset-8 col-4">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">Driver Tip $</div>
					</div>
					<?php echo $this->form_direct_order['delivery_tip_html'] ?>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($this->storeInfo['supports_bag_fee']) { ?>
	<div class="row mt-2">
		<div class="col-5">
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">Bag Quantity</div>
				</div>
				<?php echo $this->form_direct_order['total_bag_count_html'] ?>
			</div>
		</div>
		<div class="col-7 bg-">
			<div class="custom-control custom-checkbox ">
				<?php echo $this->form_direct_order['opted_to_bring_bags_html'] ?>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php if ($this->storeInfo['supports_meal_customization']) { ?>
		<div class="row mt-2">
			<div class="col-8">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">Meal Customization Fee $</div>
					</div>
					<?php echo $this->form_direct_order['subtotal_meal_customization_fee_html'] ?>
				</div>
			</div>
			<div class="col-4">
				<div class="input-group">
					<?php echo $this->form_direct_order['opted_to_customize_recipes_html'] ?>
				</div>
			</div>
		</div>
	<?php } ?>


	<?php if($this->should_allow_meal_customization) { ?>
	<input id="manual_customization_fee" name="manual_customization_fee" type="hidden" value="<?php echo ($this->dont_recalculate_customization_cost == "true"?"true":"false");?>"/>

	<div class="row mb-2" id="customization-row-container">
		<div class="col">
			<h2 class="font-weight-bold font-size-medium-small text-uppercase text-left mt-3"><a href="#" id="customization-header">Meal Customizations</a></h2>
			<p class="font-size-small text-left ml-4" style="margin-bottom: 0px;">* The following options are automatically saved when changed</p>

			<div id="customization-row" class="col-8">
				<?php foreach($this->meal_customization_preferences as $key => $pref) { ?>
				<div class="ml-4">
					<?php switch ($pref->type ) {
						case 'INPUT': ?>
							<div class="">
								<br>
								<label class="control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description; ?>:</span></label>
								<input id="<?php echo $key ?>" type="text" data-dd_type="customization_fee_input" data-name="<?php echo $pref->description; ?>"  data-org_value="<?php if (!empty($pref->value)) { echo htmlentities($pref->value); } ?>" style="width: 300px;display: inline;" data-user_pref="<?php echo $key ?>" size="20" maxlength="15" class="form-control" value="<?php if (!empty($pref->value)) { echo htmlentities($pref->value); } ?>"></input>
							</div>
						<?php break; case 'CHECKBOX': ?>
							<div class="custom-control custom-switch">
								<input class="custom-control-input"  data-dd_type="customization_fee_toggle" data-name="<?php echo $pref->description; ?>" data-org_value="<?php echo $pref->value == 'OPTED_IN' ? 'Opted in' : 'Opted out';?>" id="<?php echo $key ?>" data-user_pref="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
								<label class="custom-control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description; ?></span></label> <span class="font-size-small"><?php if(!empty($pref->information)){ ?><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?>"></span><?php } ?>
							</div>
						<?php break; case 'SPECIAL_REQUEST': ?>
							<div class="custom-control custom-switch">
								<input class="custom-control-input"  data-dd_type="customization_fee_toggle" data-name="<?php echo $pref->description; ?>" data-org_value="<?php echo $pref->value == 'OPTED_IN' ? 'Opted in' : 'Opted out';?>" id="<?php echo $key ?>" data-user_pref="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
								<label class="custom-control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description; ?></span></label> <span class="font-size-small"><?php if(!empty($pref->information)){ ?><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?> selection that applies to this order."></span><?php } ?>
								<textarea maxlength="<?php echo OrdersCustomization::SPECIAL_REQUEST_MAX_CHARS; ?>" data-dd_type="customization_fee_detail" id="<?php echo OrdersCustomization::determineDetailsKey($key) ?>" data-name="<?php echo $pref->description; ?>" rows="1" cols="20" class="form-control" data-user_pref="<?php echo OrdersCustomization::determineDetailsKey($key) ?>" data-org_value="<?php echo htmlentities($pref->details); ?>"><?php echo htmlentities($pref->details); ?></textarea>
									* Special Request notes are visible to guests. 90 Character limit.
							</div>

						<?php break; } ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<br>
		<?php } ?>
</div>