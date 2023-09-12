var open_state_cookie_name = "";
var select_state_cookie_name = "";
var current_date_range_start = null;
var current_date_range_duration = null;
var current_selected_item = null;

function reports_food_sales_init()
{

	open_state_cookie_name = false; //'dd_pr_tree_open_state';
	select_state_cookie_name = false; //'dd_pr_tree_selected_state';

	if (show_store_selectors)
	{
		init_store_selector();
		init_store_selection();
	}

	init_date_selection();
}

function init_store_selection()
{
	$("#set_selected_stores").off('click').on('click', function (e) {

		var postVal = [];

		var displayValue = {};

		var selectedStoreNodes = $('#store_selector').jstree(true).get_selected();

		for (var i = 0; i < selectedStoreNodes.length; ++i)
		{

			var testNode = $('#store_selector').jstree(true).get_node("#" + selectedStoreNodes[i]);
			if (testNode.state.hidden)
			{
				$('#store_selector').jstree(true).deselect_node("#" + selectedStoreNodes[i]);
				continue;
			}

			if (selectedStoreNodes[i].indexOf("tree_store") == 0)
			{
				var thisID = selectedStoreNodes[i].split("-");
				postVal[i] = thisID[1];

			}
			else if (selectedStoreNodes[i].indexOf("tree_state") == 0)
			{
				var thisNode = $('#store_selector').jstree(true).get_node("#" + selectedStoreNodes[i]);
				displayValue[thisNode.text] = {
					name: thisNode.text,
					children: {}
				};
			}
		}

		for (var i = 0; i < selectedStoreNodes.length; ++i)
		{

			var testNode = $('#store_selector').jstree(true).get_node("#" + selectedStoreNodes[i]);
			if (testNode.state.hidden)
			{
				$('#store_selector').jstree(true).deselect_node("#" + selectedStoreNodes[i]);
				continue;
			}

			if (selectedStoreNodes[i].indexOf("tree_store") == 0)
			{
				// note that parents (states ) are not selected if not all children are selected
				var thisNode = $('#store_selector').jstree(true).get_node("#" + selectedStoreNodes[i]);
				var parentNode = $('#store_selector').jstree(true).get_node("#" + thisNode.parent);

				if (!displayValue[parentNode.text])
				{
					displayValue[parentNode.text] = {
						name: parentNode.text,
						children: {}
					};
				}

				displayValue[parentNode.text]['children'][thisNode.text] = thisNode.text;
			}
		}

		var listHTML = "<div><ul>";
		for (var thisState in displayValue)
		{
			listHTML += "<li>" + thisState;

			//children
			listHTML += "<ul>";

			var chillens = displayValue[thisState].children;
			for (thisTown in chillens)
			{
				listHTML += "<li>" + chillens[thisTown] + "</li>";
			}

			listHTML += "</ul>";
		}
		listHTML += "</ul>";

		$("#chosen_stores_inner").html(listHTML);
		$("#chosen_stores").show();

		store_id = postVal;

		$("#store_selector_open").hide();
		$("#store_selector_closed").show();

		//	$("#menu_info_outer").hide();
		//	$("#guest_list_outer").hide();
		//	$("#item_selector_inner").hide();

	});

	$("#unset_selected_stores").off('click').on('click', function (e) {
		$("#store_selector_open").show();
		$("#store_selector_closed").hide();
	});

}

function select_session(id)
{
	bounce("/?page=admin_main&session=" + id, "_blank");
	$("#dd_session_selector").remove();
}

function select_order(o_id, s_id)
{
	bounce("/?page=admin_main&session=" + s_id + "&order=" + o_id, "_blank");
	$("#dd_session_selector").remove();
}

function init_session_popups()
{
	$("[data-session_starts]").off('click').on('click', function (e) {

		var session_starts = $(this).data('session_starts');
		var session_ids = $(this).data('session_ids');
		var order_ids = $(this).data('order_ids');

		session_starts = session_starts.split(",");
		session_ids = session_ids.toString().split(",");
		order_ids = order_ids.toString().split(",");

		var html = "<div>";

		for (i = 0; i < session_starts.length; i++)
		{
			html += "<div><button class='button' onclick='select_session(" + session_ids[i] + ");'>Go to " + session_starts[i] + "</button>";
			html += "<button class='button' onclick='select_order(" + order_ids[i] + ", " + session_ids[i] + ");'>Order " + order_ids[i] + "</button></div>";
		}

		html += "</div>";

		dd_message({
			title: 'Go to Session or Orders',
			width: 300,
			message: html,
			div_id: "dd_session_selector"
		});

	});
}

