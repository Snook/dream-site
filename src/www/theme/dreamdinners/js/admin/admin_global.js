function admin_global_init()
{
	// load tooltips
	data_tooltips_init();

	help_system_init();

	fadmin_idle_timeout();

	// drop down menus in fadmin
	menu_bar_init();
	// Auto load [page_name]_init() functions

	handle_inline_guest_search();

	handle_helpdesk();

	handle_tabbed_content();

	handle_accordion_content();

	// Show ajax processing indicator
	$(document).ajaxStart(function () {

		AJAX_IN_PROCESS = true;

		$('.img_throbber_circle').show();

	});

	// Stop ajax processing indicator
	$(document).ajaxStop(function () {
		AJAX_IN_PROCESS = false;

		$('.img_throbber_circle').hide();

	});

	// this monitors ajax requests so you can do something always with them
	$(document).ajaxSuccess(function (event, xhr, settings) {

		// load any data-tooltips that may have been returned in ajax content
		data_tooltips_init();

		handle_inline_guest_search();

	});

}

function click_dd_message_button(div_id, button_title)
{
	var topDiv = $("#" + div_id).parent();
	var found = false;
	topDiv.find("[type=button]").each(function () {
		var buttonText = $(this).find("span").html();
		if (buttonText == button_title)
		{
			$(this).click();
			return;
		}

		var buttonTextAttempt2 = $(this).html();
		if (buttonTextAttempt2 == button_title)
		{
			$(this).click();

		}

	});
}

function onNBCreateSingleSession()
{
	var menus_elem = document.getElementById("menus");

	var menus_clause = "";
	if (menus_elem)
	{
		var menu_id = document.getElementById("menus").value;
		menus_clause = "?menu=" + menu_id;
	}

	if (typeof selectedCell != "undefined" && selectedCell != null)
	{
		cellName = selectedCell.id;
		bounce("/backoffice/create-session?selectedCell=" + cellName);
	}
	else
	{
		bounce("/backoffice/create-session" + menus_clause);
	}
}

function data_tooltips_init()
{
	if (!IS_TOUCH_SCREEN_DEVICE)
	{
		// Load tooltips
		if ($.fn.qtip)
		{
			$('[data-tooltip]').qtip({
				content: {
					attr: 'data-tooltip'
				},
				position: {
					my: 'bottom left',
					at: 'top left',
					viewport: $(window),
					adjust: {
						method: 'shift',
						y: parseInt(0, 10) || 0,
						x: parseInt(10, 10) || 0
					}
				},
				style: {
					classes: 'ui-tooltip-dreamdinners_tt qtip-shadow'
				}
			});
		}
	}
}

function hostessDreamTasteOrder(session_id, session_text, userid)
{
	create_and_submit_form({
		action: '/?page=admin_order_mgr&user=' + userid,
		input: ({
			'session': session_id,
			'request': 'savedTasteOrder',
			'sessionText': session_text
		})
	});
}

function handle_tabbed_content()
{
	if ($('.tabbed-content').length == 0)
	{
		return;
	}
	window.tabbed_content = {};

	$('.tabbed-content').each(function (count) {

		if ($(this).data('tabid'))
		{
			if ($(this).data('tabid') in tabbed_content)
			{
				dd_console_log('data-tabid for tabbed-content must be unique.');
			}
			else
			{
				count = $(this).data('tabid');
			}
		}

		// store tabb
		var this_content = tabbed_content[count] = this;

		tabbed_content[count].tab_clicks = {};

		var tabs = $(this_content).find('.tabs');
		var tab_content = $(this_content).find('.tabs-content');

		$(tabs).find('[data-tabid]').each(function () {
			var tabid = $(this).data('tabid');

			// tab needs a close button
			if ($(this).hasClass('close'))
			{
				var confirmClass = '';

				if ($(this).hasClass('confirm'))
				{
					confirmClass = ' confirm';
				}

				$(this).append('<span class="close' + confirmClass + '" data-tooltip="Close">X</span>').find('.close').on('click', function (e) {

					// keep the outer tab click even from firing
					e.stopPropagation();

					// assign this to var to use within dd_message
					var this_close_button = this;

					// if the close button has a confirmation class then prompt
					// if it has class noconfirm skip to else, this is a one-time class to override confirm prompt
					if ($(this_close_button).hasClass('confirm') && !$(this_close_button).hasClass('noconfirm'))
					{
						dd_message({
							title: 'Confirm',
							message: 'Are you sure you wish to close this tab?',
							confirm: function () {
								// hide the tab that is closing
								$(this_close_button).parent().addClass('hidden');

								// select the first available tab
								$(this_close_button).parent().parent().children().not('.hidden, .disabled').first().trigger('click');
							},
							cancel: function () {
							}
						});
					}
					else
					{
						// has class noconfirm, remove it this is a one-time override
						$(this_close_button).removeClass('noconfirm');

						// hide the tab that is closing
						$(this_close_button).parent().addClass('hidden');

						// select the first available tab
						$(this_close_button).parent().parent().children().not('.hidden, .disabled').first().trigger('click');
					}

				});
			}

			// get the selected tab on load to select primary content
			if ($(this).hasClass('selected'))
			{
				selected_tab = this;
				$(tab_content).find('[data-tabid="' + tabid + '"]').addClass('selected');
			}

			// add content class to content divs
			if (!$(this).hasClass('content'))
			{
				$(tab_content).find('[data-tabid="' + tabid + '"]').addClass('content');
			}

			// store tab object to fire click events on
			tabbed_content[count].tab_clicks[tabid] = this;

			$(this).on('click', function () {

				// already selected, don't do anything
				if ($(this).hasClass('selected'))
				{
					return;
				}

				// disabled, don't do anything
				if ($(this).hasClass('disabled'))
				{
					return;
				}

				var exit_early = false;
				// iterate over tabs to remove selected class from current selected tab
				$(tabs).find('[data-tabid]').each(function () {

					if ($(this).hasClass('selected'))
					{

						// handle optional callback on tab being de-selected
						if ($(this).data('deselect_callback'))
						{
							var data_callback = $(this).data('deselect_callback');
							var callback = window[data_callback];

							if (callback != undefined)
							{
								// pass the clicked object to callback
								if (!callback(this))
								{
									exit_early = true;
									return false;
								}

							}
						}

						$(this).removeClass('selected');
					}

				});

				if (exit_early)
				{
					return false;
				}

				// handle optional link url
				if ($(this).data('link'))
				{
					bounce($(this).data('link'));
					return;
				}
				// iterate over content div to remove selected from current selected content
				$(tab_content).find('[data-tabid]').each(function () {

					if ($(this).hasClass('selected'))
					{
						$(this).removeClass('selected');
					}

				});

				// add selected class to clicked tab
				$(tabs).find('[data-tabid="' + tabid + '"]').addClass('selected');

				// add selected class to clicked content
				$(tab_content).find('[data-tabid="' + tabid + '"]').addClass('selected');

				// handle optional callback
				if ($(this).data('callback'))
				{
					var data_callback = $(this).data('callback');
					var callback = window[data_callback];

					if (callback != undefined)
					{
						// pass the clicked object to callback
						callback(this);
					}
				}

				// html5 update url history
				if ($(this).data('urlpush') === undefined || $(this).data('urlpush') === 'true')
				{
					if (getQueryVariable('tabs'))
					{
						var tab_sections = getQueryVariable('tabs').split(',');

						var new_query = [];
						var new_query_tabs = [];

						$.each(tab_sections, function (index, value) {

							var view_tab = value.split('.');

							if (view_tab[0] == count)
							{
								new_query.push(view_tab[0] + '.' + tabid);
							}
							else
							{
								new_query.push(view_tab[0] + '.' + view_tab[1]);
							}

							new_query_tabs[view_tab[0]] = true;

						});

						if (new_query_tabs[count] === undefined)
						{
							new_query.push(count + '.' + tabid);
						}

						new_query_string = setQueryString('tabs', new_query.toString());
						historyPush({url: new_query_string});
					}
					else
					{
						new_query_string = setQueryString('tabs', count + '.' + tabid);
						historyPush({url: new_query_string});
					}
				}

			});

		});

	});

	// select tabs based on url variable
	if (getQueryVariable('tabs'))
	{
		var tab_sections = getQueryVariable('tabs').split(',');

		$.each(tab_sections, function (index, value) {

			var view_tab = value.split('.');

			if (view_tab[0] !== undefined && view_tab[1] !== undefined)
			{
				if (!$(tabbed_content[view_tab[0]].tab_clicks[view_tab[1]]).hasClass('hidden'))
				{
					$(tabbed_content[view_tab[0]].tab_clicks[view_tab[1]]).click().mouseleave();
				}
			}

		});
	}

}

