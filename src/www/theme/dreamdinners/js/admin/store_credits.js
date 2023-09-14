
function process_pending_credit_now(user_id, credit_id, referred_user_id, store_id)
{

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_store_credits',
			action:'process_now',
			user_id: user_id,
			referred_user_id: referred_user_id,
			credit_id: credit_id,
			store_id: store_id
		},
		success: function(json)
		{
			if(json.processor_success)
			{
				var rowName = 'pscr_' + credit_id;

				$('#' + rowName).replaceWith(json.html);

				if (window.calculateBalance && typeof(window.calculateBalance) == "function")
				{
					calculateBalance();
				}

			}
			else
			{
				dd_message({
					title: 'Processing Error',
					message: json.processor_message
				});
			}
        },
		error: function(objAJAXRequest, strError)
		{
			dd_message({
				title: 'Error',
				message: response = 'Unexpected error:' + strError
			});
		}
	});

    return false;
}


// Order Editor is different enough to have it's own version
function eo_process_pending_credit_now(user_id, credit_id, referred_user_id, store_id)
{

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_store_credits',
			action:'process_now',
			user_id: user_id,
			referred_user_id: referred_user_id,
			credit_id: credit_id,
			store_id: store_id,
			client: 'order_editor'
		},
		success: function(json)
		{
			if(json.processor_success)
			{

				var rowName = 'pscr_' + credit_id;
				$('#' + rowName).remove();

				if (document.getElementById('pending_credits_table').rows.length == 2)
				{
					$('#pending_credits_table').remove();
				}


				$('#eoTotalStoreCredit').html(json.newSCTotal);
				$('#store_credits_amount').val(json.newSCTotal);
				$('#store_credits_amount').prop("disabled", false);
				$('#use_store_credits').prop("checked", true);

				if (window.reportPaymentStatus && typeof(window.reportPaymentStatus) == "function")
				{
					reportPaymentStatus(false);
				}

			}
			else
			{
				dd_message({
					title: 'Processing Error',
					message: json.processor_message
				});
			}
        },
		error: function(objAJAXRequest, strError)
		{
			dd_message({
				title: 'Error',
				message: response = 'Unexpected error:' + strError
			});
		}
	});

    return false;
}




//Order Editor is different enough to have it's own version
function delete_store_credit(credit_id, user_id)
{

	dd_message({
		title: 'Delete Store Credit',
		width:400,
		message: '<b>Are you sure? This operation is undoable.</b><br />Enter an optional note:<br /><input id="delete_SC_notes" type="text" size="60" maxsize="256" />',
		modal: true,
		confirm: function() {
			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data : {
					processor: 'admin_store_credits',
					action:'delete',
					user_id: user_id,
					credit_id: credit_id,
					notes: $('#delete_SC_notes').val()
				},
				success: function(json)
				{
					if(json.processor_success)
					{
						var rowName = 'scr_' + credit_id;
						$('#' + rowName).remove();

						var totalsName = 'sctc_' + json.store_id;
						$('#' + totalsName).html(formatAsMoney(json.newSCTotal));

						if (document.getElementById('avail_credits_tbl').rows.length == 2)
						{
							$('#avail_credits_header_row').after('<tr><td class="bgcolor_light" colspan="8" style="text-align:center;"><i>There is no available store credit for this customer</i></td></tr>');
						}

					}
					else
					{
						dd_message({
							title: 'Processing Error',
							message: json.processor_message
						});
					}
		        },
				error: function(objAJAXRequest, strError)
				{
					dd_message({
						title: 'Error',
						message: response = 'Unexpected error:' + strError
					});
				}
			});
		},
		cancel: function() {
		}
	});



    return false;
}