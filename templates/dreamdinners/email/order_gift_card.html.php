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
<td>
<p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Thank you for your order. <strong>This receipt is your &quot;Proof of Purchase&quot;</strong>.</p>

<?php if ($this->charged_amount >= 125 && (date('Y-m-d') >= '2021-12-01' && date('Y-m-d') <= '2021-12-25' )) { ?>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #000000;"><strong>Our Gift to You </strong></p>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Thank you for gifting Dream Dinners this year. We want to give you something special. Use the coupon code below to get $10 off your next standard order between January and March 2022.</p>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;"> <strong>Code: Gift22</strong></p>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;">Happy Holidays from Dream Dinners.</p>
 <hr>
 <p style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;"><em>*$125 Dream Dinners gift card purchase must be made in one transaction. $10 off coupon code will be sent in your order confirmation email. Only one coupon code can be applied per an 2022 order. Coupon code is valid January – March 2022. Coupon code not eligible with other coupons or offers. Coupon not valid for use in conjunction with a Meal Prep Starter Pack or Special Event session type. No cash redemption permitted. Available for use at participating locations.<em></p>
<?php } ?>
<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1px solid #b9bf33; background-color: #F8F7F5;">
        <tr style="background-color:#afbd21;">
          <td style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;"><strong>&nbsp;Gift Cards Purchased</strong></td>
        </tr>
        <tr>
         <td>
         <!--start gift card details looping table-->
         <?php foreach($this->cards_purchased as $id => $thisCard) { ?>
         <table role="presentation" width="100%" align="center" cellpadding="2">
           <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
             <td align="left" valign="top"><?=$thisCard['card_image']?>
				 <p>&nbsp;</p>
               <strong>Card Type:</strong>
<?php if ($thisCard['media_type'] == 'PHYSICAL') { ?>
               Traditional card (shipped to address)<br />
               <?php } else { ?>
               eGift card (sent via email)<br />
               <?php } ?>
               <strong>Card Design:</strong> <?=$thisCard['design_title']?><br />
               <strong>Amount:</strong> $<?=$thisCard['card_amount']?><br />
               <strong>To:</strong> <?=$thisCard['to_name']?><br />
               <strong>From:</strong> <?=$thisCard['from_name']?><br />
               <strong>Message:</strong> <?=$thisCard['message_text']?><br />
               <?php if ($thisCard['media_type'] == 'VIRTUAL') { ?>
               <strong>Recipient Email Address:</strong> <?=$thisCard['recipient_email']?><br />
               <strong>Resend eGift Card Email:</strong> &nbsp;<a href="<?=HTTPS_BASE?>?page=resend_egift&oid=<?=$thisCard['confirm_id']?>">Click here to resend</a>
               <p>&nbsp;</p>
               <?php } else { ?>
               <strong>Recipient Name:</strong> <?=$thisCard['ship_to_name']?><br />
               <strong>Recipient Address:</strong> <br />
			   <?=$thisCard['shipping_address_1']?> <br />
			   <?php if (!empty($thisCard['shipping_address_2'])) { ?>
			   <?=$thisCard['shipping_address_2']?><br />
               <?php } ?>
               <?=$thisCard['shipping_city']?>, <?=$thisCard['shipping_state']?> <?=$thisCard['shipping_zip']?><p>&nbsp;</p>
               </td>
           </tr>
               <?php } ?>
         </table>
          <?php } // end loop?>
         <!--end gift card details looping table-->
         </td>
        </tr>
        <tr>
          <td>
       <!--start payment details table-->
    <table role="presentation" width="100%" align="center" cellpadding="2">
      <tr style="background-color:#b9bf33;">
        <td colspan="2" style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #000000;"><strong>&nbsp;Payment Details</strong></td>
      </tr>
      <tr style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
        <td width="50%" align="right" valign="middle"><strong>Card Type:</strong></td>
        <td width="50%" align="left" valign="middle"><?= $this->payment_card_type?></td>
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
      <p style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">*<em>Gift Card orders that require shipping will take 2-6 business days for processing &amp; shipping</em><em>.<br />
Note: $2 shipping/service fee added each Traditional Gift Card ordered.</em></p>
      <p style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;"><strong>Have questions?</strong> To view the complete Gift Card Policies &amp; Terms online or if you have questions regarding your Gift Card or Customer Service options please visit <a href="<?=HTTPS_SERVER?>/?static=terms">DreamDinners.com</a></p>
      <p>&nbsp; </p>
</td>
</tr>
</table>
</body>
</html>