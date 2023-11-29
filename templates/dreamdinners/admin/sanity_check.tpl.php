<?php $this->assign('page_title', 'Sanity Check'); ?>
<?php $this->assign('topnav', 'tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">
		<div class="row mb-4">
			<div class="col">
				<h1 id="fadash">Customer Website</h1>
				<div class="list-group">
					<a class="list-group-item list-group-item-action" href="/login" target="_blank">Login</a>
					<a class="list-group-item list-group-item-action" href="/my_account" target="_blank">View My Account</a>
					<a class="list-group-item list-group-item-action" href="/account" target="_blank">Edit My Account</a>
					<a class="list-group-item list-group-item-action" href="/my_meals?tab=nav-past_orders" target="_blank">View My Orders</a>
					<a class="list-group-item list-group-item-action" href="/recipe-resources" target="_blank">View Recipe Resources</a>
					<a class="list-group-item list-group-item-action" href="/item?recipe=1030&tab=cooking" target="_blank">View Recipe Cooking Instructions</a>
					<a class="list-group-item list-group-item-action" href="/item?recipe=1030&tab=nutrition" target="_blank">View Recipe Nutrionals</a>
					<a class="list-group-item list-group-item-action" href="/session-menu" target="_blank">View Session Menu</a>
					<a class="list-group-item list-group-item-action" href="/browse-menu" target="_blank">View Menu - Long URL</a>
					<a class="list-group-item list-group-item-action" href="/menu/244-<?=$this->current_menu_id; ?>" target="_blank">View Menu - Short URL</a>
					<a class="list-group-item list-group-item-action" href="/menu/244-<?=$this->current_menu_id; ?>-starter" target="_blank">View Starter Menu - Short URL</a>
					<a class="list-group-item list-group-item-action" href="/menu/244-<?=$this->current_menu_id; ?>-events" target="_blank">View Event Menu - Short URL</a>
					<a class="list-group-item list-group-item-action" href="/gift-card-order" target="_blank">View Gift Card Page</a>
					<a class="list-group-item list-group-item-action" href="/location/244" target="_blank">View Store Info - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/locations" target="_blank">Start Order Process - Locations Page</a>
					<a class="list-group-item list-group-item-action" href="/print?store=244&menu=<?=$this->current_menu_id; ?>" target="_blank">Print <?=$this->current_menu_name; ?> Menu PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/print?store=244&menu=<?=$this->next_menu_id; ?>" target="_blank">Print <?=$this->next_menu_name; ?> Menu PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/print?store=244&menu=<?=$this->current_menu_id; ?>&nutrition=true" target="_blank">Print <?=$this->current_menu_name; ?> Nutrionals PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/print?store=244&menu=<?=$this->next_menu_id; ?>&nutrition=true" target="_blank">Print <?=$this->next_menu_name; ?> Nutrionals PDF - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/nutritionals?store=244&menu=<?=$this->current_menu_id; ?>&nutrition=true" target="_blank">View <?=$this->current_menu_name; ?> Nutrionals - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/nutritionals?store=244&menu=<?=$this->next_menu_id; ?>&nutrition=true" target="_blank">View <?=$this->next_menu_name; ?> Nutrionals - Mill Creek</a>

				</div>
			</div>
		</div>

		<div class="row mb-4">
			<div class="col">
				<h1 id="fadash">BackOffice</h1>
				<div class="list-group">
					<a class="list-group-item list-group-item-action" href="/backoffice/main" target="_blank">View Home</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/order-history?id=<?=$this->current_user_id; ?>" target="_blank">View Orders</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/order-mgr?user=<?=$this->current_user_id; ?>" target="_blank">Add Order</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/user_details?id=<?=$this->current_user_id; ?>" target="_blank">View Account</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/account?id=<?=$this->current_user_id; ?>" target="_blank">Edit Account</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/store_details?id=244" target="_blank">View Store Information - Mill Creek</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/session_mgr" target="_blank">View <?=$this->current_menu_name; ?> Session Calendar</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/create-session?menu=<?=$this->current_menu_id; ?>" target="_blank">Create Session</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/session_template_mgr" target="_blank">View Session Template Manager</a>
					<a class="list-group-item list-group-item-action" href="/backoffice/finishing-touch-printable-form?store_id=244&menu_id=<?=$this->current_menu_id; ?>" target="_blank">Print <?=$this->current_menu_name; ?> Sides & Sweets</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/backoffice/reports-entree" data-params='<?=$this->params_entree_report; ?>'>Run Entree Report for <?=$this->current_menu_name; ?> Menu</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/backoffice/reports-growth-scorecard" data-params='<?=$this->params_growth_scoreboard; ?>'>Growth Scoreboard <?=$this->current_menu_name; ?></a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/backoffice/reports-customer-menu-item-labels?interface=1" data-params='<?=$this->params_cooking_instructions; ?>'> <?=$this->current_menu_name; ?> All Core Cooking Instructions PDF</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/backoffice/reports-menu-item-nutritional-labels" data-params='<?=$this->params_nutritional_labels; ?>'> <?=$this->current_menu_name; ?> All Core Nutritional Labels PDF</a>
					<a class="list-group-item list-group-item-action dynamic-form-submit" href="" data-action="/backoffice/reports-customer-menu-item-labels?interface=1" data-params='<?=$this->params_cooking_instructions; ?>'> <?=$this->current_menu_name; ?> All Core Cooking Instructions PDF</a>


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