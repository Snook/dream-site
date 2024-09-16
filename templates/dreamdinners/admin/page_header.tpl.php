<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo (!empty($this->page_title)) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - Quick, Healthy and Easy Family Dinners'; ?></title>
	<?php include $this->loadTemplate('admin/page_head_css.tpl.php'); ?>
	<?php if (!$this->page_is_bootstrap) { ?>
	<?php include $this->loadTemplate('admin/page_header_javascript.tpl.php'); ?>
	<?php include $this->loadTemplate('admin/page_footer_onload.tpl.php'); ?>
	<?php } ?>
</head>
<body>

<?php include $this->loadTemplate('admin/application_maintenance_msg.tpl.php'); ?>

<?php if (empty($this->print_view) || $this->print_view != true)
{
	include $this->loadTemplate('admin/subtemplate/admin_nav.tpl.php');
}
?>

<?php if (empty($this->suppress_table_wrapper)) { ?>
<table class="page">
	<tr>
		<td>
<?php } ?>