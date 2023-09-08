<?php $this->setScript('head', SCRIPT_PATH . '/admin/status.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/status.css'); ?>
<?php $this->setOnload('status_init();'); ?>
<?php $this->assign('page_title','Dream Dinners Status'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h1 style="margin-bottom: 20px;">Dream Dinners Status</h1>

<?php foreach ($this->menus AS $menu_id => $menu) { ?>
	<h2><?php echo $menu->menu_name; ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-sm">
			<thead>
			<tr>
				<th data-tooltip="Available for public">Public</th>
				<th data-tooltip="Stores can create sessions">Sessions</th>
				<th data-tooltip="Menu data has been imported">Menu</th>
				<th data-tooltip="Inventory data has been imported">Inventory</th>
				<th data-tooltip="Nutritional data has been imported">Nutritionals</th>
				<th data-tooltip="Sides mapping has been imported">Sides Map</th>
				<th data-tooltip="Meal Prep Starter Pack bundle has been setup">Meal Prep Starter Pack</th>
				<th data-tooltip="Meal Prep Workshop bundle has been setup">Meal Prep Workshop</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><a href="?page=admin_menus&amp;menu_edit=<?php echo $menu->id; ?>"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/<?php echo (!empty($menu->is_active)) ? 'accept' : 'delete' ; ?>.png" /></a></td>
				<td><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/accept.png" /></td>
				<td><a href="?page=admin_import_menu"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" data-check_status="import_menu" data-menu_id="<?php echo $menu->id; ?>" /></a></td>
				<td><a href="?page=admin_import_inventory"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" data-check_status="import_inventory" data-menu_id="<?php echo $menu->id; ?>" /></a></td>
				<td><a href="?page=admin_import_nutritionals"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" data-check_status="import_nutritionals" data-menu_id="<?php echo $menu->id; ?>" /></a></td>
				<td><a href="?page=admin_import_sidesmap"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" data-check_status="import_sidesmap" data-menu_id="<?php echo $menu->id; ?>" /></a></td>
				<td><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" data-check_status="bundle_intro" data-menu_id="<?php echo $menu->id; ?>" /></td>
				<td><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" data-check_status="bundle_dreamtaste" data-menu_id="<?php echo $menu->id; ?>" /></td>
			</tr>
			</tbody>
		</table>
	</div>
<?php } ?>

	<hr />

	<div id="cron_status_div"></div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>