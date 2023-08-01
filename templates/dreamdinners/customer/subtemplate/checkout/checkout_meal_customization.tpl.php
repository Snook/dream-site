<?php if ($this->should_allow_meal_customization) { ?>
	<div class="row mb-2">
		<div class="col">
			<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left">Meal Customization*</h2>
			<div id="customization-row">
				<p class="font-size-small">
					<?php echo OrdersCustomization::RECIPE_DESCRIPTION;?>
					<?php if(!$this->allow_preassembled_customization) { ?>
				<div class="font-size-small text-danger"><?php echo OrdersCustomization::RECIPE_NO_PRE_ASSEMBLED; ?></div>
				<?php } ?>
				</p>
				<div class="row py-2 meal-customization-row">
					<div class="col-12 text-left">
						<div class="custom-control custom-checkbox">
							<input class="custom-control-input" type="checkbox" name="apply-customization" id="apply-customization" <?php echo ($this->has_meal_customization_selected ? 'checked' : ''); ?>>
							<label class="custom-control-label" for="apply-customization">Customize this order</label>
						</div>
					</div>
				</div>
				<?php foreach($this->meal_customization_preferences as $key => $pref) { ?>
					<div class="ml-4">
						<?php switch ($pref->type ) { case 'INPUT': ?>
							<div>
								<label class="control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description ?>:</span></label>
								<input class="form-control" id="<?php echo $key ?>" type="text" data-user_pref="<?php echo $key ?>" data-user_pref_meal="<?php echo $key ?>" size="20" maxlength="15" value="<?php if (!empty($pref->value)) { echo htmlentities($pref->value); } ?>">
							</div>
							<?php break; case 'CHECKBOX': ?>
							<div class="custom-control custom-switch">
								<input class="custom-control-input"  id="<?php echo $key ?>" data-user_pref="<?php echo $key ?>" data-user_pref_meal="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
								<label class="custom-control-label" for="<?php echo $key ?>"><?php echo $pref->description; ?></label>
								<?php if (!empty($pref->information)) { ?>
									<span class="font-size-small"><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?>"></span>
								<?php } ?>
							</div>
							<?php break; case 'SPECIAL_REQUEST': ?>
								<?php if (!empty($pref->details)) { ?>
									<div class="custom-control custom-switch">
										<input class="custom-control-input"  id="<?php echo $key ?>" data-user_pref="<?php echo $key ?>" data-user_pref_meal="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
										<label class="custom-control-label" for="<?php echo $key ?>"><?php echo $pref->description; ?></label>
										<?php if (!empty($pref->information)) { ?>
											<span class="font-size-small"><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?>"></span>
										<?php } ?>
										<br><span class="font-italic font-size-small customization-readonly-option text-gray-400">( <?php echo htmlentities($pref->details);?> )</span>
									</div>
								<?php } ?>
							<?php break; } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>