<?php if (!empty($this->menuInfo['bundle_items'] )) { ?>
	<?php foreach ($this->menuInfo['bundle_items'] as $biid => $itemInfo) { ?>
		<div class="row bg-gray-light mb-2 border">
			<div class="col-4 col-md-2 p-0">
				<img src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($this->menuInfo['menu_image_override'])) ? 'default' : $this->menuInfo['menu_image_override']; ?>/<?php echo $itemInfo['recipe_id']; ?>.webp" alt="<?php echo $itemInfo['display_title']; ?>" class="img-fluid">
			</div>
			<div class="col-8 col-md-10 py-2">
				<div class="row mb-2 mb-md-4">
					<div class="col text-uppercase font-weight-bold">
						<a href="/main.php?page=item&amp;recipe=<?php echo $itemInfo['recipe_id']; ?>"><?php echo $itemInfo['display_title']; ?></a>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<?php echo $itemInfo['qty']; ?> <?php echo CMenuItem::translatePricingType($itemInfo['pricing_type'] ); ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
<?php } ?>
