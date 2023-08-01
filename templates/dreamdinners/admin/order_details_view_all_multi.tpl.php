<?php if (!isset($this->suppress_header_footer)) { ?>
	<?php $this->assign('page_title','Reports'); ?>
	<?php $this->assign('print_view', true); ?>
	<?php $this->assign('suppress_table_wrapper', true); ?>
	<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
	<div class="container">
<?php } ?>

<?php echo $this->output;?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>