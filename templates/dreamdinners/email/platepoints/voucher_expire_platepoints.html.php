<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table width="550"  border="0" cellspacing="0" cellpadding="0" class="border">
<tr>
<td colspan="2"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/platepoints-generic-header.png" alt="PLATEPOINTS" border="0" width="550" height="120" /></td>
</tr>
<tr>
<td style="padding:10px 10px 5px 10px;"><p>Hello <?php echo $this->firstname;?>,<br />
  Don't forget! You have a voucher* that is expiring soon. Don't miss out on the chance to bring it in to your next session and redeem it.</p>
  <p>Sincerely,<br />
Dream Dinners
</p></td>
</tr>
</table>
<table width="550"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td align="left"><img src="<?=EMAIL_IMAGES_PATH?>/email/platepoints/platepoints-footer-grey.png" width="550" height="50"></td>
</tr>
<tr>
  <td align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>?page=session_menu">Order</a> | <a href="<?=HTTPS_BASE ?>?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>?page=my_platepoints">My PLATEPOINTS</a></td>
  </tr>
<tr>
  <td align="leftt" style="padding: 5px"><i>*Voucher sent via email approx 4 months ago as part of your badge level up notification. Voucher expires 45 days from sent date of this email. Contact your local store for questions or concerns.</i></td>
</tr>
</table>
</body>
</html>