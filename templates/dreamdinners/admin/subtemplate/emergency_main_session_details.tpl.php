<table id="session_details_table">
	<tbody>
	<tr>
		<td class="label" style="width: 126px;">Session Type</td>
		<td class="value" style="width: 90px;"><?php echo $this->session_info['session_type_desc']; ?><?php echo ($this->session_info['session_publish_state'] == CSession::CLOSED) ? ' (Closed)' : ''; ?></td>

		<td class="label" style="width: 120px;">Duration</td>
		<td class="value"><?php echo $this->session_info['duration_minutes']; ?> Minutes</td>
	</tr>
	<tr>
		<td class="label">Maximum Slots</td>
		<td class="value"><?php echo $this->session_info['available_slots']; ?></td>

		<td class="label">Booked Standard</td>
		<td class="value"><?php echo $this->session_info['booked_standard_slots']; ?></td>
	</tr>
	<?php if ($this->session_info['session_type'] != CSession::DREAM_TASTE) { ?>
		<tr>
			<td class="label">Starter Pack Slots</td>
			<td class="value"><?php echo $this->session_info['introductory_slots']; ?></td>

			<td class="label">Booked Intro</td>
			<td class="value"><?php echo $this->session_info['booked_intro_slots']; ?></td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['session_host'])) { // Meal Prep Workshop host ?>
		<tr>
			<td class="label">Host</td>
			<td colspan="3" class="value"><a href="?page=admin_user_details&amp;id=<?php echo $this->session_info['session_host']; ?>"><?php echo $this->session_info['session_host_firstname']; ?> <?php echo $this->session_info['session_host_lastname']; ?></a></td>
		</tr>
	<?php } ?>
	<?php if ($this->session_info['session_type'] == CSession::DREAM_TASTE && !empty($this->session_info['dream_taste_available_on_customer_site'])) { ?>
		<tr>
			<td class="label">Operations</td>
			<td colspan="3" class="value">
				<span id="sd_invitation_pdf" class="button">Invitation PDF</span>
				<?php if (!empty($this->session_info['session_host'])) { // Meal Prep Workshop host ?><span id="sd_resend_hostess_email" class="button">Resend Host Notification</span><?php } ?>
			</td>
		</tr>
	<?php } ?>
	<?php if ($this->session_info['session_type'] == CSession::FUNDRAISER && !empty($this->session_info['dream_taste_available_on_customer_site'])) { ?>
		<tr>
			<td class="label">Fundraiser</td>
			<td colspan="3" class="value">
				<?php echo $this->session_info['fundraiser_name']; ?>
			</td>
		</tr>
		<tr>
			<td class="label">Operations</td>
			<td colspan="3" class="value">
				<span id="sd_fundraiser_invitation_pdf" class="button">Invitation PDF</span>
			</td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['session_discount_id'])) { ?>
		<tr>
			<td class="label">Discount</td>
			<td colspan="3" class="value"><?php echo $this->session_info['discount_var']; ?>%</td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['session_password']) && !($this->session_info['session_type'] == CSession::DREAM_TASTE && empty($this->session_info['dream_taste_available_on_customer_site']))) { ?>
		<tr>
			<td class="label">Invite Code</td>
			<td colspan="3" class="value"><?php echo $this->session_info['session_password']; ?></td>
		</tr>
	<?php } ?>
	<tr>
		<td class="label">Registration Closes</td>
		<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['session_close_scheduling'], NORMAL); ?></td>
	</tr>
	<?php if (!empty($this->session_info['session_lead'])) { ?>
		<tr>
			<td class="label">Lead</td>
			<td colspan="3" class="value"><a href="?page=admin_user_details&amp;id=<?php echo $this->session_info['session_lead']; ?>"><?php echo $this->session_info['lead_firstname']; ?> <?php echo $this->session_info['lead_lastname']; ?></a></td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['session_details'])) { ?>
		<tr>
			<td colspan="4" class="label">Session Details</td>
		</tr>
		<tr>
			<td colspan="4" class="value"><div id="session_details_text" class="session_details_text"><?php echo $this->session_info['session_details']; ?></div></td>
		</tr>
		<tr>
			<td class="label">Guest Signup Link</td>
			<td colspan="3" class="value">
				<input type="text" value="<?php echo HTTPS_SERVER; ?>/session/<?php echo $this->session_info['id']; ?>" style="width: 98%;" />
			</td>
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
			<td colspan="3" class="value"><a href="?page=admin_user_details&amp;id=<?php echo $this->session_info['created_by']; ?>"><?php echo $this->session_info['created_by_firstname']; ?> <?php echo $this->session_info['created_by_lastname']; ?></a></td>
		</tr>
		<tr>
			<td class="label">Created</td>
			<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['timestamp_created'], NORMAL, $this->session_info['store_id'], CONCISE); ?></td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['updated_by'])) { ?>
		<tr>
			<td class="label">Updated By</td>
			<td colspan="3" class="value"><a href="?page=admin_user_details&amp;id=<?php echo $this->session_info['updated_by']; ?>"><?php echo $this->session_info['updated_by_firstname']; ?> <?php echo $this->session_info['updated_by_lastname']; ?></a></td>
		</tr>
		<tr>
			<td class="label">Updated</td>
			<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['timestamp_updated'], NORMAL, $this->session_info['store_id'], CONCISE); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>