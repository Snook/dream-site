var selected_session_id, view_session, manualSearchTypeChange, manualOrderSearchTypeChange;
var init_go_to_today = false;
var suppress_fastlane_labels = false;

function main_init()
{
	handle_agenda_click();

	handle_agenda_month_select();

	handle_session_tools();

	handle_go_to_today();

	handle_guest_search();

	handle_preselection();

	handle_order_details_table();

	init_looping_timers();
}

function init_looping_timers()
{
	// refreshes dashboard snapshot
	start_dashboard_snapshot_timer();

	// greys out agenda days and session as the expire
	start_unix_expiry_timer();
}

function register_booking_click_handlers()
{
	// make it so you can highlight a guest row on click
	handle_booking_row_click();

	// register guest click handlers
	// no show checkbox
	handle_booking_no_show();

	// handle platepoints gift tools
	handle_platepoints_gifts();

	// handle food testing recipe assignment
	handle_food_testing_recipe();

	// handle opted out users
	handle_pp_opted_out();

	// handle showing order details
	handle_order_details_table();

	// handle delayed payment tc
	handle_delayed_payment_tc();

	// handle session rsvp functions
	handle_session_rsvp();

	//ShipStation Calls
	handle_resend_delivered_to_shipstation();
	handle_fetch_tracking_from_shipstation()
}

function handle_resend_delivered_to_shipstation()
{
	$('.handle-resend-shipstation').each(function () {
		$(this).on('click', function (event) {
			event.preventDefault();
			var orderId = $(this).data('order_id');

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 60000,
				dataType: 'json',
				data: {
					processor: 'admin_shipstation_manager',
					op: 'resend-order',
					order: orderId
				},
				success: function (json) {
					if (json.processor_success)
					{
					}
					else
					{
						dd_toast({'message': 'There was a problem sending to ShipStation.'});
					}
				},
				error: function (objAJAXRequest, strError) {

				}
			});

		});
	});
}

function handle_fetch_tracking_from_shipstation()
{
	$('.handle-fetch-tracking-number').each(function () {
		$(this).on('click', function (event) {
			event.preventDefault();
			var orderId = $(this).data('order_id');

			var control = $(this);
			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 60000,
				dataType: 'json',
				data: {
					processor: 'admin_shipstation_manager',
					op: 'fetch-tracking-number',
					order: orderId
				},
				success: function (json) {
					if (json.processor_success)
					{
						if (json.processor_tracking_number != null && typeof json.processor_tracking_number != 'undefined' && json.processor_tracking_number !== '' && json.processor_tracking_number !== 'null')
						{
							control.html(json.processor_tracking_number);
						}
					}
					else
					{
						dd_toast({'message': 'There was a problem updating the tracking number from ShipStation.'});
					}
				},
				error: function (objAJAXRequest, strError) {

				}
			});

		});
	});
}

