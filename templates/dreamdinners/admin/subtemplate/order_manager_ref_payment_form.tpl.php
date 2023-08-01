
<div id="payment1_reference" style="display: none;">
	<table width="100%">
		<?php if (!empty($this->refTransArray)) {
			foreach($this->refTransArray as $id => $val)
			{
				?>
				<tr>
					<td class="form_field_cell">Original Transaction ID</td>
					<td class="form_field_cell"><?=$id?></td>
				</tr>
				<tr>
					<td class="form_field_cell">Amount to Debit</td>
					<td class="form_field_cell">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Amount to Charge $</div>
							</div>
							<input type="text" class="form-control" name="RT_<?=$id?>" id="RT_<?=$id?>" onkeyup="validateRefTrans(this, 500);">
						</div>
					</td>
				</tr>
				<?php
			}
		}
		?>
	</table>
</div>

