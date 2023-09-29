<?php $this->assign('page_title','Search Franchises'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Search Franchises</h1>

<form action="/backoffice/list-franchise" method="get">
	Search (string or id):
	<input type="hidden" name="letter_select" value="<?php echo $this->letter_select; ?>" />
	<input type="text" name="q" value="<?php echo $this->q; ?>" />
	<input type="submit" class="button" value="Search" />
</form>

<table style="width: 100%;">
<tr>
	<td>
		<a href="/?page=admin_list_franchise&amp;letter_select=A" class="button">A</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=B" class="button">B</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=C" class="button">C</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=D" class="button">D</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=E" class="button">E</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=F" class="button">F</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=G" class="button">G</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=H" class="button">H</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=I" class="button">I</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=J" class="button">J</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=K" class="button">K</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=L" class="button">L</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=M" class="button">M</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=N" class="button">N</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=O" class="button">O</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=P" class="button">P</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=Q" class="button">Q</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=R" class="button">R</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=S" class="button">S</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=T" class="button">T</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=U" class="button">U</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=V" class="button">V</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=W" class="button">W</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=X" class="button">X</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=Y" class="button">Y</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=Z" class="button">Z</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=etc" class="button">ETC</a>
		<a href="/?page=admin_list_franchise&amp;letter_select=all" class="button">Show	All</a>
	</td>
</tr>
</table>

<?php if ($this->rows) { ?>
<div style="margin-top: 10px;">

	<span style="float: right;"><?php include $this->loadTemplate('admin/export.tpl.php'); ?></span>
	Your query returned <?php echo $this->rowcount; ?> matches:

	<table style="width: 100%;">
	<tr>
		<td class="bgcolor_medium header_row">ID</td>
		<td class="bgcolor_medium header_row">Entity Name</td>
		<td class="bgcolor_medium header_row">Active</td>
		<td class="bgcolor_medium header_row">Date Created</td>
		<td class="bgcolor_medium header_row">Last Updated</td>
	</tr>
	<?php $active = 1; foreach( $this->rows as $id => $row ) { ?>
	<?php if ($active && $active != $row['active']) { $active = false; ?>
	<tr>
		<td class="bgcolor_medium header_row" colspan="5">Inactive</td>
	</tr>
	<?php } ?>
	<tr>
		<td class="bgcolor_light"><a href="/?page=admin_franchise_details&amp;id=<?php echo $row['id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row['id']; ?></a></td>
		<td class="bgcolor_light"><a href="/?page=admin_franchise_details&amp;id=<?php echo $row['id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $row['franchise_name']; ?></a></td>
		<td class="bgcolor_light" style="text-align: center;"><?php echo (!empty($row['active'])) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>'; ?></td>
		<td class="bgcolor_light" style="text-align: center;"><?php echo CTemplate::dateTimeFormat($row['timestamp_created'], MONTH_DAY_YEAR); ?></td>
		<td class="bgcolor_light" style="text-align: center;"><?php echo CTemplate::dateTimeFormat($row['timestamp_updated'], MONTH_DAY_YEAR); ?></td>
	</tr>
	<?php } ?>
	</table>

</div>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>