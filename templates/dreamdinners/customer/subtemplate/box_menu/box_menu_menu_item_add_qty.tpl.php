<?php foreach ($this->itemArray as $itemData) { ?>
	<div class="col-<?php echo (count($this->itemArray) == '2') ? '6' : '12'; ?> p-1">
		<button class="btn btn-primary btn-block btn-ripple py-3 <?php if ((!empty($this->box_info->box_type) && $this->box_info->box_type == CBox::DELIVERED_FIXED) || !empty($itemData['out_of_stock'])) { ?>disabled<?php } else { ?>box-item-update<?php } ?>" data-box_update_action="add" data-menu_item_id="<?php echo $itemData['id']; ?>">
			<?php if ( empty($itemData['is_chef_touched'])) { ?>
				<span class="float-left"><?php echo $itemData['pricing_type_info']['pricing_type_name_short']; ?></span>
			<?php } ?>
			<?php if ($itemData['out_of_stock']) { ?>
				<span class="text-white title">Sold out</span>
			<?php } else { ?>
				<?php if (!empty($this->box_info->box_type) && $this->box_info->box_type == CBox::DELIVERED_FIXED) { ?>
					<span class="text-white title">Included</span>
					<i class="fas fa-check float-right text-dark pt-1 d-print-none"></i>
				<?php } else { ?>
					<span class="text-white title">Add item</span>
					<i class="fas fa-plus float-right text-white pt-1 d-print-none"></i>
				<?php } ?>
			<?php } ?>
		</button>
		<div class="row d-md-none d-print-none">
			<div class="col text-center font-size-small text-green-dark-extra mt-2">Serves <?php echo $itemData['pricing_type_info']['pricing_type_serves_display']; ?></div>
		</div>
	</div>
<?php } ?>