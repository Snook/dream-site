function init_store_selector(key)
{
	$('#store_selector').bind("loaded.jstree", function (event, data)
	{
		$("#store_selector").show();
	}).jstree({
		"types": {
			"default": {
				"li_attr": {
					"style": "color:black;"
				},
				"icon": false

			},
			"active_corp": {
				"li_attr": {
					"style": "color:black;"
				},
				"icon": PATH.image_admin + "/icon/corp_active.png"
			},
			"inactive_corp": {
				"li_attr": {
					"style": "color:gray;"
				},
				"icon": PATH.image_admin + "/icon/corp_inactive.png"
			},
			"active_franchise": {
				"li_attr": {
					"style": "color:black;"
				},
				"icon": PATH.image_admin + "/icon/franchise_active.png"
			},
			"inactive_franchise": {
				"li_attr": {
					"style": "color:gray;"
				},
				"icon": PATH.image_admin + "/icon/franchise_inactive.png"
			},
			"active_dist_ctr": {
				"li_attr": {
					"style": "color:black;"
				},
				"icon": PATH.image_admin + "/icon/DC_active.png"
			},
			"inactive_dist_ctr": {
				"li_attr": {
					"style": "color:gray;"
				},
				"icon": PATH.image_admin + "/icon/DC_inactive.png"
			},

		},
		"plugins": [
			"checkbox",
			"types",
			"state"
		],
		"core": {
			"themes": {
				"name": "default",
				"dots": true,
				"icons": true,
				"stripes": true
			}
		},
		"state": {
			"key": key,
			"state_events": "",
			"filter": stateFilter

		}
	});

	var storeTree = $.jstree.reference('#store_selector');

	if (storeTree != null)
	{
		storeTree.clear_state();

		$("#hide_inactive").off('change').change(function ()
		{
			storeTree.save_state();
			storeTree.open_all();
			storeTree.show_all();
			storeTree.deselect_all();
			showOrHideInactiveStores($("#hide_inactive").is(":checked"));
			storeTree.close_all();
			storeTree.restore_state();

		});

		storeTree.open_all();
		showOrHideInactiveStores(true);
		storeTree.close_all();
		storeTree.open_node('#topNode');
	}

	$("#ddd_menu").menu({
		select: function (event, ui)
		{
			var inactive_hidden = $("#hide_inactive").is(":checked");
			var selection = ui.item[0].id;
			switch (selection)
			{
				case '#reset_tree':
					resetTree();
					break;
				case '#select_all_tree':
					select_all(inactive_hidden);
					break;
				case '#select_franchise_tree':
					select_all_franchise(inactive_hidden);
					break;
				case '#select_corporate_tree':
					select_all_corporate(inactive_hidden);
					break;
				case '#select_dist_ctr_tree':
					select_all_dist_ctrs(inactive_hidden);
					break;
				case '#select_non_DC_tree':
					select_all_but_dist_ctrs(inactive_hidden);
					break;
				default:
			}

			$("#ddd_menu").hide();
		}
	});

	$("#ddd_menu").css("width", "200px");

	$("#ddd_menu").position({
		my: "left top",
		at: "left bottom",
		of: $("#tree_menu_button"), // or $("#otherdiv")
		collision: "fit"
	});

	$("#tree_menu_button").off('mousedown').mousedown(function ()
	{
		$("#ddd_menu").show();

		$("#ddd_menu").hover(function ()
		{
			// cancel timeout
			$.doTimeout('ddd_menu_timer_hide');

			// short timeout to prevent menus from popping up when moused over
			$.doTimeout('ddd_menu_timer_show', 100, function ()
			{
				$('#ddd_menu').show();
			});

		}, function ()
		{
			// cancel timeout
			$.doTimeout('ddd_menu_timer_show');

			// short timeout to prevent menus from disappearing when moused away
			$.doTimeout('ddd_menu_timer_hide', 150, function ()
			{
				$('#ddd_menu').hide();
			});
		});
	});
}

function stateFilter(state)
{
	// if we are restoring with
	// hide inactive as true be sure inactive stores are not selected

	var inactive_hidden = $("#hide_inactive").is(":checked");

	if (inactive_hidden)
	{
		numNodes = state.core.selected.length;

		for (i = 0; i < numNodes; i++)
		{
			var thisNodeID = state.core.selected[i];
			var thisNode = $('#store_selector').jstree(true).get_node("#" + thisNodeID);
			var datuh = thisNode.data.jstree;
			if (datuh && datuh.type && (datuh.type == 'inactive_corp' || datuh.type == 'inactive_franchise' || datuh.type == 'us_state' ))
			{
				state.core.selected[i] = null;
			}

		}
	}

	return state;
}

function showOrHideInactiveStores(hide)
{
	var process = setInactiveVisible;
	if (hide)
	{
		process = setInactiveInvisible;
	}

	var storeTree = $.jstree.reference('#store_selector');
	var rootNode = storeTree.get_node('#topNode');
	var childNodes = storeTree.get_children_dom(rootNode);

	for (var i = 0; i < childNodes.length; ++i)
	{
		processRecursive(storeTree, childNodes[i], process, hide);
	}
}

function setInactiveVisible(storeTree, node, hide_inactive)
{
	var datuh = $(node).data('jstree');

	if (datuh && datuh.type && (datuh.type == 'inactive_corp' || datuh.type == 'inactive_franchise' || datuh.type == 'inactive_dist_ctr'))
	{
		storeTree.show_node(node);
	}
}

