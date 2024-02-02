<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>my-account">My Account</a> | <a href="<?=HTTPS_BASE ?>my-meals">Rate My Meals</a></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Rescheduled</span></p></td>
</tr>
</table>
<table width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear <?= $this->customer_name ?>, </p>
      <p>Your order scheduled for delivery on <b><?=$this->dateTimeFormat($this->origSessionInfo['session_start'], NORMAL);?></b> has been rescheduled.</p>
      <p>The new delivery window starts at <b><?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?></b>.</p>
      <p>If you have any questions or concerns regarding this order please contact us. The details of your order are listed below.</p>
      <p>Thank you</p>
<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
	<p><i>*Delivery fees are non-refundable once charged to your order. If the driver arrives and no one is available to accept the delivery, the driver will leave your order at your front door and take a photo before they leave. If the driver cannot leave your order for any reason, they may have to return your order back to the store. This will incur an additional return delivery fee and a possible restocking fee for your order. <a href="<?php echo HTTPS_SERVER; ?>/terms">View the full Terms and Conditions</a>.</i></p>
<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
</table>
 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>

</body>
</html>