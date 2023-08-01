<ul id="bi_<?php echo $this->box_inst_id; ?>" class="list_group p-0 rounded">
	<li class="list-group-item pl-1 active">
		<span id="box_inst_id_<?php echo $this->box_inst_id; ?>"
			  data-box_id="<?php echo $this->box_id; ?>"
			  data-box_price="<?php echo $this->bundle_data->price; ?>"
			  data-price_shipping="<?php echo $this->bundle_data->price_shipping; ?>"
			  data-number_servings_required="<?php echo $this->bundle_data->number_servings_required; ?>"
			  data-contents_are_fixed="<?php echo ($this->box_type == CBox::DELIVERED_FIXED ? "true" : "false"); ?>"
			  data-number_items_required="<?php echo $this->bundle_data->number_items_required; ?>"
			  data-box_label="<?php echo $this->box_label; ?>"
			  data-is_saved="<?php echo ($this->is_saved ? 'true' : 'false');?>" >
					<?php echo "[id# " . $this->box_inst_id . "] " .  $this->box_label; ?>
			</span>
		<input type="hidden" name="biq_<?php echo $this->box_inst_id; ?>" value="1" />
		<span data-box_id="1">$<?php echo $this->bundle_data->price; ?></span>
		<span data-toggle="collapse" data-target=".bil_<?php echo $this->box_inst_id; ?>"  aria-expanded="true" aria-controls=".bil_<?php echo $this->box_inst_id; ?>" class='cursor-pointer' >(<u>show/hide items</u>)</span>
		</span>&nbsp;<button class="btn ml-8 float-right box-delete" id="del_box_<?php echo $this->box_inst_id; ?>" data-box_instance_id="<?php echo $this->box_inst_id; ?>" >X</button><br />
		<span >Please select <?php echo $this->bundle_data->number_items_required; ?> dinners.</span>


	</li>
	<?php if ($this->box_type == CBox::DELIVERED_FIXED)
	{
		foreach($this->bundle_items as $id => $itemData) { ?>
			<li class="list-group-item p-1 bil_<?php echo $this->box_inst_id; ?>  collpase show" data-box_inst_id="<?php echo $this->box_inst_id; ?>" data-menu_item_id="<?php echo $id; ?>"><?php echo CAppUtil::truncate($itemData['display_title'], 50); ?>
				<span class="float-right"  data-recipe_id="<?php echo $itemData['recipe_id']; ?>"  id="inv_<?php echo $this->box_inst_id . '_' . $id; ?>"></span></li>
			<input type="hidden" name="qty_<?php echo $this->box_inst_id; ?>_<?php echo $id; ?>" id="qty_<?php echo $this->box_inst_id; ?>_<?php echo $id; ?>" value="1" data-recipe_id="<?php echo $itemData['recipe_id']; ?>" data-servings_per_item="<?php echo $itemData['servings_per_item']; ?>"
				   data-last_qty="<?php echo (!empty($itemData['qty']) ? $itemData['qty'] : "0"); ?>" data-claimed_qty="<?php echo (!empty($itemData['qty'])  && $this->orderState == 'ACTIVE' ? $itemData['qty'] : "0"); ?>" />
		<?php }
	}
	else
	{
		foreach($this->bundle_items as $id => $itemData) {
			if (!empty($itemData['out_of_stock'])) { ?>
				<li class="list-group-item p-1 bil_<?php echo $this->box_inst_id; ?> collpase show" id='bil_<?php echo $this->box_inst_id; ?>' data-menu_item_id="<?php echo $id; ?>">
					<input id="qty_<?php echo $this->box_inst_id; ?>_<?php echo $id; ?>" class="mr-2" style="width:60px;" type="number" data-box_inst_id="<?php echo $this->box_inst_id; ?>"
						   data-last_qty="<?php echo (!empty($itemData['qty']) ? $itemData['qty'] : "0"); ?>"   data-servings_per_item="<?php echo $itemData['servings_per_item']; ?>"
						   data-claimed_qty="<?php echo (!empty($itemData['qty'])  && $this->orderState == 'ACTIVE' ? $itemData['qty'] : "0"); ?>"
						   data-recipe_id="<?php echo $itemData['recipe_id']; ?>" data-menu_item_id="<?php echo $id; ?>"
						   data-title="<?php echo CAppUtil::truncate($itemData['display_title'], 44); ?>"
						   name="qty_<?php echo $this->box_inst_id; ?>_<?php echo $id; ?>" value="<?php echo (!empty($itemData['qty']) ? $itemData['qty'] : "0"); ?>" disabled="disabled" />
					<?php echo CAppUtil::truncate($itemData['display_title'], 44); ?>
					<span>Out of Stock</span></li>
			<?php } else { ?>
				<li class="list-group-item p-1 bil_<?php echo $this->box_inst_id; ?> collpase show" id='bil_<?php echo $this->box_inst_id; ?>' data-menu_item_id="<?php echo $id; ?>">
					<input id="qty_<?php echo $this->box_inst_id; ?>_<?php echo $id; ?>" class="mr-2" style="width:60px;" type="number" data-box_inst_id="<?php echo $this->box_inst_id; ?>"
						   data-servings_per_item="<?php echo $itemData['servings_per_item']; ?>" data-recipe_id="<?php echo $itemData['recipe_id']; ?>"  data-menu_item_id="<?php echo $id; ?>"
						   data-last_qty="<?php echo (!empty($itemData['qty']) ? $itemData['qty'] : "0"); ?>"
						   data-claimed_qty="<?php echo (!empty($itemData['qty']) && $this->orderState == 'ACTIVE' ? $itemData['qty'] : "0"); ?>"
						   data-title="<?php echo CAppUtil::truncate($itemData['display_title'], 44); ?>"
						   name="qty_<?php echo $this->box_inst_id; ?>_<?php echo $id; ?>" value="<?php echo (!empty($itemData['qty']) ? $itemData['qty'] : "0"); ?>" />
					<?php echo CAppUtil::truncate($itemData['display_title'], 44); ?><span class="float-right"  data-recipe_id="<?php echo $itemData['recipe_id']; ?>" id="inv_<?php echo $this->box_inst_id . '_' . $id; ?>"></span></li>
			<?php } } ?>
	<?php }  ?>
</ul>