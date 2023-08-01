<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports.min.js'); ?>
<?php $this->assign('page_title','Reports'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h3 style="text-align:center; margin-bottom:10px;">Reports Menu</h3>
<div style="text-align:left">

<?php
if (isset($this->template_name))
{
	include $this->loadTemplate($this->template_name);
}
else
{
	echo "Sorry, you do not have any access rights to view any of these reports";
}
?>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>