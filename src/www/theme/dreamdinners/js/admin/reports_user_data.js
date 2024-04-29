function reports_user_data_init()
{

	init_filters();

}

function init_filters()
{

	$("#dfa_All").change(function () {

		if (this.checked)
		{
			$("[id^=df_]").each(function () {
				$(this).prop('checked', true).change();
				//	this.checked = true;
			});
		}
		else
		{
			$("[id^=df_]").each(function () {
				$(this).prop('checked', false).change();
			});
		}

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}

	});

	$('[id^="rf_"]').each(function () {

		if ($.totalStorage(this.id) != null)
		{
			if ($.totalStorage(this.id) == "1")
			{
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		}

	});

	$('[id^="rf_"]').bind("mouseup change", function (e) {

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}

	});

	$('[id^="df_"]').each(function () {

		if ($.totalStorage(this.id) != null)
		{
			if ($.totalStorage(this.id) == "1")
			{
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		}

	});

	$('[id^="df_"]').bind("mouseup change", function (e) {

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}

	});

	$("#dfa_All").each(function () {

		if ($.totalStorage(this.id) != null)
		{
			if ($.totalStorage(this.id) == "1")
			{
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		}

	});

	$("[id^=guest_type]").bind("mouseup change", function (e) {

		var selectedValue = "";
		var selected = $("input[type='radio'][name='guest_type']:checked");
		if (selected.length > 0)
		{
			selectedValue = selected.val();
		}

		$.totalStorage('guest_type', selectedValue);

		$("#df_INSTRUCTIONS").attr('disabled', 'disabled');
		$("#df_CUSTOMIZATIONS").attr('disabled', 'disabled');
		$("#df_INSTRUCTIONS").prop("checked", false);
		$("#df_CUSTOMIZATIONS").prop("checked", false);

		if (selectedValue == "has_future_sessions")
		{
			$("#df_INSTRUCTIONS").attr('disabled', false);
			$("#df_CUSTOMIZATIONS").attr('disabled', false);
			$("#df_INSTRUCTIONS").prop("checked", $.totalStorage('df_INSTRUCTIONS'));
			$("#df_CUSTOMIZATIONS").prop("checked", $.totalStorage('df_CUSTOMIZATIONS'));
		}
	});

	if ($.totalStorage('guest_type') != null)
	{
		var $radios = $('input:radio[name=guest_type]');
		$radios.filter('[value=' + $.totalStorage('guest_type') + ']').prop('checked', true);

		$("#df_INSTRUCTIONS").attr('disabled', 'disabled');
		$("#df_CUSTOMIZATIONS").attr('disabled', 'disabled');
		$("#df_INSTRUCTIONS").prop("checked", false);
		$("#df_CUSTOMIZATIONS").prop("checked", false);

		if ($.totalStorage('guest_type') == "has_future_sessions")
		{
			$("#df_INSTRUCTIONS").attr('disabled', false);
			$("#df_CUSTOMIZATIONS").attr('disabled', false);
			$("#df_INSTRUCTIONS").prop("checked", $.totalStorage('df_INSTRUCTIONS'));
			$("#df_CUSTOMIZATIONS").prop("checked", $.totalStorage('df_CUSTOMIZATIONS'));
		}
	}

}