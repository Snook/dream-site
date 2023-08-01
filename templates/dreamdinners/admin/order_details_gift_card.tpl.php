<table border="0" cellspacing="0" cellpadding="2" style="width: 100%;" class="order_receipt_table">
<tr><td>
    <table border="0" cellspacing="0" cellpadding="0" style="width: 100%;">
          <tr>
            <td colspan="6" style="background-color: #DDDDDD; padding: 2px;"><strong>Gift Cards</strong> (Cards shipped via standard mail include $2 shipping/handling fee)</td>
          </tr>
          <tr>
          <td style="padding-top: 8px; padding-bottom: 8px; padding-left: 6px;">Card Type</td>
          <td style="padding-top: 8px; padding-bottom: 8px; padding-left: 6px;">Recipient</td>
          <td style="padding-top: 8px; padding-bottom: 8px; padding-left: 6px;"></td>
          <td nowrap="nowrap" align="right" style="padding-top: 8px; padding-bottom: 8px; padding-left: 6px;"></td>
          <td style="padding-top: 8px; padding-bottom: 8px; padding-left: 6px; width: 15px;">&nbsp;</td>
          <td align="right" style="padding-top: 8px; padding-bottom: 8px; padding-left: 6px;">Total</td>
        </tr>
        <tr><td></td></tr>
        <?php   foreach($this->gift_card_purchase_array as $giftcard){?>
        <tr>
			<td style="padding-left: 6px;"><b><?php echo $giftcard['gc_media_type'];?></b>
            <?php if (($giftcard['gc_media_type'] == 'Virtual eGift Card') && isset($this->can_resend)) {?>
            &nbsp;<font color="red">[</font> <a href="javascript:resend_eGiftCard(<?=$giftcard['id']?>)">Resend eGift Card </a><font color="red">]</font>
            <?php } ?>
			</td>
            <td style="padding-left: 6px;"><?php echo $giftcard['to_name'];?></td>
            <td></td><td></td><td></td>
            <td align="right"><?php echo $this->moneyFormat($giftcard['gc_amount']);?></td>
        </tr>
		<tr><td style="padding-left:30px;" colspan="3"><?=$giftcard['desc']?><br /></td></tr>
		<?php } ?>
	</table>
    </td>
  </tr>
  <tr>
    <td valign="bottom">
<br />
<table border="0" cellspacing="0" cellpadding="0" style="width: 100%;">
  <tr>
    <td width="40%" align="left" valign="top"><b>Payment Details</b></td>
    <td width="60%" align="left" valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td width="40%" align="left" valign="top"> Card Type:&nbsp;
      <?=$this->gcPaymentCardType;?>
      <br />
      Last 4 digits:&nbsp;
      <?=substr($this->gcPaymentCardNumber, strlen($this->gcPaymentCardNumber)-4, strlen($this->gcPaymentCardNumber))?>
      <br />
      Amount:&nbsp;$
      <?=$this->gift_card_total?>
      <br />
      Payment Date:&nbsp;
      <?=CTemplate::dateTimeFormat($this->gcPaymentDate);?></td>
    <td width="60%" align="left" valign="top">Billing Name: <?=$this->billing_name?><br />
      Billing Address: <?=$this->billing_address?>, <?=$this->billing_zip?><br />
      Billing Email: <?=$this->billing_email?></td>
  </tr>
  <tr>
    <td colspan="2" align="left" valign="top"><br /><b>Shipping Details:</b> Allow 2-6 business days for shipped card, emailed eGift Cards are sent instantly.</td>
    </tr>
</table></td>
  </tr>
 <tr><td colspan="2">
 <table>
 <tr>
	<td></td>
</tr>
 </table>
 </td></tr>
</table>



