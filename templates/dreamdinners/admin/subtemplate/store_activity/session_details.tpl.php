<div>
	&#8226; <?php echo CTemplate::dateTimeFormat($item['time'], TIME, $this->store, CONCISE); ?>
	<span data-tooltip="<?php echo CUser::userTypeText($item['user_type']); ?>"><a href="/?page=admin_user_details&amp;id=<?php echo $item['user_id']; ?>" target="_blank"><?php echo $item['user']; ?></a></span>
	<b>Created</b> <a href="/backoffice?session=<?php echo $item['session_id']; ?>" target="_blank"><span class="type"><?php echo CCalendar::sessionTypeNote($item['session_data']['session_type_true']); ?></span></a>
	Session for <?php echo CTemplate::dateTimeFormat($item['session_start'], VERBOSE); ?>
	<button class="show_edit_notes astext" data-index="<?php echo $index; ?>">&bigtriangledown;</button>

	<ul class="edit_note_content collapse list-unstyled px-3" data-index="<?php echo $index; ?>">
		<li class="additional-info">
			<div id="session_details_table_div">
				<table id="session_details_table">
					<tbody>
					<tr>
						<td class="label" colspan="2" style="width: 216px;font-weight: 600;"><?php echo $item['session_data']['session_type_title'];?></td>
						<td class="label" style="width: 120px;"></td>
						<td class="value"></td>
					</tr>
					<tr>
						<td class="label" style="width: 126px;">Menu</td>
						<td class="value" style="width: 90px;"><?php echo $item['session_data']['menu_name'];?></td>
						<td class="label" style="width: 120px;">Session State</td>
						<td class="value"><span class="text-uppercase"><?php echo strtolower($item['session_data']['session_publish_state']);?> </span></td>
					</tr>
					</tbody>
				</table>
			</div>
			<?php $this->session_info = $item['session_data']; include $this->loadTemplate('admin/subtemplate/main_session_details.tpl.php'); ?>
		</li>
	</ul>
</div>