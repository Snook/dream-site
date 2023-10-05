<div id="zohoWebToLead" style="display:none;">

<form action="https://support.zoho.com/support/WebToCase" method="POST">
<input type="hidden" name="xnQsjsdp" value="FkiRnZejYcRthYo*kRl79w$$" />
<input type="hidden" name="xmIwtLD" value="aaiX9X*-DLhfUICwMz7mTIe-o4Q-M6zK" />
<input type="hidden" name="actionType" value="Q2FzZXM=" />
<input type="hidden" name="Department" value="0f33ccb4ba7c8de75710439f27d48b29f3148ec5bcd90913" />
<input type="hidden" name="returnURL" value="<?php echo HTTPS_SERVER; ?>/?<?php echo $_SERVER['QUERY_STRING']; ?>" />
<input type="hidden" name="Browser" value="<?php echo $_SERVER["HTTP_USER_AGENT"]; ?>" />
<input type="hidden" name="Reporting Page" value="?<?php echo $_SERVER['QUERY_STRING']; ?>" />
<input type="hidden" name="Store ID" value="<?php echo CBrowserSession::getCurrentStore(); ?>" />
<input type="hidden" name="User ID" value="<?php echo CUser::getCurrentUser()->id; ?>" />

<table style="width: 100%;">

<tr>
	<td style="text-align: right;" width="25%">First Name:</td>
	<td><input type="text" maxlength="120" name="First Name" value="<?php echo CUser::getCurrentUser()->firstname; ?>" /></td>
</tr>
<tr>
	<td style="text-align: right;">Last Name:</td>
	<td><input type="text" maxlength="120" name="Contact Name" value="<?php echo CUser::getCurrentUser()->lastname; ?>" /></td>
</tr>
<tr>
	<td style="text-align: right;">Email:</td>
	<td><input type="text" maxlength="120" name="Email" value="<?php echo CUser::getCurrentUser()->primary_email; ?>" /></td>
</tr>
<tr>
	<td style="text-align: right;">Phone:</td>
	<td><input type="text" maxlength="120" name="Phone" value="<?php echo CUser::getCurrentUser()->telephone_1; ?>" /></td>
</tr>
<tr>
	<td style="text-align: right;">Subject:</td>
	<td><input type="text" maxlength="120" name="Subject" style="width: 404px;" /></td>
</tr>
<tr>
	<td style="text-align: right; vertical-align: top;">Description:</td>
	<td><textarea name="Description" maxlength="30000" style="width: 404px; height: 120px;"></textarea></td>
</tr>
<tr>
	<td style="text-align: right;">Problem URL:</td>
	<td><input type="text" maxlength="255" name="Problem URL" style="width: 404px;" /></td>
</tr>
<!--
<tr>
	<td style="text-align: right;">Status:</td>
	<td>
		<select name="Status">
			<option value="Open">Open</option>
			<option value="On Hold">On Hold</option>
			<option value="Escalated">Escalated</option>
			<option value="Closed">Closed</option>
		</select>
	</td>
</tr>
-->
<tr>
	<td style="text-align: right;">Priority:</td>
	<td>
		<select name="Priority">
			<option value="-None-">-None-</option>
			<option value="High">High</option>
			<option value="Medium">Medium</option>
			<option value="Low">Low</option>
		</select>
	</td>
</tr>
<tr>
	<td style="text-align: right; vertical-align: top;">Browser:</td>
	<td><?php echo $_SERVER["HTTP_USER_AGENT"]; ?></td>
</tr>
<tr>
	<td colspan="2" style="text-align: center;">
		<div>
			<input type="submit" name="save" value="Submit" class="btn btn-primary btn-sm" />
			<input type="reset" name="reset" value="Reset" class="btn btn-primary btn-sm" />
		</div>
		<div>Submitting will refresh the page</div>
	</td>
</tr>
</table>

</form>
</div>