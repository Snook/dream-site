<table id="session_details_table">
	<tbody>
	<tr>
		<td class="label" style="width: 126px;">Session Type</td>
		<td class="value" style="width: 90px;">Walk-In<?php echo ($this->session_info['session_publish_state'] == CSession::CLOSED) ? ' (Closed)' : ''; ?></td>

		<td class="label" style="width: 120px;">Duration</td>
		<td class="value">All Day</td>
	</tr>
	<tr>
		<td class="label">Booked Intro</td>
		<td class="value"><?php echo $this->session_info['booked_intro_slots']; ?></td>

		<td class="label">Booked Standard</td>
		<td class="value"><?php echo $this->session_info['booked_standard_slots'];  if ($this->session_info['num_rsvps']) echo " (+ " . $this->session_info['num_rsvps'] . " RSVPs)"; ?></td>
	</tr>
	<?php if (!empty($this->session_info['session_discount_id'])) { ?>
		<tr>
			<td class="label">Discount</td>
			<td colspan="3" class="value"><?php echo $this->session_info['discount_var']; ?>%</td>
		</tr>
	<?php } ?>
	<tr>
		<td class="label">Closes</td>
		<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['session_close_scheduling'], NORMAL); ?></td>
	</tr>
	<?php if (!empty($this->session_info['session_details'])) { ?>
		<tr>
			<td colspan="4" class="label">Session Details</td>
		</tr>
		<tr>
			<td colspan="4" class="value"><div id="session_details_text" class="session_details_text"><?php echo $this->session_info['session_details']; ?></div></td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['admin_notes'])) { ?>
		<tr>
			<td colspan="4" class="label">Administrative Notes</td>
		</tr>
		<tr>
			<td colspan="4" class="value"><?php echo $this->session_info['admin_notes']; ?></td>
		</tr>
	<?php } ?>
	</tbody>
	<tbody class="session_meta">
	<?php if (!empty($this->session_info['created_by'])) { ?>
		<tr>
			<td class="label">Created By</td>
			<td colspan="3" class="value"><a href="/?page=admin_user_details&amp;id=<?php echo $this->session_info['created_by']; ?>"><?php echo $this->session_info['created_by_firstname']; ?> <?php echo $this->session_info['created_by_lastname']; ?></a></td>
		</tr>
		<tr>
			<td class="label">Created</td>
			<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['timestamp_created'], NORMAL, $this->session_info['store_id'], CONCISE); ?></td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['updated_by'])) { ?>
		<tr>
			<td class="label">Updated By</td>
			<td colspan="3" class="value"><a href="/?page=admin_user_details&amp;id=<?php echo $this->session_info['updated_by']; ?>"><?php echo $this->session_info['updated_by_firstname']; ?> <?php echo $this->session_info['updated_by_lastname']; ?></a></td>
		</tr>
		<tr>
			<td class="label">Updated</td>
			<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['timestamp_updated'], NORMAL, $this->session_info['store_id'], CONCISE); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>