function handle_session_rsvp()
{
	$('.add_session_rsvp_guest_create').each(function () {
		$(this).on('click', function (e) {
			var rsvp_session_id = $(this).data('session_id');

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 60000,
				dataType: 'json',
				data: {
					processor: 'admin_session_rsvp',
					op: 'get_form_add_guest'
				},
				success: function (json) {
					if (json.processor_success)
					{
						dd_message({
							title: 'Add RSVP',
							message: json.form_add_guest,
							height: 400,
							width: 300,
							div_id: 'add_guest_div',
							closeOnEscape: false,
							noOk: true,
							open: function (event, ui) {
								$('.telephone').mask('999-999-9999');

								// get button objects
								var buttons = $(this).dialog("option", "buttons");

								// assign accept button function to variable
								var acceptFunction = buttons.Accept;

								//hide close button.
								$(this).parent().find('.ui-dialog-titlebar-close').hide();

								// register keyup on password field, if enter submit form
								$('#add_guest_password_login').keyup(function (e) {
									if (e.keyCode == $.ui.keyCode.ENTER)
									{
										acceptFunction();
									}

								});

							},
							buttons: {
								Accept: function () {
									var add_guest_ui = $("#add_guest_div")[0];
									$("#add_guest_div").find('#error_message').html('');

									if (_check_form($('#form_add_guest')[0]))
									{
										$.ajax({
											url: 'ddproc.php',
											type: 'POST',
											timeout: 20000,
											dataType: 'json',
											data: {
												processor: 'admin_session_rsvp',
												op: 'rsvp_dream_taste',
												primary_email_login: $("#add_guest_div").find('#add_guest_primary_email_login').val(),
												password_login: $("#add_guest_div").find('#add_guest_password_login').val(),
												firstname: $("#add_guest_div").find('#add_guest_firstname').val(),
												lastname: $("#add_guest_div").find('#add_guest_lastname').val(),
												telephone_1: $("#add_guest_div").find('#add_guest_telephone_1').val(),
												rsvp_dream_taste: rsvp_session_id
											},
											success: function (json) {
												if (json.processor_success)
												{
													$(add_guest_ui).remove();

													handle_preselection();
												}
												else
												{
													dd_message({
														message: json.processor_message
													});
												}
											},
											error: function (objAJAXRequest, strError) {
												response = 'Unexpected error';
											}

										});
									}

								},
								Cancel: function () {
									$(this).remove();
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

		});
	});

	$('.cancel_session_rsvp').each(function () {
		$(this).on('click', function (e) {
			var user_id = $(this).data('user_id');
			var session_id = $(this).data('session_id');

			dd_message({
				title: 'Confirmation',
				message: 'Are you sure you wish to cancel this RSVP?',
				modal: true,
				confirm: function () {
					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_session_rsvp',
							user_id: user_id,
							session_id: session_id,
							op: 'delete_rsvp'
						},
						success: function (json, status) {
							if (json.processor_success)
							{
								$('#session_rsvp-' + session_id + '-' + user_id).remove();

								dd_toast({'message': 'RSVP has been canceled.'});
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
				}
			});

		});

	});

}

function handle_session_rsvp_guest_find(guest)
{
	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_session_rsvp',
			user_id: $(guest).data('user_id'),
			session_id: $(guest).data('session_id'),
			op: 'add_rsvp'
		},
		success: function (json, status) {
			if (json.processor_success)
			{
				dd_toast({'message': 'RSVP has been added.'});

				handle_preselection();
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
}

function handle_order_details_table()
{
	$('[data-view_order_details]').each(function () {

		// handle view order details
		$(this).on('click', function (e) {

			e.preventDefault();

			var booking_id = $(this).data('booking_id');
			var order_id = $(this).data('order_id');

			// hide existing open order
			$('.order_details_tbody').hide();

			// show this order
			$('#order_details_tbody_id_' + booking_id).show();

			// scroll to guest details
			$('html, body').animate({

				scrollTop: $('#guest_details_tbody_id_' + booking_id).offset().top - 40

			}, 500);

			// cancel scroll if user manually interrupts the scroll
			$('html, body').on('scroll mousedown DOMMouseScroll mousewheel keyup', function (e) {

				$('html, body').stop();

			});

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 60000,
				dataType: 'json',
				data: {
					processor: 'admin_fadmin_home_order_details',
					op: 'order_details',
					order_id: order_id,
					booking_id: booking_id
				},
				success: function (json) {
					if (json.processor_success)
					{
						$('#order_details_table_div_id_' + json.booking_id).html(json.order_details_table);

						if (getQueryVariable('session'))
						{
							update_page_url('?page=' + getQueryVariable('page') + '&session=' + getQueryVariable('session') + '&order=' + json.order_id);
						}
						else if (getQueryVariable('day'))
						{
							update_page_url('?page=' + getQueryVariable('page') + '&day=' + getQueryVariable('day') + '&order=' + json.order_id);
						}

						// get order history
						$.ajax({
							url: 'ddproc.php',
							type: 'POST',
							timeout: 20000,
							dataType: 'json',
							data: {
								processor: 'admin_order_mgr_processor',
								store_id: STORE_DETAILS.id,
								op: 'get_history',
								user_id: USER_DETAILS.id,
								order_id: order_id
							},
							success: function (json) {
								$('#order_history_div_id_' + json.booking_id).html(json.html);

							},
							error: function (objAJAXRequest, strError) {
								response = 'Unexpected error: ' + strError;
								dd_message({
									title: 'Error',
									message: response
								});

							}

						});
					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});

		});

		// handle closing of order details
		$('.close_order_details_table').on('click', function (e) {

			$('#order_details_tbody_id_' + $(this).data('booking_id')).hide();

			if (getQueryVariable('session'))
			{
				update_page_url('?page=' + getQueryVariable('page') + '&session=' + getQueryVariable('session'));
			}
			else if (getQueryVariable('day'))
			{
				update_page_url('?page=' + getQueryVariable('page') + '&day=' + getQueryVariable('day'));
			}

		});

	});
}

function handle_platepoints_gifts()
{
	$('[data-pp_gift_reward]').each(function () {

		$(this).on('click', function (e) {

			user_id = $(this).data('user_id');
			level = $(this).data('level');
			gift_id = $(this).data('gift_id');
			order_sequence_number = $(this).data('order_sequence_number');
			order_id = $(this).data('order_id');

			dd_message({
				title: 'Mark received confirmation',
				message: 'Are you sure you wish to mark the gift as received?',
				modal: true,
				confirm: function () {

					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'plate_points_processor',
							user_id: user_id,
							op: 'mark_gift_received',
							level: level,
							gift_id: gift_id,
							order_sequence_number: order_sequence_number
						},
						success: function (json, status) {
							if (json.processor_success)
							{
								if (level == 'enrolled')
								{
									$('[data-pp_gift_reward_div=' + json.user_id + '_' + gift_id + ']').remove();
									dd_toast({'message': 'The guest has received a Member level gift: ' + json.giftDisplayString});

									var numDue = $("div[data-is_due*='true']").length;
									var numNotDue = $("div[data-is_due*='false']").length;

									if (numDue == 0)
									{
										$('#rewardsdueheader_' + json.user_id).hide();
									}

									if (numNotDue == 0)
									{
										$('#rewardsnotdueheader_' + json.user_id).hide();
									}

									if ($("#received_order_based_gifts_" + order_id).length)
									{
										$("#received_order_based_gifts_" + order_id).html(json.received_gifts);
									}
								}
								else
								{
									$('[data-pp_gift_reward_div=' + json.user_id + ']').hide();

									dd_toast({'message': 'The guest has received their current level gift: ' + json.giftDisplayString});
								}

							}
							else
							{
								dd_message({
									title: 'Error',
									message: 'There was a problem marking the gift as received.'
								});

							}
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error';
						}

					});
				}
			});

		});

	});

}

function start_dashboard_snapshot_timer()
{
	// cancel existing timer if there is one
	$.doTimeout('dashboard_snapshot_timer');

	// check every 2 minutes for dashboard updates
	$.doTimeout('dashboard_snapshot_timer', 120000, function () {

		handle_dashboard_update();

		return true;

	});

	// call it immediately
	// $.doTimeout('dashboard_snapshot_timer', true );
}

function start_unix_expiry_timer()
{
	// cancel existing timer if there is one
	$.doTimeout('unix_expiry_timer');

	// check every minute and set agenda display is_past based on session/day expiry
	$.doTimeout('unix_expiry_timer', 60000, function () {

		var unixtime = Math.round(new Date().getTime() / 1000);

		$('[data-unix_expiry]').filter(function () {

			return $(this).data('unix_expiry') < unixtime && !$(this).hasClass('is_past');

		}).addClass('is_past');

		return true;

	});

	// call it immediately
	$.doTimeout('unix_expiry_timer', true);
}

function set_selected_date(new_date, cur_menu_id, new_menu_id)
{
	selected_date = new_date;

	if (cur_menu_id != new_menu_id)
	{
		handle_dashboard_update();
		return true;
	}
	else
	{
		return false;
	}
}

function handle_dashboard_update()
{
	if ($('#dashboard_snapshot_table_div').length && $('#dashboard_snapshot_container').is(":visible"))
	{
		// short timeout to prevent processing message from showing for normal cached load times
		$.doTimeout('dashboard_processing_timer', 1000, function () {

			$('#dashboard_processing').show();
			$('#dashboard_snapshot_table').addClass('processing');

		});

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 60000,
			dataType: 'json',
			data: {
				processor: 'admin_fadmin_home',
				op: 'dashboard_details',
				store_id: STORE_DETAILS.id,
				dashboard_date: selected_date
			},
			success: function (json) {
				if (json.processor_success)
				{
					$.doTimeout('dashboard_processing_timer');

					$('#dashboard_snapshot_table_div').html(json.dashboard_metrics);
					$('#ds_title_date').html(json.dashboard_title);

					$('#dashboard_snapshot_table').removeClass('processing');
					$('#dashboard_processing').hide();
					help_system_init();
				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
				//		handle_dashboard_update();
			}
		});
	}
}

function handle_agenda_month_select()
{
	$('#month_selector').on('change', function (e) {

		selected_agenda_month = $('#month_selector').val();
		selected_agenda_month_id = $('#month_selector').find(':selected').data('menu_id');

		doasync = true;
		if (init_go_to_today)
		{
			doasync = false;
		}

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			async: doasync,
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_fadmin_home',
				op: 'agenda_details',
				store_id: STORE_DETAILS.id,
				agenda_date: selected_agenda_month,
				agenda_month_id: selected_agenda_month_id
			},
			success: function (json) {
				if (json.processor_success)
				{
					$('#session_selector').html(json.session_info);

					handle_agenda_click();

					if (!init_go_to_today)
					{
						$('#day_' + json.date).scrollToTarget($('#session_selector'));
					}
				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});

	});
}

function handle_delayed_payment_tc()
{
	$('[data-delayed_payment_tc]').on('click', function (e) {

		var user_id = $(this).data('delayed_payment_tc');

		dd_message({
			title: lang.en.tc.terms_and_conditions,
			message: lang.en.tc.delayed_payment,
			modal: true,
			noOk: true,
			buttons: {
				'Agree': function () {

					$(this).remove();

					$('[data-delayed_payment_tc="' + user_id + '"]').hide();

					set_user_pref('TC_DELAYED_PAYMENT_AGREE', 1, user_id);

				},
				'Decline': function () {

					dd_message({
						title: lang.en.tc.terms_and_conditions,
						message: lang.en.tc.delayed_payment_decline
					});

					set_user_pref('TC_DELAYED_PAYMENT_AGREE', 0, user_id);

				}
			}
		});

	});
}

function handle_agenda_click()
{
	$('#session_selection li').each(function () {

		$(this).on('click', function (e) {

			if (typeof window.booking_query != 'undefined')
			{
				window.booking_query.abort();
			}

			$('.selected').removeClass('selected');

			$(this).addClass('selected');
			// cancel timeout
			$.doTimeout('booked_guests_table_timer');

			// if fetching guests takes longer than 1 second, show a loading message
			$.doTimeout('booked_guests_table_timer', 1000, function () {

				$('#booked_guests_table').html('<img src="' + PATH.image_admin + '/style/throbber_circle.gif" class="img_valign img_throbber_circle" data-tooltip="Processing" alt="Processing" style="display: inline;" /> Loading...');

			});

			if ($(this).data('session_id'))
			{
				view_session = true;

				selected_session_id = $(this).data('session_id');

				window.booking_query = $.ajax({
					url: 'ddproc.php',
					type: 'POST',
					timeout: 120000,
					dataType: 'json',
					data: {
						processor: 'admin_fadmin_home',
						op: 'session_details',
						store_id: STORE_DETAILS.id,
						session_id: selected_session_id
					},
					success: function (json) {
						if (json.processor_success)
						{
							// if getting order details, show it after
							var order_details = false;
							// only check if not switching between day and session view
							if (!getQueryVariable('day'))
							{
								order_details = getQueryVariable('order');
							}

							// cancel timer
							$.doTimeout('booked_guests_table_timer');
							update_page_url('?page=' + getQueryVariable('page') + '&session=' + json.session_info.id);

							var current_menu_id = selected_menu_id;
							selected_menu_id = json.session_info.menu_id;
							set_selected_date(json.session_info.session_start_dtf_ymd, current_menu_id, selected_menu_id);

							// update session tools
							update_session_tool_links();

							// update title
							$('#selected_day').html(json.session_info.session_start_dtf_verbose_date);
							$('#selected_date_link').attention();

							// show title time
							$('#selected_session').html(' - ' + json.session_info.session_start_dtf_time_only);
							$('#selected_session').show();

							// Hide Day Details
							$('#day_details_container').hide();

							// fill session details container
							$('#session_details_table_div').html(json.session_details);

							$('#sd_edit_session').show();
							$('#sd_session_meta').show();

							var publish_state_button_text = json.session_info.session_publish_state == 'PUBLISHED' ? 'Close Session' : 'Open Session';
							$('#sd_publish_state').data('session_publish_state', json.session_info.session_publish_state).html(publish_state_button_text);
							// update agenda display with new details in-case the state has changed since last load
							if (json.session_data.session_type === 'DELIVERED')
							{
								$('#session-' + selected_session_id + ' > span.shipped').html(json.session_info.shipping_bookings_count);
								$('#session-' + selected_session_id + ' > span.attending').html(json.session_info.delivered_bookings_count);
								$('#session-' + selected_session_id + ' > span.remaining').html(json.session_info.remaining_slots);
							}
							else if (json.session_data.session_type_subtype === 'WALK_IN')
							{
								$('#sd_edit_session').hide();
								$('#sd_session_meta').hide();
							}
							else
							{
								if (typeof json.session_info.additional_orders !== undefined && json.session_info.additional_orders != null && json.session_info.additional_orders > 0)
								{
									$('#session-' + selected_session_id + ' > span.attending').html((json.session_info.booked_count * 1) + (json.session_info.num_rsvps * 1) + '/' + json.session_info.additional_orders);
								}
								else
								{
									$('#session-' + selected_session_id + ' > span.attending').html((json.session_info.booked_count * 1) + (json.session_info.num_rsvps * 1));
								}
								$('#session-' + selected_session_id + ' > span.remaining').html(json.session_info.remaining_slots + '/' + (json.session_info.remaining_intro_slots > 0 ? json.session_info.remaining_intro_slots : 0));
							}

							if (json.session_info.session_publish_state == 'PUBLISHED')
							{
								$('#session-' + selected_session_id + ' > span.time').removeClass('closed');
							}
							else
							{
								$('#session-' + selected_session_id + ' > span.time').addClass('closed');
							}

							// show session details container
							$('#session_details_container').show();

							// show guest list
							$('#booked_guests_table').html(json.booking_details);

							// register click handlers
							register_booking_click_handlers();

							// show order details
							if (order_details)
							{
								$('[data-view_order_details][data-order_id="' + order_details + '"]').trigger('click');
							}

							// dream taste buttons
							$('#sd_invitation_pdf').on('click', function (e) {

								bounce('?page=print&dream_taste_event_pdf=' + selected_session_id, '_blank');

							});

							// fundraiser buttons
							$('#sd_fundraiser_invitation_pdf').on('click', function (e) {
								bounce('?page=print&fundraiser_event_pdf=' + selected_session_id, '_blank');

							});

							// community pickup buttons
							$('#sd_pick_up_event_invitation_pdf').on('click', function (e) {

								bounce('?page=print&remote_pickup_private_event_pdf=' + selected_session_id, '_blank');

							});

							$('#sd_resend_hostess_email').on('click', function (e) {

								resend_dream_taste_notification();

							});

						}
					},
					error: function (objAJAXRequest, strError) {
						if (strError != 'abort')
						{
							dd_message({
								title: 'Error',
								message: 'An unexpected error has occurred, error message: ' + strError + '. If you continue to experience this issue please contact support.'
							});
						}
					}
				});
			}
			else
			{
				view_session = false;
				var days_menu = $(this).data('menu_id');

				var submitted_date = $(this).data('date');
				window.booking_query = $.ajax({
					url: 'ddproc.php',
					type: 'POST',
					timeout: 120000,
					dataType: 'json',
					data: {
						processor: 'admin_fadmin_home',
						op: 'date_details',
						store_id: STORE_DETAILS.id,
						date: submitted_date
					},
					success: function (json) {
						if (json.processor_success)
						{
							// if getting order details, show it after
							var order_details = false;
							// only check if not switching between day and session view
							if (!getQueryVariable('session'))
							{
								order_details = getQueryVariable('order');
							}

							var current_menu_id = selected_menu_id;
							selected_menu_id = days_menu;

							set_selected_date(submitted_date, current_menu_id, selected_menu_id);

							// cancel timer
							$.doTimeout('booked_guests_table_timer');
							update_page_url('?page=' + getQueryVariable('page') + '&day=' + selected_date);

							// update session tools
							update_session_tool_links();

							// update title
							$('#selected_day').html(json.date_info.date_dtf_verbose_date);
							$('#selected_date_link').attention();

							// Hide session specific details
							$('#session_details_container, #selected_session').hide();

							// show guest list
							if (json.booking_details.length)
							{
								$('#booked_guests_table').html(json.booking_details);
								// fill date details container
								$('#day_details_table_div').html(json.date_details);
							}
							else
							{
								$('#booked_guests_table').html('<div class="no_day_sessions">No sessions for this day.</div>');
								// fill date details container
								$('#day_details_table_div').html('<div class="no_day_sessions">No sessions for this day.</div>');
							}
							// show day details container
							$('#day_details_container').show();

							// register click handlers
							register_booking_click_handlers();

							// show order details
							if (order_details)
							{
								$('[data-view_order_details][data-order_id="' + order_details + '"]').trigger('click');
							}

							// clicking session time will link to that session
							$('[id^="gd_title-"]').on('click', function (e) {

								$('#session-' + $(this).data('session_id')).scrollToTarget($('#session_selector'));

								$.scrollTo($('body'), 100);

							}).hover(function () {

								$(this).addClass('hover');

							}, function () {

								$(this).removeClass('hover');

							});
						}
					},
					error: function (objAJAXRequest, strError) {
						if (strError != 'abort')
						{
							dd_message({
								title: 'Error',
								message: 'An unexpected error has occurred, error message: ' + strError + '. If you continue to experience this issue please contact support.'
							});
						}
					}
				});
			}

		});

	});
}

function update_page_url(page_url)
{
	// update url
	historyPush({url: page_url});
	// update title link
	$('#selected_date_link').prop('href', page_url);
}

function handle_pp_opted_out()
{
	$('.pp_opted_out').each(function () {

		$(this).on('click', function (e) {

			e.preventDefault();

			var bounce_to = $(this).prop('href');

			dd_message({
				title: 'Guest has opted out',
				message: 'The guest has previously opted out, are you sure you wish to continue?',
				modal: true,
				confirm: function () {

					bounce(bounce_to);

				}
			});

		});

	});
}

function resend_dream_taste_notification()
{
	dd_message({
		title: 'Send notice confirmation',
		message: 'Are you sure you wish to resend the host email notification?',
		modal: true,
		confirm: function () {
			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_fadmin_home',
					op: 'resend_dream_taste_email',
					store_id: STORE_DETAILS.id,
					session_id: selected_session_id
				},
				success: function (json) {
					if (json.processor_success)
					{
						dd_message({
							title: 'Send notice confirmation',
							message: 'Email notification has been resent to the session host.'
						});
					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});
		}
	});
}

function handle_booking_no_show()
{
	$('[id^="gd_noshow-"]').each(function (e) {

		$(this).on('click', function (e) {

			var state = this.checked ? 'yes' : 'no';

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_no_show_state',
					bid: $(this).data('booking_id'),
					store_id: STORE_DETAILS.id,
					state: state
				},
				success: function (json) {
					if (json.processor_success)
					{

					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';

				}
			});
		});

	});
}

