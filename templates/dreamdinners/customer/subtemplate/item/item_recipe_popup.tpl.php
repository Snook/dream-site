<div class="container-fluid">
	<div class="row mb-4">
		<div class="col-lg-6 col-md-12 p-0">
			<img class="img-fluid" alt="<?php echo htmlspecialchars($this->menu_item->menu_item_name); ?>" src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($this->menu_item->menu_image_override)) ? 'default' : $this->menu_item->menu_image_override; ?>/<?php echo $this->menu_item->recipe_id; ?>.webp" />
		</div>
		<div class="col-lg-6 col-md-12 pl-lg-5">
			<div class="row">
				<div class="col pt-3 pt-lg-0">
					<h3>
						<a href="/?page=item&recipe=<?php echo $this->menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->menu_item->menu_id; ?>"><?php echo $this->menu_item->menu_item_name; ?></a>
					</h3>
					<p>
						<?php if (!empty($this->menu_item->menu_label)) { ?>
							<span class="font-weight-bold"><?php echo $this->menu_item->menu_label; ?></span>
						<?php } ?>
						<?php echo trim($this->menu_item->menu_item_description); ?>
					</p>
					<div class="row mb-3">
						<div class="col col-md-5 mb-2 text-center text-md-left">
							<?php foreach ($this->icons AS $icon) { ?>
								<?php if ($icon['meal_detail_enabled'] && !empty($icon['show'])) { ?>
									<i class="font-size-medium-large dd-icon <?php echo $icon['css_icon']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $icon['tooltip']; ?>"></i>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if (empty($this->menu_item->is_bundle)) { ?>
		<?php include $this->loadTemplate('customer/subtemplate/item/item_recipe_popup_nutritional_summary.tpl.php'); ?>
	<?php } ?>

</div>