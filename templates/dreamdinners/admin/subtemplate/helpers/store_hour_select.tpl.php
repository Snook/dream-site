<table style="width: 100%; margin-bottom: 10px;">
	<?php $mydays = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
		foreach ($mydays as $day) { ?>
		<tr>
			<td class="bgcolor_light" style="text-align: right;vertical-align:top;"><?php echo $day; ?>:</td>
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
		<td class="bgcolor_light"><input type="button" id="preview-hours" class="button" value="Preview Hours"></td>
	</tr>
	<tr>
		<td class="bgcolor_light" colspan="2">
			<?php echo $this->form_store_details['bio_store_hours_html']; ?>
		</td>
	</tr>
	<tr>
		<td class="bgcolor_light" colspan="2">
			<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="bio_store_hours_preview"></div>
		</td>
	</tr>
</table>