function handle_food_testing_recipe()
{
	$('[id^="food_testing-"]').each(function (e) {

		$(this).on('change', function (e) {

			var selector_elem = $(this);
			var user_id = $(this).data('user_id');
			var session_id = $(this).data('session_id');
			var menu_id = $(this).data('menu_id');
			var survey_name = $('option:selected', $(this)).text();
			var survey_id = $(this).val().split("-")[0];
			var survey_size = $(this).val().split("-")[1];

			if ($(this).val() == '0')
			{
				return;
			}

			dd_message({
				title: 'Confirm',
				message: 'Are you sure you wish to assign <span style="font-weight: bold;">' + survey_name + '</span> to this guest? The testing item is <span style="font-weight: bold;">not</span> editable once assigend and the guest will immediately receive an email upon confirmation which will include testing instructions.',
				noOk: true,
				modal: true,
				buttons: {
					'Confirm': function () {
						$.ajax({
							url: 'ddproc.php',
							type: 'POST',
							timeout: 20000,
							dataType: 'json',
							data: {
								processor: 'admin_fadmin_home',
								op: 'assign_test_recipe',
								user_id: user_id,
								session_id: session_id,
								menu_id: menu_id,
								survey_id: survey_id,
								survey_size: survey_size
							},
							success: function (json) {
								if (json.processor_success)
								{
									$('[data-food_testing_select_user_id="' + json.user_id + '"]').text(survey_name);
								}
							},
							error: function (objAJAXRequest, strError) {
								response = 'Unexpected error';
							}
						});

						$(this).remove();

					},
					'Cancel': function () {
						$(this).remove();
						$(selector_elem).val(0);
					}
				}
			});

		});

	});
}

