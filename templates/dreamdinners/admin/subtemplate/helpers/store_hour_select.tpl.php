<table style="width: 100%; margin-bottom: 10px;">
	<tr>
		<td class="bgcolor_light"></td>
		<td class="bgcolor_light"><input type="button" id="set-default-hours" class="button" value="Use Default Hours">&nbsp;<input type="button" id="clear-store-hours" class="button" value="Clear Hours"></td>
	</tr>
	<?php
		foreach (array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun') as $day) { ?>
		<tr>
			<td class="bgcolor_light" style="text-align: right;vertical-align:top;width: 30px;"><?php echo $day; ?>:</td>
			<td class="bgcolor_light">
				<div class="store-hour-selection-container" data-day="<?php echo $day; ?>">
					<div style="display: inline;" class="store-hours-selector-open" data-day="<?php echo $day; ?>"></div> -
					<div style="display: inline;" class="store-hours-selector-close" data-day="<?php echo $day; ?>"></div> &nbsp;
					<div style="display: inline;" class="store-closed" data-day="<?php echo $day; ?>"></div>
				</div>
			</td>
		</tr>
		<?php } ?>
	<tr>
		<td class="bgcolor_light"></td>
		<td class="bgcolor_light"><input type="button" id="preview-store-hours" class="button" value="Update Preview"></td>
	</tr>
	<tr>
		<td class="bgcolor_light" colspan="2">
			<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="bio_store_hours_preview"></div>
		</td>
	</tr>
</table>