function setInactiveInvisible(storeTree, node)
{
	var datuh = $(node).data('jstree');

	if (datuh && datuh.type && (datuh.type == 'inactive_corp' || datuh.type == 'inactive_franchise' || datuh.type == 'inactive_dist_ctr'))
	{
		storeTree.hide_node(node);
		storeTree.deselect_node(node);
	}
}

function selectAndShowCorporateStores(storeTree, node, inactive_hidden)
{
	var datuh = $(node).data('jstree');

	if (datuh && datuh.type && (datuh.type == 'active_corp' || (datuh.type == 'inactive_corp' && !inactive_hidden)))
	{
		storeTree.select_node(node);
		storeTree.show_node(node);
	}
	else if (datuh && datuh.type && datuh.type != 'us_state')
	{
		storeTree.hide_node(node);
	}
}

function selectAndShowFranchiseStores(storeTree, node, inactive_hidden)
{
	var datuh = $(node).data('jstree');

	if (datuh && datuh.type && (datuh.type == 'active_franchise' || (datuh.type == 'inactive_franchise' && !inactive_hidden)))
	{
		storeTree.select_node(node);
		storeTree.show_node(node);
	}
	else if (datuh && datuh.type && datuh.type != 'us_state')
	{
		storeTree.hide_node(node);
	}
}

function selectAndShowDistCtrs(storeTree, node, inactive_hidden)
{
	var datuh = $(node).data('jstree');

	if (datuh && datuh.type && (datuh.type == 'active_dist_ctr' || (datuh.type == 'inactive_dist_ctr' && !inactive_hidden)))
	{
		storeTree.select_node(node);
		storeTree.show_node(node);
	}
	else if (datuh && datuh.type && datuh.type != 'us_state')
	{
		storeTree.hide_node(node);
	}
}

function selectAndShowNonDistCtrs(storeTree, node, inactive_hidden)
{
	var datuh = $(node).data('jstree');

	if (datuh && datuh.type && (datuh.type == 'active_franchise' || (datuh.type == 'inactive_franchise' && !inactive_hidden) || datuh.type == 'active_corp' || (datuh.type == 'inactive_corp' && !inactive_hidden)))
	{
		storeTree.select_node(node);
		storeTree.show_node(node);
	}
	else if (datuh && datuh.type && datuh.type != 'us_state')
	{
		storeTree.hide_node(node);
	}
}


function selectAndShowAllStores(storeTree, node, inactive_hidden)
{
	var datuh = $(node).data('jstree');

	if (datuh && datuh.type && (datuh.type == 'active_franchise' || (datuh.type == 'inactive_franchise' && !inactive_hidden) || datuh.type == 'active_corp' || (datuh.type == 'inactive_corp' && !inactive_hidden)
		|| datuh.type == 'active_dist_ctr' || (datuh.type == 'inactive_dist_ctr' && !inactive_hidden)))
	{
		storeTree.select_node(node);
		storeTree.show_node(node);
	}
}

function resetAll(storeTree, node, inactive_hidden)
{
	storeTree.deselect_node(node);

	var datuh = $(node).data('jstree');

	var is_inactive = (datuh && datuh.type && (datuh.type == 'inactive_franchise' || datuh.type == 'inactive_corp' || datuh.type == 'inactive_dist_ctr'));

	if (!inactive_hidden || !is_inactive)
	{
		storeTree.show_node(node);
	}

}

function processRecursive(storeTree, node, callback, inactive_hidden)
{
	callback(storeTree, node, inactive_hidden);

	var childNodes = storeTree.get_children_dom(node);

	for (var i = 0; i < childNodes.length; ++i)
	{
		processRecursive(storeTree, childNodes[i], callback, inactive_hidden);
	}
}

function begin_process(process, inactive_hidden)
{

	var storeTree = $.jstree.reference('#store_selector');
	storeTree.open_all();

	var rootNode = storeTree.get_node('#topNode');
	var childNodes = storeTree.get_children_dom(rootNode);

	for (var i = 0; i < childNodes.length; ++i)
	{
		processRecursive(storeTree, childNodes[i], process, inactive_hidden);
	}

}

function resetTree(inactive_hidden)
{
	begin_process(resetAll, inactive_hidden);
	return false;
}

function select_all(inactive_hidden)
{
	resetTree(inactive_hidden);
	begin_process(selectAndShowAllStores, inactive_hidden);
	return true;
}

function select_all_active(inactive_hidden)
{
	begin_process(selectAndShowAllActiveStores, true);
	return false;
}

function select_all_franchise(inactive_hidden)
{
	resetTree(inactive_hidden);
	begin_process(selectAndShowFranchiseStores, inactive_hidden);
	return false;
}

function select_all_active_franchise()
{
	resetTree(true);
	begin_process(selectAndShowActiveFranchiseStores, true);
	return false;
}

function select_all_corporate(inactive_hidden)
{
	resetTree(inactive_hidden);
	begin_process(selectAndShowCorporateStores, inactive_hidden);
	return false;
}

function select_all_active_corporate()
{
	resetTree(true);
	begin_process(selectAndShowActiveCorporateStores, true);
	return false;
}

function select_all_dist_ctrs(inactive_hidden)
{
	resetTree(inactive_hidden);
	begin_process(selectAndShowDistCtrs, inactive_hidden);
	return false;
}

function select_all_active_dist_ctrs()
{
	resetTree(true);
	begin_process(selectAndShowActiveDistCtrs, true);
	return false;
}

function select_all_but_dist_ctrs(inactive_hidden)
{
	resetTree(inactive_hidden);
	begin_process(selectAndShowNonDistCtrs, inactive_hidden);
	return false;
}