function handle_accordion_content()
{
	if ($('.accordion-content').length == 0)
	{
		return;
	}
	window.accordion_content = {};

	$('.accordion-content').each(function (count) {
		if ($(this).data('tabid'))
		{
			if ($(this).data('tabid') in accordion_content)
			{
				dd_console_log('data-tabid for accordion-content must be unique.');
			}
			else
			{
				count = $(this).data('tabid');
			}
		}

		// store tabb
		var this_content = accordion_content[count] = this;
		accordion_content[count].tab_clicks = {};
		var tabs = $(this_content).find('.tabs');

		$(tabs).find('[data-tabid]').each(function () {
			var tabid = $(this).data('tabid');

			// get the selected tab on load to select primary content
			if ($(this).hasClass('active'))
			{
				selected_tab = this;
				$(this).next().slideDown();
			}

			// store tab object to fire click events on
			accordion_content[count].tab_clicks[tabid] = this;

			$(this).on('click', function () {

				// already selected, don't do anything
				if ($(this).hasClass('active') || $(this).hasClass('disabled'))
				{
					return;
				}

				var exit_early = false;
				// iterate over tabs to remove selected class from current selected tab
				$(tabs).find('[data-tabid]').each(function () {
					if ($(this).hasClass('active'))
					{
						// handle optional callback on tab being de-selected
						if ($(this).data('deselect_callback'))
						{
							var data_callback = $(this).data('deselect_callback');
							var callback = window[data_callback];

							if (callback != undefined)
							{
								// pass the clicked object to callback
								if (!callback(this))
								{
									exit_early = true;
									return false;
								}

							}
						}

						$(this).removeClass('active');
					}

				});

				if (exit_early)
				{
					return false;
				}

				// handle optional link url
				if ($(this).data('link'))
				{
					bounce($(this).data('link'));
					return;
				}
				// iterate over content div to remove selected from current selected content
				$(tabs).find('[data-tabidcontent]').each(function () {
					if ($(this).hasClass('active'))
					{
						$(this).hide();
						$(this).removeClass('active');
					}
				});

				// add selected class to clicked tab
				$(tabs).find('[data-tabid="' + tabid + '"]').addClass('active');

				// add selected class to clicked content
				$(tabs).find('[data-tabidcontent="' + tabid + 'Content"]').addClass('active').slideDown();

				// handle optional callback
				if ($(this).data('callback'))
				{
					var data_callback = $(this).data('callback');
					var callback = window[data_callback];

					if (callback != undefined)
					{
						// pass the clicked object to callback
						callback(this);
					}
				}

				// html5 update url history
				if ($(this).data('urlpush') === undefined || $(this).data('urlpush') === 'true')
				{
					if (getQueryVariable('atabs'))
					{
						var tab_sections = getQueryVariable('atabs').split(',');

						var new_query = [];
						var new_query_tabs = [];

						$.each(tab_sections, function (index, value) {

							var view_tab = value.split('.');

							if (view_tab[0] == count)
							{
								new_query.push(view_tab[0] + '.' + tabid);
							}
							else
							{
								new_query.push(view_tab[0] + '.' + view_tab[1]);
							}

							new_query_tabs[view_tab[0]] = true;

						});

						if (new_query_tabs[count] === undefined)
						{
							new_query.push(count + '.' + tabid);
						}

						new_query_string = setQueryString('atabs', new_query.toString());
						historyPush({url: new_query_string});
					}
					else
					{
						new_query_string = setQueryString('atabs', count + '.' + tabid);
						historyPush({url: new_query_string});
					}
				}
			});
		});
	});

	// select tabs based on url variable
	if (getQueryVariable('atabs'))
	{
		var tab_sections = getQueryVariable('atabs').split(',');

		$.each(tab_sections, function (index, value) {

			var view_tab = value.split('.');

			if (view_tab[0] !== undefined && view_tab[1] !== undefined && accordion_content)
			{
				if (!$(accordion_content[view_tab[0]].tab_clicks[view_tab[1]]).hasClass('hidden'))
				{
					$(accordion_content[view_tab[0]].tab_clicks[view_tab[1]]).click().mouseleave();
				}
			}

		});
	}

}