function init_export_purchasers()
{
	$("#export_purchasers").off('click').on('click', function () {

		var omit_menu_id = $('#order_since_menu_id').val();

		create_and_submit_form({
			action: "/?page=admin_reports_food_sales",
			input: ({
				export: 'xlsx',
				range_start: current_date_range_start,
				duration: current_date_range_duration,
				store_id: store_id,
				omit_menu_id: omit_menu_id,
				items: current_selected_item
			})
		});

	});

}

function init_show_purchasers()
{

	$("#guest_list_outer").html('');

	$("[id^='rid_']").off('click').on('click', function () {
		var itemList = [];
		var omit_menu_id = $('#order_since_menu_id').val();

		itemList.push(this.id.split("_")[1]);
		current_selected_item = this.id.split("_")[1];

		$("#menu_info_outer").hide();

		$('#guest_list_outer').html('<img style="margin-left:auto; margin-right:auto;" src="' + PATH.image + '/style/throbber_circle.gif" alt="" /> Processing...');
		$("#guest_list_outer").show();

		$.ajax({
			url: 'ddproc.php?processor=admin_food_sales',
			type: 'POST',
			dataType: 'json',
			data: {
				op: "show_purchasers",
				range_start: current_date_range_start,
				duration: current_date_range_duration,
				store_id: store_id,
				omit_menu_id: omit_menu_id,
				items: itemList
			},
			success: function (json) {
				$("#guest_list_outer").html(json.guest_data);
				$("#menu_info_outer").hide();

				init_session_popups();
				init_export_purchasers();

			},
			error: function (objAJAXRequest, strError) {

				$("#guest_list_outer").hide();

				dd_message({
					title: 'Error',
					message: strError
				});
			}
		});

		return true;
	});
}

function init_date_selection()
{
	$("#set_date_range").off('click').on('click', function () {
		var rangeType = $("input[name='pickDate']:checked").val();
		var month = false;
		var year = false;
		var dateStart = false;
		var dateEnd = false;
		var useMenuMonth = true;

		switch (rangeType)
		{
			case "1":
				// single date
				dateStart = $("input[name='single_date']").val();
				break;
			case "2":
				dateStart = $("input[name='range_day_start']").val();
				dateEnd = $("input[name='range_day_end']").val();
				break;
			case "3":
				month = $("#month_popup").val();
				year = $("#year_field_001").val();

				if ($("input[name='menu_or_calendar']:checked").val() == "cal")
				{
					useMenuMonth = false;
				}

				break;
			default:
			// error
		}

		$('#item_selector_inner').html('<img style="margin-left:auto; margin-right:auto;" src="' + PATH.image + '/style/throbber_circle.gif" alt="" /> Processing...');
		$("#menu_info_outer").html("");
		$("#guest_list_outer").html("");

		var searchStr = $("#search_string").val();

		$.ajax({
			url: 'ddproc.php?processor=admin_food_sales',
			type: 'POST',
			dataType: 'json',
			data: {
				op: "set_date_range",
				rangeType: rangeType,
				month: month,
				year: year,
				dateStart: dateStart,
				dateEnd: dateEnd,
				store_id: store_id,
				useMenuMonth: useMenuMonth,
				search_string: searchStr
			},
			success: function (json) {

				current_date_range_start = json.range_start_date;
				current_date_range_duration = json.range_duration;

				$("#item_list_title").html(json.title);
				$("#item_list_title").show();

				$("#item_selector_inner").html(json.item_data);

				init_show_purchasers();
				handle_menu_item_info_button();
				$('#item_selector_inner').removeClass('processing');

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

function handle_menu_item_info_button()
{
	$("[id^='rmid_']").off('click').on('click', function () {

		var entree_id = $(this).data('entree_id');
		var recipe_id = this.id.split("_")[1];
		var menu_id = $(this).data('menu_id');

		$("#menu_info_outer").show();
		$("#guest_list_outer").hide();

		current_selected_item = recipe_id;
		$('#menu_info_outer').html('<img style="margin-left:auto; margin-right:auto;" src="' + PATH.image + '/style/throbber_circle.gif" alt="" /> Processing...');

		$.ajax({
			url: 'ddproc.php?processor=admin_food_sales',
			type: 'POST',
			dataType: 'json',
			data: {
				processor: 'admin_food_sales',
				store_id: store_id,
				op: 'menu_item_info',
				menu_id: menu_id,
				entree_id: entree_id,
				recipe_id: recipe_id
			},
			success: function (json) {
				$("#menu_info_outer").html(json.data);

				handle_tabbed_content();
			},
			error: function (objAJAXRequest, strError) {
				$("#menu_info_outer").hide();
				$("#guest_list_outer").show();

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}

		});
	});
}