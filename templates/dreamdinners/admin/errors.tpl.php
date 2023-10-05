<?php
$this->assign('page_title','Error Log');
$this->assign('topnav', 'tools');
include $this->loadTemplate('admin/page_header.tpl.php');
?>

	<form action="/backoffice/errors" method="GET">
		<select name="log_type" onchange="form.submit();">
			<option value="">All</option>
			<option value="WARNING" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'WARNING' ) echo 'SELECTED';?>>warnings</option>
			<option value="NOTICE" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'NOTICE' ) echo 'SELECTED';?>>notices</option>
			<option value="ERROR" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'ERROR' ) echo 'SELECTED';?>>errors</option>
			<option value="SECURITY" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'SECURITY' ) echo 'SELECTED';?>>security</option>
			<option value="LOGIN" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'LOGIN' ) echo 'SELECTED';?>>logins</option>
			<option value="PHP" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'PHP' ) echo 'SELECTED';?>>PHP</option>
			<option value="DEBUG" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'DEBUG' ) echo 'SELECTED';?>>debug</option>
			<option value="CCDECLINE" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'CCDECLINE' ) echo 'SELECTED';?>>cc decline</option>
			<option value="UNHANDLED" <?php if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] == 'UNHANDLED' ) echo 'SELECTED';?>>unhandled exception</option>
		</select>
	</form>

	<table style="width:100%;">
		<tr>
			<td class="bgcolor_medium header_row">Time</td>
			<td class="bgcolor_medium header_row">User</td>
			<td class="bgcolor_medium header_row">IP&nbsp;Address</td>
			<td class="bgcolor_medium header_row">Type</td>
			<td class="bgcolor_medium header_row">Hint</td>
			<td class="bgcolor_medium header_row">Desc</td>
		</tr>
		<?php foreach ($this->stuff as $error) { ?>
			<tr class="bgcolor_light">
				<td style="white-space: nowrap;"><?php echo $error['timestamp_created'] ?></td>
				<td><a href="/backoffice/user_details?id=<?php echo $error['user_id'] ?>"><?php echo $error['user_id'] ?></a></td>
				<td><?php echo $error['ip_address'] ?></td>
				<td><a href="/backoffice/errors?log_type=<?php echo $error['log_type'] ?>"><?php echo $error['log_type'] ?></a></td>
				<td><?php echo $error['event_hint'] ?></td>
				<td><?php echo $error['description'] ?></td>
			</tr>
		<?php } ?>
	</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>