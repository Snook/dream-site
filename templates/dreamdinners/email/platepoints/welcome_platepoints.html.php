<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table role="presentation" width="550"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td colspan="2" align="center" style="padding: 5px; border-bottom: #e87722 dotted 3px !important;"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/platepoints-logo-orange-header-400x60.png" alt="PLATEPOINTS" border="0" width="400" height="60" /></td>
</tr>
<tr>
<td style="padding:20px 10px 5px 10px;"><p>Dear <?php echo $this->userObj->firstname;?>, </p>
  <p>Welcome to PlatePoints!</p>
  <p>We want to reward you for serving your family homemade  dinners. For spending less time in the kitchen and more time doing what you love.</p>
   <p>Now that you are enrolled, you  will earn points on every order and for rating your meals. Those points will convert to Dinner Dollars that you can use to discount future orders.</p>

  <p>Sincerely,<br/>
  The Dream Dinners Team</p></td>
</tr>
</table>

<table role="presentation" width="550"  border="0" cellspacing="0" cellpadding="0">
	<tr>
  <td style="padding: 5px; border-top: #e87722 dotted 3px !important;"></td>
  </tr>
<tr>
  <td align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>?page=locations">View Menu &nbsp; Order</a> | <a href="<?=HTTPS_BASE ?>?page=my_account">My Account</a></td>
  </tr>
<tr>
  <td align="left" style="padding: 5px"><p><i>*Dinner Dollars may be redeemed at participating locations. Once you have a standard order for a menu month, you can place unlimited additional orders with no minimum throughout the month. Dinner Dollars are non-transferrable and expire 30 days from the date awarded. Preferred guests and Meal Prep+ members are not eligible for Dinner Dollars. Dinner Dollars cannot be combined with other offers.</i></p></td>
</tr>
</table>
</body>
</html>