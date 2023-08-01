<div id="calendar_holder">
	<header class="container my-2">
		<div class="row">
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center col-print-12">
				<h2>Select <span class="d-none d-md-inline">the</span> <span class="font-weight-bold text-green">delivery</span> date</h2>
			</div>
		</div>
	</header>

	<main class="container">

		<?php if (!empty($this->sessions['sessions'] )) {
		foreach ($this->sessions['sessions'] AS $date => $day) { ?>
			<?php if ($day['info']['has_available_sessions']) { ?>
				<?php include $this->loadTemplate('admin/subtemplate/order_mgr/box_delivery_date_card.tpl.php'); ?>
			<?php } ?>
		<?php } } ?>

	</main>
</div>