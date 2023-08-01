<table class="table table-sm table-striped mt-3 ddtemp-table-border-collapse">
	<tr>
		<td colspan="6">Note: These quantity boxes are read only. Each bundle purchased must include 1 each of all 3 items. Please set the quantity of the bundle and the bundled item quantities will be set automatically.</td>
	</tr>
	<tr>
		<td colspan="6">
			<span id="selected_items_station_<?php echo $thisItem['id']; ?>">0</span> of required <span id="required_items_station_<?php echo $thisItem['id']; ?>"><?php echo $this->sideStationBundleInfo[$thisItem['id']]['number_items_required']; ?></span> items selected.
			<br />
			<span id="sidestation_help_<?php echo $thisItem['id']; ?>"><span>
		</td>
	</tr>

	<?php foreach ($thisItem['sub_items'] as $sid => $subItemInfo) { ?>
		<tr>
			<td></td><td><?php echo $subItemInfo['display_title']; ?></td>
			<td><img onclick="incQty(<?php echo $sid; ?>,<?php echo $subItemInfo['entree_id']; ?>,<?php echo $subItemInfo['servings_per_item']; ?>,\'sbi_\');" src="<?php echo ADMIN_IMAGES_PATH; ?>/oe_plus.gif"><br /><img onclick="decQty(<?php echo $subItemInfo['entree_id']; ?>,\'sbi_\');" src="<?php echo ADMIN_IMAGES_PATH; ?>/oe_minus.gif"></td>
			<td><input type="text" value="1" readonly="readonly" size="2" maxlength="2" data-lastqty="0" data-servings="1" data-entreeid="<?php echo  $sid ; ?>" onkeyup="qtyUpdate(this);" class="cform_input" id="sbi_<?php echo  $sid ; ?>" name="sbi_<?php echo  $sid ; ?>"></td>
			<td><span style="background:#f4e4d6;" id="sbi_inv_<?php echo $sid; ?>"><?php echo $this->entreeToInventoryMap[$sid]['remaining']; ?></span></td>
			<td width="200"></td>
		</tr>
	<?php } ?>

</table>