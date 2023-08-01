<?php $this->assign('page_title', 'Order Details');?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/order_details.min.js'); ?>
<?php if (defined('ENABLE_SMS_PREFERENCE_ORDER_DETAILS') && ENABLE_SMS_PREFERENCE_ORDER_DETAILS == true) { ?>
	<?php $this->setScript('foot', SCRIPT_PATH . '/customer/account.min.js'); ?>
	<?php $this->setScriptVar('sms_special_case = "' . $this->sms_special_case . '";'); ?>
<?php } ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5 d-print-none">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<a href="/main.php?page=my_account" class="btn btn-primary"><span class="pr-2">&#10094;</span> My Account</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Order details</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

<?php include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_detail.tpl.php'); ?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>