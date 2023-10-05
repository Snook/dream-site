function food_testing_init()
{
	create_survey_button_handler();
	add_stores_button_handler();
	add_files_button_handler();
	handle_category_toggle();
	store_paid_hander();
	download_file_handler();
	close_survey_hander();

	$('[id^="export_store_report-"]').on('click', function ()
	{

		id = this.id.split("-")[1];

		bounce('/backoffice/food-testing?export=xlsx&export_store=' + id);
	});

	$('[id^="export_guest_report-"]').on('click', function ()
	{

		id = this.id.split("-")[1];

		bounce('/backoffice/food-testing?export=xlsx&export_guest=' + id);
	});
}

function food_testing_survey_init()
{
	handle_category_toggle();
	add_guests_button_handler();
	guest_received_button_handler();
	download_file_handler();
	handler_delete_guest();

	$('[id^="recipe_survey_store-"]').on('click', function ()
	{

		id = this.id.split("-")[1];

		bounce('/backoffice/food-testing-survey-store?recipe=' + id);

	});
}

function food_testing_survey_store_init()
{
	$('[id^="question-"]').bind("keyup change click", function (e)
	{

		check_survey_completed();

	});
}

function check_survey_completed()
{
	form_completed = true;

	$('input[type=radio][id^="question-"]').each(function (e)
	{

		if (!$("input[name='" + this.name + "']").is(':checked'))
		{
			form_completed = false;


		}

	});

	$('textarea[id^="question-"]').each(function (e)
	{

		if ($(this).val().trim().length < 1)
		{
			form_completed = false;


		}

	});

	if (form_completed)
	{
		$('#submit_my_survey').attr('disabled', false);
		$('#submit_my_survey_note').hide();
	}
	else
	{
		$('#submit_my_survey').attr('disabled', true);
		$('#submit_my_survey_note').show();
	}

}

function guest_received_button_handler()
{
	$('[id^="size_select_submit-"]').on('click', function ()
	{

		id = this.id.split("-")[1];

		serving_size = $('#size_select-' + id + ' option:selected').val();

		if (serving_size == 0)
		{
			dd_message({message: 'Please select the entree size the guest received.'});
			return;
		}

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_food_testing',
				op: 'do_entree_receive',
				survey_id: id,
				serving_size: serving_size
			},
			success: function (json)
			{
				if (json.processor_success)
				{
					$('#size_select_td-' + json.survey_id).html(json.timestamp_received);
				}
			},
			error: function (objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}
		});

	});
}

function handler_delete_guest()
{
	$('[id^="delete_guest-"]').on('click', function ()
	{

		var survey_id = $(this).data('survey_submission_id');

		dd_message({
			title: 'Confirm',
			message: 'Are you sure you wish to delete this guest? The guest has already been emailed testing instructions for this meal.',
			noOk: true,
			modal: true,
			buttons: {
				'Confirm': function ()
				{
					$.ajax({
						url: '/processor',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_food_testing',
							op: 'do_delete_guest',
							survey_id: survey_id
						},
						success: function (json)
						{
							if (json.processor_success)
							{
								$('#survey_row-' + survey_id).remove();
							}
						},
						error: function (objAJAXRequest, strError)
						{
							response = 'Unexpected error';
						}
					});

					$(this).remove();

				},
				'Cancel': function ()
				{
					$(this).remove();
				}
			}
		});

	});
}

function close_survey_hander()
{
	$('[id^="survey_closed-"]').on('click', function ()
	{
		id = $(this).data("survey_id");
		is_closed = ($(this).is(':checked') ? 'true' : 'false');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_food_testing',
				op: 'do_close_survey',
				survey_id: id,
				is_closed: is_closed
			},
			success: function (json)
			{
				if (json.processor_success)
				{
					if (json.is_closed == '1')
					{
						$('#add_stores-' + json.survey_id).addClass('disabled').off();
						$('#add_files-' + json.survey_id).addClass('disabled').off();
					}
					else
					{
						$('#add_stores-' + json.survey_id).removeClass('disabled');
						$('#add_files-' + json.survey_id).removeClass('disabled');

						add_stores_button_handler();
						add_files_button_handler();
					}
				}
			},
			error: function (objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}
		});

	});
}

