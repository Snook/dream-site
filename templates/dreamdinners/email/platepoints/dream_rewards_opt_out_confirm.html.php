<html>
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="515" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td><div align="right"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dreamdinners_logo_olive.png" alt="DreamDinners.com" border="0" width="80" /></div>
    </td>
    </tr>
     <tr>
     <td>
      <p class="sectionhead"><?= $this->userObj->firstname ?>,</p>
      <p>This is an email confirmation that you have chosen to stay in Dream Rewards and not transition into our PLATEPOINTS program.</p>
      <p>As a reminder, Dream Rewards will be discontinued at the end of 2014. When you enroll in the PLATEPOINTS program during the 3 month transition window, your Dream Rewards order history will be used to transition you to your advanced Chef Badge Milestone. After the transition period is over, you can join PLATEPOINTS at any time as a new PLATEPOINTS member.</p>
      <p>If you have any questions or concerns or change your mind and wish to enroll in PLATEPOINTS, please contact your local store.</p>
      <p>Warm Regards,<br />
      
    <?php  if ($this->storeObj) { ?>
      	Dream Dinners Home Office on behalf of<br />
		<?=$this->storeObj->store_name?><br />
		email: <?=$this->storeObj->email_address ?><br />
		phone: <?=$this->storeObj->telephone_day ?></p>
	<?php } else { ?>	
		Dream Dinners Home Office
	<?php } ?>		
	</td>
  </tr>
</table>
</body>
</html>