function handle_guest_account_notes()
{
	$('[id^="gd_guest_account_note_button-"]').each(function () {

		$(this).on('click', function (e) {

			booking_id = $(this).data('booking_id');
			user_id = $(this).data('user_id');
			do_op = 'get';
			guest_note = '';

			if ($(this).data('edit_mode') == true)
			{
				do_op = 'save';

				guest_note = $('#gd_guest_account_note-' + booking_id + ' > textarea').val();
			}

			if (do_op == 'get')
			{
				get_user_pref('USER_ACCOUNT_NOTE', user_id, function (json) {

					var textarea_elem = $('<textarea></textarea>').addClass('form-control').val((json.guest_preferences[user_id]['USER_ACCOUNT_NOTE'].value ? json.guest_preferences[user_id]['USER_ACCOUNT_NOTE'].value : "")).on('keyup', function (e) {

						if ($(this).val() != strip_tags($(this).val()))
						{
							$(this).val(strip_tags($(this).val()));
						}

					});

					$('#gd_guest_account_note-' + booking_id).addClass('guest_note_edit').html(textarea_elem);

					$('#gd_guest_account_note_button-' + booking_id).data('edit_mode', true).html('Save Note');

					$('#gd_guest_account_note_cancel_button-' + booking_id).show();

					$('#gd_guest_account_note_cancel_button-' + booking_id).data('user_data_value', (json.guest_preferences[user_id]['USER_ACCOUNT_NOTE'].value ? json.guest_preferences[user_id]['USER_ACCOUNT_NOTE'].value : "")).on('click', function (e) {

						$('#gd_guest_account_note-' + $(this).data('booking_id')).removeClass('guest_note_edit').html($(this).data('user_data_value'));

						$('#gd_guest_account_note_button-' + $(this).data('booking_id')).data('edit_mode', false).html('Account Notes');

						$(this).hide();

					});

				});

			}
			else
			{
				dd_message({
					title: 'Confirm',
					message: "This note is viewable and editable by the guest in their preferences. Are you sure you wish to modify the guest's account note?",
					noOk: true,
					modal: true,
					buttons: {
						'Confirm': function () {
							$(this).remove();

							set_user_pref('USER_ACCOUNT_NOTE', guest_note, user_id);

							$('#gd_guest_account_note-' + booking_id).removeClass('guest_note_edit').html(nl2br(guest_note));

							$('#gd_guest_account_note_cancel_button-' + booking_id).hide();

							$('#gd_guest_account_note_button-' + booking_id).data('edit_mode', false).html('Account Notes');
						},
						'Cancel': function () {
							$(this).remove();
						}
					}
				});

			}

		});

	});
}

function handle_admin_carryover_notes()
{
	// handle button for stores that have hidden carryover notes
	$('[id^="gd_show_guest_note-"]').each(function () {

		$(this).on('click', function (e) {

			var booking_id = $(this).data('booking_id');

			var button_obj = this;

			$(button_obj).hide();

			$('#gd_guest_note-' + booking_id).show();

			$.doTimeout('show_guest_note_timer-' + booking_id, 10000, function () {

				$(button_obj).show();

				$('#gd_guest_note-' + booking_id).hide();

			});

		});

	});

	$('[id^="gd_guest_note_button-"]').each(function () {

		$(this).on('click', function (e) {

			hide_carryover_notes = $(this).data('hide_carryover_notes');

			booking_id = $(this).data('booking_id');
			user_id = $(this).data('user_id');
			do_op = 'get';
			guest_note = '';

			if ($(this).data('edit_mode') == true)
			{
				do_op = 'save';

				guest_note = $('#gd_guest_note-' + booking_id + ' > textarea').val();
			}

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_fadmin_home',
					op: 'guest_carryover_note',
					'do': do_op,
					store_id: STORE_DETAILS.id,
					user_id: user_id,
					note: guest_note
				},
				success: function (json) {
					if (json.processor_success)
					{
						if (do_op == 'get')
						{
							// handle button for stores that have hidden carryover notes
							$.doTimeout('show_guest_note_timer-' + booking_id); //cancel timer
							$('#gd_guest_note-' + booking_id).show(); // show notes
							$('#gd_show_guest_note-' + booking_id).hide(); // hide button
							$('#gd_guest_note-' + booking_id).addClass('guest_note_edit').html('<textarea class="form-control">' + (json.guest_note.user_data_value ? json.guest_note.user_data_value : "") + '</textarea>');

							$('#gd_guest_note_button-' + booking_id).data('edit_mode', true).html('Save Note');

							$('#gd_guest_note_cancel_button-' + booking_id).data('user_data_value', (json.guest_note.user_data_value ? json.guest_note.user_data_value : "")).on('click', function (e) {

								// handle button for stores that have hidden carryover notes
								if ($(this).data('hide_carryover_notes') === 1)
								{
									$('#gd_guest_note-' + booking_id).hide(); // hide notes
									$('#gd_show_guest_note-' + booking_id).show(); // hide button
								}

								$('#gd_guest_note-' + $(this).data('booking_id')).removeClass('guest_note_edit').html($(this).data('user_data_value'));

								$('#gd_guest_note_button-' + $(this).data('booking_id')).data('edit_mode', false).html('Admin Carryover');

								$(this).hide();

							}).show();
						}
						else
						{
							// handle button for stores that have hidden carryover notes
							if (hide_carryover_notes === 1 && json.guest_note.user_data_value)
							{
								$('#gd_guest_note-' + booking_id).hide(); // hide notes
								$('#gd_show_guest_note-' + booking_id).show(); // hide button
							}
							$('#gd_guest_note-' + booking_id).removeClass('guest_note_edit').html(nl2br(json.guest_note.user_data_value));

							$('#gd_guest_note_cancel_button-' + booking_id).hide();

							$('#gd_guest_note_button-' + booking_id).data('edit_mode', false).html('Admin Carryover');
						}

					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});

		});

	});
}

