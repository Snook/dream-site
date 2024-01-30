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
				Please visit or the <a href="/backoffice/session_template_mgr">Template Manager</a> or view the
				<a href="javascript:NewWindowScroll('/backoffice/help-system?section=SP','Help','675','575');">Help</a> page.
			</td>
		</tr>
	</table>

<?php } else { ?>

	<form id="fillWindowForm" method="post">
		<table style="width: 100%;">
			<tr>
				<td style="padding-left:20px;">
					<input type="button" class="btn btn-primary btn-sm" value="Set Fill Start Date" onclick="onSetFillWindowStart();" />
					<input type="button" class="btn btn-primary btn-sm" value="Set Fill End Date" onclick="onSetFillWindowEnd();" />
					<input type="button" class="btn btn-primary btn-sm" value="Clear Fill Window" onclick="onClearFillWindow();" />
				</td>
				<td>
					<?php echo $this->templateSetForm['fill_window_html']; ?> <?php echo $this->templateSetForm['set_id_html']; ?>
				</td>
				<td style="padding-right:20px;text-align:right;">
					<input id="saveButton" type="button" class="btn btn-primary btn-sm" value="Save" onclick="saveItems();" />
					<input id="publishButton" type="button" class="btn btn-primary btn-sm" value="Publish" onclick="publishItems();" />
				</td>
			</tr>
			<?php echo $this->templateSetForm['hidden_html']; ?>
		</table>
		<table style="width: 100%;">
			<tr>
				<?php if (isset($this->storeAndMenu['store_html'])) { ?>
					<td style="padding-left:20px;font-weight:bold;">Store <?php echo $this->storeAndMenu['store_html']; ?></td>
				<?php } ?>
				<td style="padding-right:20px;font-weight:bold;text-align:right;">Menu <?php echo $this->storeAndMenu['menus_html']; ?></td>
			</tr>
		</table>
	</form>

	<div><?php include $this->loadTemplate('admin/subtemplate/calendar.tpl.php');?></div>

	<div class="mt-2">
		<?php include $this->loadTemplate('admin/help/help_session_mgr.tpl.php'); ?>
	</div>

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>