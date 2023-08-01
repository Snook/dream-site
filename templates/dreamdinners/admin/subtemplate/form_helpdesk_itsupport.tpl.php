<div>

	<form id="form_helpdesk" enctype="multipart/form-data">

		<input type="hidden" name="reporting_page"
			value="<?php echo HTTPS_SERVER . $this->request_url; ?>" /> <input
			type="hidden" name="browser"
			value="<?php echo $_SERVER["HTTP_USER_AGENT"]; ?>" /> <input
			type="hidden" name="store_id"
			value="<?php echo CBrowserSession::getCurrentStore(); ?>" /> <input
			type="hidden" name="user_id"
			value="<?php echo CUser::getCurrentUser()->id; ?>" />

<table style="width: 100%;">
<tr>
				<td style="text-align: right;" width="15%">First Name:</td>
				<td nowrap="nowrap" style="width: 35%;"><input type="text"
					maxlength="120" style="width: 100%; background-color:#c0c0c0;" name="first_name"
					value="<?php echo CUser::getCurrentUser()->firstname; ?>" readonly="readonly" />
				<td style="text-align: right; width: 15%;">Last Name:</td>
				<td style="width: 35%;" nowrap="nowrap"><input type="text"
					maxlength="120" style="width: 100%; background-color:#c0c0c0;" name="last_name"
					value="<?php echo CUser::getCurrentUser()->lastname; ?>"  readonly="readonly"/>
</tr>
<tr>
	<td style="text-align: right;">Email:</td>
				<td nowrap="nowrap"><input type="email" maxlength="120"
					name="email_address" style="width: 100%; background-color:#c0c0c0;"
					value="<?php echo CUser::getCurrentUser()->primary_email; ?>"  readonly="readonly"/></td>
	<td style="text-align: right;">Phone:</td>
				<td nowrap="nowrap"><input type="tel" maxlength="120"
					name="phone_number" style="width: 100%"
					value="<?php echo CUser::getCurrentUser()->telephone_1; ?>" /><span
					style="color: red">*</span></td>
</tr>
			<tr><td colspan="4" style="text-align:center; font-size:8pt;"><i>Note: The requestor will be the currently signed in user. You may also provide a CC email address below. </i></td></tr>
		</table>
		<table style="width: 100%;">
			<tr>
				<td width="30%" colspan="1" style="text-align: right;">Additional Email Contact:</td>
				<td  colspan="3" nowrap="nowrap"><input type="email" maxlength="120"
					name="alt_contact" style="width: 100%;" value="" /></td>
			</tr>
			<tr>
				<td colspan="1" style="text-align: right;">Preferred Contact Method:</td>
				<td colspan="3"><select name="preferred_contact_type"
					style="margin: 0px;">
						<option value="">please select</option>
						<option value="Email" selected="selected">Email</option>
						<option value="Call">Call</option>
						<option value="Feedback - No Response Needed">No Response Needed</option>
				</select><span style="color: red">*</span></td>
			</tr>
		</table>

		<hr />

		<table>
			<tr>
				<td style="text-align: right;">Type:</td>
				<td colspan="2"><select name="ticket_type" style="margin: 0px;">
						<option value="">please select</option>
						<option value="LovingWithFood.com">LovingWithFood.com</option>
						<option value="Customer Service">Customer Service</option>
						<option value="Convention">Convention</option>
						<option value="Dream Connect">Dream Connect</option>
						<option value="Email">Email</option>
						<option value="FAdmin">BackOffice</option>
						<option value="Finance">Finance</option>
						<option value="Equipment Support">Equipment Support</option>
						<option value="Feature Request">Feature Request</option>
						<option value="Feedback">Feedback</option>
						<option value="Food Related (Not Sysco)">Food Related (Not Sysco)</option>
						<option value="Food Related (Sysco Support)">Food Related (Sysco Support)</option>
						<option value="Franchise Inquiry">Franchise Inquiry</option>
						<option value="Gift Card">Gift Card</option>
						<option value="Marketing">Marketing</option>
						<option value="Monthly Packet">Monthly Packet</option>
						<option value="PCI">PCI</option>
						<option value="PLATEPOINTS">PLATEPOINTS</option>
						<option value="Report a Bug ">Report a Bug</option>
						<option value="Sales">Sales</option>
						<option value="Service Outage">Service Outage</option>
						<option value="Store Operations">Store Operations</option>						
						<option value="Webinars">Webinars</option>
						<option value="Other">Other</option>
						<option value="Home Office (Admin Only)">Home Office (Admin Only)</option>
				</select><span style="color: red">*</span></td>
				<td style="text-align: center;">Is This Urgent?: <input
					type="checkbox" name="is_urgent" />
				</td>
			</tr>



<tr>
	<td style="text-align: right;">Summary:</td>
				<td nowrap="nowrap" colspan="3"><input type="text" maxlength="120"
					name="subject" style="height: 27px; width: 100%" /><span
					style="color: red">*</span></td>
</tr>
<tr>
	<td style="text-align: right; vertical-align: top;">
		<div>Description/Request:</div>

					<div style="font-size: .75em; text-align: left; padding-top: 12px;">Please
						include as necessary</div>
		<ul style="font-size: .75em; text-align: left; margin-top: 0px;">
			<li style="margin-left: -24px;">Steps to reproduce issues</li>
			<li style="margin-left: -24px;">Guest IDs or Emails</li>
			<li style="margin-left: -24px;">Order IDs</li>
			<li style="margin-left: -24px;">Session IDs or Times</li>
		</ul>
	</td>
				<td colspan="3" style="vertical-align: top;"><textarea
						name="description" maxlength="30000"
						style="height: 120px; width: 100%;"></textarea><span
					style="color: red">*</span></td>
</tr>
<tr>
	<td style="text-align: right;">
		<div>Problem URL:</div>
		<div style="font-size: .75em;">Please change if not this page</div>
	</td>
				<td colspan="3" style="vertical-align: top;"><input type="text"
					maxlength="255" name="problem_url"
					style="width: 100%; height: 27px;"
					value="<?php echo HTTPS_SERVER . $this->request_url; ?>" /></td>
</tr>
<tr>
				<td style="text-align: right; vertical-align: top;">Browser:</td>
				<td colspan="3"><?php echo $_SERVER["HTTP_USER_AGENT"]; ?></td>
			</tr>
			<tr>
				<td style="text-align: right;"><!-- Sysco Account #:--></td>
					<td colspan="3">
					<table>
						<tr>
							<td><!--
						 	<input style="width: 100%" type="text" maxlength="40"
								name="sysco_account_number" /> 
								</td>
							<td style="text-align: right;">&nbsp;Sysco Account Exec:</td>
							<td><input style="width: 100%" type="text" maxlength="80" name="sysco_account_exec" /></td>
						</tr>
					</table>
	--></td>
</tr>
<!--  
<tr>
				<td style="text-align: right; vertical-align: top;" colspan="1">Attachment</td>
				<td colspan="1">
					<table style="width: 50%;">
						<tbody>
							<tr>
								<td valign="top"><input id="email_attachment"
									name="email_attachment" type="file"><br /> 8MB .pdf .doc .docx
									.xls .xlsx .jpg .png .gif</td>
							</tr>
						</tbody>
					</table>
				</td>
				<td><span style="color: red">* = Required</span></td>
</tr>
-->
</table>

</form>

</div>
