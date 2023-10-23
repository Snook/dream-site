<?php $this->assign('page_title','Unexpected Error'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
			<h2>Unexpected Error</h2>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

		</div>
	</div>
</header>

<main class="container">
	<div class="row">
		<div class="col">
			<p>We are sorry, an error has occurred in our system. Dream Dinners technical staff has been notified.
			<p>Please try again later.</p>
		</div>
	</div>
</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>