function handle_admin_order_notes()
{
	$('[id^="gd_admin_note_button-"]').each(function () {

		$(this).on('click', function (e) {

			let unique_id = $(this).data('uid');
			if (typeof unique_id == 'undefined' || unique_id == null || unique_id == '')
			{
				unique_id = '';
			}
			else
			{
				unique_id += '-';
			}
			let buttonName = $(this).data('button_name');
			if (typeof buttonName == 'undefined' || buttonName == null || buttonName == '')
			{
				buttonName = 'Admin Order Note';
			}
			booking_id = $(this).data('booking_id');
			order_id = $(this).data('order_id');
			user_id = $(this).data('user_id');
			do_op = 'get';
			admin_note = '';

			if ($(this).data('edit_mode') == true)
			{
				do_op = 'save';

				admin_note = $('#gd_admin_note-' + unique_id + booking_id + ' > textarea').val();
			}

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_fadmin_home',
					op: 'order_admin_note',
					'do': do_op,
					order_id: order_id,
					note: admin_note
				},
				success: function (json) {
					if (json.processor_success)
					{
						if (do_op == 'get')
						{
							$('#gd_admin_note-' + unique_id + booking_id).addClass('admin_note_edit').html('<textarea class="form-control">' + (json.admin_note ? json.admin_note : "") + '</textarea>');

							$('#gd_admin_note_button-' + unique_id + booking_id).data('edit_mode', true).html('Save Note');

							$('#gd_admin_note_cancel_button-' + unique_id + booking_id).data('admin_note_value', (json.admin_note ? json.admin_note : "")).on('click', function (e) {

								$('#gd_admin_note-' + unique_id + $(this).data('booking_id')).removeClass('admin_note_edit').html($(this).data('admin_note_value'));

								$('#gd_admin_note_button-' + unique_id + $(this).data('booking_id')).data('edit_mode', false).html(buttonName);

								$(this).hide();

							}).show();
						}
						else
						{
							$('#gd_admin_note-' + unique_id + booking_id).removeClass('admin_note_edit').html(nl2br(json.admin_note));

							$('.multi-populate-admin-note').each(function () {
								$(this).html(nl2br(json.admin_note));
							});

							$('#gd_admin_note_cancel_button-' + unique_id + booking_id).hide();

							$('#gd_admin_note_button-' + unique_id + booking_id).data('edit_mode', false).html(buttonName);
						}
					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});

		});

	});
}

function handle_reschedule_button()
{
	$('[id^="gd_reschedule-"]').each(function () {

		$(this).css({'cursor': 'pointer'}).on('click', function (e) {

			create_and_submit_form({
				method: 'post',
				action: '/?page=admin_reschedule',
				input: ({
					'session_id': $(this).data('session_id'),
					'original_session_id': $(this).data('session_id'),
					'store_id': $(this).data('store_id'),
					'menus': $(this).data('menu_id'),
					'menu_id': $(this).data('menu_id'),
					'order_id': $(this).data('order_id'),
					'back_address': back_path()
				})
			});

		});

	});
}

function handle_booking_row_click()
{
	handle_guest_account_notes();

	handle_admin_carryover_notes();

	handle_admin_order_notes();

	handle_reschedule_button();

	$('#booked_guests_table tbody.guest').each(function () {

		$(this).on('click', function (e) {

			if ($(this).hasClass('select_guest'))
			{
				$('.select_guest').removeClass('select_guest');
			}
			else
			{
				$('.select_guest').removeClass('select_guest');

				$('#guest_details_tbody_id_' + $(this).data('booking_id')).addClass('select_guest');
				$('#order_details_tbody_id_' + $(this).data('booking_id')).addClass('select_guest');
			}
			$('[id^="gd_guest_menu-"]').hide();

			$('#gd_guest_menu-' + $(this).data('user_id')).show();

		});

	});

	$('[id^="gd_guest-"]').each(function () {

		var booking_id = $(this).data('booking_id');

		/*
		 * Handle touch events under development
		 * Aug 29, 2014
		 * -- Ryan
		 * ------------------- this -----VVV is a problem
		 $('#gd_guest-' + booking_id +', #gd_guest_menu-' + booking_id).on({
		 touchstart: function(e) {

		 e.preventDefault();

		 if ($('#gd_guest_menu-' + booking_id).is(':visible'))
		 {
		 $('#gd_guest_menu-' + booking_id).hide();
		 }
		 else
		 {
		 $('#gd_guest_menu-' + booking_id).show();
		 }

		 },
		 touchend: function(e) {

		 e.preventDefault();

		 },
		 mouseenter: function(e) {

		 // cancel timeout
		 $.doTimeout('guest_menu_timer_hide-' + booking_id);

		 // short timeout to prevent menus from popping up when moused over
		 $.doTimeout( 'guest_menu_timer_show-' + booking_id, 100, function() {

		 $('#gd_guest_menu-' + booking_id).show();

		 });

		 },
		 mouseleave: function(e) {

		 // cancel timeout
		 $.doTimeout('guest_menu_timer_show-' + booking_id);

		 // short timeout to prevent menus from disappearing when moused away
		 $.doTimeout( 'guest_menu_timer_hide-' + booking_id, 150, function() {

		 $('#gd_guest_menu-' + booking_id).hide();

		 });

		 }
		 });
		 */

		$('#gd_guest-' + booking_id + ', #gd_guest_menu-' + booking_id).hover(function () {

			// cancel timeout
			$.doTimeout('guest_menu_timer_hide-' + booking_id);

			// short timeout to prevent menus from popping up when moused over
			$.doTimeout('guest_menu_timer_show-' + booking_id, 100, function () {

				$('#gd_guest_menu-' + booking_id).show();

			});

		}, function () {

			// cancel timeout
			$.doTimeout('guest_menu_timer_show-' + booking_id);

			// short timeout to prevent menus from disappearing when moused away
			$.doTimeout('guest_menu_timer_hide-' + booking_id, 150, function () {

				$('#gd_guest_menu-' + booking_id).hide();

			});

		});

	});

	handle_cancel_order();
	handle_cancel_order_delivered();
	handle_delete_saved_order();
}

