<html lang="en">
<head>
</head>
<body>
<table role="presentation" width="500" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td align="center"><p style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #444444;">You've received a $<?=$this->card_amount?> Dream Dinners Gift Card.</p></td>
  </tr>
</table>
<table role="presentation" width="500" align="center" style="border: 1px solid #CCCCCC;">
  <tr>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><img src="<?=EMAIL_IMAGES_PATH?>/gift_cards/egift-card-celebrate-500x317.png" width="500" height="317" alt="Time to celebrate"></td>
  </tr>
</table>
<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="6">
  <tr>
    <td width="50%" align="left" valign="top"><p><span style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #444444;">Gift Card Number:</span><br />
      <span style="font-family:Arial, Helvetica, sans-serif; font-size: 18px; color: #444444;"><?=$this->account_number?></span><br />
      <a href="<?=HTTPS_SERVER?>/main.php?page=session_menu"><img src="<?=EMAIL_IMAGES_PATH?>/gift_cards/view-menu-button-olive-150x45.gif" alt="View Menu" width="150" height="45" border="0"></a></p></td>
    <td width="50%" align="left" valign="top"><span style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #444444;">Gift Card Amount:</span><br />
      <span style="font-family:Arial, Helvetica, sans-serif; font-size: 36px; color: #444444; font-weight:bold;">$<?=$this->card_amount?></span></td>
  </tr>
  <tr>
    <td align="left" valign="top" colspan="2"><p><span style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #444444;">To: </span><span style="font-family:Arial, Helvetica, sans-serif; font-size: 18px; color: #444444; font-weight:bold;"><?=$this->to_name?></span></p>
    <p><span style="font-family:Arial, Helvetica, sans-serif; font-size: 12px; color: #444444;">From:</span> <span style="font-family:Arial, Helvetica, sans-serif; font-size: 18px; color: #444444; font-weight:bold;"><?=$this->from_name?></span></p>
    <p><span style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #444444;"><?=$this->message_text?></span></p></td>
  </tr>
</table></td>
  </tr>
</table>
<table role="presentation" width="500" border="0" align="center" cellpadding="8" cellspacing="0">
  <tr>
    <td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #444444;"><strong>Redeem your Dream Dinners gift card:</strong></p>
      <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #444444;">1. Visit <a href="https://dreamdinners.com/" style="color:#444444;">dreamdinners.com</a> and click "Get Started".<br />
2. Enter your zip code to find a store location near you or see if we can ship to you.<br />
3. Next, choose the menu month you want to order from under Standard Order. If you are new to Dream Dinners you can sample our menu by selecting our Meal Prep Workshop. If we are shipping to you, select the box that fits your family size.<br />
4. Choose your menu items.<br />
5. Next, if you are ordering from a store location, choose the date, time, and how you would like to get your order from their calendar. Most locations offer pick up and home delivery options. If we are shipping to you select the date you would like your order delivered from the calendar.<br />
6. At checkout, login or create an account. Then enter your gift card number.<br />
7. Finalize your order.<br />
8. You will receive an email confirmation or your order details.</p>

      <p style="font-family:Arial, Helvetica, sans-serif; font-size: 14px; color: #444444;">Not ready to use your Gift Card yet?<br />
        SAVE THIS EMAIL. This email is your &quot;Gift Card&quot;. <br />
        If possible, please print this out or write down your Gift Card number for future use.</p>
    </td>
  </tr>
</table>
</body>
</html>

