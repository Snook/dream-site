<?php $this->assign('page_title', 'About Us');?>
<?php $this->assign('page_description','Dream Dinners is about getting families around the dinner table with a delicious home cooked meal.'); ?>
<?php $this->assign('page_keywords','family dinner, dinner around the table, dinner preparation, meal preparation, homemade dinner, fix and freeze, freezer dinner'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>About Us</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">

		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-10 mx-auto bg-image-mission p-5">
						<p class="font-size-large text-uppercase text-center"><br/>
							At Dream Dinners, <span class="font-weight-bold">our mission</span> is to make gathering around the family table a cornerstone of daily life.<br/>
                        </p>
					</div>
				</div>
			</div>
		</section>
		
		</section>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>