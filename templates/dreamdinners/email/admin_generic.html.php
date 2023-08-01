<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#afbd21">
		<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinnners.gif" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?php echo HTTPS_BASE; ?>main.php?page=my_account"><span style="color: #FFF;">My Account</span></a><span style="color: #FFF;"> | </span><a href="<?=HTTPS_BASE ?>main.php?page=my_meals"><span style="color: #FFF;">Rate My Meals</span></a></td>
	</tr>
</table>
<table width="650"  border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p><?php echo $this->email_body; ?></p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
		</td>
	</tr>
</table>

</body>
</html>

