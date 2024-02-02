<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
<tr bgcolor="#fff">
<td align="center" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_logotype_darkgrey_300x28.png" alt="Dream Dinners" width="300" height="28"></td>
</tr>
<tr bgcolor="#5c6670">
  <td style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Home Delivery Order Reminder</span></p></td>
</tr>
</table>
<table width="100%"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td>
<p>Dear <?= $this->firstname ?>, <br /><br />
  It's almost time to pick up your meals. We're looking forward to seeing you at the community pick up location during your pick up window on <b><?=$this->dateTimeFormat($this->session_start, NORMAL);?> to <?= date("g:i A", strtotime($this->session_end))?></b>.</p>

<p>Community Pick Up Location:<br>
<?php echo $this->sessionInfo['session_title']; ?><br>
<?php echo $this->sessionInfo['session_remote_location']->address_line1 . ((!empty($this->sessionInfo['session_remote_location']->address_line2)) ? ' ' . $this->sessionInfo['session_remote_location']->address_line2 : '') . ', ' . $this->sessionInfo['session_remote_location']->city . ', ' . $this->sessionInfo['session_remote_location']->state_id . ' ' .$this->sessionInfo['session_remote_location']->postal_code; ?></p>

	<p>If you have questions about your order please contact the store.</p>
<p><b>What to Expect:</b></p>
<ol>
<li>We will have your dinners ready when you arrive. Bring your cooler to take them home.</li>
<li>Add a few of our delicious sides, breakfast and sweets to your order by <a href="<?=HTTPS_BASE?>freezer">completing your request today.</a></li>
<li><a href="<?=HTTPS_BASE?>session-menu">Place your next order to reserve your preferred community pick up spot.</a></li>
</ol>
		
<p>We look forward to seeing you soon.</p>
<p>Enjoy!<br/>
 Dream Dinners</p>
</td>
</tr>
<tr>
  <td>
	  <hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"><br/>
	  <p><b>Not feeling well?</b><br/>
		If you are experiencing a fever or other illness symptoms within 24 hours of your pick up or assembly session, please call to reschedule your visit.</p>

		<p><b>Reschedule and Cancelation Policy</b><br/>
		If you need to reschedule or cancel your order, contact us six days prior to your order date. Cancelations with six or more days’ notice will receive a full refund. Cancelations within five or fewer days’ notice will be subject to a 25% restocking fee.</p>
	 	
	  	<p><a href="http://blog.dreamdinners.com">Dream Dinners Blog</a> | <a href="<?=HTTPS_BASE?>/locations/<?=$this->store_id?>">Contact your local store</a> | <a href="<?=HTTPS_BASE?>terms">View Terms and Conditions</a></p>
	</td>
</tr>
</table>
</body>
</html>