<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_logotype_darkgrey_300x28.png" alt="Dream Dinners" width="300" height="28"></td>
<td width="300" align="right" style="padding: 5px"><a href="<?php echo HTTPS_BASE ?>my-meals">Rate My Meals</a></td>
</tr>
<tr bgcolor="#5c6670">
 <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Edited</span></p></td>
</tr>
</table>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="8">
<tr>
<td>
<p>Your order with Dream Dinners has been edited. We look forward to preparing delicious meals for your family. Please find your new order details below.</p>
<p>Your prepped dinners will be delivered to your home on your selected delivery date. Be on the lookout for tracking information once your order has shipped. Make sure to place your dinners in the refrigerator as soon as you can to enjoy them within the first few days or if you plan to use them later, place them in the freezer.</p>
<p>Sincerely,<br />
 Your Dream Dinners Team</p>

<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
</tr>
</table>
<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_delivered_email.tpl.php'); ?>
</body>
</html>