<ul class="nav nav-tabs" id="myTab" role="tablist">
	<li class="nav-item" role="presentation">
		<button class="nav-link active" id="reports_select_site_admin-tab" data-toggle="tab" data-target="#reports_select_site_admin" type="button" role="tab" aria-controls="reports_select_site_admin" aria-selected="true"><span data-toggle="tooltip" data-placement="top" title="reports_select_site_admin.tpl.php">Site Admin</span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_event_coordinator-tab" data-toggle="tab" data-target="#reports_select_event_coordinator" type="button" role="tab" aria-controls="reports_select_event_coordinator" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_event_coordinator.tpl.php"><?php echo CUser::userTypeText(CUser::EVENT_COORDINATOR); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_franchise_lead-tab" data-toggle="tab" data-target="#reports_select_franchise_lead" type="button" role="tab" aria-controls="reports_select_franchise_lead" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_franchise_lead.tpl.php"><?php echo CUser::userTypeText(CUser::FRANCHISE_LEAD); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_franchise_manager-tab" data-toggle="tab" data-target="#reports_select_franchise_manager" type="button" role="tab" aria-controls="reports_select_franchise_manager" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_franchise_manager.tpl.php"><?php echo CUser::userTypeText(CUser::FRANCHISE_MANAGER); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_franchise_owner-tab" data-toggle="tab" data-target="#reports_select_franchise_owner" type="button" role="tab" aria-controls="reports_select_franchise_owner" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_franchise_owner.tpl.php"><?php echo CUser::userTypeText(CUser::FRANCHISE_OWNER); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_franchise_staff-tab" data-toggle="tab" data-target="#reports_select_franchise_staff" type="button" role="tab" aria-controls="reports_select_franchise_staff" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_franchise_staff.tpl.php"><?php echo CUser::userTypeText(CUser::FRANCHISE_STAFF); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_guest_server-tab" data-toggle="tab" data-target="#reports_select_guest_server" type="button" role="tab" aria-controls="reports_select_guest_server" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_guest_server.tpl.php"><?php echo CUser::userTypeText(CUser::GUEST_SERVER); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_home_office_manager-tab" data-toggle="tab" data-target="#reports_select_home_office_manager" type="button" role="tab" aria-controls="reports_select_home_office_manager" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_home_office_manager.tpl.php"><?php echo CUser::userTypeText(CUser::HOME_OFFICE_MANAGER); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_home_office_staff-tab" data-toggle="tab" data-target="#reports_select_home_office_staff" type="button" role="tab" aria-controls="reports_select_home_office_staff" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_home_office_staff.tpl.php"><?php echo CUser::userTypeText(CUser::HOME_OFFICE_STAFF); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_manufacturer_staff-tab" data-toggle="tab" data-target="#reports_select_manufacturer_staff" type="button" role="tab" aria-controls="reports_select_manufacturer_staff" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_manufacturer_staff.tpl.php"><?php echo CUser::userTypeText(CUser::MANUFACTURER_STAFF); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_ops_lead-tab" data-toggle="tab" data-target="#reports_select_ops_lead" type="button" role="tab" aria-controls="reports_select_ops_lead" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_ops_lead.tpl.php"><?php echo CUser::userTypeText(CUser::OPS_LEAD); ?></span></button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="reports_select_ops_support-tab" data-toggle="tab" data-target="#reports_select_ops_support" type="button" role="tab" aria-controls="reports_select_ops_support" aria-selected="false"><span data-toggle="tooltip" data-placement="top" title="reports_select_ops_support.tpl.php"><?php echo CUser::userTypeText(CUser::OPS_SUPPORT); ?></span></button>
	</li>
</ul>
<div class="tab-content pt-3" id="myTabContent">
	<div class="tab-pane fade show active" id="reports_select_site_admin" role="tabpanel" aria-labelledby="reports_select_site_admin-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_site_admin.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_event_coordinator" role="tabpanel" aria-labelledby="reports_select_event_coordinator-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_event_coordinator.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_franchise_lead" role="tabpanel" aria-labelledby="reports_select_franchise_lead-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_lead.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_franchise_manager" role="tabpanel" aria-labelledby="reports_select_franchise_manager-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_manager.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_franchise_owner" role="tabpanel" aria-labelledby="reports_select_franchise_owner-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_owner.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_franchise_staff" role="tabpanel" aria-labelledby="reports_select_franchise_staff-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_staff.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_guest_server" role="tabpanel" aria-labelledby="reports_select_guest_server-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_guest_server.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_home_office_manager" role="tabpanel" aria-labelledby="reports_select_home_office_manager-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_home_office_manager.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_home_office_staff" role="tabpanel" aria-labelledby="reports_select_home_office_staff-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_home_office_staff.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_manufacturer_staff" role="tabpanel" aria-labelledby="reports_select_manufacturer_staff-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_manufacturer_staff.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_ops_lead" role="tabpanel" aria-labelledby="reports_select_ops_lead-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_ops_lead.tpl.php"); ?>
	</div>
	<div class="tab-pane fade" id="reports_select_ops_support" role="tabpanel" aria-labelledby="reports_select_ops_support-tab">
		<?php include $this->loadTemplate("admin/reports_sub_templates/reports_select_ops_support.tpl.php"); ?>
	</div>
</div>