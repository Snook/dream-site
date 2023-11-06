<div class="row bg-gray-light mb-2 border">
	<div class="d-none d-md-block col-md-2 p-0">
		<img src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($this->menuInfo['menu_image_override'])) ? 'default' : $this->menuInfo['menu_image_override']; ?>/<?php echo $itemInfo['recipe_id']; ?>.webp" alt="<?php echo $itemInfo['display_title']; ?>" class="img-fluid">
	</div>
	<div class="col-md-10 py-2">
		<div class="row">
			<div class="col text-uppercase font-weight-bold">
				<a href="/item?recipe=<?php echo $itemInfo['recipe_id']; ?>"><?php echo $itemInfo['display_title']; ?></a>
			</div>
		</div>
		<div class="row mb-2 mb-md-4">
			<div class="col">
				<?php if ($itemInfo['is_preassembled']) { ?><span class="font-weight-light font-size-small">&nbsp;&nbsp;&nbsp;- Pre-Assembled</span> <br><?php } ?>
				<?php if ($this->order_has_meal_customization) { ?>
					<?php
					if ($itemInfo['is_freezer_menu'] == true || $itemInfo['is_chef_touched'] == true  || ($itemInfo['is_preassembled'] == true  && !$this->store_allows_preassembled_customization)) { ?>
						<span class="font-weight-light font-size-small text-danger""><span>&nbsp;&nbsp;&nbsp;- Not Customizable</span>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<div class="row">
			<div class="col-4 col-md-2 pr-0">
				<?php echo $itemInfo['qty']; ?> <?php echo ($itemInfo['is_chef_touched'] ?  "" : CMenuItem::translatePricingType($itemInfo['pricing_type'] )); ?>
			</div>
			<div class="col-5 col-md-2 pr-0">
				<?php echo CTemplate::moneyFormat(isset($itemInfo['discounted_price']) ?  $itemInfo['discounted_price'] :  $itemInfo['price']); ?> ea.
			</div>
			<div class="col-3 col-md-8 text-right">
				$<?php echo $itemInfo['subtotal']; ?>
			</div>
		</div>
	</div>
</div>