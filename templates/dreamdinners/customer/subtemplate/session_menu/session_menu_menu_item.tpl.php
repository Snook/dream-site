<div class="col-lg-6 col-print-4 p-0">
	<div class="card m-2">
		<img class="card-img-top" data-recipe_img="<?php echo $mainItem->recipe_id; ?>" src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo $mainItem->menuItemImagePath(); ?>/<?php echo $mainItem->recipe_id; ?>.webp" alt="<?php echo $mainItem->menu_item_name; ?>">
		<div class="card-body pb-0">
			<h4 class="card-title font-size-small"><?php echo $mainItem->menu_item_name; ?><?php if (DEBUG) {echo " (" .$mainItem->remaining_servings . ")"; } ?></h4>
			<div class="row">
				<div class="col font-size-small">
					<?php echo (!empty($mainItem->menu_label)) ? '<span class="font-weight-bold">' . $mainItem->menu_label . '</span>' : ''; ?> <?php echo $mainItem->menu_item_description; ?>
				</div>
			</div>
			<?php if (!empty($mainItem->DAO_food_survey->rating) || !empty($mainItem->DAO_food_survey_comments->comment)) { ?>
				<div class="row mt-2 bg-gray mx-1 py-2">
					<div class="col font-size-small">
						<span class="font-weight-bold">Personal note</span>
						<?php if (!empty($mainItem->DAO_food_survey->rating)) { ?>
							<ul class="list-inline d-inline-flex mb-1">
								<li class="list-inline-item m-0">
									<i class="<?php echo ($mainItem->DAO_food_survey->rating >= 1) ? 'fas text-yellow' : 'far'; ?> fa-star" alt="1 Star"></i>
								</li>
								<li class="list-inline-item m-0">
									<i class="<?php echo ($mainItem->DAO_food_survey->rating >= 2) ? 'fas text-yellow' : 'far'; ?> fa-star" alt="2 Star"></i>
								</li>
								<li class="list-inline-item m-0">
									<i class="<?php echo ($mainItem->DAO_food_survey->rating >= 3) ? 'fas text-yellow' : 'far'; ?> fa-star" alt="3 Star"></i>
								</li>
								<li class="list-inline-item m-0">
									<i class="<?php echo ($mainItem->DAO_food_survey->rating >= 4) ? 'fas text-yellow' : 'far'; ?> fa-star" alt="4 Star"></i>
								</li>
								<li class="list-inline-item m-0">
									<i class="<?php echo ($mainItem->DAO_food_survey->rating >= 5) ? 'fas text-yellow' : 'far'; ?> fa-star" alt="5 Star"></i>
								</li>
							</ul>
						<?php } ?>
						<?php if (!empty($mainItem->DAO_food_survey_comments->personal_note)) { ?>
							<div><?php echo nl2br($mainItem->DAO_food_survey_comments->personal_note); ?></div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>
		<div class="card-footer border-0 p-0">
			<div class="row bg-white mx-0 pt-2">
				<?php if (!$mainItem->is_bundle && !$mainItem->is_shiftsetgo) { ?>
					<div class="col-6">
						<a class="card-link text-uppercase d-print-none link-dinner-details" href="/item?recipe=<?php echo $mainItem->recipe_id; ?>" data-recipe_id="<?php echo $mainItem->recipe_id; ?>" data-store_id="<?php echo $this->cart_info['storeObj']->id; ?>" data-menu_item_id="<?php echo $mainItem->id; ?>" data-menu_id="<?php echo $mainItem->menu_id; ?>" data-size="large" data-detailed="false">Dinner details</a>
					</div>
				<?php } ?>
				<div class="col-6 text-right">
					<?php foreach ($mainItem->icons AS $icon) { ?>
						<?php if ($icon['meal_detail_enabled'] && $icon['show']) { ?>
							<i class="font-size-medium-large dd-icon <?php echo $icon['css_icon']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $icon['tooltip']; ?>"></i>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
			<div class="row bg-gray-light m-0">
				<?php if ($this->cart_info['cart_info_array']['navigation_type'] == CBundle::DELIVERED) { ?>
					<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_item_add_qty.tpl.php'); ?>
				<?php } else { ?>
					<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item_add_qty.tpl.php'); ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>