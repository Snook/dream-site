



<?php foreach ($this->sessions['sessions'] AS $date => $day) { ?>
	<?php if ($day['info']['has_available_sessions']) { ?>
		<?php include $this->loadTemplate('customer/subtemplate/session/session_day_card.tpl.php'); ?>
	<?php } ?>
<?php } ?>