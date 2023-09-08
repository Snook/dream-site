<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_SERVER?>/?page=my_events&sid=<?=$this->sessionInfo['session_id']?>">Invite Friends</a> | <a href="<?=HTTPS_BASE ?>?page=my_meals">Rate My Meals</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Advanced Order - Session Balance Due</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear <?=$this->customer_name?>,</p>
<p>Our records indicate that you placed and Advanced Order from a Dream Dinners location.</p>
      <p>Your session is: <?=$this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE)?></p>
      <p>At the following store: <?=$this->sessionInfo['store_name']; ?></p>
      <p>However, in order to keep your order and your spot reserved, you need to pay for your
		session in full at this time.  If your session is not paid in full, within 48 hours, your spot
		and order will be released.</p>
		<p>To pay now, go to: <a href="<?= HTTPS_BASE?>?page=order_details&order=<?=$this->orderInfo['id']?>">
		<b><?= $this->orderInfo['order_confirmation'] ?></b></a></p>
		<p>Once you've paid, you will be sent a reminder email of your session,
		3 days prior to your session starting.</p>

		Sincerely,<br />
		Dream Dinners
<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
<tr>
  <td><a href="http://blog.dreamdinners.com">Dream Dinners Blog</a> | <a href="<?=HTTPS_BASE?>?page=locations&store_id=<?=$this->store_id?>">Contact your local store</a> |
<a href="<?=HTTPS_BASE?>?static=terms">View Terms and Conditions</a></td>
</tr>
</table>
</body>
</html>