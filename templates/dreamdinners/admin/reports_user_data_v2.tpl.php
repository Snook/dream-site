<?php
$PAGETITLE = $this->page_title;
$HIDDENPAGENAME = "admin_reports_user_data_v2";
$this->setScript('head', SCRIPT_PATH . '/admin/reports_user_data.min.js');
$this->setScriptVar('isFranchiseAccess = ' . (CUser::getCurrentUser()->isFranchiseAccess() ? 'true' : 'false') . ';');
$this->setOnload('reports_user_data_init();');
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>

	<div id="query_form">
		<form method="post" action="/backoffice/reports_user_data_v2">
			<table><tr><td width="100%" style="padding-left: 5px;" colspan="3"><b>

							<?php if (isset($this->form['store_html'])) { ?>
								Select Store: <?=$this->form['store_html'] ?>
							<?php  } ?>
						</b></td></tr>
				<tr>
					<td>
						<div style="max-width:350px; border:thin solid black; margin-left:25px; margin-top:10px; padding:25px; padding-top:10px; background-color:#BEB7AE;">

							<h2>Guest Type</h2>
							<input type="radio" name="guest_type" id="guest_type_all" value="all" checked="checked" />&nbsp;<label for="guest_type_all">All</label> <br />
							<input type="radio" name="guest_type" id="guest_type_has_future_sessions" value="has_future_sessions" />&nbsp;<label for="guest_type_has_future_sessions">Has Scheduled Sessions</label> <br />
							<input type="radio" name="guest_type" id="guest_type_never_attended" value="never_attended" />&nbsp;<label for="guest_type_never_attended">Never Attended</label> <br />
							<input type="radio" name="guest_type" id="guest_type_inactive" value="inactive" />&nbsp;<label for="guest_type_inactive">Inactive Account Status</label> <br />
						</div>
						<div style="max-width:350px; border:thin solid black; margin-left:25px; margin-top:10px; padding:25px; padding-top:10px; background-color:#BEB7AE;">
							<h2>Inactivity</h2>
							<input type="radio" name="guest_type" id="guest_type_45_day_lost_guest" value="45_day_lost_guest" />&nbsp;<label for="guest_type_45_day_lost_guest">45 Day Lost Guest List</label> <br />
						</div>

					</td>
					<td>
						<div style="max-width:350px; border:thin solid black; margin-left:25px; margin-top:10px; padding:25px; padding-top:10px; float:left; background-color:#BEB7AE;">

							<h2>Account Details</h2>
							<div><input type="checkbox" name="dfa_All" id="dfa_All">&nbsp;<label for="dfa_All">All</label></div>

							<div style="margin-left:10px;"><input type="checkbox" name="df_ACTIVE" id="df_ACTIVE" />&nbsp;<label for="df_ACTIVE">Active</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_ACCT_CREATE_DATE" id="df_ACCT_CREATE_DATE" />&nbsp;<label for="df_ACCT_CREATE_DATE">Account Creation Date</label></div>

							<?php if ($this->supportsMemberships) { ?>
								<div style="margin-left:10px;"><input type="checkbox" name="df_MEMBERSHIP_STATUS" id="df_MEMBERSHIP_STATUS" />&nbsp;<label for="df_MEMBERSHIP_STATUS">Membership Status</label></div>
							<?php }?>

							<?php if ($this->supportsPlatePoints) { ?>
								<div style="margin-left:10px;"><input type="checkbox" name="df_DR_STATUS" id="df_DR_STATUS" />&nbsp;<label for="df_DR_STATUS">PLATEPOINTS Status</label></div>
								<div style="margin-left:10px;"><input type="checkbox" name="df_DR_LEVEL" id="df_DR_LEVEL" />&nbsp;<label for="df_DR_LEVEL">PLATEPOINTS</label></div>
							<?php } else { ?>
								<div style="margin-left:10px;"><input type="checkbox" name="df_DR_STATUS" id="df_DR_STATUS" />&nbsp;<label for="df_DR_STATUS">DR Status</label></div>
								<div style="margin-left:10px;"><input type="checkbox" name="df_DR_LEVEL" id="df_DR_LEVEL" />&nbsp;<label for="df_DR_LEVEL">DR Level</label></div>
							<?php }?>
							<div style="margin-left:10px;"><input type="checkbox" name="df_EMAIL" id="df_EMAIL" />&nbsp;<label for="df_EMAIL">Email</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_PHONE" id="df_PHONE" />&nbsp;<label for="df_PHONE">Phone</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_ADDRESS" id="df_ADDRESS" />&nbsp;<label for="df_ADDRESS">Address</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_LAST_SESSION_ATTENDED" id="df_LAST_SESSION_ATTENDED" />&nbsp;<label for="df_LAST_SESSION_ATTENDED">Last Session Attended</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_DAYS_INACTIVE" id="df_DAYS_INACTIVE" />&nbsp;<label for="df_DAYS_INACTIVE">Days Inactive</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_NEXT_SESSION" id="df_NEXT_SESSION" />&nbsp;<label for="df_NEXT_SESSION">Next Session</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_PROFILE_DATA" id="df_PROFILE_DATA" />&nbsp;<label for="df_PROFILE_DATA">Profile Data</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_INSTRUCTIONS" id="df_INSTRUCTIONS" />&nbsp;<label for="df_INSTRUCTIONS">Special Instructions for Upcoming Sessions</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_CUSTOMIZATIONS" id="df_CUSTOMIZATIONS" />&nbsp;<label for="df_CUSTOMIZATIONS">Meal Customizations for Upcoming Sessions</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_USER_ACCOUNT_NOTE" id="df_USER_ACCOUNT_NOTE" />&nbsp;<label for="df_USER_ACCOUNT_NOTE">Account Notes</label></div>
							<div style="margin-left:10px;"><input type="checkbox" name="df_USER_SHARE_URL" id="df_USER_SHARE_URL" />&nbsp;<label for="df_USER_SHARE_URL">Share URL</label></div>

						</div>

					</td>

					<td>
						<div style="width:150px; padding-top:15px; text-align:right; float:left;"><input style="height:120px;" type="submit" name="submit_report" value="Run Report" /> </div>

					</td>
				</tr>

			</table>
		</form>
	</div>
	<div id="results_mess">
		<?php if (isset($this->no_results) && $this->no_results) { ?>
			<table><tr><td width="610" style="padding-left: 5px;"><b>Sorry, could not generate a report for this date. There were no results for this query.</b></td></tr></table>
		<?php } ?>
	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>