<?php $this->assign('page_title', 'Franchise Notice');?>
<?php $this->assign('page_description','At Dream Dinners, our mission is to make gathering around the family table a cornerstone of daily life.'); ?>
<?php $this->assign('page_keywords','franchise, dream dinners franchise'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Franchise Notice</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<div class="row mb-5">
					<div class="col">
						<p class="text-center"><img src="<?php echo IMAGES_PATH; ?>/events_programs/franchise.jpg" alt="Dream Dinners franchise" class="img-fluid" /></p>
					
						<p>Dream Dinners has temporarily postponed the sale of new franchisees. We plan to resume franchising in Spring 2020. If you are interested in purchasing an existing franchised store, contact us at <a href="mailto:support@dreamdinners.com">support@dreamdinners.com</a>.</p>
												
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>