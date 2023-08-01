<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>main.php?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>main.php?page=my_meals">Rate My Meals</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Password Reset</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear <?= $this->firstname ?>, </p>
      <p>To reset your Dream Dinners password please click the link below.</p>
      <p><a href="<?= $this->password_link ?>">Reset Password</a></p>
      <p>You can also reset your password by copying and pasting the following link into a browser.</p>
      <p>Note that this link will expire 1 hour after it is sent.</p>
      <p><?= $this->password_link ?></p>
      <p>If you did not request this password reset you can ignore this message. If you think someone
		has accessed your account without your permission you can notify Dream Dinners support at
			<a href="mailto:support@dreamdinners.com">support@dreamdinners.com</a>.</p>

      <p>Dream Dinners</p>
</td>
</tr>
</table>

</body>
</html>
