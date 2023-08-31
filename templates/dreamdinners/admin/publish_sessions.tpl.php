<?php $this->setScript('head', SCRIPT_PATH . '/admin/publish_sessions.min.js'); ?>
<?php $this->setScriptVar("calendarName = '" . $this->calendarName . "';"); ?>
<?php $this->setScriptVar('isSafari = ' . ($this->isSafari() ? 'true' : 'false') . ';'); ?>
<?php $this->assign('page_title','Publish Multiple Sessions'); ?>
<?php $this->assign('topnav','sessions'); ?>
<?php $this->assign('helpLinkSection','SP'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if ($this->noValidSet) { ?>

<table style="width: 100%;">
<tr>
<td>
	You must first visit the <b>Template Manager</b> and create a template before the sessions can be pulished on this page.<br /><br />
	Please visit or the <a href="/main.php?page=admin_session_template_mgr">Template Manager</a> or view the
	<a href="javascript:NewWindowScroll('main.php?page=admin_help_system&section=SP','Help','675','575');">Help</a> page.
</td>
</tr>
</table>

<?php } else { ?>

<form id="fillWindowForm" method="post">
<table style="width: 100%;">
<tr>
	<td style="padding-left:20px;">
		<input type="button" class ="button" value="Set Fill Start Date" onclick="onSetFillWindowStart();" />
		<input type="button" class ="button" value="Set Fill End Date" onclick="onSetFillWindowEnd();" />
		<input type="button" class ="button" value="Clear Fill Window" onclick="onClearFillWindow();" />
	</td>
	<td>
		<?=$this->templateSetForm['fill_window_html']?> <?=$this->templateSetForm['set_id_html']?>
	</td>
	<td style="padding-right:20px;text-align:right;">
		<input id="saveButton" type="button" class ="button" value="Save" onclick="saveItems();" />
		<input id="publishButton" type="button" class ="button" value="Publish" onclick="publishItems();" />
	</td>
</tr>
<?=$this->templateSetForm['hidden_html']?>
</table>
<table style="width: 100%;">
<tr>
<?php if (isset($this->storeAndMenu['store_html'])) { ?>
	<td style="padding-left:20px;font-weight:bold;">Store <?=$this->storeAndMenu['store_html']; ?></td>
<?php } ?>
	<td style="padding-right:20px;font-weight:bold;text-align:right;">Menu <?=$this->storeAndMenu['menus_html']?></td>
</tr>
</table>
</form>

<center><?php include $this->loadTemplate('admin/subtemplate/calendar.tpl.php');?></center>
<table style="width: 100%;">
<tr><td height="4" colspan="5"></td></tr>
<tr>
	<td><b>LEGEND</b>&nbsp;&nbsp;&nbsp;</td>
	<td><img src="<?= ADMIN_IMAGES_PATH?>/session_closed.png">&nbsp;= Closed or Expired Session</td>
	<td><img src="<?= ADMIN_IMAGES_PATH?>/session_saved.png">&nbsp;= Saved Session (never published)</td>
	<td><img src="<?= ADMIN_IMAGES_PATH?>/session_pub.png">&nbsp;= Published Session</td>
</tr>
	<tr><td>&nbsp;</td><td height="4" colspan="4"><img src="<?= ADMIN_IMAGES_PATH?>/session_new.png">&nbsp;= a New Session (not editable until saved or published)
	</td></tr>
</table>

 <?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>