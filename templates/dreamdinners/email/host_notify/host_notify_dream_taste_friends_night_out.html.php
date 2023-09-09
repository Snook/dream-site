<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>my-events">My Events</a> | <a href="<?=HTTPS_BASE ?>my-account">My Account</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Your Friends Night Out is Scheduled!</span></p></td>
	</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>You can now share Dream Dinners with your friends and family. Below are the details of your exclusive event and, most importantly, the link for everyone to use to RSVP.</p>
			<p>Visit the <a href="<?=HTTPS_BASE ?>my-events">My Events page</a> in your Dream Dinners account to get access to our online invite tool and easy to use sharing features.</p>
			<p>If you have any questions or concerns, please contact your store.</p>
			<p>Have a great event!</p>
			<hr />
			<table role="presentation" width="90%"  border="0" align="center" cellpadding="4" cellspacing="3">
				<tr align="left" valign="middle" bgcolor="#5c6670">
					<td colspan="2"><strong><span style="color:#FFF;">Event Details</span></strong></td>
				</tr>
				<tr>
					<td width="48%" align="right" valign="middle" bgcolor="#b9bf33">Date:</td>
					<td width="52%" align="left" valign="middle"><?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], VERBOSE_DATE);  ?></td>
				</tr>
				<tr>
					<td align="right" valign="middle" bgcolor="#b9bf33">Time:</td>
					<td align="left" valign="middle"><?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY);  ?></td>
				</tr>
				<tr>
					<td align="right" valign="middle" bgcolor="#b9bf33">RSVP Link:</td>
					<td align="left" valign="middle"><?=HTTPS_BASE ?>session/<?php echo $this->session_info['id']; ?></td>
				</tr>
				<tr>
					<td align="right" valign="middle" bgcolor="#b9bf33">Invite Code:</td>
					<td align="left" valign="middle"><?= $this->session_info['session_password'] ?></td>
				</tr>
				<tr>
					<td align="right" valign="middle" bgcolor="#b9bf33">Store Location:</td>
					<td align="left" valign="middle"><?=$this->session_info['store_name'] ?></td>
				</tr>
				<tr>
					<td align="right" valign="top" bgcolor="#b9bf33">Address:</td>
					<td align="left" valign="middle"><?= $this->session_info['address_line1'] ?> <?= $this->session_info['address_line2'] ?>
						<br />
						<?= $this->session_info['city'] ?>
						,
						<?= $this->session_info['state_id'] ?>
						<br />
						<?= $this->session_info['postal_code'] ?></td>
				</tr>
				<tr>
					<td align="right" valign="top" bgcolor="#b9bf33">Invite Tools:</td>
					<td align="left" valign="middle"><a href="<?=HTTPS_BASE ?>my-events">Login and start sharing.</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>