function handle_delete_saved_order_delivered()
{
	$('[id^="gd_delete_delivered_order-"]').each(function () {

		$(this).on('click', function (e) {

			var session_id = $(this).data('session_id');
			var order_id = $(this).data('order_id');
			var store_id = $(this).data('store_id');
			var user_id = $(this).data('user_id');
			var bounce_to = $(this).data('bounce');

			dd_message({
				title: 'Confirm Delete Saved Order',
				message: "Are you sure you want to delete this saved order?",
				confirm: function () {
					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_order_mgr_processor_delivered',
							store_id: store_id,
							op: 'delete_saved_order',
							session_id: session_id,
							user_id: user_id,
							order_id: order_id
						},
						success: function (json) {
							if (json.processor_success)
							{
								if (typeof bounce_to == 'undefined' || bounce_to == "")
								{
									bounce_to = window.location.href;
								}

								bounce(bounce_to);
							}
							else
							{

								if (json.doRefresh == true)
								{

									dd_message({
										title: 'Problem Deleting Saved Order',
										message: json.processor_message,
										modal: true,
										noOk: true,
										closeOnEscape: false,
										buttons: {
											'Refresh': function () {
												$(this).remove();
												bounce(window.location.href);
											}
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

							}
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error: ' + strError;
							dd_message({
								title: 'Error',
								message: response
							});

						}

					});
				}
			});

		});

	});

}

function handle_delete_saved_order()
{
	$('[id^="gd_delete_order-"]').each(function () {

		$(this).on('click', function (e) {

			var session_id = $(this).data('session_id');
			var order_id = $(this).data('order_id');
			var store_id = $(this).data('store_id');
			var user_id = $(this).data('user_id');
			var bounce_to = $(this).data('bounce');

			dd_message({
				title: 'Confirm Delete Saved Order',
				message: "Are you sure you want to delete this saved order?",
				confirm: function () {
					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_order_mgr_processor',
							store_id: store_id,
							op: 'delete_saved_order',
							session_id: session_id,
							user_id: user_id,
							order_id: order_id
						},
						success: function (json) {
							if (json.processor_success)
							{
								if (typeof bounce_to == 'undefined' || bounce_to == "")
								{
									bounce_to = window.location.href;
								}

								bounce(bounce_to);
							}
							else
							{

								if (json.doRefresh == true)
								{

									dd_message({
										title: 'Problem Deleting Saved Order',
										message: json.processor_message,
										modal: true,
										noOk: true,
										closeOnEscape: false,
										buttons: {
											'Refresh': function () {
												$(this).remove();
												bounce(window.location.href);
											}
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

							}
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error: ' + strError;
							dd_message({
								title: 'Error',
								message: response
							});

						}

					});
				}
			});

		});

	});

}

function validateKey(evt)
{
	evt = (evt) ? evt : ((event) ? event : null);
	if (evt)
	{
		var charCode = (evt.charCode || evt.charCode == 0) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : evt.which);

		if (charCode > 13 && (charCode < 48 || charCode > 57))
		{
			//alert("Only whole numbers are allowed.");
			if (charCode == 46)
			{
				return true;
			}

			if (evt.returnValue)
			{
				evt.returnValue = false;
			}
			else if (evt.preventDefault)
			{
				evt.preventDefault();
			}
			else
			{
				return false;
			}
		}
	}
}

function validateAmount(obj, limit)
{
	if (isNaN(obj.value))
	{
		obj.value = 0.00;
	}

	if (obj.value > limit)
	{
		obj.value = limit;
	}
}

function handle_cancel_order()
{
	$('[id^="gd_cancel_order-"]').each(function () {

		$(this).on('click', function (e) {

			var booking_id = $(this).data('booking_id');
			var session_id = $(this).data('session_id');
			var order_id = $(this).data('order_id');
			var store_id = $(this).data('store_id');
			var user_id = $(this).data('user_id');
			var bounce_to = $(this).data('bounce');

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_order_mgr_processor',
					op: 'cancel_preflight',
					store_id: store_id,
					session_id: session_id,
					user_id: user_id,
					order_id: order_id
				},
				success: function (json, status) {
					if (json.processor_success && json.data)
					{

						dd_message({
							title: 'Cancel Order?',
							message: json.data,
							width: 700,
							height: 480,
							modal: true,
							open: function () {
								$("#order_admin_notes_cancel").on('keyup', function (e) {

									if ($(this).val() != strip_tags($(this).val()))
									{
										$(this).val(strip_tags($(this).val()));
									}

								});
							},
							canConfirm: function () {
								var reason = $("#cancellation_reason").val();
								if (reason == "")
								{

									dd_message({
										title: 'Error',
										div_id: 'Uneek262020',
										message: "Please select a reason for cancelling"
									});

									return false;
								}
								else
								{
									return true;
								}

							},
							confirm: function (json, status) {

								var paymentList = {};
								$("#cancel_parent :input").each(function () {

									paymentList[this.id] = $(this).val();

								});

								var order_notes = $("#order_admin_notes_cancel").val().trim();
								var reason = $("#cancellation_reason").val();
								var declined_MFY = $('#declined_MFY').is(':checked');
								var declined_Reschedule = $('#declined_to_reschedule').is(':checked');
								var suppress_cancel_email = $('#suppress_cancel_email').is(':checked');

								dd_message({
									title: 'Cancelling order',
									message: '<img src="' + PATH.image_admin + '/style/throbber_circle.gif" class="img_valign" alt="Processing" /> Processing order cancellation...',
									div_id: 'cancel_order',
									modal: true,
									noOk: true,
									closeOnEscape: false,
									open: function (event, ui) {
										$(this).parent().find('.ui-dialog-titlebar-close').hide();
									}
								});

								$.ajax({
									url: 'ddproc.php',
									type: 'POST',
									timeout: 20000,
									dataType: 'json',
									data: {
										processor: 'admin_order_mgr_processor',
										op: 'cancel',
										store_id: store_id,
										session_id: session_id,
										user_id: user_id,
										order_id: order_id,
										paymentList: paymentList,
										order_notes: order_notes,
										reason: reason,
										declined_MFY: declined_MFY,
										declined_Reschedule: declined_Reschedule,
										suppress_cancel_email: suppress_cancel_email
									},
									success: function (json) {
										if (json.processor_success)
										{
											dd_message({
												title: 'Order was Canceled',
												message: json.data,
												div_id: 'cancel_order',
												closeOnEscape: false,
												modal: true,
												noOk: true,
												open: function (event, ui) {
													$(this).parent().find('.ui-dialog-titlebar-close').hide();
												},
												buttons: {
													"OK": function () {

														if (typeof bounce_to != 'undefined')
														{
															bounce(bounce_to);
														}
														else
														{
															bounce(removeQueryVariable('order', window.location.href));
														}
													}

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
										response = 'Unexpected error: ' + strError;
										dd_message({
											title: 'Error',
											message: response
										});

									}

								});

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
					response = 'Unexpected error: ' + strError;
					dd_message({
						title: 'Error',
						message: response
					});

				}

			});

		});

	});
}

function handle_cancel_order_delivered()
{
	$('[id^="gd_cancel_delivered_order-"]').each(function () {

		$(this).on('click', function (e) {

			var session_id = $(this).data('session_id');
			var order_id = $(this).data('order_id');
			var store_id = $(this).data('store_id');
			var user_id = $(this).data('user_id');
			var bounce_to = $(this).data('bounce');

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_order_mgr_processor_delivered',
					op: 'cancel_preflight',
					store_id: store_id,
					session_id: session_id,
					user_id: user_id,
					order_id: order_id
				},
				success: function (json, status) {
					if (json.processor_success && json.data)
					{

						dd_message({
							title: 'Cancel Order?',
							message: json.data,
							width: 700,
							height: 480,
							modal: true,
							open: function () {
								$("#order_admin_notes_cancel").on('keyup', function (e) {

									if ($(this).val() != strip_tags($(this).val()))
									{
										$(this).val(strip_tags($(this).val()));
									}

								});
							},
							canConfirm: function () {
								var reason = $("#cancellation_reason").val();
								if (reason == "")
								{

									dd_message({
										title: 'Error',
										div_id: 'Uneek262020',
										message: "Please select a reason for cancelling"
									});

									return false;
								}
								else
								{
									return true;
								}

							},
							confirm: function (json, status) {

								var paymentList = {};
								$("#cancel_parent :input").each(function () {

									paymentList[this.id] = $(this).val();

								});

								var order_notes = $("#order_admin_notes_cancel").val().trim();
								var reason = $("#cancellation_reason").val();
								var declined_MFY = $('#declined_MFY').is(':checked');
								var declined_Reschedule = $('#declined_to_reschedule').is(':checked');
								var suppress_email = $('#suppress_reschedule_email').is(':checked');

								dd_message({
									title: 'Cancelling order',
									message: '<img src="' + PATH.image_admin + '/style/throbber_circle.gif" class="img_valign" alt="Processing" /> Processing order cancellation...',
									div_id: 'cancel_order',
									modal: true,
									noOk: true,
									closeOnEscape: false,
									open: function (event, ui) {
										$(this).parent().find('.ui-dialog-titlebar-close').hide();
									}
								});

								$.ajax({
									url: 'ddproc.php',
									type: 'POST',
									timeout: 20000,
									dataType: 'json',
									data: {
										processor: 'admin_order_mgr_processor_delivered',
										op: 'cancel',
										store_id: store_id,
										session_id: session_id,
										user_id: user_id,
										order_id: order_id,
										paymentList: paymentList,
										order_notes: order_notes,
										reason: reason,
										declined_MFY: declined_MFY,
										declined_Reschedule: declined_Reschedule,
										suppress_email: suppress_email
									},
									success: function (json) {
										if (json.processor_success)
										{
											dd_message({
												title: 'Order was Canceled',
												message: json.data,
												div_id: 'cancel_order',
												closeOnEscape: false,
												modal: true,
												noOk: true,
												open: function (event, ui) {
													$(this).parent().find('.ui-dialog-titlebar-close').hide();
												},
												buttons: {
													"OK": function () {

														if (typeof bounce_to != 'undefined')
														{
															bounce(bounce_to);
														}
														else
														{
															bounce(removeQueryVariable('order', window.location.href));
														}
													}

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
										response = 'Unexpected error: ' + strError;
										dd_message({
											title: 'Error',
											message: response
										});

									}

								});

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
					response = 'Unexpected error: ' + strError;
					dd_message({
						title: 'Error',
						message: response
					});

				}

			});

		});

	});
}

function handle_session_tools()
{
	// session tool suppress fastlane cookie
	if ($.totalStorage('suppress_fastlane_labels') != null)
	{
		suppress_fastlane_labels = $.totalStorage('suppress_fastlane_labels');
		$('#suppress_fastlane_labels').prop('checked', !suppress_fastlane_labels);

	}

	// session tool suppress fastlane click
	$('#suppress_fastlane_labels').on('click', function (e) {

		if ($(this).is(':checked'))
		{
			$.totalStorage('suppress_fastlane_labels', false);
			suppress_fastlane_labels = false;
		}
		else
		{
			$.totalStorage('suppress_fastlane_labels', true);
			suppress_fastlane_labels = true;
		}

		update_session_tool_links();

	});
	// Dashboard Snapshot
	$('#ds_goal_tracking').on('click', function (e) {

		bounce('/?page=admin_reports_goal_management_v2&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path());

	});

	$('#ds_trending').on('click', function (e) {

		bounce('/?page=admin_reports_trending_menu_based&date=' + selected_date + '&store=' + STORE_DETAILS.id + '&back=' + back_path());

	});

	$('#ds_dashboard').on('click', function (e) {

		bounce('/?page=admin_dashboard_menu_based&override_month=' + selected_date + '&store=' + STORE_DETAILS.id + '&back=' + back_path());

	});

	// Session Controls
	$('#sd_edit_session').on('click', function (e) {

		if (view_session == true)
		{
			bounce('/?page=admin_edit_session&session=' + selected_session_id + '&back=' + back_path());
		}

	});
	// Session Controls
	$('#sd_email_session').on('click', function (e) {

		if (view_session == true)
		{
			bounce('/?page=admin_email&session=' + selected_session_id + '&back=' + back_path());
		}

	});

	$('#sd_publish_state').on('click', function (e) {

		if (view_session == true)
		{
			state = $(this).data('session_publish_state');

			if (state == 'PUBLISHED')
			{
				new_state = 'Close';
			}
			else
			{
				new_state = 'Open';
			}

			dd_message({
				title: new_state + ' session confirmation',
				message: 'Are you sure you wish to <span style="font-weight: bold;">' + new_state + '</span> this session?',
				modal: true,
				confirm: function () {
					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_fadmin_home',
							op: 'session_publish_state',
							session_id: selected_session_id,
							open_close_submit: new_state
						},
						success: function (json) {
							if (json.processor_success)
							{

								$('#session-' + selected_session_id).scrollToTarget($('#session_selector'));
							}
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error';
						}
					});
				}
			});
		}

	});
	// Session Controls
	$('#sd_session_meta').on('click', function (e) {

		$('.session_meta').toggle();

		$('#session_details_text').toggleClass('session_details_text');

	});

	// Day Controls
	$('#dd_create_session').on('click', function (e) {

		if (view_session != true)
		{
			bounce('/?page=admin_create_session&date=' + selected_date + '&menu=' + selected_menu_id + '&back=' + back_path());
		}

	});

}

function update_session_tool_links()
{
	if (view_session == true)
	{
		$('#st_customer_receipt').prop('href', '/?page=admin_order_details_view_all&customer_print_view=1&session_id=' + selected_session_id + '&menuid=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_franchise_receipt').prop('href', '/?page=admin_order_details_view_all&session_id=' + selected_session_id + '&menuid=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_entree_summary').prop('href', '/?page=admin_reports_select_multi_session&query_submit=1&report_id=2&printer=1&pickSession=2&session_id=' + selected_session_id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_side_dish_report').prop('href', '/?page=admin_order_details_view_all&issidedish=1&session_id=' + selected_session_id + '&menuid=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_fast_lane_report').prop('href', '/?page=admin_order_details_view_all&ispreassembled=1&session_id=' + selected_session_id + '&menuid=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_dream_rewards').prop('href', '/?page=admin_reports_dream_rewards_for_session&session_id=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_future_orders').prop('href', '/?page=admin_order_details_view_all_future&session_id=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_session_goal_sheet_print').prop('href', '/?page=admin_reports_goal_tracking&session_id=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&report_submit=true&print=true&back=' + back_path()).prop('target', '_blank');
		$('#st_session_goal_sheet_xls').prop('href', '/?page=admin_reports_goal_tracking&export=xlsx&hideheaders=true&csvfilename=SessionGoalSheet&session_id=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&report_submit=true&back=' + back_path());
		$('#st_print_labels').prop('href', '/?page=admin_reports_customer_menu_item_labels&session_id=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&back=' + back_path() + '&suppressFastlane=' + suppress_fastlane_labels).prop('target', '_blank');
		$('#st_print_labels_w_breaks').prop('href', '/?page=admin_reports_customer_menu_item_labels&session_id=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&back=' + back_path() + '&break=1&suppressFastlane=' + suppress_fastlane_labels).prop('target', '_blank');
		$('#st_print_labels_by_dinner').prop('href', '/?page=admin_reports_customer_menu_item_labels&session_id=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&order_by=dinner&suppressFastlane=' + suppress_fastlane_labels).prop('target', '_blank');
		$('#st_print_enrollment_forms').prop('href', '/?page=admin_user_plate_points&session_id=' + selected_session_id + '&print_sessions_forms=true').prop('target', '_blank');
		$('#st_plate_points_status_and_rewards_report').prop('href', '/?page=admin_reports_points_status_and_rewards&session_id=' + selected_session_id + '&back=' + back_path());
		$('#st_customer_menu_core').prop('href', '/?page=admin_session_tools_printing&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&core=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_core_current').prop('href', '/?page=admin_session_tools_printing&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&core=true&cur=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_freezer').prop('href', '/?page=admin_session_tools_printing&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&freezer=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_nutrition').prop('href', '/?page=admin_session_tools_printing&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&nutrition=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_core_freezer').prop('href', '/?page=admin_session_tools_printing&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&core=true&freezer=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_core_freezer_nutrition').prop('href', '/?page=admin_session_tools_printing&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&core=true&freezer=true&nutrition=true&back=' + back_path()).prop('target', '_blank');
		$('#st_daily_boxes_shipped').prop('href', '/?page=admin_reports_delivered_daily_boxes&type=shipping&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_daily_boxes_delivered').prop('href', '/?page=admin_reports_delivered_daily_boxes&type=delivering&do=print&session=' + selected_session_id + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');

	}
	else
	{
		$('#st_customer_receipt').prop('href', '/?page=admin_order_details_view_all_multi&customer_print_view=1&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_franchise_receipt').prop('href', '/?page=admin_order_details_view_all_multi&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_entree_summary').prop('href', '/?page=admin_reports_select_multi_session&query_submit=1&report_date=' + selected_date + '&report_id=1&printer=1&back=' + back_path()).prop('target', '_blank');
		$('#st_side_dish_report').prop('href', '/?page=admin_order_details_view_all_multi&issidedish=1&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_fast_lane_report').prop('href', '/?page=admin_order_details_view_all_multi&ispreassembled=1&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_dream_rewards').prop('href', '/?page=admin_reports_dream_rewards_for_session_multi&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_future_orders').prop('href', '/?page=admin_order_details_view_all_future_multi&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_session_goal_sheet_print').prop('href', '/?page=admin_reports_goal_tracking&multi_session=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&report_submit=true&print=true&back=' + back_path()).prop('target', '_blank');
		$('#st_session_goal_sheet_xls').prop('href', '/?page=admin_reports_goal_tracking&export=xlsx&hideheaders=true&csvfilename=SessionGoalSheetSummary&multi_session=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&report_submit=true&back=' + back_path());
		$('#st_print_labels').prop('href', '/?page=admin_reports_customer_menu_item_labels_multi&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&back=' + back_path() + '&suppressFastlane=' + suppress_fastlane_labels).prop('target', '_blank');
		$('#st_print_labels_w_breaks').prop('href', '/?page=admin_reports_customer_menu_item_labels_multi&labels_per_sheet=4&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&back=' + back_path() + '&break=1&suppressFastlane=' + suppress_fastlane_labels).prop('target', '_blank');
		$('#st_print_labels_by_dinner').prop('href', '/?page=admin_reports_customer_menu_item_labels_multi&labels_per_sheet=4&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&menuid=' + selected_menu_id + '&order_by=dinner&back=' + back_path() + '&suppressFastlane=' + suppress_fastlane_labels).prop('target', '_blank');
		$('#st_print_enrollment_forms').prop('href', '/?page=admin_user_plate_points&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&print_days_forms=true').prop('target', '_blank');
		$('#st_plate_points_status_and_rewards_report').prop('href', '/?page=admin_reports_points_status_and_rewards&report_date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path());
		$('#st_customer_menu_core').prop('href', '/?page=admin_session_tools_printing&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&core=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_core_current').prop('href', '/?page=admin_session_tools_printing&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&core=true&cur=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_freezer').prop('href', '/?page=admin_session_tools_printing&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&freezer=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_nutrition').prop('href', '/?page=admin_session_tools_printing&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&nutrition=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_core_freezer').prop('href', '/?page=admin_session_tools_printing&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&core=true&freezer=true&back=' + back_path()).prop('target', '_blank');
		$('#st_customer_menu_core_freezer_nutrition').prop('href', '/?page=admin_session_tools_printing&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&core=true&freezer=true&nutrition=true&back=' + back_path()).prop('target', '_blank');
		$('#st_daily_boxes_shipped').prop('href', '/?page=admin_reports_delivered_daily_boxes&type=shipping&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
		$('#st_daily_boxes_delivered').prop('href', '/?page=admin_reports_delivered_daily_boxes&type=delivering&do=print&date=' + selected_date + '&store_id=' + STORE_DETAILS.id + '&back=' + back_path()).prop('target', '_blank');
	}

	$('#st_finishing_touch_pick_sheet').prop('href', '/?page=admin_finishing_touch_printable_form&store_id=' + STORE_DETAILS.id + '&menu_id=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
	$('#st_print_generic_labels').prop('href', '/?page=admin_reports_customer_menu_item_labels&store_id=' + STORE_DETAILS.id + '&interface=1&menuid=' + selected_menu_id + '&back=' + back_path()).prop('target', '_blank');
}

function handle_guest_search()
{
	$(document).on('click', '#gs_search_go', function (e) {

		create_and_submit_form({
			method: 'get',
			action: 'main.php',
			input: ({
				'page': 'admin_list_users',
				'search_type': $('#gs_search_type').val(),
				'all_stores': ($('#gs_search_all').is(':checked') ? 1 : 0),
				'q': $('#gs_search_value').val(),
				'back': back_path()
			})
		});

	});

	$(document).on('change', '#gs_search_type', function (e) {

		manualSearchTypeChange = true;

		$('#gs_search_value').unmask();

		if ($('#gs_search_type').val() == 'phone')
		{
			$('#gs_search_value').mask('999-999-9999');
		}

	});

	$(document).on('keyup', '#gs_search_value', function (e) {

		if (e.which == 13)
		{
			$('#gs_search_go').trigger('click');
		}

		detect_guest_search_type();

	});

}

function detect_order_search_type()
{
	// user manually changed search type, don't automatically change it
	if (manualOrderSearchTypeChange == true)
	{
		return;
	}

	var value = $('#os_search_value').val();

	// not numeric entry, start with last name
	if (!$.isNumeric(value))
	{
		$('#os_search_type').val('confirmation');
	}

	// numeric entry, probably guest id
	if ($.isNumeric(value))
	{
		$('#os_search_type').val('id');
	}

}

function detect_guest_search_type()
{
	// user manually changed search type, don't automatically change it
	if (manualSearchTypeChange == true)
	{
		return;
	}

	var value = $('#gs_search_value').val();

	// not numeric entry, start with last name
	if (!$.isNumeric(value))
	{
		$('#gs_search_type').val('lastname');
	}

	// numeric entry, probably guest id
	if ($.isNumeric(value))
	{
		$('#gs_search_type').val('id');
	}

	// has @ symbol, probably email
	if (value.indexOf("@") != -1)
	{
		$('#gs_search_type').val('email');
	}

	// has space, probably first and last name
	if (value.indexOf(" ") != -1)
	{
		$('#gs_search_type').val('firstlast');
	}
}

function handle_go_to_today()
{
	$('#go_to_today').on('click', function (e) {

		go_to_today();

	});
}

function go_to_today()
{
	init_go_to_today = true;

	date_select = get_todays_date().slice(0, -3);

	if (selected_agenda_month != undefined && selected_agenda_month != date_select)
	{
		let val = $("#month_selector option[data-menu_month='"+date_select+"']").val();
		$('#month_selector').val(val).trigger('change');
	}

	$('.selected').removeClass('selected');

	var todays_date = get_todays_date();

	$('#day_' + todays_date).scrollToTarget($('#session_selector')).addClass('selected');

	init_go_to_today = false;
}

function handle_preselection()
{
	if (getQueryVariable('session') && $('#session-' + getQueryVariable('session')).length)
	{
		$('#session-' + getQueryVariable('session')).scrollToTarget($('#session_selector'));
	}
	else if (getQueryVariable('day') && $('#day_' + getQueryVariable('day')).length)
	{
		$('#day_' + getQueryVariable('day')).scrollToTarget($('#session_selector'));
	}
	else
	{
		go_to_today();
	}
}

function confirm_order_attended(user_id, order_id)
{
	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'plate_points_processor',
			user_id: user_id,
			op: 'confirm_order',
			order_id: order_id
		},
		success: function (json) {
			if (json.processor_success)
			{
				$("[id^=pp_co_" + user_id + "_" + order_id + "]").html(json.points_this_order);

				if (json.total_credit)
				{
					$("[id^=pp_credit_" + user_id + "_" + order_id + "]").text('$' + json.total_credit);
				}
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
			response = 'Unexpected error: ' + strError;
		}

	});
}

// jquery plugins
(function ($) {

	//create jquery scrollToTarget method
	$.fn.scrollToTarget = function (target, callback) {
		var elem = $(this);

		if (elem.length)
		{
			var target_top = $(target).position().top;
			var target_bottom = target_top + $(target).height();

			var elem_top = $(elem).position().top;
			var elem_bottom = elem_top + $(elem).height();

			// only scroll if element is out of bounds
			if ((elem_top > target_top && elem_bottom < target_bottom) || (elem_top < target_top && elem_bottom > target_bottom))
			{
				elem.trigger('click');
			}
			else
			{
				target.scrollTo(
					this,
					800,
					{
						offset: -25,
						onAfter: function () {
							elem.trigger('click');
						}
					}
				);
			}
		}

		return elem;
	};

})(jQuery);


$(document).on('click keyup', '#os_search_go, #os_search_value', function (e) {

	let this_id = $(this).attr('id');

	if (this_id == 'os_search_value')
	{
		// if the enter key is pressed, execute search else stop
		if (e.which != 13)
		{
			return;
		}
	}

	let order_id = $.trim($('#os_search_value').val());

	if (!order_id.match(/^[0-9a-z]+$/i))
	{
		bootbox.alert("Invalid character in order ID.");

		return;
	}

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_helpers',
			op: 'order_search',
			order_identifier: order_id
		},
		success: function (json) {
			if (json.processor_success)
			{
				bounce(json.bounce_to);
			}
			else
			{
				bootbox.alert(json.processor_message);
			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error: ' + strError;
		}

	});

});