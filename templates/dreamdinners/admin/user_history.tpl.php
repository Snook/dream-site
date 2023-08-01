<?php $this->assign('page_title','Guest History'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h3>User History for <a href="main.php?page=admin_user_details&amp;id=<?php echo $this->user_info->id ?>"><?php echo $this->user_info->firstname ?> <?php echo $this->user_info->lastname ?></a></h3>

<table style="width:100%;margin-top:20px;">
<tr>
	<td class="bgcolor_dark header_row">Event</td>
	<td class="bgcolor_dark header_row">IP Address</td>
	<td class="bgcolor_dark header_row">Time Created (server time)</td>
</tr>
<?php while ($this->user_history->fetch()) { ?>
<tr>
	<td class="bgcolor_light" style="white-space:nowrap;padding:6px;font-weight:bold;"><?php echo ucwords(strtolower(str_replace("_", " ", CUserHistory::$eventDescMap[$this->user_history->event_id]))); ?></td>
	<td class="bgcolor_light" style="white-space:nowrap;text-align:center;padding:6px;"><?php echo $this->user_history->ip_address; ?></td>
	<td class="bgcolor_light" style="white-space:nowrap;text-align:right;padding:6px;"><?php echo CTemplate::dateTimeFormat($this->user_history->timestamp_created, NORMAL); ?></td>
</tr>
<tr>
	<td colspan="3" class="bgcolor_lighter" style="padding:6px;"><?php echo $this->user_history->description; ?></td>
</tr>
<?php } ?>
</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>