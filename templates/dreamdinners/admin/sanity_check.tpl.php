<?php $this->assign('page_title', 'Sanity Check'); ?>
<?php $this->assign('topnav', 'tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">
		<div class="row mb-4">
			<div class="col">
				<h1 id="fadash">Customer Website</h1>
				<div class="list-group">
					<a class="list-group-item list-group-item-action" href="/?page=login" target="_blank">Login</a>
					<a class="list-group-item list-group-item-action" href="/?page=my_account" target="_blank">View My Account</a>
					<a class="list-group-item list-group-item-action" href="/?page=account" target="_blank">Edit My Account</a>
					<a class="list-group-item list-group-item-action" href="/?page=my_meals&tab=nav-past_orders" target="_blank">View My Orders</a>
					<a class="list-group-item list-group-item-action" href="/?page=recipe_resources" target="_blank">View Recipe Resources</a>
					<a class="list-group-item list-group-item-action" href="/?page=item&recipe=1030&tab=cooking" target="_blank">View Recipe Cooking Instructions</a>
					<a class="list-group-item list-group-item-action" href="/?page=item&recipe=1030&tab=nutrition" target="_blank">View Recipe Nutrionals</a>
					<a class="list-group-item list-group-item-action" href="/session-menu" target="_blank">View Session Menu</a>
					<a class="list-group-item list-group-item-action" href="/?page=browse_menu" target="_blank">View Menu - Long URL</a>
					<a class="list-group-item list-group-item-action" href="/menu/244-<?=$this->current_menu_id; ?>" target="_blank">View Menu - Short URL</a>
					<a class="list-group-item list-group-item-action" href="/menu/244-<?=$this->current_menu_id; ?>-starter" target="_blank">View Starter Menu - Short URL</a>
					<a class="list-group-item list-group-item-action" href="/menu/244-<?=$this->current_menu_id; ?>-events" target="_blank">View Event Menu - Short URL</a>
					<a class="list-group-item list-group-item-action" href="/?page=gift_card_order" target="_blank">View Gift Card Page</a>
					<a class="list-group-item list-group-item-action" href="/location/244" target="_blank">View Store Info - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/?page=locations" target="_blank">Start Order Process - Locations Page</a>
					<a class="list-group-item list-group-item-action" href="/?page=print&store=244&menu=<?=$this->current_menu_id; ?>" target="_blank">Print <?=$this->current_menu_name; ?> Menu PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/?page=print&store=244&menu=<?=$this->next_menu_id; ?>" target="_blank">Print <?=$this->next_menu_name; ?> Menu PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/?page=print&store=244&menu=<?=$this->current_menu_id; ?>&nutrition=true" target="_blank">Print <?=$this->current_menu_name; ?> Nutrionals PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/?page=print&store=244&menu=<?=$this->next_menu_id; ?>&nutrition=true" target="_blank">Print <?=$this->next_menu_name; ?> Nutrionals PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/?page=nutritionals&store=244&menu=<?=$this->current_menu_id; ?>&nutrition=true" target="_blank">View <?=$this->current_menu_name; ?> Nutrionals - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/?page=nutritionals&store=244&menu=<?=$this->next_menu_id; ?>&nutrition=true" target="_blank">View <?=$this->next_menu_name; ?> Nutrionals - Mill Creek</a>

				</div>
			</div>
		</div>

		<div class="row mb-4">
			<div class="col">
				<h1 id="fadash">BackOffice</h1>
				<div class="list-group">
					<a class="list-group-item list-group-item-action" href="/?page=admin_main" target="_blank">View Home</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_order_history&id=<?=$this->current_user_id; ?>" target="_blank">View Orders</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_order_mgr&user=<?=$this->current_user_id; ?>" target="_blank">Add Order</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_user_details&id=<?=$this->current_user_id; ?>" target="_blank">View Account</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_account&id=<?=$this->current_user_id; ?>" target="_blank">Edit Account</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_store_details&id=244" target="_blank">View Store Information - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_session_mgr" target="_blank">View <?=$this->current_menu_name; ?> Session Calendar</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_create_session&menu=<?=$this->current_menu_id; ?>" target="_blank">Create Session</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_session_template_mgr" target="_blank">View Session Template Manager</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_finishing_touch_printable_form&store_id=244&menu_id=<?=$this->current_menu_id; ?>" target="_blank">Print <?=$this->current_menu_name; ?> Sides & Sweets</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/?page=admin_reports_entree" data-params='<?=$this->params_entree_report; ?>'>Run Entree Report for <?=$this->current_menu_name; ?> Menu</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/?page=admin_reports_growth_scorecard" data-params='<?=$this->params_growth_scoreboard; ?>'>Growth Scoreboard <?=$this->current_menu_name; ?></a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/?page=admin_reports_customer_menu_item_labels&interface=1" data-params='<?=$this->params_cooking_instructions; ?>'> <?=$this->current_menu_name; ?> All Core Cooking Instructions PDF</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/?page=admin_reports_menu_item_nutritional_labels" data-params='<?=$this->params_nutritional_labels; ?>'> <?=$this->current_menu_name; ?> All Core Nutritional Labels PDF</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/?page=admin_reports_customer_menu_item_labels&interface=1" data-params='<?=$this->params_cooking_instructions; ?>'> <?=$this->current_menu_name; ?> All Core Cooking Instructions PDF</a>


				</div>
			</div>
		</div>
	</div>

<script>
	$(document).ready(function(){
		$('.dynamic-form-submit').click(function(e){
			e.preventDefault();

			let action = $(this).data('action');
			let params = $(this).data('params');
			//params = JSON.parse(params);

			post_to_url(action, params, 'post');
		});
	});
	function post_to_url(path, params, method) {
		method = method || "post";

		var form = document.createElement("form");
		form.setAttribute("method", method);
		form.setAttribute("action", path);
		form.setAttribute("target", "_blank");

		for(var key in params) {
			if(params.hasOwnProperty(key)) {
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", key);
				hiddenField.setAttribute("value", params[key]);

				form.appendChild(hiddenField);
			}
		}

		document.body.appendChild(form);
		form.submit();
	}
</script>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>