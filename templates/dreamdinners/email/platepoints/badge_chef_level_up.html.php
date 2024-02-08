<html>
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_platepoints.css'); ?></style>
</head>
<body>

<table width="550" border="0" cellspacing="0" cellpadding="0" class="border">
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/platepoints-generic-header.png" alt="PLATEPOINTS Chef" border="0" width="550" height="150" /></td>
	</tr>
	<tr>
		<td style="padding:10px 15px 5px 15px;"><p>
				<img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/badge-chef-119x119.png" align="right" alt="PLATEPOINTS Chef" border="0" width="119" height="119" />
        <span class="subheads">Chef Milestone: Chef</span><br />
				<span class="subheads">Total PLATEPOINTS: <?php echo $this->program_summary['lifetime_points'];?></span><br />
				<span class="subheads">Available Dinner Dollars: $<?php echo CTemplate::moneyFormat($this->total_available_credit);?></span>
				</p>
		 <p>Congratulations <?php echo $this->userObj->firstname;?>!<br />
				You assembled your way to Dream Dinners Chef and we want to celebrate you for gathering your family around the table for homemade meals. </p>
			<?php if (!$this->user_is_preferred) { ?>
				<p>For your accomplishments, you have earned a fun lunch bag*. Bring this voucher/email in to your next session to claim your prize.<br /><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/platepoints/chef-voucher.gif" alt="Perk Voucher" border="0" width="463" height="135" /></p>
				<p>For earning your Chef Badge Milestone you will also receive 10 Bonus Dinner Dollars.** Plus, you will now earn 2x PLATEPOINTS when you sign up in-store every month. As an added bonus you are now eligible to host a Meal Prep Workshop. Use this opportunity to share Dream Dinners with your friends and family in an exclusive session. Reach out to your local store for more details.
		  </p>
				<?php } // end is not preferred ?>
			<p>Sincerely,<br />
		 Dream Dinners</p>
   <p><a href="<?php echo HTTPS_BASE ?>platepoints">Learn more about the perks of PLATEPOINTS &gt;</a><br /></p></td>
	</tr>
</table>
<table width="550" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left"><img src="<?php echo EMAIL_IMAGES_PATH?>/email/platepoints/platepoints-footer-grey.png" width="550" height="50"></td>
	</tr>
	<tr>
		<td align="right" style="padding: 5px"><a href="<?php echo HTTPS_BASE ?>session-menu">Order</a> | <a href="<?php echo HTTPS_BASE ?>my-account">My Account</a> | <a href="<?php echo HTTPS_BASE ?>my-platepoints">My PLATEPOINTS</a></td>
	</tr>
	<tr>
		<td align="left" style="padding: 5px"><p><i>*Voucher is non-transferable. One voucher per person. Voucher expires 6 months from date awarded.</i><br>
	  <i>**Dinner Dollars can be used on standard orders above 36 servings only. Spend them on items in our Sides &amp; Sweets freezer and Made for You service fees at participating locations. When ordering more than 36 servings, Dinner Dollars can be applied toward your lowest priced menu item.</i></p></td>
	</tr>
</table>
</body>
</html>