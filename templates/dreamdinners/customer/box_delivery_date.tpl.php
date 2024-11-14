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
				<?php if (new DateTime() >= new DateTime('2024-12-01') && new DateTime() < new DateTime('2024-12-31')) { ?>
				<p>NOTE: Due to the holidays this year, UPS will be unable to ship for us between December 23rd and January 1st. Order by December 13th to get delivery before the holidays! We apologize for the inconvenience.</p><?php } ?>
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