function hideStatusMessage(type)
{
	$('#' + type).slideUp();
}

function modal_message(settings)
{
	var config = {
		title: false,
		message: false,
		size: false // small, large, extra-large
	};

	$.extend(config, settings);

	if (typeof config.confirm != 'undefined')
	{
		// convert old dd_message
		if (typeof config.confirm == 'function')
		{
			config.buttons = {
				confirm: {
					label: 'Confirm',
					className: 'btn-primary',
					callback: config.confirm
				},
				cancel: {
					label: 'Cancel',
					className: 'btn-secondary',
					callback: config.cancel
				}
			}
		}

		bootbox.dialog(config);
	}
	else if (typeof config.buttons != 'undefined')
	{
		// convert old dd_message
		var buttons = {};

		$.each(config.buttons, function (key, value) {

			if (typeof value == 'function')
			{
				buttons[key] = {
					label: key,
					className: 'btn-primary',
					callback: value
				}
			}

		});

		config.buttons = buttons;

		bootbox.dialog(config);
	}
	else
	{
		bootbox.alert(config);
	}

}

function dd_message(settings)
{
	var config = {
		resizable: false,
		modal: true,
		hide_qtips: true,
		div_id: 'dd_message'
	};

	$.extend(config, settings);

	// hide open tooltips so they aren't on top of the dialog
	if (config.hide_qtips)
	{
		if ($.fn.qtip)
		{
			$('.qtip.ui-tooltip').qtip('hide');
		}
	}

	if (config.type == 'error_msg')
	{
		// TODO: to fix this http://jsfiddle.net/XkSu9/
		//config.title = '<img src="' + PATH.image + '/icon/error.png" class="img_valign" /> <span style="color:#ffff00;">' + config.title + '</span>';
	}

	// Require message
	if (!config.message)
	{
		dd_message({message: "dd_message({message : 'requires message!' })"});
	}
	else
	{
		// Set up buttons object
		if (!config.buttons)
		{
			config.buttons = {};
		}

		// Add confirm button
		if (settings.confirm)
		{
			config.buttons.Confirm = function () {
				if (settings.canConfirm)
				{
					if (settings.canConfirm())
					{
						settings.confirm();
						// Destroy div
						$(this).remove();
					}
				}
				else
				{
					settings.confirm();
					// Destroy div
					$(this).remove();
				}
			};
		}

		// Add cancel button with callback, else if add plain cancel if confirm button has been added, else add ok button
		if (settings.cancel)
		{
			config.buttons.Cancel = function () {
				settings.cancel();
				// Destroy hidden div and dialog
				$(this).remove();
			};
		}
		else if (settings.confirm)
		{
			config.buttons.Cancel = function () {
				// Destroy hidden div and dialog
				$(this).remove();
			};
		}
		else if (!settings.noOk)
		{
			config.buttons.Ok = function () {
				// Destroy hidden div and dialog
				if (settings.closeCallback)
				{
					settings.closeCallback();
				}
				$(this).remove();
			};
		}
	}

	// Create hidden message div
	if ($('#' + config.div_id).length == 0)
	{
		var div = $('<div></div>');
		div.attr('id', config.div_id);
		div.attr('style', 'display:none;');
		$(document.body).append(div);
	}

	// Add message to hidden div
	$('#' + config.div_id).html(config.message);

	// Launch the dialog
	$('#' + config.div_id).dialog(config);
}

