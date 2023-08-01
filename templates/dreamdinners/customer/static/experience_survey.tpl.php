<?php $this->assign('page_title', 'Dream Dinners Listens Customer Survey');?>
<?php $this->assign('page_description','Let us know how we are doing by fillin out our survey'); ?>
<?php $this->assign('page_keywords','dream dinners listens, survey, customer service, dream dinners experience'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h2>Dream Dinners Listens Customer Survey</h2>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section id="faq">
			<div class="container faq">
				<div class="row">
					<div class="col-md-8 mx-auto">
						<img src="<?php echo IMAGES_PATH; ?>/style/landing/cust_serv_header.jpg" class="img-fluid" alt="Dream Dinners" border="0" />
					</div>
				</div>
				<div class="row">
					<div id="surveyMonkeyInfo"></div>
					<script src="https://www.surveymonkey.com/jsEmbed.aspx?sm=d_2biNiANhDmXuJqdgH_2f1Lkw_3d_3d"></script>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>