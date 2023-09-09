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
<td><p>Dear <?= $this->firstname ?>
  , <br /><br />
  Your Made For You pick up time is almost here! Please come into the store on <b>
	  <?=$this->dateTimeFormat($this->session_start, NORMAL);?>
	  </b> to <b>
	  <?= date("g:i A", strtotime($this->session_end))?>
	  </b> at our <b>
	  <?=$this->store_name?>
	  </b> location.</p>

<?php include $this->loadTemplate('email/session_reminder/standard_visit.html.php'); ?>

<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
<tr>
  <td><p>Reschedule and Cancellation Policy<br />
    If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee.</p>
   </td>
</tr>
<tr>
  <td><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
<tr>
  <td><a href="http://blog.dreamdinners.com">Dream Dinners Blog</a> | <a href="<?=HTTPS_BASE?>locations/<?=$this->store_id?>">Contact your local store</a> |
<a href="<?=HTTPS_BASE?>terms">View Terms and Conditions</a></td>
</tr>
</table>

</body>
</html>