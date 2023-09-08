<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_color_325x75.gif" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>?page=locations">Locations</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Event Session Reminder</span></p></td>
</tr>
</table>
<table width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td>
<p>Dear <?= $this->firstname ?>,<br />
Thank you for registering to attend a Dream Dinners event, as a reminder it is scheduled for <strong><?=$this->dateTimeFormat($this->session_start, NORMAL);?></strong> at our <strong><?=$this->store_name?></strong> location. Be ready to have a great time and meet new friends.</p>
<p><strong>Don't forget to bring a cooler or box to transport your dinners home.</strong></p>
<p>If you have any questions regarding this party please <a href="<?=HTTPS_BASE?>?page=locations">contact the store hosting this party.</a></p>
<p>We look forward to seeing you soon,<br />
  - Dream Dinners </p>
  </td>
</tr>
</table>
</body>
</html>