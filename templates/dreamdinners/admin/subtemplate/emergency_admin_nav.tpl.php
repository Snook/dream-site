<?php
if (!isset($this->topnav))
{
	$this->topnav = 'guests';
}

if (empty($this->back) && !empty($_REQUEST['back']))
{
	$this->back = $_REQUEST['back'];
}

$navigationArray = array
(
	'home' => array
	(
		'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::MANUFACTURER_STAFF, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD),
		'title' => 'Home',
		'link' => '/backoffice/emergency-main',
		'submenu' => array()
	),
	'guests' => array
	(
		'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::MANUFACTURER_STAFF, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD),
		'title' => 'Guests',
		'link' => '/backoffice/list_users',
		'submenu' => array
		(
			'admin_list_users' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::MANUFACTURER_STAFF, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD),
				'title' => 'Guest Search',
				'link' => '/backoffice/list_users',
			)
		)
	),
	'reports' => array
	(
		'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD, CUser::OPS_SUPPORT),
		'title' => 'Reports',
		'link' => '/backoffice/reports',
		'submenu' => array
		(
			'admin_reports' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD, CUser::OPS_SUPPORT),
				'title' => 'Select Report',
				'link' => '/backoffice/reports',
			),
			'admin_reports_entree' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD, CUser::OPS_SUPPORT),
				'title' => 'Entr&eacute;e Report',
				'link' => '/backoffice/reports_entree',
			),
			'admin_dashboard_new' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD),
				'title' => 'Dashboard',
				'link' => '/backoffice/dashboard-menu-based',
			),
			'admin_reports_trending' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::OPS_LEAD),
				'title' => 'Trending',
				'link' => '/backoffice/reports_trending_menu_based',
			),
			'admin_reports_goal_management_v2' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD),
			    'title' => 'Goal Management',
				'link' => '/backoffice/reports_goal_management_v2',
			),
			'admin_reports_manufacturer_labels' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER),
				'title' => 'Manufacturing Labels',
				'link' => '/backoffice/reports_manufacturer_labels',
			)
		)
	),

	'store' => array
	(
		'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::OPS_LEAD, CUser::EVENT_COORDINATOR),
		'title' => 'Store/Franchise',
		'link' => '/backoffice/resources',
		'submenu' => array
		(
			'admin_resources' => array
			(
				'access' => array(CUser::HOME_OFFICE_MANAGER, CUser::HOME_OFFICE_STAFF, CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::EVENT_COORDINATOR, CUser::OPS_LEAD),
				'title' => 'Resources',
				'link' => '/backoffice/resources',
			),
			'admin_estore' => array
			(
				'access' => array(CUser::FRANCHISE_OWNER, CUser::FRANCHISE_MANAGER, CUser::FRANCHISE_LEAD, CUser::FRANCHISE_STAFF, CUser::GUEST_SERVER, CUser::OPS_LEAD),
				'title' => 'Estore',
				'link' => 'http://dreammerch.com',
				'target' => '_blank'
			)
		)
	)
);
?>

		<table style="width:100%">
		<tr>
			<td colspan="3" style="padding:20px;font-weight:bold;font-size:16px;">
				<span style="float:right;white-space:nowrap;font-size:14px;">Welcome <span id="fadmin_username"><?php echo CUser::getCurrentUser()->firstname . ' '. CUser::getCurrentUser()->lastname; ?></span></span>
				<span style="float:left;white-space:nowrap;font-size:16px;"><span id="fadmin_usertype"><?php echo CUser::userTypeText(CUser::getCurrentUser()->user_type); ?></span> &ndash; <?php echo (!empty(CStore::getFranchiseStore()->store_name)) ? CStore::getFranchiseStore()->store_name : 'Store not set'; ?> <input type="button" class="btn btn-primary btn-sm" value="Change" onclick="bounce('/backoffice/location_switch?back=' + back_path());" /></span>
			</td>
		</tr>
		<tr>
			<td rowspan="2" class="fadmin_nav_logo"><a id="awmAnchor-admin_nav-gr0" href="/backoffice"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/fadmin_dreamdinners_logo.png" width="131" height="76" border="0" /></a></td>
			<td style="height:37px;vertical-align:top;">

			<ul id="menubar">
			<?php foreach ($navigationArray as $topnav) { ?>
			<?php if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || $topnav['access'] === true || in_array(CUser::getCurrentUser()->user_type, $topnav['access'])) { ?>
				<li class="fadmin_nav"><a href="<?php echo $topnav['link']; ?>"><?php echo $topnav['title']; ?></a>
				<?php if(!empty($topnav['submenu']) && is_array($topnav['submenu'])) { ?>
					<ul>
					<?php foreach ($topnav['submenu'] as $submenu_page => $submenu) {

					$HiddenByCustomOverride = false;
					if (is_array($submenu['access']) && in_array('HIDE_IF_PLATE_POINTS_STORE', $submenu['access']))
					{
						if (CUser::getCurrentUser()->isFranchiseAccess())
						{
							$storeObj = CStore::getFranchiseStore();
							if (CStore::storeInPlatePointsTest($storeObj->id))
								$HiddenByCustomOverride = true;
						}
					}
					?>

					<?php if ((CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || $submenu['access'] === true || in_array(CUser::getCurrentUser()->user_type, $submenu['access'])) && !$HiddenByCustomOverride) { ?>


						<li<?php echo ($this->page == $submenu_page) ? ' class="active"' : ''; ?>><a href="<?php echo $submenu['link'];?>" <?php if (!empty($submenu['target'])) {echo " target='" .  $submenu['target'] . "'";}?>><?php echo $submenu['title']; ?></a></li>
					<?php } ?>
					<?php } ?>
					</ul>
				<?php } ?>
				</li>
			<?php } ?>
			<?php } ?>
			</ul>

			</td>
			<td style="vertical-align:top;width:120px;">
				<a href="/signout?back=/backoffice/login" class="fadmin_nav fadmin_nav_right">Log Out</a>
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top;">
				<?php if (!empty($navigationArray[$this->topnav]['submenu'])) { ?>
				<?php foreach ($navigationArray[$this->topnav]['submenu'] as $menu => $submenu) {
					$HiddenByCustomOverride = false;
					if (is_array($submenu['access']) && in_array('HIDE_IF_PLATE_POINTS_STORE', $submenu['access']))
					{
						if (CUser::getCurrentUser()->isFranchiseAccess())
						{
							$storeObj = CStore::getFranchiseStore();
							if (CStore::storeInPlatePointsTest($storeObj->id))
								$HiddenByCustomOverride = true;
						}
					}
					?>

				<?php if ((CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || $submenu['access'] === true || in_array(CUser::getCurrentUser()->user_type, $submenu['access'])) && !$HiddenByCustomOverride) { ?>

				<a href="<?php echo $submenu['link'];?>" class="fadmin_nav fadmin_subnav <?php echo ($this->page === $menu) ? 'fadmin_subnav_active' : ''; ?>"
				<?php if (!empty($submenu['target'])) {echo " target='" .  $submenu['target'] . "'";}?>>
				<?php echo $submenu['title']; ?></a>
				<?php } ?>
				<?php } ?>
				<?php } ?>
			</td>
			<td style="vertical-align:top;">
				<?php if (isset($this->back)) { ?>
				<a href="<?php echo $this->back; ?>" class="fadmin_nav fadmin_nav_right">Back</a>
				<?php } ?>
				<?php if (isset($this->helpLinkSection)) { ?>
				<a href="javascript:NewWindowScroll('/backoffice/help-system?section=<?php echo $this->helpLinkSection; ?>','Help','675','575');" class="fadmin_nav fadmin_nav_right"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/help.png" alt="Print" style="vertical-align:middle;margin-bottom:.25;" /> Page Help</a>
				<?php } ?>
			</td>
		</tr>
		</table>

	</td>
</tr>
</table>

<br />

<?php include $this->loadTemplate('admin/application_status_msg.tpl.php'); ?>

<table class="page">
<tr>
	<td>