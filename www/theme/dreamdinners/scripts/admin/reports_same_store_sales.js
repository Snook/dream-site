var sss_open_state_cookie_name = "";
var sss_select_state_cookie_name = "";

function reports_same_store_sales_init()
{

	sss_open_state_cookie_name = false; //'dd_pr_tree_open_state';
	sss_select_state_cookie_name = false; //'dd_pr_tree_selected_state';

	init_store_selector();

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
    	        			"initially_closed" : [ "topNode" ]
    	        		  },
    		  "checkbox" : {
    			  "override_ui" : true
    			  },
    			"ui": {
    				'initially_select' : []
    			},
		        "cookies" : {
					"save_opened" : sss_open_state_cookie_name,
					"save_selected" : sss_select_state_cookie_name
				}
	    });

	}

}


function onTitleClick()
{
}
