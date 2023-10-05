<html lang="english">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?= EMAIL_IMAGES_PATH ?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px">&nbsp;</td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px">
			<p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Food Testing Recipe Available</span></p>
		</td>
	</tr>
</table>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>A Food Testing recipe has been made available to your store, please visit the <a href="<?php echo HTTPS_BASE; ?>backoffice/food-testing-survey?recipe=<?php echo $this->survey_id; ?>">Food Testing Manager</a> to retrieve your documents.</p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
		</td>
	</tr>
</table>

</body>
</html>