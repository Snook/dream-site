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
						<p class="mb-3 text-center">Browse our list of help topics outlined below. If you still have questions and would like to speak with a team member at your local store, click <a href="/locations">Locations</a> to find the direct contact information for the store that you are interested in. Additionally, you can contact our home office from our <a href="/?static=contact_us">Contact Us page</a> for website support or guest services.</p>

						<p class="mb-3 text-center"><a href="#health_and_nutrition">Health & Nutrition</a> | <a href="#home_delivery">Local Home Delivery</a> | <a href="#delivered">Shipped to Your Door</a> | <a href="#platepoints">PlatePoints</a> | <a href="#events">Events</a> | <a href="#gift_cards">Gift Cards</a></p>

						<h3 id="store_orders" class="mb-3 semibold-subtitle">Store Orders</h3>
						<?php include $this->loadTemplate('customer/subtemplate/faq/faq_dreamdinners.tpl.php'); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-8 mx-auto mt-2">
						<h3 id="health_and_nutrition" class="mb-3 semibold-subtitle">Health &amp; Nutrition</h3>
						<?php include $this->loadTemplate('customer/subtemplate/faq/faq_health_nutrtion.tpl.php'); ?>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-md-8 mx-auto">
						<h3 id="home_delivery" class="mb-3 semibold-subtitle">Local Home Delivery</h3>
						<?php include $this->loadTemplate('customer/subtemplate/faq/faq_made_for_you_delivery.tpl.php'); ?>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-md-8 mx-auto">
						<h3 id="delivered" class="mb-3 semibold-subtitle">Shipped to Your Door</h3>
						<?php include $this->loadTemplate('customer/subtemplate/faq/faq_delivered.tpl.php'); ?>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-md-8 mx-auto">
						<h3 id="platepoints" class="mb-3 semibold-subtitle">Platepoints</h3>
						<?php include $this->loadTemplate('customer/subtemplate/faq/faq_platepoints.tpl.php'); ?>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-md-8 mx-auto">
						<h3 id="events" class="mb-3 semibold-subtitle">Events</h3>
						<?php include $this->loadTemplate('customer/subtemplate/faq/faq_events.tpl.php'); ?>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-md-8 mx-auto">
						<h3 id="gift_cards" class="mb-3 semibold-subtitle">Gift cards</h3>
						<?php include $this->loadTemplate('customer/subtemplate/faq/faq_giftcards.tpl.php'); ?>
					</div>
				</div>

			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>