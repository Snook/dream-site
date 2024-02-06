<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table width="550" border="0" cellspacing="0" cellpadding="0" class="border">
<tr>
<td colspan="2"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/platepoints-generic-header.png" alt="PLATEPOINTS" border="0" width="550" height="120" /></td>
</tr>
<tr>
<td style="padding:10px 10px 5px 10px;"><p>Hello <?php echo $this->firstname;?>,<br />
 Don't forget! You have Dinner Dollars that are expiring soon. Dinner Dollars can only be applied to standard orders above 36 servings. Spend them on items in our Sides &amp; Sweets freezer or apply them toward your next Made For You fee at participating locations. When purchasing more than 36 servings, Dinner Dollars can be applied toward the lowest-priced menu item.</p>
 <p>Too busy to come in for a session? Use your Dinner Dollars on Made for You fees and have us assemble your order for you. </p>
 <p><span class="subheads">Dinner Dollar details:</span><br />
 <table>
 <?php foreach ($this->creditArray as $id => $credit) {?>
 <tr>
 <td> #<?php echo $id?></td>
 <td> $<?php echo CTemplate::moneyFormat($credit['amount']);?></td>
 <td> <?php echo CTemplate::dateTimeFormat($credit['expiration_date']);?></td>
 <?php } ?>
 </tr>
 </table>
 </p>
<p>Sincerely,<br />
Dream Dinners
</p></td>
</tr>
</table>
<table width="550" border="0" cellspacing="0" cellpadding="0">
<tr>
<td align="left"><img src="<?php echo EMAIL_IMAGES_PATH?>/email/platepoints/platepoints-footer-grey.png" width="550" height="50"></td>
</tr>
<tr>
 <td align="right" style="padding: 5px"><a href="<?php echo HTTPS_BASE ?>session-menu">Order</a> | <a href="<?php echo HTTPS_BASE ?>my-account">My Account</a> | <a href="<?php echo HTTPS_BASE ?>my-platepoints">My PLATEPOINTS</a></td>
 </tr>
</table>
</body>
</html>