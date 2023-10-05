<?php $this->assign('page_title','Search Franchisees'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Search Franchisees</h1>

<form action="/backoffice/list-franchisees" method="get">
	Search (string or id):
	<input type="hidden" name="letter_select" value="<?php echo $this->letter_select; ?>" />
	<input type="text" name="q" value="<?php echo $this->q; ?>" />
	<input type="submit" class="button" value="Search" />
</form>

<table style="width: 100%;">
<tr>
	<td>
		<a href="/backoffice/list_franchisees?letter_select=A" class="button">A</a>
		<a href="/backoffice/list_franchisees?letter_select=B" class="button">B</a>
		<a href="/backoffice/list_franchisees?letter_select=C" class="button">C</a>
		<a href="/backoffice/list_franchisees?letter_select=D" class="button">D</a>
		<a href="/backoffice/list_franchisees?letter_select=E" class="button">E</a>
		<a href="/backoffice/list_franchisees?letter_select=F" class="button">F</a>
		<a href="/backoffice/list_franchisees?letter_select=G" class="button">G</a>
		<a href="/backoffice/list_franchisees?letter_select=H" class="button">H</a>
		<a href="/backoffice/list_franchisees?letter_select=I" class="button">I</a>
		<a href="/backoffice/list_franchisees?letter_select=J" class="button">J</a>
		<a href="/backoffice/list_franchisees?letter_select=K" class="button">K</a>
		<a href="/backoffice/list_franchisees?letter_select=L" class="button">L</a>
		<a href="/backoffice/list_franchisees?letter_select=M" class="button">M</a>
		<a href="/backoffice/list_franchisees?letter_select=N" class="button">N</a>
		<a href="/backoffice/list_franchisees?letter_select=O" class="button">O</a>
		<a href="/backoffice/list_franchisees?letter_select=P" class="button">P</a>
		<a href="/backoffice/list_franchisees?letter_select=Q" class="button">Q</a>
		<a href="/backoffice/list_franchisees?letter_select=R" class="button">R</a>
		<a href="/backoffice/list_franchisees?letter_select=S" class="button">S</a>
		<a href="/backoffice/list_franchisees?letter_select=T" class="button">T</a>
		<a href="/backoffice/list_franchisees?letter_select=U" class="button">U</a>
		<a href="/backoffice/list_franchisees?letter_select=V" class="button">V</a>
		<a href="/backoffice/list_franchisees?letter_select=W" class="button">W</a>
		<a href="/backoffice/list_franchisees?letter_select=X" class="button">X</a>
		<a href="/backoffice/list_franchisees?letter_select=Y" class="button">Y</a>
		<a href="/backoffice/list_franchisees?letter_select=Z" class="button">Z</a>
		<a href="/backoffice/list_franchisees?letter_select=etc" class="button">ETC</a>
		<a href="/backoffice/list_franchisees?letter_select=all" class="button">Show	All</a>
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
		<td class="bgcolor_light"><a href="/backoffice/user_details?id=<?php echo $row['user_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row['user_id']; ?></a></td>
		<td class="bgcolor_light"><?php echo $row['lastname']; ?></td>
		<td class="bgcolor_light"><?php echo $row['firstname']; ?></td>
		<td class="bgcolor_light"><a href="/backoffice/email?id=<?php echo $row['user_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row['primary_email']; ?></a></td>
		<td class="bgcolor_light"><?php echo (!empty($row['telephone_day'])) ? $row['telephone_day'] : 'None'; ?></td>
		<td class="bgcolor_light"><a href="/backoffice/franchise-details?id=<?php echo $row['franchise_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row['franchise_name']; ?></a></td>
		<td class="bgcolor_light"><?php if ( $row['active'] ) echo 'yes'; else echo 'no';?></td>
	</tr>
	<?php } ?>
	</table>

</div>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>