<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?php echo HTTPS_SERVER?>/my-events?sid=<?php echo $this->sessionInfo['session_id']?>">Invite Friends</a> | <a href="<?php echo HTTPS_BASE ?>my-meals">Rate My Meals</a></td>
</tr>
<tr bgcolor="#5c6670">
 <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Payment Declined</span></p></td>
</tr>
</table>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear <?php echo $this->customer_name ?>, </p>
   <p>Your payment for order scheduled for <b><?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?></b> at our <b><?php echo $this->sessionInfo['store_name']?>
   </b> has been declined.</p>
   <p>The reason we are told that the payment was declined is: <?php echo $this->declinedPaymentReason?></p>
   <p><b>Please contact us to update your payment information.</b> We are happy to help solve this over the phone or in the store. The details of the order are listed below.</p>
   <p>Thank you</p>
<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
</table>
 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>

</body>
</html>