function handle_helpdesk()
{
	if (!$('#helpdesk', '#footerlinks').length)
	{
		$('#footerlinks').append('<div id="helpdesk">Dream Dinners Support</div>');
	}

	$('#helpdesk, #helpdesk_footer_link, #helpdesk_button, .helpdesk-popup').on('click', function (e) {

		e.preventDefault();

		var request_url = window.location.pathname + window.location.search;

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 60000,
			dataType: 'json',
			data: {
				processor: 'admin_helpdesk',
				op: 'get_helpdesk',
				request_url: request_url

			},
			success: function (json) {
				if (json.processor_success)
				{
					var dialog_contents = json.helpdesk_form;

					dd_message({
						title: 'Dream Dinners Support',
						message: dialog_contents,
						height: 620,
						width: 650,
						modal: false,
						div_id: 'helpdesk_dd_message',
						noOk: true,
						open: function (event, ui) {
							$("#form_helpdesk input[name='subject']").focus();
						},
						buttons: {
							'Support Portal': function () {
								bounce('https://support.dreamdinners.com', '_blank');
							},
							'Submit Ticket': function () {

								var validationMessages = "";

								if ($("#form_helpdesk input[name='subject']").val().trim().length == 0)
								{
									validationMessages = "Subject<br />";
								}

								if ($("#form_helpdesk textarea[name='description']").val().trim().length == 0)
								{
									validationMessages += "Description<br />";
								}

								if ($("#form_helpdesk select[name='ticket_type']").val().trim().length == 0)
								{
									validationMessages += "Ticket Type<br />";
								}

								if (validationMessages.length > 0)
								{
									var message = "Please Fill out the following fields:<br /><br />" + validationMessages;
									dd_message({
										title: 'Input error',
										message: message
									});

									return false;
								}

								var helpdesk_dd_message = $("#helpdesk_dd_message")[0];

								//	var fileElem = $("#email_attachment")[0];
								//	var files = fileElem.files;

								var ticket = $("#form_helpdesk").serialize();
								var formData = new FormData();

								//	for (var i = 0; i < files.length; i++)
								//	{
								//		var file = files[i];

								// Add the file to the request.
								//		formData.append('email_attachment', file, file.name);
								//	}

								formData.append('op', 'submit_ticket');
								formData.append('processor', 'admin_helpdesk');
								formData.append('ticket_data', ticket);
								formData.append('reporting_url', window.location.href);

								if (DEBUG_INFO)
								{
									formData.append('debugInfo', DEBUG_INFO);
								}

								$.ajax({
									url: '/processor',
									type: 'POST',
									timeout: 60000,
									cache: false,
									dataType: 'json',
									contentType: false,
									processData: false,
									data: formData,
									success: function (json) {
										if (json.processor_success)
										{
											$(helpdesk_dd_message).remove();
										}
										else
										{
											dd_message({
												title: 'Error',
												message: json.processor_message
											});
										}
									},
									error: function (objAJAXRequest, strError) {
										dd_message({
											title: 'Error',
											message: strError
										});
									}
								});

							},
							Cancel: function () {
								$(this).remove();
							}
						}
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				dd_message({
					title: 'Error',
					message: strError
				});
			}
		});

	});
}

function handle_inline_guest_search()
{
	$(document).on('click', '[data-guestsearch]', function (e) {

		if ($('#ilgs_container').length)
		{
			return;
		}

		var search_button_parameters = $(this).data();
		delete search_button_parameters['qtip']; // remove unnecessary data from being sent to processor

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 60000,
			dataType: 'json',
			data: {
				processor: 'admin_guestSearch',
				op: 'get_search_form',
				data: search_button_parameters
			},
			success: function (json) {
				if (json.processor_success)
				{
					var dialog_contents = json.search_form;

					dd_message({
						title: 'Guest Search',
						message: dialog_contents,
						modal: false,
						div_id: 'ilgs_container',
						height: 300,
						width: 600,
						closeOnEscape: false,
						noOk: true,
						close: function () {
							$('#ilgs_container').remove();
						},
						open: function (event, ui) {
							$('#ilgs_search_value').focus();

							var manualSearchTypeChange = false;

							$(document).on('change', '#ilgs_search_type', function (e) {

								manualSearchTypeChange = true;

								$('#ilgs_search_value').unmask();

								if ($('#ilgs_search_type') == 'phone')
								{
									$('#ilgs_search_value').mask('999-999-9999');
								}
								else
								{
									$('.telephone');
								}
							});

							$(document).on('keyup', '#ilgs_search_value', function (e) {

								if (e.which == 13)
								{
									$('#ilgs_search_go').trigger('click');
								}
								// user manually changed search type, don't automatically change it
								if (manualSearchTypeChange == true)
								{
									return;
								}
								var value = $('#ilgs_search_value').val();
								// not numeric entry, start with last name
								if (!$.isNumeric(value))
								{
									$('#ilgs_search_type').val('lastname');
								}
								// numeric entry, probably guest id
								if ($.isNumeric(value))
								{
									$('#ilgs_search_type').val('id');
								}
								// has @ symbol, probably email
								if (value.indexOf("@") != -1)
								{
									$('#ilgs_search_type').val('email');
								}
								// has space, probably first and last name
								if (value.indexOf(" ") != -1)
								{
									$('#ilgs_search_type').val('firstlast');
								}
							});

							$(document).on('click', '#ilgs_search_go', function (e) {

								var searchtype = $('#ilgs_search_type').val();
								var searchvalue = $('#ilgs_search_value').val();
								var store_filter = $('#ilgs_search_all').prop("checked") ? 1 : 0;
								var currentStore = $('#currentStore').val();

								$('#ilgs_results').slideUp();

								$.ajax({
									url: '/processor?processor=admin_guestSearch',
									type: 'POST',
									dataType: 'json',
									data: {
										search_type: searchtype,
										q: searchvalue,
										results_type: 'inline_search',
										store: (currentStore ? currentStore : 'none'),
										filter: store_filter,
										select_button_title: search_button_parameters.select_button_title
									},
									success: function (json) {
										if (json.result_code == 1)
										{
											$('#ilgs_results').html(json.data).slideDown();

											$(document).on('click', '[data-ilgs_result]', function (e) {

												if (typeof window[search_button_parameters.select_function] === 'function')
												{
													// this will merge in all data- from the data-guestsearch button for use when the data-select_function function is called
													$.extend(this.dataset, search_button_parameters);

													// call data-select_function and pass 'this' as variable
													window[search_button_parameters.select_function](this);

													// remove the search container
													$('#ilgs_container').remove();
												}
												else if (typeof search_button_parameters.select_function !== undefined && search_button_parameters.select_function !== false)
												{
													dd_console_log(search_button_parameters.select_function + '() specified in data-select_function, but not defined in javascript.');
												}
												else
												{
													// if there is no data-select_function then the button navigates to user_details
													bounce('/?page=admin_user_details&id=' + $(this).data('user_id'));
												}
											});
										}
										else
										{
											dd_message({
												title: 'Error',
												message: json.processor_message
											});
										}
									},
									error: function (objAJAXRequest, strError) {
										dd_message({
											title: 'Error',
											message: strError
										});
									}
								});
							});
						}
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
				handle_dashboard_update();
			}
		});
	});
}

function menu_bar_init()
{
	if ($.fn.make_dropdown)
	{
		$('#menubar > li').make_dropdown({'timeout': 250});
	}
}

function fadmin_idle_timeout()
{
	if (!USER_LOGGEDIN || !$.cookie('DreamSite_TO'))
	{
		return;
	}

	TO = $.cookie('DreamSite_TO');

	fadmin_is_idle(true);

	$(document).idle(
		function () {

			// When idle
			if (!is_idle)
			{
				fadmin_is_idle(true);
			}

		},
		function () {

			// When active again
			if (is_idle)
			{
				fadmin_is_idle(false);
			}

		},
		{
			after: 2000
		}
	);
}

function fadmin_is_idle(idle)
{
	is_idle = idle;
	ajax_login_in_view = false;
	warning_time = 120000; // 2 minute warning

	var date = new Date();
	var sdate = new Date();

	var minutes = TO;
	if (!idle)
	{
		minutes = 60;
	}
	var milliseconds = minutes * 60 * 1000;

	date.setTime(date.getTime() + (milliseconds));
	sdate.setTime(date.getTime() + (milliseconds) + 1000);
	// Note: add 1 second so that idle period expiration handler has a chance to grab the session id

	if (!idle)
	{
		dd_console_log('Not Idle ' + milliseconds);

		// cancel future alert popup
		$.doTimeout('alert_timeout_timer');

		// cancel future warning toast
		$.doTimeout('toast_timeout_warning');

		// close existing warning toast
		if (dd_toast_id_close('toast_timeout_warning'))
		{
			dd_toast({message: 'Activity has canceled the logout.'});
		}
	}
	else
	{
		dd_console_log('Idle ' + milliseconds);

		if (USER_LOGGEDIN && !ajax_login_in_view)
		{
			// start time until alert popup
			// default warning in warning_time, half timeout if timeout less than warning_time (for testing)
			warning_milliseconds = milliseconds / 2;

			if (milliseconds > warning_time)
			{
				warning_milliseconds = milliseconds - warning_time;
			}

			$.doTimeout('toast_timeout_warning', warning_milliseconds, function () {
				dd_toast({
					message: 'You will soon be logged out due to inactivity. Activity will cancel the logout.',
					delay: false,
					toast_id: 'toast_timeout_warning',
					sound: false
				});
			});

			// start time until alert popup
			$.doTimeout('alert_timeout_timer', milliseconds, function () {
				do_alert_timeout();
			});
		}
	}

	if (USER_LOGGEDIN)
	{
		$.cookie('DreamSite_Timer', true, {
			expires: date,
			domain: COOKIE.domain,
			secure: true
		});
		$.cookie(COOKIE.dd_sc_name, $.cookie(COOKIE.dd_sc_name), {
			expires: sdate,
			domain: COOKIE.domain,
			secure: true
		});
	}
}

function do_alert_timeout()
{
	if ($.cookie('DreamSite_Timer')) // cookie still exists from another tab activity, check again in 3 seconds
	{
		$.doTimeout('alert_timeout_timer', 3000, function () {
			do_alert_timeout();
		});
	}
	else // cookie expired, so do popup
	{
		alert_timeout();
	}
}

function dd_toast(settings)
{
	// delay: false; will prevent fadeout, click to remove toast
	// message: message;
	// toast_id: 'unique_id'; assign a toast id to manipulate programmically, typically to close a sticky
	// position: 'topleft', 'botleft', 'topright', 'botright', 'topcenter', 'center', 'botcenter'
	// sound: true/false, play sound
	// xclose: true/false, only X closes instead of clicking anywhere

	var config = {
		delay: 4000,
		message: false,
		toast_id: false,
		position: 'topright',
		css_class: false,
		sound: false,
		xclose: false
	};

	$.extend(config, settings);

	if (typeof toast_obj === 'undefined')
	{
		toast_obj = {};
	}

	var toast_container_selector = '.dd_toast_container.' + config.position;

	if (config.toast_id)
	{
		dd_toast_id_close(config.toast_id, true);
	}

	if (config.message)
	{
		// create container if not available
		if (!$(toast_container_selector).length)
		{
			$('<div class="dd_toast_container ' + config.position + '"></div>').appendTo('body');
		}

		// add toast to container, register click to close handler
		if (config.toast_id)
		{
			toast_obj[config.toast_id] = $('<div class="dd_toast"></div>').appendTo(toast_container_selector);

			toast = toast_obj[config.toast_id];
		}
		else
		{
			toast = $('<div class="dd_toast"></div>').appendTo(toast_container_selector);
		}

		if (config.css_class)
		{
			$(toast).addClass(config.css_class);
		}

		// add the message
		$(toast).html(config.message);

		// add close button for sticky
		if (!config.delay || config.xclose == true)
		{
			$('<div class="dd_toast_close">x</div>').on('click', function () {

				$(toast).stop().fadeOut('slow', function () {

					// fadeout animation complete
					dd_toast_cleanup(toast);

				});

			}).css({'cursor': 'pointer'}).prependTo(toast);
		}

		// close when click anywhere on div
		if (config.xclose == false)
		{
			$(toast).on('click', function () {

				$(this).stop().fadeOut('slow', function () {

					// fadeout animation complete
					dd_toast_cleanup(this);

				});

			}).css({'cursor': 'pointer'});
		}

		// play sound if true
		if (config.sound)
		{
			$('<embed src="' + PATH.script + '/customer/media/dd_toast_alert.mp3" hidden="true" autostart="true" loop="false"></embed>').appendTo(toast);
		}

		// fadein, auto close if delay is not false
		$(toast).fadeIn('fast', function () {

			// fadein animation complete
			if (config.delay)
			{
				$(this).delay(config.delay).fadeOut('slow', function () {

					// fadeout animation complete
					dd_toast_cleanup(this);

				});
			}

		});

	}
}

function dd_toast_id_close(toast_id, instant)
{
	if (typeof toast_obj !== 'undefined' && typeof toast_obj[toast_id] !== 'undefined')
	{
		if (instant == true)
		{
			dd_toast_cleanup($(toast_obj[toast_id]));

			delete toast_obj[toast_id];
		}
		else // fadeout
		{
			$(toast_obj[toast_id]).stop().fadeOut('slow', function () {
				// fadeout animation complete
				dd_toast_cleanup(this);

				delete toast_obj[toast_id];
			});
		}

		return true;
	}

	return false;
}

function dd_toast_exists(toast_id)
{
	if (typeof toast_obj !== 'undefined' && typeof toast_obj[toast_id] !== 'undefined')
	{
		return true;
	}

	return false;
}

function dd_toast_cleanup(elem)
{
	// cleanup, remove from dom
	$(elem).remove();

	// if all toasts are closed, remove container
	$('.dd_toast_container').each(function () {
		if ($(this).is(':empty'))
		{
			$(this).remove();
		}
	});
}

function alert_timeout()
{
	USER_LOGGEDIN = false;
	ajax_login_in_view = true;

	dd_toast_id_close('toast_timeout_warning');
	// hide elements that should be hidden on logout
	$('[data-fadmin_perm]').each(function () {
		if ($(this).is(":visible"))
		{
			$(this).attr('data-hide_on_logout', true).hide();
		}
	});

	// invalidate DreamSite cookie
	date = new Date();
	date.setDate(date.getDate() - 1);

	var last_session_id = $.cookie(COOKIE.dd_sc_name);

	$.cookie(COOKIE.dd_sc_name, false, {
		expires: date,
		domain: COOKIE.domain
	});

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 60000,
		dataType: 'json',
		data: {
			processor: 'login',
			op: 'get_login_form'
		},
		success: function (json) {
			if (json.processor_success)
			{
				var dialog_contents = json.login_form;

				dd_message({
					title: 'Session has expired due to inactivity',
					message: dialog_contents,
					height: 300,
					width: 300,
					div_id: 'to_login_div',
					closeOnEscape: false,
					noOk: true,
					open: function (event, ui) {

						var buttons = $(this).dialog("option", "buttons");

						var acceptFunction = buttons.Accept;

						//hide close button.
						$(this).parent().find('.ui-dialog-titlebar-close').hide();

						// focus password field
						$('#fadmin_password_login').focus();

						// register keyup on password field, if enter submit form
						$('#fadmin_password_login').keyup(function (e) {

							if (e.keyCode == $.ui.keyCode.ENTER)
							{

								acceptFunction();

							}

						});

						$('.ui-widget-overlay').css({
							'background': '#462200 url("' + PATH.image_admin + '/fadmin_background.jpg")',
							'opacity': '1',
							'filter': 'none'
						});

					},
					buttons: {
						Accept: function () {

							var login_ui = $("#to_login_div")[0];
							$("#to_login_div").find('#error_message').html('');

							$.ajax({
								url: '/processor',
								type: 'POST',
								timeout: 20000,
								dataType: 'json',
								data: {
									processor: 'login',
									primary_email_login: $("#to_login_div").find('#fadmin_primary_email_login').val(),
									password_login: $("#to_login_div").find('#fadmin_password_login').val(),
									nobo: 1,
									submit_login: 1,
									remember_login: 1,
									last_session_id: last_session_id
								},
								success: function (json) {
									if (json.processor_message)
									{
										$("#to_login_div").find('#error_message').html(json.processor_message);
									}

									if (json.processor_success)
									{
										if (json.user_type == 'CUSTOMER')
										{
											bounce('/');
										}
										else
										{
											$(login_ui).remove();
											ajax_login_in_view = false;
											USER_LOGGEDIN = true;

											$('#fadmin_username').html(json.firstname + ' ' + json.lastname);

											$('#fadmin_usertype').html(json.user_type_text);

											// find elements that were hidden on logout and show them if user_type permits
											$('[data-fadmin_perm][data-hide_on_logout]').each(function () {

												perm_array = $(this).data('fadmin_perm').split(',');

												if (~$.inArray(json.user_type, perm_array))
												{
													$(this).removeProp('data-hide_on_logout').show();
												}

											});

											$.cookie('DreamSite_Timer', true, {
												expires: 1,
												domain: COOKIE.domain,
												secure: true
											});

											fadmin_is_idle(false);
										}
									}
								},
								error: function (objAJAXRequest, strError) {
									response = 'Unexpected error';
								}

							});

						},
						Cancel: function () {
							window.location.reload();
						}
					}
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';

			handle_dashboard_update();
		}
	});
}

function displayModalWaitDialog(div_id, message)
{
	var processWindowContent = '<span style="font-weight:bold;">' + message + '</span>&nbsp;&nbsp;';
	processWindowContent += '<img src="' + PATH.image + '/style/throbber_circle.gif" class="img_valign" alt="Processing" />';

	dd_message({
		title: 'Processing',
		message: processWindowContent,
		noOk: true,
		closeOnEscape: false,
		dialogClass: "noclose",
		modal: true,
		width: 500,
		height: 90,
		resizable: false,
		div_id: div_id
	});
}

function set_platepoints_progress_bar(new_value, animate)
{
	if (!animate)
	{
		$('#pp_progressbar').progressbar({
			value: new_value,
			change: function () {
				$('#pp_progresslabel').text(new_value + '%');
			}
		});
	}
	else
	{
		$.doTimeout('platepoints_progress', 2, function () {

			var current_value = $('#pp_progressbar').progressbar('option', 'value');

			if (isNaN(current_value))
			{
				current_value = 0;
			}

			if (current_value < 50)
			{
				$('#pp_progresslabel').addClass('dark');
			}
			else
			{
				$('#pp_progresslabel').removeClass('dark');
			}

			if (new_value > current_value)
			{
				increase = true;
				value_count = current_value + 1;
			}
			else
			{
				increase = false;
				value_count = current_value - 1;
			}

			if (increase)
			{
				if (value_count > new_value)
				{
					return false;
				}
				else
				{
					$('#pp_progressbar').progressbar({
						value: value_count,
						change: function () {
							$('#pp_progresslabel').text($('#pp_progressbar').progressbar('value') + '%');
						}
					});
					return true;
				}
			}
			else
			{
				if (value_count < new_value)
				{
					return false;
				}
				else
				{
					$('#pp_progressbar').progressbar({
						value: value_count,
						change: function () {
							$('#pp_progresslabel').text($('#pp_progressbar').progressbar('value') + '%');
						}
					});
					return true;
				}
			}

		});
	}
}

function help_system_init()
{
	$('[data-help]').each(function () {
		$(this).addClass("help_enabled");

		var indexStr = $(this).attr('data-help');
		var index = indexStr.split("-");

		var thisHelpData = ddHelpData;
		for (var x = 0; x < index.length; x++)
		{
			thisHelpData = thisHelpData[index[x]];
		}

		// Load tooltips
		if ($.fn.qtip && typeof thisHelpData != 'undefined')
		{
			$(this).qtip({
				show: {
					solo: true
				},
				content: {
					text: "<div class='help_tip_title'>" + thisHelpData.title + "</div><div class='help_tip_text'>" + thisHelpData.help_text + "</div><div class='help_tip_tech_text'>" + thisHelpData.tech_text + "</div>"
				},
				position: {
					my: 'bottom left',
					at: 'top left',
					viewport: $(window),
					adjust: {
						method: 'flip',
						y: parseInt(0, 10) || 0,
						x: parseInt(10, 10) || 0
					}
				},
				style: {
					classes: 'ui-tooltip-dreamdinners_tt ui-tooltip-shadow'
				}
			});
		}

	});

	/*
	 $('[data-help]').on('click', function () {

	 var indexStr = $(this).attr('data-help');
	 var index = indexStr.split("-");

	 var thisHelpData = ddHelpData;
	 for (var x = 0; x < index.length; x++)
	 {
	 thisHelpData = thisHelpData[index[x]];
	 }


	 dd_message({
	 title: 'Help',
	 message: thisHelpData,
	 modal: false
	 });

	 });
	 */
}

//Legacy function, needs replacement
function NewWindowScroll(mypage, myname, w, h)
{
	var LeftPosition = (screen.width) ? Math.floor((screen.width - w) / 2) : 0;
	var TopPosition = (screen.height) ? Math.floor((screen.height - h) / 2) : 0;
	var settings = 'height=' + h + ',width=' + w + ',top=' + TopPosition + ',left=' + LeftPosition + ',status=no,toolbar=no,scrollbars=1,resizable=1';
	window.open(mypage, myname, settings);
}

$(function () {

	$(document).on('click', '#menu-toggle', function (e) {
		e.preventDefault();
		$("#wrapper").toggleClass("toggled");
	});

	// prevent mouse wheel from changing number fields
	$(document).on('focus', 'input[type=number]', function (e) {
		$(this).on('wheel.disableScroll', function (e) {
			e.preventDefault()
		})
	});
	$(document).on('blur', 'input[type=number]', function (e) {
		$(this).off('wheel.disableScroll')
	});

	$(document).on('click', '[data-confirm-nav]', function (e) {
		e.preventDefault();
		var href = this.href;
		var message = $(this).data('confirm-nav');
		bootbox.confirm({
			message: message,
			callback: function (result) {
				if (result)
				{
					window.location = href;
				}
			}
		});
	});

	$(document).on('click', '[data-multi_store_select_id], [data-multi_store_select_filter]', function (e) {

		e.preventDefault();

		// prevent multiple clicks
		if ($(this).hasClass('disabled'))
		{
			return;
		}

		// check if they are using the form filters, otherwise they are requesting the form below
		if ($(this).data('multi_store_select_filter'))
		{
			switch ($(this).data('multi_store_select_filter'))
			{
				case 'all':
					$("#multi_store_select_form input[type=checkbox]").prop('checked', true);
					break;
				case 'none':
					$("#multi_store_select_form input[type=checkbox]").prop('checked', false);
					break;
				case 'active-all':
					$('#multi_store_select_form [data-store_type="FRANCHISE"][data-active="1"]').prop('checked', true);
					break;
				case 'active-corporate':
					$('#multi_store_select_form [data-store_type="FRANCHISE"][data-active="1"][data-franchise_id="220"]').prop('checked', true);
					break;
				case 'active-non-corporate':
					$('#multi_store_select_form [data-store_type="FRANCHISE"][data-active="1"][data-franchise_id!="220"]').prop('checked', true);
					break;
				case 'inactive-all':
					$('#multi_store_select_form [data-store_type="FRANCHISE"][data-active="0"]').prop('checked', true);
					break;
				case 'inactive-corporate':
					$('#multi_store_select_form [data-store_type="FRANCHISE"][data-active="0"][data-franchise_id="220"]').prop('checked', true);
					break;
				case 'inactive-non-corporate':
					$('#multi_store_select_form [data-store_type="FRANCHISE"][data-active="0"][data-franchise_id!="220"]').prop('checked', true);
					break;
				case 'dist-all':
					$('#multi_store_select_form [data-store_type="DISTRIBUTION_CENTER"]').prop('checked', true);
					break;
				case 'dist-none':
					$('#multi_store_select_form [data-store_type="DISTRIBUTION_CENTER"]').prop('checked', false);
					break;
			}

			return;
		}

		let multi_store_select_id = $(this).data('multi_store_select_id');

		let this_button = $(this);

		// get currently set ids
		let store_ids = $('#' + multi_store_select_id).val();

		$(this_button).addClass('disabled');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_helpers',
				op: 'multi_store_select',
				store_id: store_ids
			},
			success: function (json) {
				if (json.processor_success)
				{
					bootbox.confirm({
						message: json.html,
						scrollable: true,
						callback: function (result) {
							if (result)
							{
								let form = $('#multi_store_select_form');
								let checked = form.serializeArray();

								let store_ids = [];
								$.each(checked, function (index, value) {
									store_ids.push(value.value);
								});

								$('#' + multi_store_select_id).val(store_ids.join());

								$('#' + multi_store_select_id + '-count').text(checked.length);
							}

							$(this_button).removeClass('disabled');
						}
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				strError = 'Unexpected error';
			}
		});

	});

	$(document).on('click', '[data-user_id_pp_tooltip]', function (e) {

		e.preventDefault();

		var user_id = $(this).data('user_id_pp_tooltip');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_fadmin_home',
				op: 'platepoints_tooltip',
				user_id: user_id
			},
			success: function (json, status) {
				if (json.processor_success)
				{
					bootbox.dialog({
						message: json.tooltip_html,
						buttons: {
							manage: {
								label: 'Manage',
								className: 'btn-success',
								callback: function () {
									bounce('/?page=admin_user_plate_points&id=' + user_id + '&back=' + back_path());
								}
							},
							close: {
								label: 'Close',
								className: 'btn-success'
							}
						}
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});

	});

	$(document).on('click', '.link-dinner-details', function (e) {

		e.preventDefault();

		var menu_id = $(this).data('menu_id');
		var menu_item_id = $(this).data('menu_item_id');
		var store_id = $(this).data('store_id');
		var target = $(this).prop('target');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'menu_item',
				op: 'find_item',
				menu_id: menu_id,
				menu_item_id: menu_item_id,
				store_id: store_id,
				detailed: true
			},
			success: function (json) {
				if (json.processor_success)
				{
					bootbox.dialog({
						message: json.html,
						size: 'large',
						buttons: {
							"Full details": function () {
								bounce('?page=item&recipe=' + json.recipe_id + '&ov_menu=' + json.menu_id, target);
							},
							cancel: {
								label: "Close"
							}
						}
					})
				}
				else
				{
					dd_message({
						title: 'Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});

	});

	$(document).on('click', '.backoffice_change_store:not(.disabled)', function (e) {

		e.preventDefault();

		let backoffice_change_store = {
			'back': back_path(),
			'dialog': null
		}

		// create the modal ui with bootbox
		backoffice_change_store.dialog = bootbox.dialog({
			message: '<p><i class="fa fa-spin fa-spinner"></i> Loading store selector...</p>',
			size: 'large',
			centerVertical: true,
			closeButton: false,
			buttons: {
				ok: {
					label: "Select store",
					className: 'btn-primary backoffice_change_store-confirm disabled',
					callback: function () {
						let store = $('#change_store-selector').val();
						let store_text = $('#change_store-selector option:selected').text();

						backoffice_change_store.dialog.find('.bootbox-body').html('<p><i class="fa fa-spin fa-spinner"></i> Loading ' + store_text + '</p>');
						backoffice_change_store.dialog.find('.modal-footer > .backoffice_change_store-confirm').addClass('disabled');

						$.ajax({
							url: '/processor',
							type: 'POST',
							dataType: 'json',
							timeout: 60000,
							data: {
								processor: 'admin_helpers',
								op: 'store_selector',
								do: 'selector_select',
								store: store,
								back: backoffice_change_store.back
							},
							success: function (json) {
								if (json.processor_success)
								{
									bounce(decodeURIComponent(backoffice_change_store.back));
								}
								else
								{
									backoffice_change_store.dialog.find('.bootbox-body').html('<p>' + json.processor_message + '</p>');
								}
							},
							error: function (objAJAXRequest, strError) {
								backoffice_change_store.dialog.find('.bootbox-body').html('<p>Unexpected error: ' + strError + '</p>');
							}
						});

						return false;
					}
				},
				cancel: {
					label: "Cancel",
					className: 'btn-danger',
					callback: function () {
						return true;
					}
				}
			},
			onShow: function (e) {
				// load the menu items
				$.ajax({
					url: '/processor',
					type: 'POST',
					dataType: 'json',
					timeout: 60000,
					data: {
						processor: 'admin_helpers',
						op: 'store_selector',
						do: 'selector_get'
					},
					success: function (json) {
						if (json.processor_success)
						{
							backoffice_change_store.dialog.find('.bootbox-body').html(json.form);
							backoffice_change_store.dialog.find('.modal-footer > .backoffice_change_store-confirm').removeClass('disabled');
						}
						else
						{
							backoffice_change_store.dialog.find('.bootbox-body').html('<p>' + json.processor_message + '</p>');
						}
					},
					error: function (objAJAXRequest, strError) {
						backoffice_change_store.dialog.find('.bootbox-body').html('<p>Unexpected error: ' + strError + '</p>');
					}
				});
			}
		});

		$(backoffice_change_store.dialog).on('change', '#change_store-selector', function (e) {
			$('.backoffice_change_store-confirm').trigger('click');
		});

	});

	if (getQueryVariable('tab'))
	{
		var tabid = getQueryVariable('tab');

		$('#' + tabid + '-tab').tab('show');
	}

	/* Handle Bootstrap Tabbed Content */
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		e.target; // newly activated tab
		e.relatedTarget; // previous active tab

		// html5 update url history
		if ($(e.target).data('urlpush') !== undefined && $(e.target).data('urlpush') === true)
		{
			if ($(e.target).data('target') !== undefined)
			{
				var target = $(e.target).data('target');
			}
			else
			{
				var target = $(e.target).attr('href');
			}

			var tabid = $(target).attr('id');

			new_query_string = setQueryString('tab', tabid);
			historyPush({url: new_query_string});
		}
	});

});