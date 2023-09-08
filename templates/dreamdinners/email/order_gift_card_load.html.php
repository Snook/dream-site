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
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Dream Dinners Gift Card Receipt</span></p></td>
</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">Thank you for your order. <strong>This receipt is your &quot;Proof of Purchase&quot;</strong>.</p>

	<?php if ($this->charged_amount >= 125 && (date('Y-m-d') >= '2021-12-01' && date('Y-m-d') <= '2021-12-25' )) { ?>
		<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #000000;"><strong>Our Gift to You </strong></p>
		<p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Thank you for gifting Dream Dinners this year. We want to give you something special. Use the coupon code below to get $10 off your next standard order between January and March 2022.</p>
		<p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;"> <strong>Code: Gift22</strong></p>
		<p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Happy Holidays from Dream Dinners.</p>
		<hr>
		<p style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;"><em>*$125 Dream Dinners gift card purchase must be made in one transaction. $10 off coupon code will be sent in your order confirmation email. Only one coupon code can be applied per an 2022 order. Coupon code is valid January – March 2022. Coupon code not eligible with other coupons or offers. Coupon not valid for use in conjunction with a Meal Prep Starter Pack or Special Event session type. No cash redemption permitted. Available for use at participating locations.<em></p>
	<?php } ?>

  <!--start gift cards purchased table-->
      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1px solid #afbd21; background-color: #F8F7F5;">
        <tr >
          <td>
<table role="presentation" width="100%" align="center" cellpadding="2">
      <tr style="background-color: #afbd21 ">
        <td colspan="2"  style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;"><strong>Gift Card Details</strong></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td width="50%" align="right" valign="middle"><strong>Amount Loaded on Card:</strong></td>
        <td width="50%" align="left" valign="middle">$<?php echo $this->charged_amount?></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td width="50%" align="right" valign="middle"><strong>Gift Card Number:</strong></td>
        <td width="50%" align="left" valign="middle"><?php echo $this->gift_card_number;?></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td width="50%" align="right" valign="middle"><strong>Date of Purchase:</strong></td>
        <td width="50%" align="left" valign="middle"><?php echo $this->date_of_purchase;?></td>
      </tr>
    </table>
         <!--end gift card details table-->
         </td>
        </tr>
        <tr>
          <td>
       <!--start payment details table-->
    <table role="presentation" width="100%" align="center" cellpadding="2">
      <tr style="background-color:#afbd21;">
        <td colspan="2" style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;"><strong>&nbsp;Payment Details</strong></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td width="50%" align="right" valign="middle"><strong>Card Type:</strong></td>
        <td width="50%" align="left" valign="middle"><?= $this->credit_card_type?></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td width="50%" align="right" valign="middle"><strong>Last 4 digits:</strong></td>
        <td width="50%" align="left" valign="middle"><?= $this->credit_card_number?></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td align="right" valign="middle"><strong>Amount:</strong></td>
        <td align="left" valign="middle">$<?= $this->charged_amount?></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td align="right" valign="middle"><strong>Date of Purchase:</strong></td>
        <td align="left" valign="middle"><?= $this->date_of_purchase?></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td width="50%" align="right" valign="middle"><strong>Billing Name:</strong></td>
        <td width="50%" align="left" valign="middle"><?= $this->billing_name?></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td align="right" valign="top"><strong>Billing Address:</strong></td>
        <td align="left" valign="middle"><?= $this->billing_address?><br />
          <?= $this->billing_zip?>
		</td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td align="right" valign="middle"></td>
        <td align="left" valign="middle"></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td align="right" valign="middle"><strong>Billing Email: </strong></td>
        <td align="left" valign="middle"><?=$this->billing_email?></td>
      </tr>
    </table>
     <!--end payment details table-->
    </td>
        </tr>
      </table>
      <!--end gift cards purchased table-->
      <p style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;"><strong>Have questions?</strong> To view the complete Gift Card Policies &amp; Terms online or if you have questions regarding your Gift Card or Customer Service options please visit <a href="<?=HTTPS_SERVER?>/?static=giftcards" style="color:#c0362c;">DreamDinners.com/giftcards</a>.</p></td>
</tr>
</table>
</body>
</html>