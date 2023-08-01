<?php if (!isset($this->suppress_header_footer)) { ?>
	<?php $this->assign('page_title','Reports'); ?>
	<?php $this->assign('print_view', true); ?>
	<?php $this->assign('suppress_table_wrapper', true); ?>
	<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
	<div class="container">
<?php } ?>

<?php if (!empty($_REQUEST['issidedish'])) { ?>
	<table class="Normalview">
		<tr>
			<td style="width:100px;"><img style="height: 100px;" src="<?= ADMIN_IMAGES_PATH ?>/style/light_green_logo.png" /></td>
			<td style="padding-left:6px;font-weight:bold;font-size:18px;">Side Dish Customized Report <?php echo (!$this->customer_view) ? 'Store Receipt' : ''; ?></td>
			<?php if (isset($this->null_array) && $this->null_array == TRUE) { ?>
		</tr>
		<tr>
			<td>&nbsp;</td><td>No Side Dishes were ordered for this session.</td>
			<?php } ?>
		</tr>
	</table>
	<br />
<?php } ?>

<?php if (!empty($_REQUEST['ispreassembled'])) { ?>
	<table class="Normalview">
		<tr>
			<td style="width:100px;"><img style="height: 100px;" src="<?= ADMIN_IMAGES_PATH ?>/style/light_green_logo.png" /></td>
			<td style="padding-left:6px;font-weight:bold;font-size:18px;">Fast Lane Customized Report <?php echo (!$this->customer_view) ? 'Store Receipt' : ''; ?></td>
			<?php if (isset($this->null_array) && $this->null_array == TRUE) { ?>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>No Fast Lane Dishes were ordered for this session.</td>
			<?php } ?>
		</tr>
	</table>
	<br/>
<?php } ?>

<?php
$counter = 0;
foreach ($this->view_all_list as $arrItem)
{
	$var = $this->other_details_list[$counter++];

	echo $arrItem;

	if (empty($var['sidereport']) && empty($var['assemblereport']))
	{
		if ($counter < count($this->view_all_list))
		{
			echo '<div style="page-break-after:always;"></div>';
		}
	}
}
?>

<?php if (!isset($this->suppress_header_footer)) { ?>
	<?php if (empty($this->suppress_table_wrapper)) { ?>
		</div>
	<?php } ?>
	</body>
	</html>
<?php } ?>