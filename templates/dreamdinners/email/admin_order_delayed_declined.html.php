<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="650"  border="0" cellspacing="0" cellpadding="0">
<tr bgcolor="#afbd21">
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinnners.gif" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px">&nbsp;</td>
</tr>
<tr bgcolor="#663300">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Payment Declined or Transaction Error</span></p></td>
</tr>
</table>
<table width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>To <?= $this->sessionInfo['store_name'] ?> Staff, </p>
      <p>The payment for the order for <b><?= $this->customer_name ?></b> which is scheduled for <b><?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?></b> at our <b><?=$this->sessionInfo['store_name']?>
      </b> has been declined or an error occurred during the transaction.</p>
      <p>The reason that the transaction failed or was declined : <b><?=$this->declinedPaymentReason?></b>.</p>
<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
</table>
 <?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>
</body>
</html>