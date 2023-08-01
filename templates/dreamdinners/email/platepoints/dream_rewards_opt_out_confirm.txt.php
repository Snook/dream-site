	Dream Dinners
    
	<?= $this->userObj->firstname ?>,
    This is an email confirmation that you have chosen to stay in Dream Rewards and not transition into our PLATEPOINTS program. As a reminder, Dream Rewards will be discontinued at the end of 2014. When you enroll in the PLATEPOINTS program during the 3 month transition window, your Dream Rewards order history will be used to transition you to your advanced Chef Badge Milestone. After the transition period is over, you can join PLATEPOINTS at any time as a new PLATEPOINTS member.
    
    If you have any questions or concerns or change your mind and wish to enroll in PLATEPOINTS, please contact your local store.
    
    Warm Regards, 
    <?php  if ($this->storeObj) { ?>
      	Dream Dinners Home Office on behalf of
		<?=$this->storeObj->store_name?>
		email: <?=$this->storeObj->email_address ?>
		phone: <?=$this->storeObj->telephone_day ?>
	<?php } else { ?>	
		Dream Dinners Home Office
	<?php } ?>		






