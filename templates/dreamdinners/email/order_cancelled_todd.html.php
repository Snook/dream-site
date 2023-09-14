<html>
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_taste.css'); ?></style>
</head>
<body>
<table width="604" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left" valign="middle"><span style="font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #666666">Party canceled</span></td>
		<td align="right" valign="middle"><span style="font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #666666">Connect with us: </span><a href="http://www.facebook.com/dreamdinners"><img align="none" alt="Dream Dinners on Facebook" border="0" height="24" src="<?=EMAIL_IMAGES_PATH?>/icon/facebook.png" title="Dream Dinners on Facebook" width="24" /></a> &nbsp;<a href="http://blog.dreamdinners.com"><img align="none" alt="Dream Dinners Blog" border="0" height="24" src="<?=EMAIL_IMAGES_PATH?>/icon/wordpress.png" title="Dream Dinners Blog" width="24" /></a> &nbsp;<a href="http://www.youtube.com/dreamdinnersvideo"><img align="none" alt="Dream Dinners on YouTube" border="0" height="24" src="<?=EMAIL_IMAGES_PATH?>/icon/youtube.png" title="Dream Dinners on YouTube" width="24" /></a></td>
	</tr>
</table>
<table width="604" border="2" cellspacing="0" cellpadding="0" style="border-color:#333;">
	<tr>
		<td><img src="<?=EMAIL_IMAGES_PATH?>/evite/taste_guest_cancelled_header.jpg" alt="Cancelled order header" width="600" height="225">
			<table width="100%"  border="0" cellspacing="0" cellpadding="8">
				<tr>
					<td>
						<p>Dear
							<?= $this->customer_name ?>
						   ,<br />
						   Your registration for a Taste of Dream Dinners party on <b>
								<?=$this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE);?>
							</b> at our <b>
								<?=$this->sessionInfo['store_name']?>
							</b> location has been canceled.</p>
						<p>If you have any questions or concerns regarding


						   this cancellation please contact your party host or the <b>
								<?=$this->sessionInfo['store_name']?>
							</b> location.</p>
						<p>Thank you,<br />
						   - Dream Dinners </p></td>
				</tr>
				<tr>
					<td align="right"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dreamdinners_logo.gif" alt="DreamDinners.com" border="0" width="80" /></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table border="0" cellpadding="4" cellspacing="0" width="604">
	<tr>
		<td colspan="2" align="center" valign="middle">Learn about Dream Dinners: <a href="<?=HTTPS_BASE?>how_it_works">How It Works</a> | <a href="<?=HTTPS_BASE?>shared_stories">Shared Stories &amp; Comments</a></td>
	</tr>
	<tr>
		<td colspan="2" align="left" valign="middle"><font color="#666666" face="Arial, Helvetica, sans-serif" size="1">Copyright &copy; Dream Dinners, Inc.&nbsp; All Rights Reserved.</font></td>
	</tr>
</table>
</body>
</html>