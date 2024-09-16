<?php $this->assign('page_title','Search Franchisees'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Search Franchisees</h1>

<form action="/backoffice/list-franchisees" method="get">
	Search (string or id):
	<input type="hidden" name="letter_select" value="<?php echo $this->letter_select; ?>" />
	<input type="text" name="q" value="<?php echo $this->q; ?>" />
	<input type="submit" class="btn btn-primary btn-sm" value="Search" />
</form>

<table style="width: 100%;">
<tr>
	<td>
		<a href="/backoffice/list_franchisees?letter_select=A" class="btn btn-primary btn-sm">A</a>
		<a href="/backoffice/list_franchisees?letter_select=B" class="btn btn-primary btn-sm">B</a>
		<a href="/backoffice/list_franchisees?letter_select=C" class="btn btn-primary btn-sm">C</a>
		<a href="/backoffice/list_franchisees?letter_select=D" class="btn btn-primary btn-sm">D</a>
		<a href="/backoffice/list_franchisees?letter_select=E" class="btn btn-primary btn-sm">E</a>
		<a href="/backoffice/list_franchisees?letter_select=F" class="btn btn-primary btn-sm">F</a>
		<a href="/backoffice/list_franchisees?letter_select=G" class="btn btn-primary btn-sm">G</a>
		<a href="/backoffice/list_franchisees?letter_select=H" class="btn btn-primary btn-sm">H</a>
		<a href="/backoffice/list_franchisees?letter_select=I" class="btn btn-primary btn-sm">I</a>
		<a href="/backoffice/list_franchisees?letter_select=J" class="btn btn-primary btn-sm">J</a>
		<a href="/backoffice/list_franchisees?letter_select=K" class="btn btn-primary btn-sm">K</a>
		<a href="/backoffice/list_franchisees?letter_select=L" class="btn btn-primary btn-sm">L</a>
		<a href="/backoffice/list_franchisees?letter_select=M" class="btn btn-primary btn-sm">M</a>
		<a href="/backoffice/list_franchisees?letter_select=N" class="btn btn-primary btn-sm">N</a>
		<a href="/backoffice/list_franchisees?letter_select=O" class="btn btn-primary btn-sm">O</a>
		<a href="/backoffice/list_franchisees?letter_select=P" class="btn btn-primary btn-sm">P</a>
		<a href="/backoffice/list_franchisees?letter_select=Q" class="btn btn-primary btn-sm">Q</a>
		<a href="/backoffice/list_franchisees?letter_select=R" class="btn btn-primary btn-sm">R</a>
		<a href="/backoffice/list_franchisees?letter_select=S" class="btn btn-primary btn-sm">S</a>
		<a href="/backoffice/list_franchisees?letter_select=T" class="btn btn-primary btn-sm">T</a>
		<a href="/backoffice/list_franchisees?letter_select=U" class="btn btn-primary btn-sm">U</a>
		<a href="/backoffice/list_franchisees?letter_select=V" class="btn btn-primary btn-sm">V</a>
		<a href="/backoffice/list_franchisees?letter_select=W" class="btn btn-primary btn-sm">W</a>
		<a href="/backoffice/list_franchisees?letter_select=X" class="btn btn-primary btn-sm">X</a>
		<a href="/backoffice/list_franchisees?letter_select=Y" class="btn btn-primary btn-sm">Y</a>
		<a href="/backoffice/list_franchisees?letter_select=Z" class="btn btn-primary btn-sm">Z</a>
		<a href="/backoffice/list_franchisees?letter_select=etc" class="btn btn-primary btn-sm">ETC</a>
		<a href="/backoffice/list_franchisees?letter_select=all" class="btn btn-primary btn-sm">Show	All</a>
	</td>
</tr>
</table>

<?php if ($this->rows) { ?>
<div style="margin-top: 10px;">

	<span style="float: right;"><?php include $this->loadTemplate('admin/export.tpl.php'); ?></span>
	Your query returned <?php echo $this->rowcount; ?> matches:

	<table style="width: 100%;">
	<tr>
		<td class="bgcolor_medium header_row">User ID</td>
		<td class="bgcolor_medium header_row">Last Name</td>
		<td class="bgcolor_medium header_row">First Name</td>
		<td class="bgcolor_medium header_row">Primary Email</td>
		<td class="bgcolor_medium header_row">Telephone (day)</td>
		<td class="bgcolor_medium header_row">Entity Name</td>
		<td class="bgcolor_medium header_row">Active</td>
	</tr>
	<?php foreach( $this->rows as $id => $row ) { ?>
	<tr>
		<td class="bgcolor_light"><a href="/backoffice/user-details?id=<?php echo $row['user_id']; ?>"><?php echo $row['user_id']; ?></a></td>
		<td class="bgcolor_light"><?php echo $row['lastname']; ?></td>
		<td class="bgcolor_light"><?php echo $row['firstname']; ?></td>
		<td class="bgcolor_light"><a href="/backoffice/email?id=<?php echo $row['user_id']; ?>"><?php echo $row['primary_email']; ?></a></td>
		<td class="bgcolor_light"><?php echo (!empty($row['telephone_day'])) ? $row['telephone_day'] : 'None'; ?></td>
		<td class="bgcolor_light"><a href="/backoffice/franchise-details?id=<?php echo $row['franchise_id']; ?>"><?php echo $row['franchise_name']; ?></a></td>
		<td class="bgcolor_light"><?php if ( $row['active'] ) echo 'yes'; else echo 'no';?></td>
	</tr>
	<?php } ?>
	</table>

</div>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>