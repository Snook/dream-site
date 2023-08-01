
<div id="dp_adjust_down_div" style="display: none;">
	<table width="100%">
		<tr>
			<td valign="top" class="form_subtitle_cell" colspan="2">
				<h3 align="center">Adjust Delayed Payment</h3>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell" colspan="1" align="right">Original Transaction:</td>
			<td><?=$this->PendingDPOriginalTransActionID ?></td>
		</tr>
		<tr>
			<td class="form_field_cell" colspan="1" align="right">Current Amount:</td>
			<td class="form_field_cell">$ <?=$this->PendingDPAmount ?></td>
		</tr>
		<tr>
			<td class="form_field_cell" colspan="1" align="right">New Delayed Payment Amount:</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<input type="text" name="adj_dp_amount_down" id="adj_dp_amount_down" value="<?=$this->PendingDPAmount ?>" disabled="disabled" size="7" />
				</div>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell" colspan="1" align="right">Adjustment Amount:</td>
			<td class="form_field_cell">- $ <span id="adjustment_amount_down">0.00</span></td>
		</tr>
	</table>
</div>
