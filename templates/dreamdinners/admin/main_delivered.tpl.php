<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.maskedinput.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.scrollTo.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/safari_validate_2.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/main.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/main.css'); ?>
<?php $this->setScriptVar('dashboard_update_required = ' . $this->dashboard_update_required . ';'); ?>
<?php $this->setScriptVar('selected_agenda_month = "' . $this->selected_agenda_month . '";'); ?>
<?php $this->setScriptVar('selected_menu_id = ' . $this->selected_menu_id . ';'); ?>
<?php $this->setScriptVar('selected_date = "' . $this->selected_date . '";'); ?>
<?php $this->setOnload('main_init();'); ?>
<?php $this->assign('page_title', 'BackOffice Home'); ?>
<?php $this->assign('topnav', 'home'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h1 id="fadash"><?php echo $this->page_title; ?> -
		<a id="selected_date_link" href="/backoffice"><span id="selected_day"><?php echo CTemplate::dateTimeFormat($this->adjusted_server_time, MONTH_DAY_YEAR); ?></span></a>
		<img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" data-tooltip="Processing" alt="Processing"/>
	</h1>

<?php if ($this->show['store_selector'] && !empty($this->form['store_html'])) { ?>
	<div id="store_selector" class="row">
		<div class="col">
			<form method="post">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">Store</span>
					</div>
					<?php echo $this->form['store_html']; ?>
				</div>
			</form>
		</div>
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
		<span id="go_to_today" class="btn btn-primary btn-sm" style="float: none;" data-tooltip="Jump to Today">Today</span>
		</span>

			<div class="clear"></div>
		</div>

		<div id="session_selector">

			<?php include $this->loadTemplate('admin/subtemplate/main_agenda_delivered.tpl.php'); ?>

		</div>

	</div>

	<div id="session_tools_container" class="container_background">

		<div class="title">Session Tools</div>

		<div id="session_tools_selector">

			<ul id="session_tools">
				<li><a href="#" id="st_customer_receipt">Order Summary - Delivery Day</a></li>
				<li><a href="#" id="st_daily_boxes_shipped">Entr&eacute;e Summary - Pick Up</a></li>
				<li><a href="#" id="st_daily_boxes_delivered">Entr&eacute;e Summary - Delivery</a></li>
			</ul>

		</div>

	</div>

<?php if ($this->show['dashboard_snapshot']) { ?>

	<div id="dashboard_snapshot_container" class="container_background" data-fadmin_perm="FRANCHISE_MANAGER,FRANCHISE_OWNER,HOME_OFFICE_MANAGER,SITE_ADMIN">

		<div style="float:right;">
			<span id="ds_dashboard" class="btn btn-primary btn-sm">Dashboard</span><span id="ds_trending" class="btn btn-primary btn-sm">Trending</span><span id="ds_goal_tracking" class="btn btn-primary btn-sm">Goal Setting</span>
		</div>

		<div class="title">
			<span id="ds_title_date"><?php echo $this->menu_month;?></span>
			Snapshot
		</div>

		<div id="dashboard_snapshot_outer">
			<div id="dashboard_snapshot_table_div"><?php include $this->loadTemplate('admin/subtemplate/main_dashboard_summary_delivered.tpl.php'); ?></div>
			<div id="dashboard_processing">Updating Dashboard</div>
		</div>

	</div>

<?php } ?>

	<div id="guest_search_container" class="container_background">

		<div style="float:right;"><a href="/backoffice/account" class="btn btn-primary btn-sm">Add Guest</a></div>

		<div class="title">Search &amp; Order Lookup</div>

		<div class="clear"></div>

		<div class="search_box">

			<select id="gs_search_type" style="width: 134px;">
				<option value="firstname">First Name</option>
				<option value="lastname" selected="selected">Last Name</option>
				<option value="firstlast">First &amp; Last Name</option>
				<option value="email">Email Address</option>
				<option value="phone">Phone Number</option>
				<option value="id">Customer ID</option>
			</select>

			<input id="gs_search_value" placeholder="Name, Email or ID" data-tooltip="Name, Email or ID" type="text"/>
			<input id="gs_search_all" type="checkbox" value="1" data-tooltip="Search All Stores"/>

			<span id="gs_search_go" class="btn btn-primary btn-sm">Search Guests</span>

			<div class="clear"></div>

		</div>

		<div class="search_box">

			<select id="os_search_type">
				<option value="confirmation">Confirmation #</option>
				<option value="id">Order ID</option>
			</select>

			<input id="os_search_value" placeholder="Order Confirmation or ID" data-tooltip="Order Confirmation or ID" type="text"/>

			<span id="os_search_go" class="btn btn-primary btn-sm">Order Details</span>

			<div class="clear"></div>

		</div>

	</div>

	<div id="session_details_container" class="container_background">

		<div style="float:right;">
			<span id="sd_edit_session" class="btn btn-primary btn-sm" data-fadmin_perm="FRANCHISE_LEAD,FRANCHISE_MANAGER,FRANCHISE_OWNER,HOME_OFFICE_STAFF,HOME_OFFICE_MANAGER,SITE_ADMIN,OPS_LEAD">Edit Session</span><span id="sd_publish_state" class="btn btn-primary btn-sm" data-fadmin_perm="FRANCHISE_LEAD,FRANCHISE_MANAGER,FRANCHISE_OWNER,HOME_OFFICE_STAFF,HOME_OFFICE_MANAGER,SITE_ADMIN">Close Session</span><span id="sd_email_session" class="btn btn-primary btn-sm">Email</span><span id="sd_session_meta" class="btn btn-primary btn-sm">View Meta</span>
		</div>

		<div class="title">Day Details</div>

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