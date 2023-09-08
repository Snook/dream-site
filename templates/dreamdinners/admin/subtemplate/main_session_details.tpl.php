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
		<td class="value"><?php echo $this->session_info['booked_standard_slots'];  if ($this->session_info['num_rsvps']) echo " (+ " . $this->session_info['num_rsvps'] . " RSVPs)"; ?></td>
	</tr>
	<?php if ($this->session_info['session_type'] != CSession::DREAM_TASTE) { ?>
		<tr>
			<td class="label">Intro Slots</td>
			<td class="value"><?php echo $this->session_info['introductory_slots']; ?></td>

			<td class="label">Booked Intro</td>
			<td class="value"><?php echo $this->session_info['booked_intro_slots']; ?></td>
		</tr>
	<?php } ?>
	<?php if (!empty($this->session_info['session_host'])) { ?>
		<tr>
			<td class="label">Host</td>
			<td colspan="3" class="value"><a href="?page=admin_user_details&amp;id=<?php echo $this->session_info['session_host']; ?>"><?php echo $this->session_info['session_host_firstname']; ?> <?php echo $this->session_info['session_host_lastname']; ?></a></td>
		</tr>
	<?php } ?>
	<?php if ($this->session_info['session_type'] == CSession::DREAM_TASTE && !empty($this->session_info['dream_taste_available_on_customer_site'])) { ?>
		<tr>
			<td class="label">Operations</td>
			<td colspan="3" class="value">
				<span id="sd_invitation_pdf" data-session="<?php echo $this->session_info['id']; ?>" class="button sd_invitation_pdf">Invitation PDF</span>
				<?php if (!empty($this->session_info['session_host'])) { // Meal Prep Workshop host ?><span id="sd_resend_hostess_email" data-store="<?php echo $this->session_info['store_id']; ?>" data-session="<?php echo $this->session_info['id']; ?>" class="button sd_resend_hostess_email">Resend Host Notification</span><?php } ?>
			</td>
		</tr>
	<?php } ?>
	<?php if ($this->session_info['session_type_true'] == CSession::REMOTE_PICKUP_PRIVATE) { ?>
		<tr>
			<td class="label">Operations</td>
			<td colspan="3" class="value">
				<span id="sd_pick_up_event_invitation_pdf" data-session="<?php echo $this->session_info['id']; ?>" class="button sd_pick_up_event_invitation_pdf">Invitation PDF</span>
			</td>
		</tr>
	<?php } ?>
	<?php if ($this->session_info['session_type'] == CSession::FUNDRAISER && !empty($this->session_info['dream_taste_available_on_customer_site'])) { ?>
		<tr>
			<td class="label">Fundraiser</td>
			<td colspan="3" class="value">
				<a href="?page=fundraiser&amp;id=<?php echo $this->session_info['store_id']; ?>&amp;fid=<?php echo $this->session_info['fundraiser_id']; ?>" target="_blank"><?php echo $this->session_info['fundraiser_name']; ?></a>
			</td>
		</tr>
		<tr>
			<td class="label">Fundraiser page</td>
			<td colspan="3" class="value">
				<input type="text" value="<?php echo HTTP_BASE; ?>?page=fundraiser&amp;id=<?php echo $this->session_info['store_id']; ?>&amp;fid=<?php echo $this->session_info['fundraiser_id']; ?>" class="form-control form-control-sm" />
			</td>
		</tr>
		<tr>
			<td class="label">Operations</td>
			<td colspan="3" class="value">
				<span id="sd_fundraiser_invitation_pdf" data-session="<?php echo $this->session_info['id']; ?>" class="button sd_fundraiser_invitation_pdf">Invitation PDF</span>
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
			<td class="label">Password</td>
			<td colspan="3" class="value"><?php echo $this->session_info['session_password']; ?></td>
		</tr>
	<?php } ?>
	<tr>
		<td class="label">Registration Closes</td>
		<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['session_close_scheduling'], NORMAL); ?></td>
	</tr>
	<?php if (!empty($this->session_info['session_close_scheduling_meal_customization']) && (strtotime($this->session_info['session_close_scheduling_meal_customization']) > strtotime('2000-01-01 00:00:00'))) { //temp until the source of 1969 date is found?>
		<tr>
			<td class="label">Customization Closes</td>
			<td colspan="3" class="value"><?php echo CTemplate::dateTimeFormat($this->session_info['session_close_scheduling_meal_customization'], NORMAL); ?></td>
		</tr>
	<?php } ?>
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
	<?php } ?>
	<tr>
		<td class="label">Session Order Link</td>
		<td colspan="3" class="value">
			<input type="text" value="<?php echo HTTPS_SERVER; ?>/session/<?php echo $this->session_info['id']; ?>" class="form-control form-control-sm" />
		</td>
	</tr>
	<?php if (!empty($this->show_start_pack_link)) { ?>
	<tr>
		<td class="label">Starter Pack Order Link</td>
		<td colspan="3" class="value">
			<input type="text" value="<?php echo HTTPS_SERVER; ?>/starter/<?php echo $this->session_info['id']; ?>" class="form-control form-control-sm" />
		</td>
	</tr>
	<?php } ?>
	<?php if ($this->session_info['session_type_true'] == CSession::REMOTE_PICKUP || $this->session_info['session_type_true'] == CSession::REMOTE_PICKUP_PRIVATE) { ?>
		<tr>
			<td class="label">Community Pick Up</td>
			<td colspan="3" class="value">
				<?php if ($this->session_info['session_type_true'] == CSession::REMOTE_PICKUP) { ?>
				<div><?php echo $this->session_info['session_title']; ?></div>
				<?php } ?>
				<div><?php echo $this->session_info['session_remote_location']->address_line1; ?><?php echo (!empty($this->session_info['session_remote_location']->address_line2)) ? ' ' . $this->session_info['session_remote_location']->address_line2 : ''; ?> <?php echo $this->session_info['session_remote_location']->city; ?>, <?php echo $this->session_info['session_remote_location']->state_id; ?> <?php echo $this->session_info['session_remote_location']->postal_code; ?></div>
			</td>
		</tr>
		<?php if (!empty($this->session_info['session_remote_location']->contact_user_id)) { ?>
			<tr>
				<td class="label">Contact</td>
				<td colspan="3" class="value">
					<a href="?page=admin_user_details&amp;id=<?php echo $this->session_info['session_remote_location']->contact_user->id; ?>"><?php echo $this->session_info['session_remote_location']->contact_user->firstname; ?> <?php echo $this->session_info['session_remote_location']->contact_user->lastname; ?></a>
				</td>
			</tr>
		<?php } ?>
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