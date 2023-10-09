<?php if ($subItemData['group_title'] != $groupTitle) { ?>
	<div class="row mb-1">
		<div class="col-12 bg-gray-300 py-2">
			<span class="font-size-medium-small"><?php echo $groupTitle = $subItemData['group_title']; ?></span>
			<span class="font-size-small">- Select <?php echo $subItemData['group_number_items_required']; ?></span>
		</div>
	</div>
<?php } ?>

<div class="row mb-1">
	<div class="col-9 font-size-small">
		<?php echo $subItemData['menu_item_name']; ?>
		<a class="card-link text-uppercase d-print-none link-dinner-details" href="/item?recipe=<?php echo $subItemData['recipe_id']; ?>" data-recipe_id="<?php echo $subItemData['recipe_id']; ?>" data-store_id="<?php echo $this->cart_info['storeObj']->id; ?>" data-menu_item_id="<?php echo $subItemData['id']; ?>" data-menu_id="<?php echo $mainItem->menu_id; ?>" data-size="large" data-detailed="false">
			<i class="fas fa-info-circle ml-1"></i>
		</a>

		<?php if (!empty($subItemData['this_type_out_of_stock'])) { ?>
			<div class="text-orange">Out of Stock</div>
		<?php } ?>
	</div>
	<div class="col-3">
		<input
			type="number"
			class="form-control form-control-sm bundle-subitem-qty"
			<?php echo ($subItemData['this_type_out_of_stock']) ? ' disabled' : ''; ?>
			<?php echo (!empty($subItemData['fixed_quantity'])) ? ' readonly' : ''; ?>
			min="0"
			value="<?php echo (!empty($subItemData['fixed_quantity'])) ? $subItemData['fixed_quantity'] : 0; ?>"
			data-parent_item="<?php echo $subItemData['parent_item']; ?>"
			data-menu_item_id="<?php echo $subItemData['id']; ?>" />
	</div>
</div>