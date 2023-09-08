<?php $this->setScript('foot', SCRIPT_PATH . '/customer/box.min.js'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('customer/subtemplate/edit_order.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1 d-print-none">
				<a href="/box-select" class="btn btn-primary"><span class="pr-2">&#10094;</span> Box select</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center col-print-12">
				<h2>Select <span class="d-none d-md-inline">your</span> <span class="font-weight-bold text-green">delivery</span> date</h2>
				<!--<p>NOTE: If you would like to receive your Complete Thanksgiving Dinner box in time to cook and serve for Thanksgiving, be sure to <b>select a delivery date on or before Friday, November 19th</b>. These items take 3-4 days to thaw in your fridge. </p>-->
			</div>
		</div>
	</header>

	<main class="container">

		<?php foreach ($this->sessions['sessions'] AS $date => $day) { ?>
			<?php if ($day['info']['has_available_sessions']) { ?>
				<?php include $this->loadTemplate('customer/subtemplate/box_delivery_date/box_delivery_date_card.tpl.php'); ?>
			<?php } ?>
		<?php } ?>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>