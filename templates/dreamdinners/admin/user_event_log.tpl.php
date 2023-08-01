<?php $this->assign('page_title','User Event Log'); ?>
<?php $this->assign('topnav', 'tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Event log for <a href="main.php?page=admin_user_details&amp;id=<?php echo $this->user->id; ?>"><?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?></a></h1>

<form action="" method="GET">
<input type="hidden" name="page" value="admin_user_event_log">
<input type="hidden" name="id" value="<?php echo $this->user->id; ?>">
<select name="log_type" onchange="form.submit();">
<option value="">All</option>
<option value="EMAIL" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'EMAIL' ) echo 'SELECTED';?>>EMAIL</option>
<option value="WARNING" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'WARNING' ) echo 'SELECTED';?>>WARNING</option>
<option value="NOTICE" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'NOTICE' ) echo 'SELECTED';?>>NOTICE</option>
<option value="ERROR" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'ERROR' ) echo 'SELECTED';?>>ERROR</option>
<option value="SECURITY" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'SECURITY' ) echo 'SELECTED';?>>SECURITY</option>
<option value="LOGIN" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'LOGIN' ) echo 'SELECTED';?>>LOGIN</option>
<option value="PHP" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'PHP' ) echo 'SELECTED';?>>PHP</option>
<option value="DEBUG" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'DEBUG' ) echo 'SELECTED';?>>DEBUG</option>
<option value="CCDECLINE" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'CCDECLINE' ) echo 'SELECTED';?>>CCDECLINE</option>
<option value="UNHANDLED" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'UNHANDLED' ) echo 'SELECTED';?>>UNHANDLED</option>
</select>
</form>

<table style="width: 100%;">
<tr>
	<td class="bgcolor_medium header_row">Time (server)</td>
	<td class="bgcolor_medium header_row">Type</td>
	<td class="bgcolor_medium header_row">Desc</td>
</tr>
<?php foreach ($this->events as $event) { ?>
<tr>
	<td class="bgcolor_light" style="white-space: nowrap;"><?php echo CTemplate::dateTimeFormat($event['timestamp_created'], CONCISE)?></td>
	<td class="bgcolor_lighter"><a href="main.php?page=admin_user_event_log&amp;id=<?php echo $this->user->id; ?>&amp;log_type=<?php echo $event['log_type'] ?>"><?php echo $event['log_type'] ?></a></td>
	<td class="bgcolor_lighter"><?php echo $event['description'] ?></td>
</tr>
<?php } ?>
	<?php if (count($this->events) == 100) { ?>
		<tr>
			<td class="bgcolor_light" colspan="3" style="text-align: center; padding: 4px; font-weight: bold;">Limited to 100 results, filter by type to narrow results.</td>
		</tr>
	<?php } ?>
</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>