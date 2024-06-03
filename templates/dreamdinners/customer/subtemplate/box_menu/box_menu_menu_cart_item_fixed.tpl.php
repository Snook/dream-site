<?php $DAO_menu_item = $item[key($item)]; ?>
<?php if (!empty($DAO_menu_item->DAO_bundle_to_menu_item->current_offering)) { ?>
	<div class="row mr-0 py-4" data-cart_menu_item="<?php echo $DAO_menu_item->menu_item_id; ?>">
		<div class="col">
			<div class="font-weight-bold font-size-small text-uppercase"><?php echo $DAO_menu_item->menu_item_name; ?></div>
		</div>
	</div>
<?php } ?>