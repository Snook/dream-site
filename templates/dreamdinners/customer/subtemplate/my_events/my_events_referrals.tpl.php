<?php if (!empty($this->usersFuturePastEvents['eventReferrals'])) { ?>
	<?php foreach ($this->usersFuturePastEvents['eventReferrals'] AS $referral_id => $referral) { ?>
		<?php include $this->loadTemplate('customer/subtemplate/my_events/my_events_manage_invited_row.tpl.php'); ?>
		<?php unset($referral); } ?>
<?php } ?>
<?php include $this->loadTemplate('customer/subtemplate/my_events/my_events_manage_invites.tpl.php'); ?>

