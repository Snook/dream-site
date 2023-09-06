<?php $this->setScript('head', SCRIPT_PATH . '/admin/import_sidesmap.min.js'); ?>
<?php $this->setOnload('import_sidesmap_init();'); ?>
<?php $this->assign('page_title','Import Menu Sides Map'); ?>
<?php $this->assign('topnav','import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h3>Import Menu Sides Map</h3>

	<form id="form_import_sidesmap" method="post" enctype="multipart/form-data">

		<h2>Step 1</h2>

		<p>Select menu:</p>
		<?php echo $this->form_menu['menu_html']?>

		<h2 style="margin-top: 10px;">Step 2</h2>

		<p>Import file:</p>

		<input type="file" id="base_menu_sidesmap" name="base_menu_sidesmap" <?php echo (empty($this->menu_count)) ? 'disabled="disabled"' : ''; ?> /><br /><br />

		<input id="submit_menu_import" name="submit_menu_import" type="submit" class="button" value="Import Sides Map" disabled="disabled" /> <img id="processing_image" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" style="display: none;" alt="Processing" />
	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>