<?php $this->setScript('head', SCRIPT_PATH . '/admin/main.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/order_history.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.stickyTableHeaders.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/order_history.css'); ?>
<?php $this->setOnload('order_history_init("'.$this->user['id'].'");'); ?>
<?php $this->assign('page_title','Order History'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<div>
	<h1>Order History for <a href="/backoffice/user-details?id=<?php echo $this->user['id']; ?>"><?php echo $this->user['firstname']; ?> <?php echo $this->user['lastname']; ?></a></h1>
	<div id="order_history">
		<?php include $this->loadTemplate('admin/subtemplate/order_history/order_history_table.tpl.php'); ?>
	</div>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>