<?php $this->setScript('head', SCRIPT_PATH . '/admin/import_bundles.min.js'); ?>
<?php $this->setOnload('import_bundles_init();'); ?>
<?php $this->assign('page_title','Import Bundles'); ?>
<?php $this->assign('topnav','import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h3>Import Menu Bundles</h3>

<?php if (empty($this->changelog))  { ?>
	<form id="form_import_bundles" method="post" enctype="multipart/form-data">

		<h2>Step 1</h2>

		<p>Select menu:</p>
		<?php echo $this->form_menu['menu_html']?>

		<h2 style="margin-top: 10px;">Step 2</h2>

		<p>Import file:</p>

		<input type="file" id="bundles" name="bundles" <?php echo (empty($this->menu_count)) ? 'disabled="disabled"' : ''; ?> /><br /><br />

		<input id="submit_menu_import" name="submit_menu_import" type="submit" class="button" value="Import Bundles" disabled="disabled" />
				<input id="testmode" name="testmode" type="checkbox" />&nbsp;<label for="testmode">Dry Run Only (returns what will happen during actual import)</label>
		<img id="processing_image" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" style="display: none;" alt="Processing" />
	</form>
<?php } ?>
<?php if (!empty($this->changelog)) { ?>
	<table style="width: 100%;">
		<thead>
		<tr>
			<th colspan="2" class="bgcolor_dark catagory_row">Dry Run Results</th>
		</tr>
		<tr>
			<th class="bgcolor_medium header_row" style="width:300px;">Result</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->changelog AS $change) { ?>
		<?php if (isset($change['event']))
		{ ?>
			<tr>
					<td  class="bgcolor_light">
						<span><?php echo $change['event']; ?> </span>
					</td>
			</tr>
<?php  } } ?>
			</tbody>
		</table>
<?php } ?>





<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>