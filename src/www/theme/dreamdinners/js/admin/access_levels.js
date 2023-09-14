function access_levels_init()
{
	user_type_val = $('#user_type').val();

	if (user_type_val == 'CUSTOMER' || user_type_val == 'HOME_OFFICE_MANAGER' || user_type_val == 'HOME_OFFICE_STAFF' || user_type_val == 'SITE_ADMIN')
	{
		$('#store_settings_div').hide();
	}
	else
	{
		$('#store_settings_div').show();
	}

	if (user_type_val == current_user_type)
	{
		$('#submit_account').prop("disabled", true);
	}
	else
	{
		$('#submit_account').prop("disabled", false);
	}
	
	handle_store_display_checkbox();
	handle_store_display_text();
	
	handle_special_permissions();
	
}


function handle_special_permissions()
{
	$("[id^='priv_']").on('change', function(){
		$('#submit_account').prop("disabled", false);
	});


}

function addStore()
{
	var msg = "Are you sure you want to add access to this store?";

	if (confirm(msg))
	{
		document.getElementById("change_access").submit();
	}
}

function showStoreSettings()
{
	if ($('#user_type').val() != 'CUSTOMER')
	{
		$('#store_settings_div').show();
	}
	else
	{
		$('#store_settings_div').hide();
	}
}

function deleteStore(store_id)
{
	var msg = "Are you sure you want to delete access to this store?";

	if (confirm(msg))
	{
		document.getElementById("al_action").value = "delete";
		document.getElementById("which_store").value = store_id;

		document.getElementById("accessAction").submit();
	}
}

function handle_store_display_text()
{
	$('[data-display_text]').each(function() {

		var uts_id = $(this).data('display_text');

		$(this).bind("input", function(e) {

			$('[data-display_text_save="' + uts_id + '"]').removeClass('disabled');

		});


		$('[data-display_text_save="' + uts_id + '"]').on('click', function () {

			if ($(this).hasClass('disabled'))
			{
				return false;
			}

			var uts_id = $(this).data('display_text_save');
			var display_text = $('[data-display_text="' + uts_id + '"]').val().trim();

			if (!display_text.length)
			{
				display_text = 0;
			}

			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data : {
					processor: 'admin_access_levels',
					op: 'do_display_text',
					uts_id: uts_id,
					display_text: display_text
				},
				success: function(json, status)
				{
					if(json.processor_success)
					{
						$('[data-display_text_save="' + uts_id + '"]').addClass('disabled');
					}
					else
					{

					}
				},
				error: function(objAJAXRequest, strError)
				{
					response = 'Unexpected error';
				}

			});

		});

	});
}

function handle_store_display_checkbox()
{
	$('[data-show_on_customer]').each(function() {

		$(this).on('click', function () {

			var display_on_site = 0;
			var uts_id = $(this).data('show_on_customer');

			if ($(this).is(':checked'))
			{
				display_on_site = 1;
			}

			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data : {
					processor: 'admin_access_levels',
					op: 'do_display_on_site',
					uts_id: uts_id,
					display: display_on_site
				},
				success: function(json, status)
				{
					if(json.processor_success)
					{

					}
					else
					{

					}
				},
				error: function(objAJAXRequest, strError)
				{
					response = 'Unexpected error';
				}

			});

		});

	});

}