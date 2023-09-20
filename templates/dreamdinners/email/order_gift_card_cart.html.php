<html lang="en">
<head>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px">&nbsp;</td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:18pt; font-weight:bold;">Gift Card Receipt</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Thank you for your order. <strong>This receipt is your &quot;Proof of Purchase&quot;</strong>.</p>

	<?php if ($this->charged_amount >= 100 && (date('Y-m-d') >= '2020-11-27' && date('Y-m-d') <= '2020-12-25' )) { ?>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #000000;"><strong>Our Gift to You </strong></p>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Thank you for gifting Dream Dinners this year. We want to give you something special. When you place your January Standard Order, use the following coupon code to get $10 off.</p>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;"> <strong>Code: Gift4U</strong></p>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Happy Holidays from Dream Dinners.</p>
<?php } ?>
<table role="presentation" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td>
		 <?php include $this->loadTemplate('customer/subtemplate/order_details_gift_card.tpl.php'); ?>
    </td>
        </tr>
  <tr>
    <td> <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">*Gift Card orders that require shipping will take 2-6 business days for processing &amp; shipping<br />
Note: $2 shipping/service fee added to each Traditional Gift Card ordered.</p>
      <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;"><strong>Have questions?</strong> To view the complete Gift Card Policies &amp; Terms online or if you have questions regarding your Gift Card or Customer Service options please visit <a href="<?php echo HTTPS_SERVER; ?>/terms">dreamdinners.com</a></p><p>&nbsp;</p></td>
  </tr>
      </table>
</td>
</tr>
</table>
</body>
</html>