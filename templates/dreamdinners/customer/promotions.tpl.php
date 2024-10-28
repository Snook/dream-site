<?php $this->assign('page_title', 'Promotions, Contests and Special Offers');?>
<?php $this->assign('page_description','Find our latest in store promotions, special offers and events listed here.'); ?>
<?php $this->assign('page_keywords','contests, offers, email thaw reminders'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Promotions & Partnerships</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 text-left p-5 my-5">
						<h2 class="font-weight-bold mt-2">Thanksgiving Dinner Bundle</h2>
						<p class="text-uppercase mb-4">Our Thanksgiving bundle includes a holiday turkey with compound butter, Pumpkin Praline Trifle for dessert, and your choice three side dishes! Not available for shipping.</p>
						<a href="/session-menu" class="btn btn-lg btn-green">Order Now</a>
					</div>
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/thanksgiving-dinner-957x657.webp" alt="Thanksgiving" class="img-fluid">
					</div>

				</div>
			</div>
		</section>

	
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>