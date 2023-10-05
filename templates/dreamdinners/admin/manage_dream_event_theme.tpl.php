<?php $this->assign('page_title','Manage Dream/Event Themes'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Manage Dream/Event/Fundraiser Themes</h1>

<?php if (!empty($this->editBundleTheme) || !empty($this->createBundleTheme)) { ?>

	<form method="post">
	<?php echo $this->form['hidden_html']; ?>

		<table style="width: 100%;">
			<tbody>
			<tr>
				<td class="bgcolor_medium header_row" style="width: 50%;">Name</td>
				<td class="bgcolor_medium header_row">Value</td>
			</tr>
			</tbody>
			<tbody id="tbody_bundle_id">
			<?php if (!empty($this->editBundleTheme)) { ?>
				<tr>
					<td class="bgcolor_light">Theme ID</td>
					<td class="bgcolor_light"><?php echo $this->editBundleTheme->id; ?></td>
				</tr>
			<?php } ?>
			</tbody>
			<tr>
				<td class="bgcolor_light">Title</td>
				<td class="bgcolor_light"><?php echo $this->form['title_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Public Title</td>
				<td class="bgcolor_light"><?php echo $this->form['title_public_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">BackOffice Acronym</td>
				<td class="bgcolor_light"><?php echo $this->form['fadmin_acronym_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Session Type</td>
				<td class="bgcolor_light"><?php echo $this->form['session_type_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Sub Theme</td>
				<td class="bgcolor_light"><?php echo $this->form['sub_theme_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Sub Sub Theme</td>
				<td class="bgcolor_light"><?php echo $this->form['sub_sub_theme_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Theme Path</td>
				<td class="bgcolor_light"><?php echo $this->form['theme_string_html']; ?></td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td class="bgcolor_light" colspan="2" style="text-align: center; padding: 6px;">
					<?php echo $this->form['submit_html']; ?>
				</td>
			</tr>
			</tfoot>
		</table>

	</form>

<?php } else { ?>

<table style="width: 100%;">
<tr>
	<td class="bgcolor_medium header_row">ID</td>
	<td class="bgcolor_medium header_row">Title</td>
	<td class="bgcolor_medium header_row">Public Title</td>
	<td class="bgcolor_medium header_row">Path</td>
	<td class="bgcolor_medium header_row">Session Type</td>
	<td class="bgcolor_medium header_row"><a href="/backoffice/manage_dream_event_theme?create" class="button">Create Theme</a></td>
</tr>
<?php foreach ($this->bundleThemeArray AS $id => $bundleTheme) { ?>
<tr>
	<td class="bgcolor_light" style="text-align: center;"><?php echo $bundleTheme->id; ?></td>
	<td class="bgcolor_light"><?php echo $bundleTheme->title; ?></td>
	<td class="bgcolor_light"><?php echo $bundleTheme->title_public; ?></td>
	<td class="bgcolor_light"><?php echo $bundleTheme->theme_string; ?></td>
	<td class="bgcolor_light"><?php echo $bundleTheme->session_type; ?></td>
	<td class="bgcolor_light" style="text-align: center;"><a href="/backoffice/manage_dream_event_theme?edit=<?php echo $bundleTheme->id; ?>" class="button">Edit</a></td>
</tr>
<?php } ?>
</table>

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>