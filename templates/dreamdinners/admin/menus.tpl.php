<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/calendarDateInput.js'); ?>
<?php $this->assign('page_title', 'Create and Edit Menus'); ?>
<?php $this->assign('topnav', 'import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h1>Menu Setup</h1>

	<div id="menu_choose">
		<form name="createMenu" action="/backoffice/menus" method="get" onsubmit="return confirm('Are you sure you wish to create this menu? Food data must be imported immediately following menu creation.');">
			<b>Create Menu</b> Next Menu to be created will be: <b><?php echo $this->form_menus['next_menu_html']; ?></b>
			<input type="hidden" name="create" value="1">
			<input type="submit" class="btn btn-primary btn-sm" value="Create Menu">
		</form>
		<br/>
		OR
		<br/><br/>
		<form name="changeMenus" action="/backoffice/menus" method="get">
			<b>Edit Menu</b> <?php echo $this->form_menus['menu_edit_html']; ?>
		</form>
		<br/>

	</div>

	<br/>

<?php if ($this->form_menus['menu_edit']) { ?>

	<div id="menu_edit" class="form_field_cell">
		<form name="editMenu" action="" method="post">
			<input type="hidden" name="menu_edit" value="<?php echo $this->form_menus['id']; ?>">
			<table>
				<tr class="form_subtitle_cell">
					<td>Menu</td>
					<td><b>ID <?php echo $this->form_menus['id']; ?> <?php echo $this->form_menus['menu_name']; ?></b></td>
				</tr>
				<tr>
					<td>Is Active</td>
					<td><?php echo $this->form_menus['is_active_html']; ?></td>
				</tr>
				<tr>
					<td>Menu Start date</td>
					<td><?php echo $this->sz_previous_menu_end; ?></td>
				</tr>
				<tr>
					<td>Menu END date</td>
					<td>
						<script type="text/javascript">DateInput('global_menu_end_date', true, 'YYYY-MM-DD', '<?php echo $this->end_date; ?>');</script>
						<?php if (empty($this->global_menu_end_date)) { ?><span style="color: #f00;">End date not set! Choose date and save changes!</span><?php } ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="save_changes" class="btn btn-primary btn-sm" value="Save Changes"></td>
				</tr>
			</table>
		</form>

	</div>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>