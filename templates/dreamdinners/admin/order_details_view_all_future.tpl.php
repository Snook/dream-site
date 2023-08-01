<?php if (!isset($this->suppress_header_footer)) { ?>
<?php $this->assign('page_title','Reports'); ?>
<?php $this->assign('print_view', true); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<style type="text/css">
P.breakhere
{
	page-break-after:always;
}
P.breakafter
{
	page-break-before:always;
}
.Normalview
{
	width:100%;
	font-family: "Trebuchet MS", serif;
	font-size: 11px;
	color: #333333;
	height: 24px;
	line-height: 135%;
}
.form_field_cell {
	font-family: "Trebuchet MS", serif;
	font-size: 11px;
	color: #333333;
	height: 24px;
	line-height: 135%;
}
.form_subtitle_cell
{
	border-top: 1px dashed #000000;
	border-bottom: 1px dashed #000000;
}
.page_wrapper
{
	max-width:800px;
	margin:auto;
}
</style>

<div class="page_wrapper">
<?php } ?>


<?php
$counter = 0;
foreach ( $this->view_all_list as $arrItem )
{
	$var = $this->other_details_list[$counter++];
?>

<table class="Normalview">
<tr>
	<td style="width:100px;"><img style="height: 100px;" src="<?= ADMIN_IMAGES_PATH ?>/style/light_green_logo.png" /></td>
	<td style="padding-left:6px;font-weight:bold;font-size:18px;">Future Order</td>
</tr>
</table>

<?php
	echo $arrItem;

	if ($counter < count($this->view_all_list))
	{
		echo '<p class="breakafter"></p>';
	}
	$counter++;
}

if($counter == 0)
{
	echo '<p style="font-family: "Trebuchet MS", serif;font-size: 18px;">No future orders have been placed by session guests.</p>';
}
?>

<?php if (!isset($this->suppress_header_footer)) { ?>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
<?php } ?>