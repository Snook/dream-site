<?php $this->assign('page_title','Access Page Override'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Manage Page Acces Overrides</h1>

<form action="" method="post" onsubmit="return _check_form(this);" >
<table style="width: 100%;">
<tr>
	<td>Grant User ID  <?php echo $this->form_create['user_id_entry_html']; ?> access to page <?php echo $this->form_create['page_dropdown_html']; ?> <?php echo $this->form_create['user_submit_html']; ?></td>
</tr>
</table>
</form >

<?php if (!empty($this->userpages)) {?>
<form action="" method="post" onsubmit="return _check_form(this);" >
<table style="width: 100%;">
<tr>
	<td class="bgcolor_dark catagory_row" style="text-align: center;" colspan="6">List of all users that have Access Page Overrides</td>
</tr>
<tr>
	<td class="bgcolor_medium header_row">Access ID</td>
	<td class="bgcolor_medium header_row">Page Name</td>
	<td class="bgcolor_medium header_row">User ID</td>
	<td class="bgcolor_medium header_row">Last Name</td>
	<td class="bgcolor_medium header_row">First Name</td>
	<td class="bgcolor_medium header_row">Remove Access</td>
</tr>
<?php foreach($this->userpages as $element ){   ?>
<tr>
	<td class="bgcolor_light"><?php echo $element['access_id']; ?></td>
	<td class="bgcolor_lighter"><?php echo $element['page_name']; ?></td>
	<td class="bgcolor_light"><a href="/backoffice/user_details?id=<?php echo $element['id']; ?>"><?php echo $element['id']; ?></a></td>
	<td class="bgcolor_lighter"><?php echo $element['lastname']; ?></td>
	<td class="bgcolor_light"><?php echo $element['firstname']; ?></td>
	<td class="bgcolor_lighter"><input type="checkbox" name="ch_<?php echo $element['access_id']; ?>" /></td>
</tr>
<?php } ?>
<tr>
	<td style="text-align:right;" colspan="6"><?php echo $this->form_create['remove_access_html']; ?></td>
</tr>
</table>
</form>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>