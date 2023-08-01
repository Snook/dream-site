var open_state_cookie_name = "";
var select_state_cookie_name = "";



function _override_check_form()
{
/*
	var isEXport = false;

	var submitter = $('#nat_royal_form').data('submitActor');

	if (submitter.attr('name') == 'report_export')
	{
		isEXport = true;
	}


 */
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


	return false;

}

function onTitleClick()
{
}

$(function () {
	init_store_selector('dd_js_tree_nat_royalty_report');
/*
	var submitActor = null;
	var $form = $('#nat_royal_form');
	var $submitActors = $form.find('input[type=submit]');

	$form.submit(function(event) {
		if (null === submitActor) {
			// If no actor is explicitly clicked, the browser will
			// automatically choose the first in source-order
			// so we do the same here
			submitActor = $submitActors[0];
		}

		$form.data('submitActor', submitActor);

		return true;
	});

	$submitActors.click(function(event) {
		$('#nat_royal_form').data('submitActor', this);
		submitActor = this;
	});
*/
});