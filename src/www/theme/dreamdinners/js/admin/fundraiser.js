function fundraiser_init()
{
	create_fundraiser_button();
	handle_fundraiser_edit();
	handle_detailed_reporting();
}

function handle_detailed_reporting()
{
	$('#show_detailed_reporting').on('click', function () {

		$('#detailed_reporting').slideToggle();

	});
}

function create_fundraiser_button()
{
	$('#add_fundraiser').on('click', function ()
	{
		dd_message({
			div_id: 'fundraiser_add',
			title: 'Add Fundraiser',
			message: $('#add_fundraiser_content').html(),
			noOk: true,
			height: '300',
			open: function (event, ui)
			{
				var this_form = $('#fundraiser_add');

				$(this_form).find('[name="fund_submit"]').on('click', function ()
				{
					var context = {
						title: $(this_form).find('[name="new_fundraiser"]').val(),
						description: $(this_form).find('[name="new_fundraiser_desc"]').val(),
						value: parseFloat($(this_form).find('[name="new_fundraiser_value"]').val()).toFixed(2)
					};

					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_fundraiser',
							store_id: STORE_DETAILS.id,
							op: 'add_fund_info',
							data: context
						},
						success: function (json, status)
						{
							if (json.processor_success)
							{
								bounce('?page=admin_fundraiser');

								$(this_form).remove();
							}
							else
							{

							}
						},
						error: function (objAJAXRequest, strError)
						{
							response = 'Unexpected error';
						}
					});

				});
			}
		});

	});
}

function handle_fundraiser_edit()
{
	$('[data-fund_id_edit]').each(function ()
	{

		$(this).off('click').on('click', function ()
		{
			var fund_id = $(this).data('fund_id_edit');

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_fundraiser',
					fund_id: fund_id,
					store_id: STORE_DETAILS.id,
					op: 'get_fund_info'
				},
				success: function (json, status)
				{
					if (json.processor_success)
					{
						var data = $.parseJSON(json.data);

						dd_message({
							div_id: 'fundraiser_add',
							title: 'Add Fundraiser',
							message: $('#add_fundraiser_content').html(),
							noOk: true,
							height: '300',
							open: function (event, ui)
							{
								var this_form = $('#fundraiser_add');

								$(this_form).find('[name="new_fundraiser"]').val(data.fundraiser_name);
								$(this_form).find('[name="new_fundraiser_desc"]').val(data.fundraiser_description);
								$(this_form).find('[name="new_fundraiser_value"]').val(parseFloat(data.donation_value).toFixed(2));

								$(this_form).find('[name="fund_submit"]').on('click', function ()
								{
									var context = {
										fund_id: data.id,
										title: $(this_form).find('[name="new_fundraiser"]').val(),
										description: $(this_form).find('[name="new_fundraiser_desc"]').val(),
										value: parseFloat($(this_form).find('[name="new_fundraiser_value"]').val()).toFixed(2)
									};

									$.ajax({
										url: 'ddproc.php',
										type: 'POST',
										timeout: 20000,
										dataType: 'json',
										data: {
											processor: 'admin_fundraiser',
											store_id: STORE_DETAILS.id,
											op: 'edit_fund_info',
											data: context
										},
										success: function (json, status)
										{
											if (json.processor_success)
											{
												location.reload();

												$(this_form).remove();
											}
											else
											{

											}
										},
										error: function (objAJAXRequest, strError)
										{
											response = 'Unexpected error';
										}
									});

								});
							}

						});
					}
					else
					{

					}
				},
				error: function (objAJAXRequest, strError)
				{
					response = 'Unexpected error';
				}
			});

		});

	});

	$('[data-enable_fund]').each(function ()
	{
		$(this).off('click').on('click', function ()
		{
			var fund_id = $(this).data('enable_fund');

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_fundraiser',
					store_id: STORE_DETAILS.id,
					op: 'toggle_fundraiser',
					fund_id: fund_id
				},
				success: function (json, status)
				{
					if (json.processor_success)
					{
					}
					else
					{
					}
				},
				error: function (objAJAXRequest, strError)
				{
					response = 'Unexpected error';
				}
			});

		});

	});
}