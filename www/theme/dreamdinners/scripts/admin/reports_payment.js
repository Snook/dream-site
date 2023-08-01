var isPaymentReconciliationReport = false;
var open_state_cookie_name = "";
var select_state_cookie_name = "";

function reports_payment_init()
{

	open_state_cookie_name = false; //'dd_pr_tree_open_state';
	select_state_cookie_name = false; //'dd_pr_tree_selected_state';

	isPaymentReconciliationReport = true;
	init_filters();
	
	init_store_selector('dd_js_tree_payment_report');

}




function init_filters()
{

	$("#pfa_All").change(function(){

		if (this.checked)
		{
			$("[id^=pf_]").each(function() {
				$(this).prop('checked', true).change();
			//	this.checked = true;
			});
		}
		else
		{
			$("[id^=pf_]").each(function() {
				$(this).prop('checked', false).change();
			});
		}

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}



	});
	$('#show_if_balance_due').each(function(){

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

	$('#show_if_balance_due').bind("mouseup change", function(e){

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}

	});


	$('[id^="pf_"]').each(function(){

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

	$('[id^="pf_"]').bind("mouseup change", function(e){

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}

	});

	$("#pfa_All").each(function(){

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

	$("[id^=select_key]").bind("mouseup change", function(e){

		var selectedValue = "";
		var selected = $("input[type='radio'][name='select_key']:checked");
		if (selected.length > 0)
		    selectedValue = selected.val();

		$.totalStorage('select_key', selectedValue);
	});


	if ($.totalStorage('select_key') != null)
	{
	    var $radios = $('input:radio[name=select_key]');
	    $radios.filter('[value='+ $.totalStorage('select_key')  + ']').prop('checked', true);
	}

}


function _override_check_form()
{

	var hasStore = false;
	var hasPaymentTypes = false;


	if (!isFranchiseAccess)
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
			hasStore = true;
		}
		else		
		{
			$("#requested_stores").val("");

			dd_message({
				message: "Please choose at least one store."
			});
		}

	}

	else
	{
		hasStore = true;
	}


	if (isPaymentReconciliationReport)
	{
		$('#pfa_All').each(function() {
			if (this.checked)
			{
				hasPaymentTypes = true;
			}
		});

		if (isPaymentReconciliationReport && !hasPaymentTypes)
		{
			has_type = false;
			$("[id^=pf_]").each(function() {
				if (this.checked)
				{
					hasPaymentTypes = true;
				}
			});
			if (!hasPaymentTypes)
			{
				dd_message({
					message: "Please choose at least one payment type."
				});

			}

		}
	}

	else
	{
		hasPaymentTypes = true;
	}

	return hasPaymentTypes && hasStore;

}


function onTitleClick()
{
}