function store_paid_hander()
{
	$('[id^="mark_paid-"]').on('click', function ()
	{
		id = this.id.split("-")[1];

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_food_testing',
				op: 'do_store_paid',
				survey_id: id
			},
			success: function (json)
			{
				if (json.processor_success)
				{
					$('#store_paid_td-' + json.survey_id).html(json.timestamp_paid);
				}
			},
			error: function (objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}
		});

	});

	$('[id^="received_w9-"]').on('click', function ()
	{

		id = this.id.split("-")[1];

		store_id = $(this).data('store_w9_id');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_food_testing',
				op: 'do_received_w9',
				store_id: store_id,
				survey_id: id
			},
			success: function (json)
			{
				if (json.processor_success)
				{
					$("[data-store_w9_id='" + json.store_id + "']").hide();
					$("[data-store_paid_id='" + json.store_id + "']").show();
				}
			},
			error: function (objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}
		});

	});
}

function add_guests_button_handler()
{
	$('[id^="add_guests-"]').on('click', function ()
	{

		id = this.id.split("-")[1];

		$('#add_guest_survey_id').val(id);

		dd_message({
			title: 'Add Staff',
			message: $('#add_guests_content').html(),
			noOk: true,
			height: '300'
		});

	});

}

function download_file_handler()
{
	$('[id^="download_files-"]').on('click', function ()
	{

		id = $(this).data('file_id');

		bounce('/backoffice/food-testing-survey?recipe_files=' + id);

	});
}

function handle_category_toggle()
{
	$('[id^="recipe_row-"]').css('cursor', 'pointer').on('click', function (e)
	{

		current_open = false;

		id = this.id.split("-")[1];

		if ($("#recipe_row_disc-" + id).hasClass('disc_open'))
		{
			current_open = true;
		}

		$('[id^="recipe_surveys-"]').hide();
		$('[id^="recipe_row_disc-"]').removeClass('disc_open');

		if (!current_open)
		{
			$("#recipe_surveys-" + id).show();

			$("#recipe_row_disc-" + id).addClass('disc_open');

			historyPush({url: '?recipe=' + id});
		}
		else
		{
			if (getQueryVariable('page'))
			{
				historyPush({url: '?page=' + getQueryVariable('page')});
			}
		}

		e.preventDefault();

	});

	if (getQueryVariable('recipe'))
	{
		id = getQueryVariable('recipe');

		$("#recipe_row-" + id).trigger("click");
	}
}

function create_survey_button_handler()
{
	$('#create_survey').on('click', function ()
	{

		dd_message({
			title: 'Add Recipe',
			message: $('#create_survey_content').html(),
			noOk: true,
			height: '300'
		});

	});


	$('#download_reports').on('click', function ()
	{

		dd_message({
			title: 'Choose Report',
			message: '<div><span id="export_all_store_open" class="button">Open Store Surveys</span> <span id="export_all_guest_open" class="button">Open Guest Surveys</span></div><div><span id="export_all_store_closed" class="button">Closed Store Surveys</span> <span id="export_all_guest_closed" class="button">Closed Guest Surveys</span></div>',
			noOk: true,
			width: '320',
			open: function () {

				$('#export_all_store_open').on('click', function () {

					bounce('/backoffice/food-testing?export=xlsx&export_store=all');

				});

				$('#export_all_store_closed').on('click', function () {

					bounce('/backoffice/food-testing?export=xlsx&export_store=all&export_closed=true');

				});

				$('#export_all_guest_open').on('click', function () {

					bounce('/backoffice/food-testing?export=xlsx&export_guest=all');

				});

				$('#export_all_guest_closed').on('click', function () {

					bounce('/backoffice/food-testing?export=xlsx&export_guest=all&export_closed=true');

				});

			}
		});

	});
}

function add_files_button_handler()
{
	$('[id^="add_files-"]').on('click', function ()
	{
		if ($(this).hasClass('disabled'))
		{
			return;
		}

		id = this.id.split("-")[1];

		$('#add_file_survey_id').val(id);

		dd_message({
			title: 'Add Files',
			message: $('#add_survey_file_content').html(),
			noOk: true,
			height: '150'
		});

	});

}

function add_stores_button_handler()
{
	$('[id^="add_stores-"]').on('click', function ()
	{
		if ($(this).hasClass('disabled'))
		{
			return;
		}

		id = this.id.split("-")[1];

		$('#add_store_survey_id').val(id);

		dd_message({
			title: 'Add Stores',
			message: $('#add_stores_content').html(),
			noOk: true,
			height: '300'
		});

	});

}