<?php $this->setScript('head', SCRIPT_PATH . '/admin/list_stores.min.js'); ?>
<?php $this->setOnload('admin_list_stores_init();'); ?>
<?php $this->assign('page_title','Search Stores'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Search Stores</h1>

<form action="/backoffice/list-stores" method="get">
	Search (string or id):
	<input type="hidden" name="letter_select" value="<?php echo $this->letter_select; ?>" />
	<input type="text" name="q" value="<?php echo $this->q; ?>" />
	<input type="submit" class="btn btn-primary btn-sm" value="Search" />
</form>

<table style="width: 100%;">
<tr>
	<td>
		<a href="/backoffice/list_stores?letter_select=A" class="btn btn-primary btn-sm">A</a>
		<a href="/backoffice/list_stores?letter_select=B" class="btn btn-primary btn-sm">B</a>
		<a href="/backoffice/list_stores?letter_select=C" class="btn btn-primary btn-sm">C</a>
		<a href="/backoffice/list_stores?letter_select=D" class="btn btn-primary btn-sm">D</a>
		<a href="/backoffice/list_stores?letter_select=E" class="btn btn-primary btn-sm">E</a>
		<a href="/backoffice/list_stores?letter_select=F" class="btn btn-primary btn-sm">F</a>
		<a href="/backoffice/list_stores?letter_select=G" class="btn btn-primary btn-sm">G</a>
		<a href="/backoffice/list_stores?letter_select=H" class="btn btn-primary btn-sm">H</a>
		<a href="/backoffice/list_stores?letter_select=I" class="btn btn-primary btn-sm">I</a>
		<a href="/backoffice/list_stores?letter_select=J" class="btn btn-primary btn-sm">J</a>
		<a href="/backoffice/list_stores?letter_select=K" class="btn btn-primary btn-sm">K</a>
		<a href="/backoffice/list_stores?letter_select=L" class="btn btn-primary btn-sm">L</a>
		<a href="/backoffice/list_stores?letter_select=M" class="btn btn-primary btn-sm">M</a>
		<a href="/backoffice/list_stores?letter_select=N" class="btn btn-primary btn-sm">N</a>
		<a href="/backoffice/list_stores?letter_select=O" class="btn btn-primary btn-sm">O</a>
		<a href="/backoffice/list_stores?letter_select=P" class="btn btn-primary btn-sm">P</a>
		<a href="/backoffice/list_stores?letter_select=Q" class="btn btn-primary btn-sm">Q</a>
		<a href="/backoffice/list_stores?letter_select=R" class="btn btn-primary btn-sm">R</a>
		<a href="/backoffice/list_stores?letter_select=S" class="btn btn-primary btn-sm">S</a>
		<a href="/backoffice/list_stores?letter_select=T" class="btn btn-primary btn-sm">T</a>
		<a href="/backoffice/list_stores?letter_select=U" class="btn btn-primary btn-sm">U</a>
		<a href="/backoffice/list_stores?letter_select=V" class="btn btn-primary btn-sm">V</a>
		<a href="/backoffice/list_stores?letter_select=W" class="btn btn-primary btn-sm">W</a>
		<a href="/backoffice/list_stores?letter_select=X" class="btn btn-primary btn-sm">X</a>
		<a href="/backoffice/list_stores?letter_select=Y" class="btn btn-primary btn-sm">Y</a>
		<a href="/backoffice/list_stores?letter_select=Z" class="btn btn-primary btn-sm">Z</a>
		<a href="/backoffice/list_stores?letter_select=etc" class="btn btn-primary btn-sm">ETC</a>
		<a href="/backoffice/list_stores?letter_select=all" class="btn btn-primary btn-sm">Show	All</a>
	</td>
</tr>
</table>

Or Select Store: <?php echo $this->form_list_stores['store_html']; ?>

<?php if ($this->rows) { ?>
<div style="margin-top: 10px;">

	<span style="float: right;"><?php include $this->loadTemplate('admin/export.tpl.php'); ?></span>
	Your query returned <?php echo $this->rowcount; ?> matches:

	<table style="width: 100%;">
	<tr>
		<td class="bgcolor_medium header_row">ID</td>
		<td class="bgcolor_medium header_row">HO ID</td>
		<td class="bgcolor_medium header_row">Store Name</td>
		<td class="bgcolor_medium header_row">City</td>
		<td class="bgcolor_medium header_row">State</td>
		<td class="bgcolor_medium header_row">Active</td>
		<td class="bgcolor_medium header_row">Merch Info</td>
		<td class="bgcolor_medium header_row">Franchise</td>

	</tr>
	<?php $active = 1; foreach( $this->rows as $id => $row ) { ?>
	<?php if ($active && $active != $row['active']) { $active = false; ?>
	<tr>
		<td class="bgcolor_medium header_row" style="text-align:left;" colspan="8">Inactive</td>
	</tr>
	<?php } ?>
	<tr>
		<td class="bgcolor_light"><a href="/backoffice/store_details?id=<?php echo $id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row['id']; ?></a></td>
		<td class="bgcolor_light"><a href="/backoffice/store_details?id=<?php echo $id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row['home_office_id']; ?></a></td>
		<td class="bgcolor_light"><a href="/backoffice/store_details?id=<?php echo $id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo CAppUtil::truncate($row['store_name'],30); ?></a></td>
		<td class="bgcolor_light"><?php echo $row['city']; ?></td>
		<td class="bgcolor_light" style="text-align:center;"><?php echo $row['state_id']; ?></td>
		<td class="bgcolor_light" style="text-align:center;"><?php echo (!empty($row['active'])) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>'; ?></td>
		<td class="bgcolor_light" style="text-align:center;"><a href="/backoffice/merchant?store=<?php echo $id; ?>">edit</a></td>
		<td class="bgcolor_light"><a href="/backoffice/franchise-details?id=<?php echo $row['franchise_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo CAppUtil::truncate($row['franchise_name'],30); ?></a></td>
	</tr>
	<?php } ?>
	</table>

</div>

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>