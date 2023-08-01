<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
</head>
<body>

<link href="<?= CSS_PATH ?>/admin/admin-styles.css" rel="stylesheet" type="text/css" />
<link href="<?= CSS_PATH ?>/admin/admin-styles-reports.css" rel="stylesheet" type="text/css" />
<script language="Javascript1.2">
<!--

function printWindow(){
	browserVersion = parseInt(navigator.appVersion);
if (browserVersion >= 4) window.print()
}

// -->
</script>
<h2>Dream Rewards History for <?=$this->user_name?></h2>
<table width="100%"  border="0" cellspacing="0" cellpadding="0" style="margin-top:4px; padding-right:15px;">
<tr>
<td><div align="right"><font size="2"><a href="javascript:printWindow()">
<img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" />&nbsp;Print This Page</a></font></div>
</td>
</tr>
</table>

<div style='padding: 5px 15px 5px 15px; '>

<table id="itemsTbl" style="width:100%px;color:#000;" margin="0">
<tr class="form_subtitle_cell">
<td class="ME_header_zebra_odd" style="padding: 2px;"><b>Event</b></td>
<td class="ME_header_zebra_even" style="padding: 2px;"><b>Original Status</b></td>
<td class="ME_header_zebra_odd" style="padding: 2px;"><b>Current Status</b></td>
<td class="ME_header_zebra_even" style="padding: 2px;"><b>Original Level</b></td>
<td class="ME_header_zebra_odd" style="padding: 2px;"><b>Current Level</b></td>
<td class="ME_header_zebra_even" style="padding: 2px; max-width:300px;"><b>Description</b></td>
<td class="ME_header_zebra_odd" style="padding: 2px;"><b>Date Time</b></td>
<td class="ME_header_zebra_even" style="padding: 2px;"><b>Admin User</b></td></tr>
<?php
 if (empty($this->history)) {

 	echo '<tr><td colspan="8"><center><i><b>There is no history for this user. Note: Only the history for the current store is shown.</b></i></center></td></tr>';

 }
else
{

	foreach ($this->history as $id => $event){ ?>
<tr>
<td class="ME_header_zebra_odd" style="padding: 2px;"><?=$event['event']?></td>
<td class="ME_header_zebra_even" style="padding: 2px;"><?=$event['org_status']?></td>
<td class="ME_header_zebra_odd" style="padding: 2px;"><?=$event['cur_status']?></td>
<td class="ME_header_zebra_even" style="padding: 2px;"><?=$event['org_level']?></td>
<td class="ME_header_zebra_odd" style="padding: 2px;"><?=$event['cur_level']?></td>
<td class="ME_header_zebra_even" style="padding: 2px;  max-width:300px; overflow:hidden;"><?=$event['description']?></td>
<td class="ME_header_zebra_odd" style="padding: 2px;"><?=$event['datetime']?></td>
<td class="ME_header_zebra_even" style="padding: 2px;"><?=$event['admin']?></td>
</tr>
<?php } }?>
</table>
</div>

<form name="windowClose" action="">
<table width="100%"  border="0" cellspacing="0" cellpadding="0" style="margin-top:4px; padding-right:15px;">
<tr>
<td><div align="right"><font size="2"><a href="javascript:printWindow()">
<img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" />&nbsp;Print This Page</a></font></div>
</td>
</tr>
<tr>
	<td>
		<div align="right" style="padding-top:8px; padding-bottom:10px;"><input name="image" type="image" title=" Close Window! " onclick="window.close();"
 			src="<?php echo ADMIN_IMAGES_PATH; ?>/btns/button_close_window_web.gif" alt="Close Window!" border="0" /></div>
</td>
</tr>
</table>



</form>

</body>
</html>