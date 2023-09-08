<?php $this->setScript('head', SCRIPT_PATH . '/admin/manage_survey.min.js'); ?>
<?php $this->assign('page_title','Test Recipes'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<table style="width:100%;">
<tr>
	<td class="bgcolor_medium header_row">Name</td>
	<td class="bgcolor_medium header_row">Link</td>
	<td class="bgcolor_medium header_row">Action</td>
</tr>
   <?php  if (count($this->rows) > 0) {
	foreach ($this->rows as $id => $row) { ?>
<tr>
	<td class="bgcolor_light" id="name_<?=$id?>"><?= $row['name'];?></td>
	<td class="bgcolor_light" id="link_<?=$id?>"><?= urlencode($row['link']);?></td>
	<td class="bgcolor_light" style="text-align: center;">
		<a href="?page=admin_manage_survey&recipe_id=<?=$id?>&action=delete" class="button">delete</a>
		<a href="javascript:edit(<?=$id?>);" class="button">edit</a>
		<a target="_blank" href="<?=$row['link'];?>" class="button">test link</a>
	</td>
 </tr>
  <?php } } else { ?>
<tr>
	<td align="center" colspan="3" class="bgcolor_light"><i>There are no recipes.</i></td>
</tr>
<?php } ?>
</table>

<form method="post" onsubmit="return _check_form(this);">
<?=$this->form['hidden_html'];?>

<br />

<table style="width:100%;">
<tr class="form_field_cell">
	<td width="30">&nbsp;</td>
	<td width="150" align="right">Recipe Name</td>
	<td width="350"><?=$this->form['name_html']; ?>*<br />
		<label id="name_lbl" for="name" data-message="The Recipe name is required."></label>
	</td>
</tr>
<tr class="form_field_cell">
	<td width="30" >&nbsp;</td>
	<td width="150" align="right">Link</td>
	<td width="350" ><?=$this->form['link_html']; ?>*<br />
		<label id="firstname_lbl" for="firstname" data-message="Please enter link."></label></td>
</tr>
<tr id="tr_Add" class="form_field_cell">
	<td width="30" >&nbsp;</td>
	<td width="500" colspan="2" align="right" ><?=$this->form['add_submit_html']; ?></td>
</tr>
<tr style="display:none;" id="tr_Edit" class="form_field_cell">
	<td width="30" >&nbsp;</td>
	<td width="500" colspan="2" align="right" ><span onclick="cancel_edit();" class="button">Cancel</span>&nbsp;<?=$this->form['edit_submit_html']; ?></td>
</tr>
</table>

</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>