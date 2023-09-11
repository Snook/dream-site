<div class="page mb-3">

	<?php
	if (!isset($this->topnav))
	{
		$this->topnav = 'guests';
	}

	if (empty($this->back) && !empty($_REQUEST['back']))
	{
		$this->back = $_REQUEST['back'];
	}

	$store_id = false;

	if (CUser::getCurrentUser()->isFranchiseAccess())
	{
		$store_id = CBrowserSession::getCurrentFadminStoreID();
	}

	$navigationArray = null;

	if( CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER )
	{
		$navigationArray = CUser::userAdminDistributionCenterPageAccessArray();
	}
	else
	{
		$navigationArray = CUser::userFadminPageAccessArray($store_id);
	}

	?>

	<table style="width:100%">
		<tr>
			<td colspan="3" style="padding:20px;font-weight:bold;font-size:16px;">
				<span style="float:right;white-space:nowrap;font-size:14px;">Welcome <span id="fadmin_username"><?php echo CUser::getCurrentUser()->firstname . ' '. CUser::getCurrentUser()->lastname; ?></span></span>
				<span style="float:left;white-space:nowrap;font-size:16px;"><span id="fadmin_usertype"><?php echo CUser::userTypeText(CUser::getCurrentUser()->user_type); ?></span> &ndash; <?php echo (!empty(CStore::getFranchiseStore()->store_name)) ? CStore::getFranchiseStore()->store_name : 'Store not set'; ?> <span class="btn btn-primary backoffice_change_store">Change</span></span>
			</td>
		</tr>
		<tr>
			<td rowspan="2" class="fadmin_nav_logo"><a id="awmAnchor-admin_nav-gr0" href="/?page=admin_main"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/fadmin_dreamdinners_logo.png" width="131" height="76" border="0" /></a></td>
			<td style="height:37px;vertical-align:top;">
				<?php if (empty($this->hide_navigation)) { // hidden for some pages, ie admin_access_agreement ?>
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
				<?php } ?>
			</td>
			<td style="vertical-align:top;width:120px;">
				<a href="/signout?back=main.php%3Fpage%3Dadmin_login" class="fadmin_nav fadmin_nav_right">Log Out</a>
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top;">
				<?php if (empty($this->hide_navigation)) { // hidden for some pages, ie admin_access_agreement ?>
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
				<?php } ?>
			</td>
			<td style="vertical-align:top;">
				<?php if (isset($this->back)) { ?>
					<a href="<?php echo $this->back; ?>" class="fadmin_nav fadmin_nav_right">Back</a>
				<?php } ?>
				<?php if (isset($this->helpLinkSection)) { ?>
					<a href="javascript:NewWindowScroll('?page=admin_help_system&section=<?php echo $this->helpLinkSection; ?>','Help','675','575');" class="fadmin_nav fadmin_nav_right"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/help.png" alt="Print" style="vertical-align:middle;" /> Page Help</a>
				<?php } ?>
			</td>
		</tr>
	</table>

</div>

<?php include $this->loadTemplate('admin/application_status_msg.tpl.php'); ?>