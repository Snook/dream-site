<?php $mainItem = $item[key($item)]; ?>
<div class="row mr-0 py-4" data-cart_menu_item="<?php echo $mainItem['menu_item_id']; ?>">
	<div class="col">
		<div class="font-weight-bold font-size-small text-uppercase"><?php echo $mainItem['display_title']; ?></div>
		<?php if (!empty($mainItem['servings_per_container_display'])) { ?>
			<div class="font-size-small text-gray-dark">Serves <?php echo ((($mainItem['servings_per_container_display'] - 2) > 0) ? ($mainItem['servings_per_container_display'] - 2) . '-' . $mainItem['servings_per_container_display'] : '1-' . $mainItem['servings_per_container_display']); ?></div>
		<?php } else if ($mainItem['servings_per_item'] == 6) { ?>
			<div class="font-size-small text-gray-dark">Serves 2-3</div>
		<?php } else { ?>
			<div class="font-size-small text-gray-dark">Serves 2-3</div>
		<?php } ?>
	</div>
</div>