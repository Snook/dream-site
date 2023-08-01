<h3 style="text-align: center">Site Admin Functions</h3>

<div style="border:2px green solid; margin:5px; padding:10px;">
	<h2 style="text-align: center">Order In-Store Status</h2>
	<table>
		<tr>
			<td><input type="checkbox" id="in-store_status" <?php if (!empty($this->orderInfo['in_store_order'])) echo 'checked="checked"'?>
				name="in-store_status" data-org_value="<?php echo (empty($this->orderInfo['in_store_order']) ? "0" : "1") ?>" <?php echo (empty($this->orderInfo['in_store_order']) ? "" : "disabled='disabled'"); ?>   ></td>
			<td><label for="in-store_status">Order was placed according to 'in-store' rules</label></td>

		</tr>
		<tr id="instore_update_row" style="display: none">
			<td colspan="2"><button class="button">Update</button></td>
		</tr>
	</table>
</div>

<div style="border:2px green solid; margin:5px; padding:10px;">
	<h2 style="text-align: center">Order PLATEPOINTS Status</h2>
	<table>
		<tr>
			<td><input type="checkbox" id="plate_points_status"  <?php if (!empty($this->orderInfo['is_in_plate_points_program'])) echo 'checked="checked"'?>
				name="plate_points_status" data-org_value="<?php echo (empty($this->orderInfo['is_in_plate_points_program']) ? "0" : "1") ?>" <?php echo (empty($this->orderInfo['is_in_plate_points_program']) ? "" : "disabled='disabled'"); ?>></td>
			<td><label for="in-store_status">Order was placed within PlatePoints
					program</label></td>
   </tr>

		<tr id="plate_points_status_update_row" style="display: none">
			<td colspan="2"><button class="button">Update</button></td>
		</tr>
	</table>
</div>