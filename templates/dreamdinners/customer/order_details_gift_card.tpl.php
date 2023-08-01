<?php $this->assign('page_title', 'Order Details');?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Order details</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<div class="container">
		<?php include $this->loadTemplate('customer/subtemplate/order_details_shared/order_details_shared_gift_card.tpl.php'); ?>
	</div>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>