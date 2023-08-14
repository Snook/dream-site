function init_label_reporting()
{
	// restore setting
	$("#ft_zero_inv_filter, #zero_inv_filter").each(function ()
	{
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

	// set to current setting on page load
	if ($("#ft_zero_inv_filter").is(":checked"))
	{
		$("#ft_menu_items option[style*='gray']").hide();
	}
	else
	{
		$("#ft_menu_items option[style*='gray']").show();
	}

	if ($("#zero_inv_filter").is(":checked"))
	{
		$("#menu_items option[style*='gray']").hide();
	}
	else
	{
		$("#menu_items option[style*='gray']").show();
	}

	// handle clicks

	$("#ft_zero_inv_filter").on("click", function (e)
	{

		if ($(this).is(":checked"))
		{
			$("#ft_menu_items option[style*='gray']").hide();
			$.totalStorage(this.id, "1");
		}
		else
		{
			$("#ft_menu_items option[style*='gray']").show();
			$.totalStorage(this.id, "0");

		}
	});

	$("#zero_inv_filter").on("click", function (e)
	{

		if ($(this).is(":checked"))
		{
			$("#menu_items option[style*='gray']").hide();
			$.totalStorage(this.id, "1");
		}
		else
		{
			$("#menu_items option[style*='gray']").show();
			$.totalStorage(this.id, "0");
		}
	});

}

function submitIt(form)
{
	form.target = "_out";
}