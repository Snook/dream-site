var da_open_state_cookie_name = "";
var da_select_state_cookie_name = "";

function reports_dashboard_aggregate_init()
{

	da_open_state_cookie_name = false; //'dd_pr_tree_open_state';
	da_select_state_cookie_name = false; //'dd_pr_tree_selected_state';

	init_store_selector('dd_js_tree_aggregate_report');

//	$("#hide_inactive").prop("disabled", true);


	init_filters();

}


function init_filters()
{

	$('[id^="dagg_"]').each(function(){

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

	$('[id^="dagg_"]').bind("mouseup change", function(e){

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}

	});


}


function storeTypeClick(elem)
{
	var topNode = $("#store_selector").find("#topNode");

	if (elem == "selected_stores")
	{
		$.jstree._focused().open_node(topNode[0]);
	}
	else
	{
		$.jstree._focused().close_node(topNode[0]);
	}


}

function _override_check_form()
{
	if ($("#store_typeselected_stores").is(":checked"))
	{

		var selectedStoreNodes =  $('#store_selector').jstree(true).get_selected();

		var postVal = [];

		var count = 0;

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
				postVal[count++] = thisID[1];
			}
		}

		if (postVal.length > 0)
		{
			$("#requested_stores").val(postVal);
			return true;
		}
		else
		{
			$("#requested_stores").val("");

			dd_message({
				message: "Please choose at least one store."
			});

			return false;
		}

	}


	return true;
}

/*


function init_store_selector()
{

	$.jstree._themes = PATH.css + "/admin/jquery/jsTree/";

	if (true)
	{
		$("#store_selector")
		.bind("loaded.jstree", function (event, data) {
			$("#store_selector").show();
			//$("#store_selector").jstree("open_all", $("#topNode"), true);
		})
	    .jstree({
	            "plugins" : [ "themes", "ui", "html_data", "checkbox", "cookies" ],
	            "themes" : {
	            		        "theme" : "dd_tree",
	            	            "dots" : true,
	            		        "icons" : false
	            	        },
    	        "core" : {
    	        			"initially_open" : [ "topNode" ]
    	        		  },
    		  "checkbox" : {
    			  "override_ui" : true
    			  },
    			"ui": {
    				'initially_select' : []
    			},
		        "cookies" : {
					"save_opened" : da_open_state_cookie_name,
					"save_selected" : da_select_state_cookie_name
				}
	    });

	}

}
*/


function onTitleClick()
{
}
