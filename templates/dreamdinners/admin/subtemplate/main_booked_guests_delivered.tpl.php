<?php

$stpl = new CTemplate();
$stpl->assign('section_title', 'Carrier Pick Ups');
$stpl->assign('session_info', $this->session_info);
$stpl->assign('type', 'shipping_bookings');

echo $stpl->fetch('admin/subtemplate/main_booked_guests_row_delivered.tpl.php');

$stpl->assign('type', 'bookings');
$stpl->assign('section_title', 'Carrier Deliveries');
echo $stpl->fetch('admin/subtemplate/main_booked_guests_row_delivered.tpl.php');
?>


<?php if (empty($this->date_info)) { ?>
	<?php if (!empty($this->session_info['bookings'])) { ?>
		<?php include $this->loadTemplate('admin/subtemplate/main_booked_guests_legend.tpl.php'); ?>
	<?php } ?>
<?php } ?>
