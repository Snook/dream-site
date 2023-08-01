<tr>
	<td scope="row"<?php echo !empty($nutriData['indent']) ? ' class="pl-3"' : ''; ?>>
		<div<?php echo (!empty($nutriData['indent']) && $nutriData['indent'] == 2) ? ' class="ml-3"' : ''; ?>>
			<?php if (!empty($nutriData['sprintf_label'])) { ?>
				<?php echo $nutriData['sprintf_label']; ?>
			<?php } else { ?>
				<span <?php echo empty($nutriData['parent_element']) ? ' class="font-weight-bold"' : ''; ?>><?php echo $nutriData['display_label']; ?></span>
				<?php echo (!empty($nutriData['value'])) ? $nutriData['value'] : 0; ?><?php echo (!empty($nutriData['note_indicator'])) ? $nutriData['note_indicator'] : ''; ?>
			<?php } ?>
		</div>
	</td>
	<td class="text-right font-weight-bold">
		<?php echo (!empty($nutriData['percent_daily_value'])) ? $nutriData['percent_daily_value'] . '%' : ''; ?>
	</td>
</tr>
