<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<title><?php echo (!empty($this->page_title)) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - Quick, Healthy and Easy Family Dinners'; ?></title>
	<?php include $this->loadTemplate('admin/subtemplate/page_header/page_head_css.tpl.php'); ?>
	<?php include $this->loadTemplate('admin/subtemplate/page_header/page_header_javascript.tpl.php'); ?>
</head>

<body id="page-top">


<div class="d-flex" id="wrapper">

	<div class="d-print-none sticky-top" id="sidebar-wrapper">
		<div class="accordion" id="accordionSidebar">
			<?php foreach (CUser::userFadminPageAccessArray() as $id => $topnav) { ?>
				<?php if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || $topnav['access'] === true || in_array(CUser::getCurrentUser()->user_type, $topnav['access'])) { ?>
					<div class="card">
						<div class="card-header" id="heading<?php echo $id; ?>">
							<h2 class="mb-0">
								<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse<?php echo $id; ?>" aria-expanded="<?php echo ($this->topnav == $id) ? 'true' : 'false' ; ?>" aria-controls="collapse<?php echo $id; ?>">
									<?php echo $topnav['title']; ?>
								</button>
							</h2>
						</div>
						<div id="collapse<?php echo $id; ?>" class="collapse <?php echo ($this->topnav == $id) ? 'show' : '' ; ?>" aria-labelledby="heading<?php echo $id; ?>" data-parent="#accordionSidebar">
							<div class="card-body font-size-small">
								<?php if(!empty($topnav['submenu']) && is_array($topnav['submenu'])) { ?>
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
											<a class="list-group-item list-group-item-action <?php echo ($this->page == $submenu_page) ? 'active' : ''; ?>" href="<?php echo $submenu['link'];?>" <?php if (!empty($submenu['target'])) {echo " target='" .  $submenu['target'] . "'";}?>><?php echo $submenu['title']; ?></a>
										<?php } ?>
									<?php } ?>
								<?php } else { ?>
									<a class="list-group-item list-group-item-action" href="<?php echo $topnav['link']; ?>"><?php echo $topnav['title']; ?></a>
								<?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="container-fluid">
			<div class="row font-size-small text-center my-2">
				<div class="col-12 mb-2">&copy; Dream Dinners, Inc.</div>
				<div class="col-12 mb-2"><a href="/" data-confirm-nav="You are still logged in as an admin! Are you sure you would like to visit the customer site as an admin user.">Customer Site</a></div>
				<div class="col-12 mb-2"><a class="helpdesk-popup" href="https://support.dreamdinners.com" target="_blank">Support Request</a></div>
				<div class="col-12 mb-2"><a href="https://support.dreamdinners.com" target="_blank">Support Portal</a></div>
				<div class="col-12 mb-2"><a href="https://dreamdinners.workplace.com" target="_blank">Workplace</a></div>
			</div>
		</div>
	</div>

	<div id="page-content-wrapper">

		<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom sticky-top">
			<button class="btn btn-light mr-3" id="menu-toggle">
				<span class="navbar-toggler-icon"></span>
			</button>
			<a class="navbar-brand" href="/backoffice/main">
				<img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/logo/dream-dinners-logo.png" alt="Dream Dinners logo" class="img-fluid">
			</a>
			<div class="nav-item">
				<button class="btn btn-sm btn-outline-green" value="Change" onclick="bounce('/backoffice/location_switch?back=' + back_path());">
					<?php echo (!empty(CStore::getFranchiseStore()->store_name)) ? CStore::getFranchiseStore()->store_name : 'Store not set'; ?>
				</button>
			</div>

			<ul class="navbar-nav ml-auto">
				<!-- Nav Item - User Information -->
				<li class="nav-item dropdown">
					<a class="nav-link" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="mr-2 d-none d-lg-inline font-size-small">
					<?php echo CUser::getCurrentUser()->firstname . ' '. CUser::getCurrentUser()->lastname; ?>
				</span>
						<img class="img-profile rounded-circle w-25" src="<?php echo IMAGES_PATH; ?>/style/platepoints/placeholder_avatar.png">
					</a>
					<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
						<a class="dropdown-item" href="/my-account">
							<i class="fas fa-user fa-sm fa-fw mr-2"></i>
							My Account
						</a>
						<a class="dropdown-item" href="/account">
							<i class="fas fa-cogs fa-sm fa-fw mr-2"></i>
							Edit Account
						</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="/signout">
							<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i>
							Logout
						</a>
					</div>
				</li>

			</ul>
		</nav>

		<?php if (!empty($this->app_maintenance_message) && (array_key_exists('SITE_WIDE', $this->app_maintenance_message['audience']) || array_key_exists('FADMIN', $this->app_maintenance_message['audience']))) { ?>
			<div class="container pt-3 d-print-none">
				<?php foreach ($this->app_maintenance_message['message'] AS $maintenance) { ?>
					<?php if ($maintenance['audience'] == 'SITE_WIDE' || $maintenance['audience'] == 'FADMIN') { ?>
						<div class="<?php echo $maintenance['alert_css']; ?>" role="alert">
							<?php if (!empty($maintenance['icon'])) { ?><img src="<?php echo IMAGES_PATH; ?>/icon/<?php echo $maintenance['icon']; ?>.png" alt="Alert" class="align-baseline" /><?php } ?> <?php echo $maintenance['message']; ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>