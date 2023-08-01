<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>main.php?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>main.php?page=locations">Locations</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Canceled</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td>
<p>Dear <?= $this->customer_name ?>,<br />
Your order for the Dream Dinners event on <b> <?=$this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE);?></b> at our <b><?=$this->sessionInfo['store_name']?></b> location has been canceled.</p>
<p>If you have any questions or concerns regarding this cancellation please contact the event host or the <b><?=$this->sessionInfo['store_name']?></b> location.</p>
<p>Thank you,<br />
  - Dream Dinners </p>
  </td>
</tr>
</table>
</body>
</html>