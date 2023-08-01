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
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Referral Credit Notification</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td>
<p>Dear <?= $this->referrer_name ?>, </p>
<p>Thank you for being a valuable participant in our Invite Friends referral program. We are pleased to inform you that
you have earned <?= $this->award_amount ?>  referral credit towards your next Dream Dinners session. This credit was earned when
<?= $this->referred_name ?> attended their session on <?=$this->session_date?>. </p>
<p>We look forward to seeing you at your next session.</p>
<p>If you have any questions or concerns regarding your referral credits, please contact us. </p>
<p>Details about referral credit currently available to can be found in your account information on our web site.
<a href="<?=HTTPS_BASE?>main.php?page=my_credits">Click here</a> to view your account.</p>
<p>Thank you,<br />
Dream Dinners<br />
</td>
</tr>
</table>
</body>
</html>
