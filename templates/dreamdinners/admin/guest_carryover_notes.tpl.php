<?php require_once('includes/DAO/BusinessObject/CUserData.php'); ?>
<?php
	$guest_carryover_notes_set = true;
	$guest_carryover_note_user_id = $this->user['id'];

	$guest_carryover_note_store_id = CBrowserSession::getCurrentFadminStore();

	$guestNote = CUserData::userCarryoverNote($guest_carryover_note_user_id, $guest_carryover_note_store_id);

	$guest_carryover_notes = htmlspecialchars(CUserData::filterUserCarryoverNote($guestNote->user_data_value));
	$guest_carryover_notes_edit = $guest_carryover_notes;

	if($guest_carryover_notes == '')
	{
		$guest_carryover_notes_set = false;
		$guest_carryover_notes = 'No carryover notes.';
	}
?>
<script type="text/javascript">
//<![CDATA[
guestNotesVar_<?php echo $guest_carryover_note_user_id; ?> = {
	image_path: '<?php echo ADMIN_IMAGES_PATH; ?>',
	user_id: '<?php echo $guest_carryover_note_user_id; ?>',
	note: '<?php echo str_replace("'", "\\'", $guest_carryover_notes_edit); ?>'
};
function editGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()
{
	$('#guest_carryover_notes_read_<?php echo $guest_carryover_note_user_id; ?>').hide();
	$('#guest_carryover_notes_edit_<?php echo $guest_carryover_note_user_id; ?>').show();
}
function cancelGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()
{
	$('#guest_carryover_notes_<?php echo $guest_carryover_note_user_id; ?>').val(guestNotesVar_<?php echo $guest_carryover_note_user_id; ?>.note);
	$('#guest_carryover_notes_read_<?php echo $guest_carryover_note_user_id; ?>').show();
	$('#guest_carryover_notes_edit_<?php echo $guest_carryover_note_user_id; ?>').hide();
}
function showGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()
{
	$('#guest_carryover_note_<?php echo $guest_carryover_note_user_id; ?>').show();
	$('#show_guest_carryover_note_<?php echo $guest_carryover_note_user_id; ?>').hide();
}
function saveGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()
{
	$('#guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>').show();
	$('#guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>').html('<img src="' + guestNotesVar_<?php echo $guest_carryover_note_user_id; ?>.image_path + '/throbber_processing_noborder.gif" alt="Processing" />');

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_guestCarryoverNotes',
			user_id: guestNotesVar_<?php echo $guest_carryover_note_user_id; ?>.user_id,
			note: $('#guest_carryover_notes_<?php echo $guest_carryover_note_user_id; ?>').val()
		},
		success: function(json)
		{
			if (json.processor_success)
			{
				if(json.note == '')
				{
					json.note = 'No carryover notes.';
				}
				guestNotesVar_<?php echo $guest_carryover_note_user_id; ?>.note = json.note;
				$('#guest_carryover_note_<?php echo $guest_carryover_note_user_id; ?>').html(guestNotesVar_<?php echo $guest_carryover_note_user_id; ?>.note);
				$('#guest_carryover_notes_read_<?php echo $guest_carryover_note_user_id; ?>').show();
				$('#guest_carryover_notes_edit_<?php echo $guest_carryover_note_user_id; ?>').hide();
				$('#guest_carryover_notes_<?php echo $guest_carryover_note_user_id; ?>').val(guestNotesVar_<?php echo $guest_carryover_note_user_id; ?>.note);
				$('#guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>').hide();
			}
			else if (json.processor_message == "Not logged in")
			{
				$('#guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>').html('<span style="color:red;font-weight:bold;">Session timed out, please login again.</span>');
			}
			else
			{
				$('#guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>').html('<span style="color:red;font-weight:bold;">' + json.message + '</span>');
			}
	  	},
		error: function(objAJAXRequest, strError)
		{
	   		$('#guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>').show();
	   		$('#guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>').html('Error: ' + strError + '. Please <a href="javascript:saveGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>();">[try again]</a>.');
	  	}
	});
}
//]]>
</script>
<div style="width:98%;">
	<div id="guest_carryover_notes_read_<?php echo $guest_carryover_note_user_id; ?>"><?php if (!$this->emergency_mode) {?><input type="button" value="Edit" class="button" onclick="editGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()" style="float:right;margin-left:4px;margin-bottom:4px;" /><?php } ?>
		<span id="guest_carryover_note_<?php echo $guest_carryover_note_user_id; ?>" <?php if ($guest_carryover_notes_set && !empty($guestNote->hide_carryover_notes)) { ?>style="display:none;"<?php } ?>><?php echo $guest_carryover_notes; ?></span>
		<?php if ($guest_carryover_notes_set && !empty($guestNote->hide_carryover_notes)) { ?><span id="show_guest_carryover_note_<?php echo $guest_carryover_note_user_id; ?>"><input type="button" value="View Note" class="button" onclick="showGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()" /></span><?php } ?>
	</div>
	<div id="guest_carryover_notes_edit_<?php echo $guest_carryover_note_user_id; ?>" style="display:none;">
		<textarea id="guest_carryover_notes_<?php echo $guest_carryover_note_user_id; ?>" name="guest_carryover_notes" rows="5" cols="70" style="width:100%;height:100px;"><?php echo $guest_carryover_notes_edit; ?></textarea>
		<table style="width:100%;">
		<tr>
			<td><span id="guest_carryover_note_proc_message_<?php echo $guest_carryover_note_user_id; ?>" style="display:none;"></span></td>
			<td style="text-align:right;">
				<input type="button" value="Save" class="button" onclick="saveGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()" />
				<input type="button" value="Cancel" class="button" onclick="cancelGuestCarryoverNotes_<?php echo $guest_carryover_note_user_id; ?>()" />
			</td>
		</tr>
		</table>
	</div>
</div>