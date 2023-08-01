<h3>Notes</h3>
<table width="100%" border="0">
	<tr>
		<td class="bgcolor_light" style="vertical-align: top; text-align: right; width: 150px;">
			<span id="gd_special_instruction_note_button-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-user_id="<?php echo $this->user_obj->id; ?>" data-edit_mode="false" data-tooltip="Guest's order instructions. Visible to Guests." class="button">Special Instructions</span>
			<span id="gd_special_instruction_note_cancel_button-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" class="button" style="display: none;">Cancel Edit</span>
		</td>
		<td colspan="5" class="bgcolor_light">
			<div id="gd_special_instruction_note-<?php echo $this->orderInfo['id']; ?>" class="guest_note" data-user-id="<?php echo $this->user_obj->id; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>"><?php echo $this->orderInfo['order_user_notes']; ?></div>
		</td>
	</tr>

	<tr>
		<td class="bgcolor_light" style="vertical-align: top; text-align: right;">
			<span id="gd_guest_account_note_button-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-user_id="<?php echo $this->user_obj->id; ?>" data-edit_mode="false" data-tooltip="Guest's account notes. Visible to Guests." class="button">Account Notes</span>
			<span id="gd_guest_account_note_cancel_button-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" class="button" style="display: none;">Cancel Edit</span>
		</td>
		<td colspan="5" class="bgcolor_light">
			<div id="gd_guest_account_note-<?php echo $this->orderInfo['id']; ?>" class="guest_note" data-user-id="<?php echo $this->user_obj->id; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>"><?php if (!empty($this->user_obj->preferences[CUser::USER_ACCOUNT_NOTE]['value'])) { echo nl2br(htmlentities($this->user_obj->preferences[CUser::USER_ACCOUNT_NOTE]['value'])); } ?></div>
		</td>
	</tr>

	<tr>
		<td class="bgcolor_light" style="vertical-align: top; text-align: right;">
			<span id="gd_guest_note_button-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-user_id="<?php echo $this->user_obj->id; ?>" data-hide_carryover_notes="<?php echo $this->storeInfo['hide_carryover_notes']; ?>" data-edit_mode="false" data-tooltip="Admin carryover notes. Not visible to guests." class="button">Admin Carryover</span>
			<span id="gd_guest_note_cancel_button-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-hide_carryover_notes="<?php echo $this->storeInfo['hide_carryover_notes']; ?>" class="button" style="display: none;">Cancel Edit</span>
		</td>
		<td colspan="5" class="bgcolor_light"><div <?php if (!empty($this->storeInfo['hide_carryover_notes'])) { ?>style="display: none;"<?php } ?> id="gd_guest_note-<?php echo $this->orderInfo['id']; ?>" class="guest_note" data-user-id="<?php echo $this->user_obj->id; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>"><?php echo (!empty($this->user_obj->userData['16'])) ? nl2br(htmlspecialchars($this->user_obj->userData['16'])) : ''; ?></div><div class="button" id="gd_show_guest_note-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" <?php if (empty($this->user_obj->userData['16']) || empty($this->storeInfo['hide_carryover_notes'])) { ?>style="display: none;"<?php } ?>>View Note</div></td>
	</tr>

	<tr>
		<td class="bgcolor_light" style="vertical-align: top; text-align: right;">
			<span id="gd_admin_note_button-one-<?php echo $this->orderInfo['id']; ?>" data-uid='one'  data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>" data-user_id="<?php echo $this->user_obj->id; ?>" data-edit_mode="false" data-tooltip="Staff notes for this order. Not visible to guests." class="button">Admin Order Note</span>
			<span id="gd_admin_note_cancel_button-one-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" class="button" style="display: none;">Cancel Edit</span>
		</td>
		<td colspan="5" class="bgcolor_light">
			<div id="gd_admin_note-one-<?php echo $this->orderInfo['id']; ?>" class="guest_note multi-populate-admin-note"  data-user-id="<?php echo $this->user_obj->id; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>"><?php echo $this->orderInfo['order_admin_notes']; ?></div></td>
	</tr>
</table>

<h3>History</h3>
<div id="history_div"></div>