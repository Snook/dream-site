<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE?>?page=my_events&sid=<?=$this->sessionInfo['session_id']?>">Invite Friends</a> | <a href="<?php echo HTTPS_BASE ?>?page=my_meals">Rate My Meals</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Account Updated Confirmation</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear <?= $this->firstname ?>, </p>
      <p>This message is confirmation of recent changes to your account information. To view your account information, go to <a href="<?= HTTPS_BASE?>?page=my_account">My Account</a></p>
      <p>If you have any questions, please email us at <a href="mailto:customerservice@dreamdinners.com">customerservice@dreamdinners.com</a>.</p>
      <p>On behalf of all of us, we&rsquo;d like to thank you for using DreamDinners.com.
        We look forward to seeing you soon.</p>
      <p>Enjoy!</p>

<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
<tr>
  <td><a href="http://blog.dreamdinners.com">Dream Dinners Blog</a> | <a href="<?=HTTPS_BASE?>?page=locations&store_id=<?=$this->store_id?>">Contact your local store</a> |
<a href="<?=HTTPS_BASE?>?static=terms">View Terms and Conditions</a></td>
</tr>
</table>

</body>
</html>