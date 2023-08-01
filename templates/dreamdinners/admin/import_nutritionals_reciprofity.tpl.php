<?php $this->setScript('head', SCRIPT_PATH . '/admin/import_nutritionals.min.js'); ?>
<?php $this->setOnload('import_nutritionals_init();'); ?>
<?php $this->assign('page_title','Import Nutritonals'); ?>
<?php $this->assign('topnav','import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>


<?php if ($this->testmode) { ?>

	<table style="width: 100%;">
		<thead>
		<tr>
			<th class="bgcolor_dark catagory_row">Dry Run Results</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->changelog AS $item) { ?>
		<tr>
			<td style="font-weight:bold; font-size:larger;"><?php echo $item['name']; ?></td>
		</tr>

		<?php foreach ($item['events'] AS $event) { ?>

			<tr>
			<td  class="bgcolor_light" colspan="2"><?php echo $event['message']; ?></td>
			</tr>

			<?php if (!empty($event['diff']))
			{
				foreach($event['diff'] as $thisDiff)
				{	?>

					<tr>
						<td  class="bgcolor_light"><span  style="font-weight:bold;"><?php echo $thisDiff['name'] . "</span> was " . $thisDiff['org'] . "<br />Diff: " .  $thisDiff['diff']; ?></td>
					</tr>


	<?php 	 } }// end foreach change (item) ?>


	<?php
	}
		foreach ($item['default_events'] AS $event) { ?>

			<tr>
			<td  class="bgcolor_lighter" colspan="2"><?php echo $event['message']; ?></td>
			</tr>

			<?php if (!empty($event['diff']))
			{
				foreach($event['diff'] as $thisDiff)
				{	?>

					<tr>
						<td  class="bgcolor_lighter"><span  style="font-weight:bold;"><?php echo $thisDiff['name'] . "</span> was " . $thisDiff['org'] . "<br />Diff: " .  $thisDiff['diff']; ?></td>
					</tr>


	<?php 	} } } } // end foreach change (item) ?>

			</tbody>
		</table>

<?php }// end test mode
	else
	{ ?>
	<h3>Import Nutritionals</h3>

	<form id="form_import_nutritionals" method="post" enctype="multipart/form-data">

		<h2>Step 1</h2>

		<p>Select menu:</p>
		<?php echo $this->form_menu['menu_html']?>

		<h2 style="margin-top: 10px;">Step 2</h2>

		<p>Import file:</p>

		<input type="file" id="base_menu_import" name="base_menu_import" <?php echo (empty($this->menu_count)) ? 'disabled="disabled"' : ''; ?> /><br /><br />

		<input id="submit_nutritionals_import" name="submit_nutritionals_import" type="submit" class="button" value="Import Nutritionals" disabled="disabled" />
		<input id="testmode" name="testmode" type="checkbox" />&nbsp;<label for="testmode">Dry Run Only (returns what will happen during actual import)</label>
		 <img id="processing_image" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" style="display: none;" alt="Processing" />
	</form>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>