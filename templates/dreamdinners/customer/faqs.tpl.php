<?php $this->assign('page_title', "FAQs | Frequently Asked Questions"); ?>
<?php $this->assign('page_description', 'Dream Dinners frequently asked questions and answers about our products and services.'); ?>
<?php $this->assign('page_keywords', 'about dream dinners, session questions, meal assembly, dinner preparation, freezing meals, attending a session'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Dream Dinners FAQs</h1>
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
						<h2 class="font-weight-semi-bold text-uppercase font-size-medium mb-4 text-center">What can we help you with?</h2>
						<p class="mb-3 text-center">If you have questions and would like to speak with a team member at your local store, click <a href="/locations">Locations</a> to find the direct contact information for the store that you are interested in. Additionally, you can contact our home office from our <a href="/contact-us">Contact Us page</a> for website support or guest services.</p>

						<?php if (defined('ENABLE_HELP_SEARCH') && ENABLE_HELP_SEARCH == true) { ?>
							<a class="btn btn-primary btn-lg btn-block help-search-launcher" href="#">Search Helpdesk</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>