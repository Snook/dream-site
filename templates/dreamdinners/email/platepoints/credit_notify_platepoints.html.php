<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table width="550"  border="0" cellspacing="0" cellpadding="0" class="border">
<tr>
<td colspan="2"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/platepoints-generic-header.png" alt="PLATEPOINTS" border="0" width="550" height="120" /></td>
</tr>
<tr>
<td style="padding:10px 10px 5px 10px;"><p>Dear <?php echo $this->userObj->firstname;?>,<br />
  You have earned <?php echo CTemplate::moneyFormat($this->amount);?> Dinner Dollars*. Dinner Dollars can only be applied to standard orders above 36 servings. Spend them on items in our Sides &amp; Sweets freezer or apply them toward your next Made For You fee at participating locations. When purchasing more than 36 servings, Dinner Dollars can be applied toward the lowest-priced menu item.</p>
  <p>Your total available Dinner Dollars are: $<?php echo CTemplate::moneyFormat($this->total_available_credit);?></p>
<p>We look forward to seeing you soon,<br />
Dream Dinners</p>
<p><br />
</td>
</tr>
</table>
<table width="550"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td align="left"><img src="<?=EMAIL_IMAGES_PATH?>/email/platepoints/platepoints-footer-grey.png" alt="" width="550" height="50"></td>
</tr>
<tr>
  <td align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>main.php?page=session_menu">Order</a> | <a href="<?=HTTPS_BASE ?>main.php?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>main.php?page=my_platepoints">My PLATEPOINTS</a></td>
  </tr>
<tr>
  <td align="left" style="padding: 5px"><i>*Dinner Dollars expire 6 months from the date awarded. <a href="<?=HTTPS_BASE?>main.php?page=my_platepoints">Login to your account</a> to view your credits and program details.</i></td>
</tr>
</table>
</body>
</html>
