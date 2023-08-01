<table class="table table-sm table-striped border border-gray-500 border-width-3-imp mt-3 ddtemp-table-border-collapse">
	<thead>
	<tr class="bg-white">
		<th>
			<span id="selected_items_station_<?php echo $thisItem['id']; ?>">0</span> of required
			<span id="required_items_station_<?php echo $thisItem['id']; ?>"><?php echo $this->sideStationBundleInfo[$thisItem['id']]['number_items_required']; ?></span> items selected.
			<div id="sidestation_help_<?php echo $thisItem['id']; ?>"></div>
		</th>
		<th class="text-center" style="width: 140px; min-width: 140px;">Quantity</th>
		<th class="text-center">Remaining<br />Inventory</th>
	</tr>
	</thead>
	<tbody>
	<?php $groupTitle = ''; foreach ($thisItem['sub_items'] as $sid => $subItemInfo) { ?>
		<?php if ($subItemInfo['group_title'] != $groupTitle) { ?>
			<tr>
				<td colspan="3" class="container">
					<div class="row mb-1">
						<div class="col-12 bg-gray-300 py-2">
							<span class="font-size-medium-small"><?php echo $groupTitle = $subItemInfo['group_title']; ?></span>
							<span class="font-size-small">- Select <span class="group-select-num" data-group_id="<?php echo $subItemInfo['bundle_to_menu_item_group_id']; ?>"><?php echo $subItemInfo['group_number_items_required']; ?></span></span>
						</div>
					</div>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<td class="font-weight-bold"><?php echo $subItemInfo['display_title']; ?></td>
			<td><?php echo $this->form_direct_order['sbi_' . $sid . '_html']; ?></td>
			<td class="text-center">
				<span data-entree_inventory_remaining="<?php echo $subItemInfo['entree_id'] ?>"><?php echo array_key_exists('remaining', $this->entreeToInventoryMap[$subItemInfo['entree_id']])?$this->entreeToInventoryMap[$subItemInfo['entree_id']]['remaining']:''; ?></span>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>