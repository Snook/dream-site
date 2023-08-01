<?php $this->setScript('head', SCRIPT_PATH . '/admin/testing_support.min.js'); ?>
<?php $this->setScriptVar("params = " . $this->params . ";"); ?>
<?php $this->setOnload('test_order_init();'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>