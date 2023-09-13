// fullcalendar handler
document.addEventListener('DOMContentLoaded', function () {

	var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
		aspectRatio: (function () {
			if ($(window).width() > 768) {
				return 2;
			} else {
				return 1.35;
			}
		})(),
		themeSystem: 'bootstrap',
		headerToolbar: {
			left: 'prev,today,next',
			center: 'title',
			right: 'listMonth,dayGridMonth'
		},
		fixedWeekCount: false,

		validRange: {
			start: calendarJS.start,
			end: calendarJS.end
		},
		initialView: (function () {
			return 'listMonth';
			/* sets default view based on window size
			if ($(window).width() <= 768) {
				return 'listMonth';
			} else {
				return 'dayGridMonth';
			}
			*/
		})(),

		events: calendarJS.sessions,
		height: "auto",
		datesSet: function (info) {
			$('#calendar_month').text(info.view.title);
			$('.fc-header-toolbar .fc-toolbar-title').addClass('d-none d-md-block');
			$('.fc-list-event').addClass('cursor-pointer');
			if($('.title-exists').length == 0)
			$('.fc-list-day .fc-list-day-text').append('<span class="font-weight-normal font-size-small d-none d-md-inline title-exists"> - Select a time below to get started</span>');
		},
		eventContent: function(arg, createElement){
			$('#calendar_results').empty();

			return createElement('i', {}, '');
		},
		eventDidMount: function (info) {

			$('#calendar_results').empty();

			if(info.view.type=='dayGridMonth'){

				$(info.el).addClass('d-none');

			if($('.fc-daygrid-day-events').children().length > 0){

				$('.fc-daygrid-day-events').children().parent().text('Times');
				$('.fc-daygrid-day-events').addClass('text-center h6 text-decoration-underline cursor-pointer font-weight-semi-bold text-break');

			}

			}

			if (info.view.type == 'listMonth') {

				if (info.event.extendedProps.remaining_slots <= 0) {
					var title = '<span class="text-decoration-none text-capitalize">' + info.event.title + '</span>';

				} else {
					var title = '<span class="btn-link text-decoration-underline text-capitalize">' + info.event.title + '</span>';
				}

				if (!$.isEmptyObject(info.event.extendedProps)) {
					//triggers if the session has no remaining slots

					if (info.event.extendedProps.remaining_slots <= 0) {
						//change the list element to gray to indicate no available slots are left
						$(info.el).find('.fc-list-event-title').addClass('text-gray-med');
						$(info.el).find('.fc-list-event-time').addClass('text-gray-med');
						$(info.el).find('.fc-list-event-dot').removeClass('fc-list-event-dot');

						//prevent mouseover interactions from events that the user cant join
						$(info.el).removeClass('cursor-pointer');
						$(info.el).removeClass('fc-event-session-made_for_you');
						$(info.el).removeClass('fc-event-future');
						$(info.el).removeClass('fc-event');
						$(info.el).removeClass('fc-list-event');

					}


					if ($.isNumeric(info.event.extendedProps.session_count)) {
						title = info.event.title + '<span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">' + info.event.extendedProps.session_count + ' session' + ((info.event.extendedProps.session_count != 1) ? 's' : '') + ' available</span></span>';
					}

					if (!$.isEmptyObject(info.event.extendedProps.session_title) && info.event.extendedProps.session_title != info.event.title) {
						title = '<span class="btn-link text-decoration-underline text-capitalize">' + info.event.title + '</span><span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">' + info.event.extendedProps.session_title + '</span></span>';
					}

					if ([
						'REMOTE_PICKUP',
						'REMOTE_PICKUP_PRIVATE'
					].includes(info.event.extendedProps.session_type_true)) {
						if (info.event.extendedProps.session_remote_pickup) {
							if (info.event.extendedProps.session_type_true == 'REMOTE_PICKUP_PRIVATE') {
								title = '<span class="btn-link text-decoration-underline text-capitalize">' + info.event.title + '</span><span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline"> Private event hosted by ' + info.event.extendedProps.session_host_informal_name + '</span></span>';

							} else {
								title = '<span class="btn-link text-decoration-underline text-capitalize">' + info.event.title + '</span><span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">' + info.event.extendedProps.session_title + '</span><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">' + info.event.extendedProps.session_remote_pickup + '</span></span>';
							}
						}
					}

					if (['FUNDRAISER'].includes(info.event.extendedProps.session_type_true)) {
						if (info.event.extendedProps.fundraiser_name) {
							title = '<span class="btn-link text-decoration-underline text-capitalize">' + info.event.title + '</span><span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">Benefits ' + info.event.extendedProps.fundraiser_name + '</span></span>';
						}
					}

					if ([
						'DREAM_TASTE',
						'PRIVATE_SESSION'
					].includes(info.event.extendedProps.session_type_true)) {
						if (info.event.extendedProps.session_host_informal_name) {
							title = '<span class="btn-link text-decoration-underline text-capitalize">' + info.event.title + '</span><span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">Hosted by ' + info.event.extendedProps.session_host_informal_name + '</span></span>';
						}
					}
				}

				if ($.isNumeric(info.event.extendedProps.remaining_slots)) {
					title += '<span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">' + info.event.extendedProps.remaining_slots + ' spot' + ((info.event.extendedProps.remaining_slots != 1) ? 's' : '') + ' remaining</span></span>';

					if ([
						'STANDARD'
					].includes(info.event.extendedProps.session_type_true)) {
						title += '<span class="font-size-small"><span class="d-none d-md-inline"> - </span><span class="d-block d-md-inline">' + info.event.extendedProps.remaining_intro_slots + ' Starter Pack spot' + ((info.event.extendedProps.remaining_intro_slots != 1) ? 's' : '') + ' remaining</span></span>';
					}
				}

				if (info.event.extendedProps.is_open_for_customization)
				{
					title += '&nbsp;<i class="dd-icon icon-customize text-orange font-size-small" data-toggle="tooltip" data-placement="top" title="Customization options available at checkout"></i>';
				}

				$(info.el).find('.fc-list-event-title').html(title);
			}
			}
		,

		eventClick: function (info) {

			if (info.event.extendedProps.remaining_slots <= 0) {
				//disables link clicking on calendar elements
				info.jsEvent.preventDefault();
			} else {
				if (info.event.extendedProps.eventType === 'session') {
					if (!info.event.extendedProps.session_has_password) {
						window.location.href = '/session?ref=store&sid=' + info.event.extendedProps.id;
					} else {
						bootbox.prompt("This event requires an invite code.", function (result) {

							if (result) {
								var session_id = info.event.extendedProps.id;
								var pwd = result.trim();

								$.ajax({
									url: 'ddproc.php',
									type: 'POST',
									timeout: 20000,
									dataType: 'json',
									data: {
										processor: 'cart_session_processor',
										op: 'save',
										pwd: pwd,
										sid: session_id
									},
									success: function (json) {
										if (json.result_code == 1) {
											bounce('/session-menu');
										} else {
											bootbox.alert(json.processor_message)
										}
									},
									error: function (objAJAXRequest, strError) {
										bootbox.alert('Unexpected error')
									}
								});
							}

						});
					}
				}
			}
		}
		,
		eventMouseEnter: function (info) {
			if (info.view.type == 'listMonth' && info.event.extendedProps.eventType === 'session') {
				$(info.el).addClass('bg-gray-light');
			}
		}
		,
		eventMouseLeave: function (info) {
			if (info.view.type == 'listMonth' && info.event.extendedProps.eventType === 'session') {

				$(info.el).removeClass('bg-gray-light');

			}
		}
		,

		dateClick: function (info) {
			$('#calendar_results').empty();
			// change the day's background color just for fun
			//info.dayEl.style.backgroundColor = 'red';

			//arrays for filtering out the events and properties that we need
			events = calendar.getEvents();

			savedEvents = [];
			savedIds = [];
			savedTypes = [];
			savedSessionHasPwds = [];
			savedEventNotes = [];
			//loop through given events to see if dates match the clicked date on calendar
			for(i =0; i<events.length; i++){
				//comparing dates
				if(strcmp(events[i].start.toLocaleDateString().trim(), info.date.toLocaleDateString().trim())==0){

					//load dates into array
						savedEvents.push(events[i]);
				}
					}
			eventsString = '';
			for(i = 0; i< savedEvents.length; i++){
				//2digit time for calendar results appended to the eventsString
				date = savedEvents[i].start.toLocaleTimeString("en-gb", {  hour: "2-digit", minute: "2-digit", hour12:true });
				if(strcmp(date.charAt(0),'0')==0){
					date = date.replace('0', '');

				}
				if(strcmp(date,"0:00 pm") == 0){

					eventsString += "12:00 pm, " + savedEvents[i].title;
				}else if(strcmp(date,"0:00 am") == 0){
					eventsString += "12:00 am, " + savedEvents[i].title;
				}else if(strcmp(date,"0:30 pm") == 0){
					eventsString += "12:30 pm, " + savedEvents[i].title;
				}else if(strcmp(date,"0:30 am") == 0){
					eventsString += "12:30 am, " + savedEvents[i].title;
				}
				else {
					eventsString += date + "," + " " + savedEvents[i].title;
				}
				//need session event id for button link later
				savedIds[i] = savedEvents[i].extendedProps.id;
				//need to know if the session has a password for button link later
				savedSessionHasPwds[i] = savedEvents[i].extendedProps.session_has_password;
				//need type of event for button link later
				savedTypes[i] = savedEvents[i].extendedProps.eventType;
				savedEventNotes[i] = savedEvents[i].extendedProps.session_title;
				if(savedEvents[i].extendedProps.remaining_slots!= null){
					//append remaining slots to the eventsString with | character for usage of split() function
					eventsString+= ", Remaining Slots: " + savedEvents[i].extendedProps.remaining_slots + '|';
				}
			}
			//split the extracted data from eventsString by defined | character
			eventsToList = eventsString.split('|');
			finalEvents= eventsToList.toString().split(',');

			//secondary counting variable
			counter = 0;
			if(eventsToList.length==1){

			}else {
				//remove last index: the array always has 1 too many from the | insertion
				eventsToList.pop();
			}
			//general Date title for calendar results
			var output = '<h5 class="text-capitalize font-weight-bold mt-4" >'+
				info.date.toLocaleDateString("en-gb", { weekday: "long", year: "numeric", month: "short", day: "numeric" }) +
				'</h5><br>';
			//this loop counts by 3's since each event has 3 properties that need to be listed and the arrays we've created allow for us to do that
			for(i = 0; i<finalEvents.length; i+=3){

				//a check to make sure it is less than 3, which means its the menu announcement event which doesnt follow the same logic as an actual event.
				if(finalEvents.length == 2){
					output += '<p class="h6 text-left d-inline-block w-25" >' + finalEvents[i] + '</p>';
					output += '<p class="h6 text-left d-inline-block w-50">' + finalEvents[i + 1] + '</p>';
				}else {
					//this grabs the Date of the event and checks to see if the event is still open to the customer via the extendedProperty.remaining_slots
					if (finalEvents[i] !== undefined && finalEvents[i + 2] !== undefined) {

						if (strcmp(finalEvents[i + 2].trim(), "Remaining Slots: 0") == 0) {
							output += '<p class="h6 text-left d-inline-block text-gray-med w-25">' + finalEvents[i] + '</p>';
						} else {
							output += '<p class="h6 text-left d-inline-block w-25">' + finalEvents[i] + '</p>';
						}
					}
					//this grabs the title of the event and checks to see if the event is still open to the customer via the extendedProperty.remaining_slots
					if (finalEvents[i + 1] !== undefined && finalEvents[i + 2] !== undefined) {
						if (strcmp(finalEvents[i + 2].trim(), "Remaining Slots: 0") == 0) {
							output += '<p href="#" id="calendarResultsBtn${counter}" class="h6 text-left d-inline-block text-gray-med btn text-uppercase w-50 disabled">' + finalEvents[i + 1] + '</p>';
							counter++;
						} else {
							if(savedEventNotes[counter]===""){
								output += `<p id="calendarResultsBtn${counter}" class="h6 text-left d-inline-block btn btn-link text-decoration-underline cursor-pointer w-50">` + finalEvents[i+1]+`</p>`;
								counter++;
							}else {
								output += `<p id="calendarResultsBtn${counter}" class="h6 text-left d-inline-block btn btn-link text-decoration-underline cursor-pointer w-50"><br>` + finalEvents[i + 1] + '<br><label class="h6 text-capitalize text-dark font-size-small" id="notes">' + ' ' + savedEventNotes[counter] + '</label>' + `</p>`;
								counter++;
							}
						}

					}
					//this grabs the remaining slots for the event and checks if the event is still open to the customer via the extendedProperty.remaining_slots
					if (finalEvents[i + 2] !== undefined) {
						if (strcmp(finalEvents[i + 2].trim(), "Remaining Slots: 0") == 0) {
							output += '<p class="h6 text-left d-inline-block text-gray-med w-25">' + finalEvents[i + 2].replace('Slots', 'Spots') + '</p><hr/>';
						} else {
							output += '<p class="h6 text-left d-inline-block w-25">' + finalEvents[i + 2].replace('Slots', 'Spots') + '</p><hr/>';
						}
					}
				}

			}

			//output the elements we've created to the calendar_results below the calendar
			$('#calendar_results').html(output);

			for(i = 0; i<counter; i++) {
				//on click function for the links in calender_results, checks the elements id and matches it to the saved property arrays from the top of function
				//and fires the onclick function eventClickCalendar that runs the location to the new page via the event id
				$("#calendarResultsBtn"+i).on("click", function (){
					eventClickCalendar(savedTypes[parseInt($(this).attr('id').slice(-1),10)], savedSessionHasPwds[parseInt($(this).attr('id').slice(-1),10)], savedIds[parseInt($(this).attr('id').slice(-1),10)]);
				});

			}
			$('html, body').animate({
				scrollTop: $('#calendar_results').offset().top
			}, 2000);


		}});

	calendar.render();

});


//utility function to compare strings
function strcmp(a, b) {
	if (a.toString() < b.toString()) return -1;
	if (a.toString() > b.toString()) return 1;
	return 0;
}

//derived from eventClick function, altered to allow for variables instead of the info from FullCalendar
function eventClickCalendar(eventType, sessionPassword, id) {

		if (eventType === 'session') {
			if (!sessionPassword) {
				window.location.href = '/session?ref=store&sid=' + id;
			} else {
				bootbox.prompt("This event requires an invite code.", function (result) {
					if (result) {

						var session_id = id;
						var pwd = result.trim();

						$.ajax({
							url: 'ddproc.php',
							type: 'POST',
							timeout: 20000,
							dataType: 'json',
							data: {
								processor: 'cart_session_processor',
								op: 'save',
								pwd: pwd,
								sid: session_id
							},
							success: function (json) {

								if (json.result_code == 1) {
									bounce('/session-menu');
								} else {
									bootbox.alert(json.processor_message)
								}
							},
							error: function (objAJAXRequest, strError) {
								bootbox.alert('Unexpected error')
							}
						});
					}

				});
		}
	}
}