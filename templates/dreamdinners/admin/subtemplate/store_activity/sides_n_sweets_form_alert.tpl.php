<div>
	&#8226; <?php echo CTemplate::dateTimeFormat($item['time'], TIME, $this->store, CONCISE); ?> - Sides & Sweets Order Form Submission

	<button class="show_edit_notes astext" data-index="<?php echo $index; ?>">&bigtriangledown;</button>

	<ul class="edit_note_content collapse list-unstyled px-3" data-index="<?php echo $index; ?>">
		<li class="additional-info">
			<div id="session_details_table_div">
				<table id="session_details_table">
					<tbody>
					<tr>
						<td class="value"><span class="text-white-space-nowrap"><?php echo $item['description']; ?></span></td>
					</tr>
					</tbody>
				</table>
			</div>

		</li>
	</ul>



</div>