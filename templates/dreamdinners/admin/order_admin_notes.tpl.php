<?php require_once('processor/admin/orderAdminNotes.php'); ?>
<?php
	if (!empty($this->orderInfo['id']))
	{

		$order_admin_note_order_id = $this->orderInfo['id'];

		$order_admin_notes = processor_admin_orderAdminNotes::loadAdminNote($order_admin_note_order_id);
		$order_admin_notes_edit = $order_admin_notes;
	}
	else
	{
		$order_admin_notes = "";
	}


	if($order_admin_notes == '')
	{
		$order_admin_notes = 'No admin notes.';
	}
?>
<script type="text/javascript">
//<![CDATA[
orderNotesVar = {
	image_path: '<?php echo ADMIN_IMAGES_PATH?>',
	order_id: <?php echo (empty($order_admin_note_order_id) ? "null" : "'" .$order_admin_note_order_id . "'"); ?>,
	note: '<?php echo  (empty($order_admin_notes_edit) ?  "'" : str_replace("'", "\\'", $order_admin_notes_edit) . "'"); ?>
};
function editOrderAdminNotes()
{
	$('#order_admin_notes_read').hide();
	$('#order_admin_notes_edit').show();
}
function cancelOrderAdminNotes()
{
	$('#order_admin_notes').val(orderNotesVar.note);
	$('#order_admin_notes_read').show();
	$('#order_admin_notes_edit').hide();
}
function saveOrderAdminNotes()
{
	$('#order_admin_note_proc_message').show();
	$('#order_admin_note_proc_message').html('<img src="' + orderNotesVar.image_path + '/throbber_processing_noborder.gif" alt="Processing" />');

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_orderAdminNotes',
			order_id: orderNotesVar.order_id,
			note: $('#order_admin_notes').val()
		},
		success: function(json)
		{
			if (json.processor_success)
			{
				if(json.note == '')
				{
					json.note = 'No admin notes.'
				}
				orderNotesVar.note = json.note;
				$('#order_admin_note').html(orderNotesVar.note);
				$('#order_admin_notes_read').show();
				$('#order_admin_notes_edit').hide();
				$('#order_admin_notes').val(orderNotesVar.note);
				$('#order_admin_note_proc_message').hide();
			}
			else if (json.processor_message == "Not logged in")
			{
				$('#order_admin_note_proc_message').html('<span style="color:red;font-weight:bold;">Session timed out, please login again.</span>');
			}
			else
			{
				$('#order_admin_note_proc_message').html('<span style="color:red;font-weight:bold;">' + json.message + '</span>');
			}
	  	},
		error: function(objAJAXRequest, strError)
		{
	   		$('#order_admin_note_proc_message').show();
	   		$('#order_admin_note_proc_message').html('Error: ' + strError + '. Please <a href="javascript:saveOrderAdminNotes();">[try again]</a>.');
	  	}
	});
}
//]]>
</script>
<div style="width:98%">
	<div id="order_admin_notes_read"><input type="button" class="btn btn-primary btn-sm" value="Edit" onclick="javascript:editOrderAdminNotes();" style="float:right;margin-left:4px;margin-bottom:4px;" />
		<span id="order_admin_note"><?php echo $order_admin_notes; ?></span>
	</div>
	<div id="order_admin_notes_edit" style="display:none;">
		<textarea id="order_admin_notes" name="order_admin_notes" style="width:100%;height:100px;"><?php echo (isset($order_admin_notes_edit) ? $order_admin_notes_edit : ''); ?></textarea>
		<table width="100%" border="0">
		<tr>
			<td><span id="order_admin_note_proc_message" style="display:none;"></span></td>
			<td style="text-align:right;">
				<input type="button" class="btn btn-primary btn-sm" value="Save" onclick="javascript:saveOrderAdminNotes();" />
				<input type="button" class="btn btn-primary btn-sm" value="Cancel" onclick="javascript:cancelOrderAdminNotes();" />
			</td>
		</tr>
		</table>
	</div>
</div>