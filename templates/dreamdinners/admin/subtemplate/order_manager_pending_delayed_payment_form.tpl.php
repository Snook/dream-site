
<div id="dp_adjust_div" style="display: none">
	<input type="hidden" id="PendingDP" name="PendingDP"
		value="<?=$this->PendingDP ?>" /> <input type="hidden"
		id="PendingDPAmount" name="PendingDPAmount"
		value="<?=$this->PendingDPAmount ?>" /> <input type="hidden"
		id="OriginalPendingDPAmount" name="OriginalPendingDPAmount"
		value="<?=$this->PendingDPAmount ?>" />

	<table width="100%">
		<tr>
			<td class="form_field_cell" colspan="1" align="right">Original
				Transaction:</td>
			<td class="form_field_cell"><?=$this->PendingDPOriginalTransActionID ?>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell" colspan="1" align="right">Current Amount:</td>
			<td class="form_field_cell">$ <?=$this->PendingDPAmount ?>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell" colspan="1" align="right">New Delayed
				Payment Amount:</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<input type="text" class="form-control" name="adj_dp_amount_up" id="adj_dp_amount_up" value="<?=$this->PendingDPAmount ?>" disabled="disabled" size="7" />
				</div>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell" colspan="1" align="right">Adjustment
				Amount:</td>
			<td class="form_field_cell">$ <span id="adjustment_amount_up">0.00</span>
			</td>
		</tr>
	</table>
</div>
