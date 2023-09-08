<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Home Office Access Levels</h1>

<table style="width: 100%;">
<tr>
	<th class="bgcolor_medium header_row">ID</th>
	<th class="bgcolor_medium header_row">User type</th>
	<th class="bgcolor_medium header_row">Last name</th>
	<th class="bgcolor_medium header_row">First name</th>
	<th class="bgcolor_medium header_row">Email</th>
	<th class="bgcolor_medium header_row">Last login</th>
</tr>
<?php foreach ($this->users as $user_id => $user) { ?>
<tr>
	<td class="bgcolor_lighter"><a href="?page=admin_user_details&id=<?php echo $user['id']; ?>"><?php echo $user['id']; ?></a></td>
	<td class="bgcolor_light"><a href="?page=admin_access_levels&amp;id=<?php echo $user['id']; ?>"><?php echo CUser::userTypeText($user['user_type']); ?></a></td>
	<td class="bgcolor_lighter"><?php echo $user['lastname']; ?></td>
	<td class="bgcolor_light"><?php echo $user['firstname']; ?></td>
	<td class="bgcolor_lighter"><?php echo $user['primary_email']; ?></td>
	<td class="bgcolor_light"><?php echo $user['last_login']; ?></td>
</tr>
<?php } ?>
</table>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>