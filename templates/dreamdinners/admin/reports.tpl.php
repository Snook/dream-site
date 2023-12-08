<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports.min.js'); ?>
<?php $this->assign('page_title','Reports'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col-lg-6 text-center mb-3 order-lg-2">
				<h1><a href="/backoffice/reports">Reports</a></h1>
			</div>
			<div class="col-8 col-lg-3 order-lg-1">
				<a href="/backoffice" class="btn btn-primary">BackOffice Home</a>
			</div>
		</div>

		<?php
		switch (CUser::getCurrentUser()->user_type)
		{
			case CUser::EVENT_COORDINATOR:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_event_coordinator.tpl.php");
				break;
			case CUser::FRANCHISE_LEAD:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_lead.tpl.php");
				break;
			case CUser::FRANCHISE_MANAGER:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_manager.tpl.php");
				break;
			case CUser::FRANCHISE_OWNER:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_owner.tpl.php");
				break;
			case CUser::FRANCHISE_STAFF:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_franchise_staff.tpl.php");
				break;
			case CUser::GUEST_SERVER:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_guest_server.tpl.php");
				break;
			case CUser::HOME_OFFICE_MANAGER:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_home_office_manager.tpl.php");
				break;
			case CUser::HOME_OFFICE_STAFF:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_home_office_staff.tpl.php");
				break;
			case CUser::MANUFACTURER_STAFF:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_manufacturer_staff.tpl.php");
				break;
			case CUser::OPS_LEAD:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_ops_lead.tpl.php");
				break;
			case CUser::OPS_SUPPORT:
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_ops_support.tpl.php");
				break;
			case CUser::SITE_ADMIN:
				// Site admin can view all templates in order to develop
				include $this->loadTemplate("admin/reports_sub_templates/reports_select_view_all.tpl.php");
				break;
			default:
				echo "Sorry, you do not have any access rights to view any of these reports";
		}
		?>
	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>