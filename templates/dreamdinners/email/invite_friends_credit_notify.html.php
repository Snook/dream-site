<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>main.php?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>main.php?static=share">Referral Dashboard</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">You Earned a Referral Reward</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="10">
<tr>
<td>
<p>Dear <?= $this->referrer_name ?>, </p>
<p>Congratulations, you have earned a <?= $this->award_amount ?> referral reward. This reward was earned when <?= $this->referred_name ?> placed an order on <?=$this->session_date?>. This reward will expire in one year. You can apply this reward at checkout towards your next order. Log in on dreamdinners.com and visit the Share page to view your referral dashboard for details.</p>
<p>We look forward to seeing you soon!</p>

<p>Thank you,<br />
	The Dream Dinners Team</p>
<p>If you have any questions or concerns regarding your referral reward, please contact your local store.</p>
</td>
</tr>
</table>
</body>
</html>
