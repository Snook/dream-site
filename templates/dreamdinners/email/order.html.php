<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE?>main.php?page=my_events&sid=<?=$this->sessionInfo['session_id']?>">Invite Friends</a> | <a href="<?php echo HTTPS_BASE ?>main.php?page=my_meals">Rate My Meals</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Confirmation</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td>
		<p><b><?=$this->user->firstname?> <?=$this->user->lastname?>,</b></p>
<?php if ($this->booking_history['bookings_made'] == 0) { ?>
<p>Thank you for placing your first order with Dream Dinners. Our guests say Dream Dinners makes them feel &quot;prepared, empowered, less stressed, and like a Rock Star Chef!&quot; We're happy to help you in the kitchen, so your family can enjoy homemade meals together. Your Dream Dinners order is summarized below. </p>
  <p><b>Things to know:</b></p>
  <ul>
    <li>Experience how to save time and money with our simple cook-at-home meals.</li>
    <li>Please bring a cooler, tote, or box to transport your dinners home.</li>
    <li>Learn from our helpful team members who can answer any questions you have.</li>
    <li>Receive your prepped meals to take home and cook for your family.</li>
    <li>Find out about special offers designed to help you serve easy homemade meals to your family.</li>
    </ul>
<?php } else { ?>
<p>Thank you for placing your order. We're excited about the change Dream Dinners is making in your life. Here's to another month of easy, homemade dinners and savoring more moments with those you love.</p>
  <!--<p>Assembling with a friend is double the fun! Invite your friends to <a href="<?=HTTPS_BASE?>main.php?page=my_events&sid=<?=$this->sessionInfo['session_id']?>">schedule the same session</a> and join you.--> </p>
<?php } ?>
	<?php if ($this->sessionInfo['session_type'] == CSession::DELIVERED) { ?>
<p>Your Dream Dinners order is summarized below.</p>
<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
	<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_delivered_email.tpl.php'); ?>
<?php } else { ?>
<p>We look forward to seeing you at your in-store assembly visit. Your Dream Dinners order is summarized below.</p>
<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>
<?php } ?>
	
	</td>
</tr>
</table>

</body>
</html>