<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.scrollTo.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/emergency_main.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/main.css'); ?>
<?php $this->setScriptVar('dashboard_update_required = ' . $this->dashboard_update_required . ';'); ?>
<?php $this->setScriptVar('selected_agenda_month = "' . $this->selected_agenda_month . '";'); ?>
<?php $this->setScriptVar('selected_menu_id = ' . $this->selected_menu_id . ';'); ?>
<?php $this->setScriptVar('selected_date = "' . $this->selected_date . '";'); ?>
<?php $this->setOnload('main_init();'); ?>
<?php $this->assign('page_title', 'Emergency Home'); ?>
<?php $this->assign('topnav', 'home'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h1 id="fadash"><?php echo $this->page_title; ?> -
		<a id="selected_date_link" href="?page=admin_main"><span id="selected_day"><?php echo CTemplate::dateTimeFormat($this->adjusted_server_time, VERBOSE_DATE); ?></span><span id="selected_session"></span></a>
		<img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" data-tooltip="Processing" alt="Processing"/>
	</h1>

<?php if ($this->show['store_selector'] && !empty($this->form['store_html'])) { ?>
	<div id="store_selector">
		<form method="post">
			Store: <?php echo $this->form['store_html']; ?>
		</form>
	</div>
<?php } ?>

	<div id="session_selector_container" class="container_background">

		<div id="agenda_title">
			<div class="title">Agenda</div>
		<span class="month">
		<select id="month_selector">
			<?php foreach ($this->menu_info_array AS $id => $menu) { ?>
				<option value="<?php echo $menu['global_menu_start_date'] ?>" data-menu_month="<?php echo $menu['year_month']; ?>"  data-menu_id="<?php echo $menu['id']; ?>" <?php echo ($menu['year_month'] == $this->selected_agenda_month) ? 'selected="selected"' : ''; ?>><?php echo $menu['month_year'] ?></option>
			<?php } ?>
		</select>
		<span id="go_to_today" class="button" style="float: none;" data-tooltip="Jump to Today">Today</span>
		</span>

			<div class="clear"></div>
		</div>

		<div id="session_selector">

			<?php include $this->loadTemplate('admin/subtemplate/main_agenda.tpl.php'); ?>

		</div>

	</div>

	<div id="session_tools_container" class="container_background">

		<div class="title">Session Tools</div>

		<div id="session_tools_selector">

			<ul id="session_tools">

				<li><a href="#" id="st_session_goal_sheet_print">Session Goal Sheet</a></li>
				<li><a href="#" id="st_customer_receipt">Order Summary</a></li>
				<li><a href="#" id="st_customer_menu_core_freezer_nutrition">Collated Session Docs</a></li>
				<!--
				<li><a href="#" id="st_customer_menu_core">Next Month Menus</a></li>
				<li><a href="#" id="st_customer_menu_freezer">Freezer Sheets</a></li>
				<li><a href="#" id="st_customer_menu_nutrition">Nutritionals</a></li>
				-->
				<li class="category">Cooking Instructions
					<input id="suppress_fastlane_labels" type="checkbox" checked="checked" data-tooltip="Include FastLane Labels, uncheck to suppress"/>
				</li>
				<li><a href="#" id="st_print_labels_w_breaks">Labels</a></li>
				<li><a href="#" id="st_print_labels_by_dinner">Labels by Dinner</a></li>
				<li><a href="#" id="st_print_generic_labels">Generic Labels</a></li>
				<li class="category">Reports</li>
				<li><a href="#" id="st_entree_summary">Entr&eacute;e Summary</a></li>
				<li><a href="#" id="st_future_orders">Future Orders Report</a></li>
				<?php if (false){//$this->storeSupportsPlatePoints) { ?>
					<li><a href="#" id="st_print_enrollment_forms">PLATEPOINTS Enrollment</a></li>
				<?php } ?>

				<li><a href="#" id="st_finishing_touch_pick_sheet">Sides and Sweets Menu</a></li>
				<li><a href="#" id="st_fast_lane_report">Pre-Assembled Pull Sheet</a></li>
				<li><a href="#" id="st_side_dish_report">Sides and Sweets Pull Sheet</a></li>

				<!--
				<li><a href="#" id="st_franchise_receipt">Store Receipt</a></li>
				<li><a href="#" id="st_print_labels">Labels</a></li>
				<li><a href="#" id="st_session_goal_sheet_xls">Session Goal Sheet - Excel</a></li>
				<li><a href="#" id="st_dream_rewards">Dream Rewards Report</a></li>
				-->

			</ul>

		</div>

	</div>

<?php if ($this->show['dashboard_snapshot']) { ?>

	<div id="dashboard_snapshot_container" class="container_background" data-fadmin_perm="FRANCHISE_MANAGER,FRANCHISE_OWNER,HOME_OFFICE_MANAGER,SITE_ADMIN">

		<div style="float:right;">
			<span id="ds_dashboard" class="button">Dashboard</span><span id="ds_trending" class="button">Trending</span><span id="ds_goal_tracking" class="button">Goal Setting</span>
		</div>

		<div class="title">
			<span id="ds_title_date"><?php echo CTemplate::dateTimeFormat($this->adjusted_server_time, VERBOSE_MONTH_YEAR); ?></span>
			Snapshot
		</div>

		<div id="dashboard_snapshot_outer">
			<div id="dashboard_snapshot_table_div"><?php include $this->loadTemplate('admin/subtemplate/main_dashboard_summary.tpl.php'); ?></div>
			<div id="dashboard_processing">Updating Dashboard</div>
		</div>

	</div>

<?php } ?>

	<div id="guest_search_container" class="container_background">

		<div class="title">Search &amp; Order Lookup</div>

		<div class="clear"></div>

		<div class="search_box">

			<select id="gs_search_type" style="width: 134px;">
				<option value="firstname">First Name</option>
				<option value="lastname" selected="selected">Last Name</option>
				<option value="firstlast">First &amp; Last Name</option>
				<option value="email">Email Address</option>
				<option value="id">Customer ID</option>
			</select>

			<input id="gs_search_value" placeholder="Name, Email or ID" data-tooltip="Name, Email or ID" type="text"/>
			<input id="gs_search_all" type="checkbox" value="1" data-tooltip="Search All Stores"/>

			<span id="gs_search_go" class="button">Search Guests</span>

			<div class="clear"></div>

		</div>

		<div class="search_box">

			<select id="os_search_type">
				<option value="confirmation">Confirmation #</option>
				<option value="id">Order ID</option>
			</select>

			<input id="os_search_value" placeholder="Order Confirmation or ID" data-tooltip="Order Confirmation or ID" type="text"/>

			<span id="os_search_go" class="button">Order Details</span>

			<div class="clear"></div>

		</div>

	</div>

	<div id="session_details_container" class="container_background">

		<div style="float:right;">
			<span id="sd_session_meta" class="button">View Meta</span>
		</div>

		<div class="title">Session Details</div>

		<div class="clear"></div>

		<div id="session_details_table_div"></div>

	</div>

	<div id="day_details_container" class="container_background">

		<div style="float:right;"></div>

		<div class="title">Day Details</div>

		<div class="clear"></div>

		<div id="day_details_table_div"></div>

	</div>

	<div class="clear"></div>

	<div id="guest_details_container" class="container_background">

		<div id="booked_guests_table"></div>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>