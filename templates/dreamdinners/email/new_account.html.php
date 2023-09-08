<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="100%"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="60%" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
<td width="40%" align="right" style="padding: 5px">&nbsp;</td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Welcome to Dream Dinners!</span></p></td>
</tr>
</table>
<table role="presentation" width="100%"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>We take care of all the shopping, chopping, prep and clean up, so you have delicious prepped meals to enjoy at home with your family.</p>
  <p>No more: </p>
<ul>
	<li>Dinnertime stress wondering what to cook</li>
	<li>Unplanned, costly or unhealthy dinners out</li>
	<li>Last minute trips to the grocery store to buy ingredients for dinner</li>
	<li>Unnecessary hours in the kitchen cooking and cleaning</li>
</ul>
  <!--<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr>
      <td width="15%" align="right"><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/cookbook-icon.gif" alt="" width="70" height="67"></td>
      <td width="85%" align="left" valign="middle">Dinnertime stress wondering what to cook</td>
    </tr>
    <tr>
      <td width="15%" align="right"><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/price-icon.gif" alt="" width="70" height="67"></td>
      <td width="85%" align="left" valign="middle">Unplanned, costly or unhealthy dinners out</td>
    </tr>
    <tr>
      <td width="15%" align="right"><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/cart-icon.gif" alt="" width="70" height="67"></td>
      <td width="85%" align="left" valign="middle">Last minute trips to the grocery store to buy ingredients for dinner</td>
    </tr>
    <tr>
      <td width="15%" align="right"><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/clock-icon.gif" alt="" width="70" height="67"></td>
      <td width="85%" align="left" valign="middle">Unnecessary hours in the kitchen cooking and cleaning</td>
    </tr>
  </table>-->

 <p><strong>Account Email Address:</strong> <?=$this->primary_email?></p>
<p>Please review our <a href="https://dreamdinners.com/?static=terms">Terms and Conditions</a> to learn more about Dream Dinners.</p>

<p>Dream Dinners has changed so many lives for the better.  We know it can change yours too.<br>
See you soon!</p>
</td>
</tr>
</table>
</body>
</html>