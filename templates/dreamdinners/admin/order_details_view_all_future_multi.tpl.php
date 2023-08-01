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

<?php echo $this->output;?>

</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>