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
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Referral Credit Expiring</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear <?= $this->szName ?>, </p>
<p>Thank you for being a valuable participant in our Invite Friends referral program. We've noticed that you have referral credit expiring soon. Are you
aware that you may lose your referral credits by not signing up for a session? Make sure you don't miss out on these rewards coming your way with your Invite Friends credits, by placing an order today.</p>
<p>Details about referral credit currently available to can be found in your account information on our web site.
<a href="<?=HTTPS_BASE?>main.php?page=my_credits">Click here</a> to view your account.</p>
<p>We look forward to seeing you again soon.<br />
  <a href="<?=HTTPS_BASE?>main.php?page=session_menu">Sign-up</a> for your next session<br />
<br />
</p></td>
</tr>
</table>

</body>
</html>
