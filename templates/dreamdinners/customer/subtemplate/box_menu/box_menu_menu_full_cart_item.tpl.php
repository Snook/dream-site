<?php $mainItem = $item[key($item)]; ?>
<div class="row mr-0 py-4" data-cart_menu_item="<?php echo $mainItem['menu_item_id']; ?>">
	<div class="col">
		<div class="font-weight-bold font-size-small text-uppercase"><?php echo $mainItem['display_title']; ?></div>
		<?php if ($mainItem['servings_per_item'] == 6) { ?>
			<div class="font-size-small text-gray-dark">Large serves 4-6</div>
		<?php if ($mainItem['servings_per_item'] == 4) { ?>
			<div class="font-size-small text-gray-dark">Medium serves 4-6</div>
		<?php if ($mainItem['servings_per_item'] == 3) { ?>
			<div class="font-size-small text-gray-dark">Medium serves 2-3</div>
		<?php if ($mainItem['servings_per_item'] == 2) { ?>
			<div class="font-size-small text-gray-dark">Small serves 1-2</div>
		<?php } else { ?>
			<div class="font-size-small text-gray-dark">Medium Serves 2-3</div>
		<?php } ?>
	</div>
</div>
