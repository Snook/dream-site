<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
<tr bgcolor="#afbd21">
<td width="60%" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinnners.gif" alt="Dream Dinners" width="325" height="75"></td>
<td width="40%" align="right" style="padding: 5px"><a href="<?=HTTPS_SERVER?>/my-events?sid=<?=$this->session_id?>"><span style="color: #FFF;">Invite Friends</span></a><span style="color: #FFF;"> | </span><a href="<?=HTTPS_BASE ?>my-meals"><span style="color: #FFF;">Rate My Meals</span></a></td>
</tr>
<tr bgcolor="#663300">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Session Reminder</span></p></td>
</tr>
</table>
<table width="100%"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td>
<p>Dear <?= $this->firstname ?>, <br /><br />
  Thank you for placing your order with Dream Dinners.  You're on your way to enjoying delicious homemade meals and simplifying your life.  We're looking forward to seeing you at your next session  <b>
  <?=$this->dateTimeFormat($this->session_start, NORMAL);?>
  </b> at our <b>
  <?=$this->store_name?>
  </b> location.</p>

<?php
if ($this->bookings_made == 0)
{
	include $this->loadTemplate('email/session_reminder/first_visit.html.php');
}
else if ($this->bookings_made == 1)
{
	include $this->loadTemplate('email/session_reminder/second_visit.html.php');
}
else
{
	include $this->loadTemplate('email/session_reminder/standard_visit.html.php');
}
?>

</td>
</tr>
<!--<tr>
  <td><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
        <p style="font-size:16px;"><strong>Special Mother's Day Offer</strong></p>
        <p style="font-size:16px;">Mother's day is just around the corner, send your mom 3 easy homemade meals. Save 10% off a ShareCrate, use promo code: <strong>LovingMom17</strong> at checkout. Visit <a href="https://lovingwithfood.com/">LovingWithFood.com</a> to order.</p>
        <hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>-->
<tr>
  <td><a href="http://blog.dreamdinners.com">Dream Dinners Blog</a> | <a href="<?=HTTPS_BASE?>/locations/<?=$this->store_id?>">Contact your local store</a> |
<a href="<?=HTTPS_BASE?>terms">View Terms and Conditions</a></td>
</tr>
</table>
</body>
</html>