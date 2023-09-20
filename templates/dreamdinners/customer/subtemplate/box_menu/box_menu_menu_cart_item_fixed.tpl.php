<?php $DAO_menu_item = $item[key($item)]; ?>
<div class="row mr-0 py-4" data-cart_menu_item="<?php echo $DAO_menu_item->id; ?>">
	<div class="col">
		<div class="font-weight-bold font-size-small text-uppercase"><?php echo $DAO_menu_item->menu_item_name; ?></div>
		<?php if (!empty($DAO_menu_item->servings_per_container_display)) { ?>
			<div class="font-size-small text-gray-dark">Serves <?php echo ((($DAO_menu_item->servings_per_container_display - 2) > 0) ? ($DAO_menu_item->servings_per_container_display - 2) . '-' . $DAO_menu_item->servings_per_container_display : '1-' . $DAO_menu_item->servings_per_container_display); ?></div>
		<?php } else if ($DAO_menu_item->servings_per_item == 6) { ?>
			<div class="font-size-small text-gray-dark">Large serves 4-6</div>
		<?php } else { ?>
			<div class="font-size-small text-gray-dark">Medium Serves 2-3</div>
		<?php } ?>
	</div>
</div>