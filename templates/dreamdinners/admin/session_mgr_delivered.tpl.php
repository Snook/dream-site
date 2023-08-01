<?php $this->setScript('head', SCRIPT_PATH . '/admin/session_mgr.min.js'); ?>
<?php $this->assign('page_title','Session Calendar'); ?>
<?php $this->assign('topnav','sessions'); ?>
<?php $this->assign('helpLinkSection','SM'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<form method="post">
<table width="100%" border="0">
<tr>
<?php if (isset($this->storeAndMenu['store_html'])) { ?>
	<td style="padding-left:20px;font-weight:bold;">Store <?=$this->storeAndMenu['store_html']; ?></td>
<?php } ?>
	<td style="padding-right:20px;font-weight:bold;text-align:right;">Menu <?=$this->storeAndMenu['menus_html']?></td>
</tr>
</table>
</form>

<center>
<?php include $this->loadTemplate('admin/subtemplate/calendar_delivered.tpl.php'); ?>
</center>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>