<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/jquery.ba-dotimeout.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/session_menu.min.js'); ?>

<?php
if ($this->isInReschedulingMode)
{
	$this->assign('page_title', 'Reschedule Session');
	$this->setScriptVar('current_session_id = ' . $this->current_session_id . ';');
	$this->setScriptVar('current_order_id = ' . $this->current_order_id . ';');

}
else
{
	$this->assign('page_title', 'Select Session Time');
}
?>
<?php $this->assign('page_description','At Dream Dinners you make homemade meals for your family in our store, then freeze, thaw and cook when you are ready at home. We are your dinnertime solution.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<?php
if ($this->isInReschedulingMode)
{
	include $this->loadTemplate('customer/subtemplate/session/session_standard_reschedule.tpl.php');
}
else
{
	if ($this->cart_info['cart_info_array']['navigation_type'] == CTemplate::EVENT)
	{
		include $this->loadTemplate('customer/subtemplate/session/session_event.tpl.php');
	}
	else
	{
		include $this->loadTemplate('customer/subtemplate/session/session_standard.tpl.php');
	}
}
?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>