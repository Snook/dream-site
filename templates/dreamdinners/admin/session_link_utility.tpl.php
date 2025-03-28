<?php $this->assign('page_title','Session Link Creation Utility'); ?>

<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

 <center><div style="max-width:1000px;">
<form method="post">
<table class="ME_menu_editor_table" width="100%">
<!-- Header area -->

<?php if (isset($this->form['store_html'])) { ?>
<!-- Store Row : for site admin only -->
<tr class="form_subtitle_cell" >
<td align="center" colspan="2" style="padding: 5px;">
<b>Selected Store:</b>&nbsp;<?=$this->form['store_html']; ?> </td>
</tr>
<?php } ?>



<tr>
	<td>

<!-- data display area -->
<tr><td colspan="2">
<table id="itemsTbl" width="100%">
<tr class="form_subtitle_cell">
<td class="ME_header_zebra_odd" style="padding: 2px;">Session Date/Time</td>
<td class="ME_header_zebra_even"  style="padding: 2px;">Session State</td>
<td class="ME_header_zebra_odd"  style="padding: 2px;">Total Standard Slots</td>
<td class="ME_header_zebra_even" style="padding: 2px;">Remaining Slots</td>
</tr>
<?php
if (count($this->sessions)) {
	foreach ($this->sessions as $id => $session_data){ ?>
<tr>
<td class="ME_zebra_odd" style="padding: 2px;"><?=$session_data['start'] ?></td>
<td class="ME_zebra_even"  style="padding: 2px;"><?=$session_data['state'] ?></td>
<td class="ME_zebra_odd"  style="padding: 2px;"><?=$session_data['slots_available'] ?></td>
<td class="ME_zebra_even" style="padding: 2px;"><?=$session_data['slots_remaining'] ?></td>
</tr>
<tr>
<td colspan="4">Copy This: <font color="green"> <a href="<?=HTTPS_BASE ?>session/<?=$id?>">Click Here</a></font>&nbsp;&nbsp;&nbsp;|
			&nbsp;&nbsp;&nbsp;or Copy This: <font color="green"><?=HTTPS_BASE?>session/<?=$id?></font></td>
</tr>
<?php } } else { ?>
<tr>
<td colspan="4" align="center"><i>There are currently no future sessions</i></td>
</tr>
<?php } ?>
</table>
</td></tr>
</table>

</form>
</div></center>



<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>