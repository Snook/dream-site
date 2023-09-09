<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table width="550"  border="0" cellspacing="0" cellpadding="0" class="border">
<tr>
  <td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/platepoints-generic-header.png" alt="PLATEPOINTS Head Chef" border="0" width="550" height="150" /></td>
</tr>
<tr>
<td style="padding:10px 15px 5px 15px;"><p>
<img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/badge-head_chef-119x119.png" align="right" alt="PLATEPOINTS Head Chef" border="0" width="119" height="119" />
  <span class="subheads">Chef Milestone: Head Chef</span><br />
<span class="subheads">Total PLATEPOINTS: <?php echo $this->program_summary['lifetime_points'];?></span><br />
<span class="subheads">Available Dinner Dollars: $<?php echo CTemplate::moneyFormat($this->total_available_credit);?></span></p>
  <p>Congratulations <?php echo $this->userObj->firstname;?>!<br />
    You  saut&eacute;ed your way to Dream Dinners Head Chef and we  want to celebrate you for gathering your family around the table for homemade meals. </p>
   <?php if (!$this->user_is_preferred) { ?>
   <p>For your accomplishments, you have earned a featured Sides &amp; Sweets item*. Bring this voucher/email in to your next session to claim your prize.<br />
     <img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/head-chef-voucher.gif" alt="Perk Voucher" border="0" width="463" height="135" /></p>
  <p>As a Head Chef, you receive 10 Bonus Dinner Dollars** and you will now earn <br>
    4x PLATEPOINTS when you sign up in-store every month. Plus, you are now eligible to receive invitations to exclusive Dream Events. We will share all the details at your next session.
<?php } // end is not preferred ?>
  </p>
  <p>Sincerely,<br />
    Dream Dinners</p>
 <p><a href="<?=HTTPS_BASE ?>platepoints">Learn more about the perks of PLATEPOINTS &gt;</a></p>
  </td>
</tr>
</table>
<table width="550"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td align="left"><img src="<?=EMAIL_IMAGES_PATH?>/email/platepoints/platepoints-footer-grey.png" alt="" width="550" height="50"></td>
</tr>
<tr>
  <td align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>session-menu">Order</a> | <a href="<?=HTTPS_BASE ?>my-account">My Account</a> | <a href="<?=HTTPS_BASE ?>my-platepoints">My PLATEPOINTS</a></td>
  </tr>
<tr>
		<td align="left" style="padding: 5px"><p><i>*Valid for one  Extended Fast Lane dinner up to a $25 value from our sides and sweets freezer. Voucher is non-transferable. One voucher per person. Voucher expires 6 months from date awarded.</i><br>
	    <i>**Dinner Dollars can be used on standard orders above 36 servings only. Spend them on items in our Sides &amp; Sweets freezer and Made for You service fees at participating locations. When ordering more than 36 servings, Dinner Dollars can be applied toward your lowest priced menu item.</i></p></td>
	</tr>
</table>
</body>
</html>