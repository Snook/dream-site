<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_private_party.css'); ?></style>
</head>
<body>

<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Your Private Party has been scheduled</span></p></td>
	</tr>
</table>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td><table width="100%" border="0" cellspacing="0" cellpadding="8">
				<tr>
					<td><p><font size="3">We are so excited you have booked your Private Party at Dream Dinners. We look forward to helping you and your friends assembly easy, homemade meals for your families. </font></p>
					 <p><font size="3">Below are the details for your party and a link to invite your friends.</font></p>
						<p>&nbsp; </p>
						<table role="presentation" width="90%" border="0" align="center" cellpadding="4" cellspacing="3">
							<tr align="left" valign="middle" bgcolor="#959a21">
								<td colspan="2"><strong>Private Party Details</strong></td>
							</tr>
							<tr>
								<td width="48%" align="right" valign="middle" bgcolor="#b9bf33">Date:</td>
								<td width="52%" align="left" valign="middle"><?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], VERBOSE_DATE); ?></td>
							</tr>
							<tr>
								<td align="right" valign="middle" bgcolor="#b9bf33">Time:</td>
								<td align="left" valign="middle"><?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY); ?></td>
							</tr>
							<tr>
								<td align="right" valign="middle" bgcolor="#b9bf33">Invite Code:</td>
								<td align="left" valign="middle"><?php echo $this->session_info['session_password'] ?></td>
							</tr>
							<tr>
								<td align="right" valign="middle" bgcolor="#b9bf33">Store Location:</td>
								<td align="left" valign="middle"><?php echo $this->session_info['store_name'] ?></td>
							</tr>
							<tr>
								<td align="right" valign="top" bgcolor="#b9bf33">Address:</td>
								<td align="left" valign="middle"><?php echo $this->session_info['address_line1'] ?> <?php echo $this->session_info['address_line2'] ?>
									<br />
									<?php echo $this->session_info['city'] ?>
									,
									<?php echo $this->session_info['state_id'] ?>
									<br />
									<?php echo $this->session_info['postal_code'] ?></td>
							</tr>
							<tr>
								<td align="right" valign="top" bgcolor="#b9bf33">Invite Tools:</td>
								<td align="left" valign="middle"><a href="<?php echo HTTPS_BASE ?>my-events?sid=<?php echo $this->session_info['session_id'] ?>">Click here to use our online invitation and sharing tools</a></td>
							</tr>
						</table>
					</td>
				</tr>
			</table></td>
	</tr>
</table>

</body>
</html>