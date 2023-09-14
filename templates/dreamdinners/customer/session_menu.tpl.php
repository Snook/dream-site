<?php $this->setScript('foot', SCRIPT_PATH . '/customer/session_menu.min.js', true); ?>
<?php $this->setScriptVar('var order_minimum = '.$this->order_minimum_json.';'); ?>
<?php $this->setScriptVar('var menu_view = "'.$this->menu_view. '";'); ?>
<?php $this->setScriptVar('order_type = "' . $this->order_type . '";'); ?>
<?php $this->setScriptVar('menuItemInfo = ' . $this->jsMenuItemInfo . ';'); ?>
<?php $this->setScriptVar('coupon = ' . ($this->coupon ? $this->coupon : "false") . ';'); ?>
<?php $this->setScriptVar('customization = ' . ($this->customization ? $this->customization : "false") . ';'); ?>
<?php if (!empty($this->number_servings_required)) { $this->setScriptVar('number_servings_required = ' . $this->number_servings_required . ';'); } ?>
<?php if ($this->bundleRequirementsString) { $this->setScriptVar('bundle_servings_needed = {' . $this->bundleRequirementsString . '};'); } ?>
<?php $this->assign('no_cache', true); ?>
<?php $this->assign('javascript_required_alert', true); ?>
<?php $this->assign('page_title', 'Select Menu Items'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<?php
if ($this->menu_view == 'session_menu_freezer')
{
	include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_freezer.tpl.php');
}
else
{
	include $this->loadTemplate('customer/subtemplate/session_menu/session_menu.tpl.php');
}
?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>