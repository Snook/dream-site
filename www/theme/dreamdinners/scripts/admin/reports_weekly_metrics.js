var da_open_state_cookie_name = "";
var da_select_state_cookie_name = "";

function reports_dashboard_aggregate_init()
{

	wm_open_state_cookie_name = false; //'dd_pr_tree_open_state';
	wm_select_state_cookie_name = false; //'dd_pr_tree_selected_state';

	init_store_selector('dd_js_tree_weekly_metrics');
	
//	$("#hide_inactive").prop("disabled", true);
	
	
	init_filters();

}


function init_filters()
{
	
	$('[id^="wm_"]').each(function(){

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

	$('[id^="wm_"]').bind("mouseup change", function(e){

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
		
		var postVal = new Array();
		
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


function onTitleClick()
{
}
