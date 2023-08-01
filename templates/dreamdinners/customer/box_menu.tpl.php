<?php $this->setScript('foot', SCRIPT_PATH . '/customer/box.min.js'); ?>
<?php $this->assign('page_title', 'Select Box Items'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/edit_order.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>