<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>?page=locations">Locations</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Edited Order Confirmation</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear
  <?= $this->customer_name ?>
,<br />
  Thank you for your order. Below is your order summary and payment information.  If you have any
  questions regarding this or any other Dream Dinners information please
  contact the store by using the information below.</p>
<p>It was great meeting you and we hope to see you again soon,<br />
  - Dream Dinners</td>
</tr>
</table>
 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email_dream_taste.tpl.php'); ?>
</body>
</html>