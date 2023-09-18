<?php $this->assign('page_title', 'Terms and Conditions');?>
<?php $this->assign('page_description','Dream Dinners terms and conditions, session terms, gift card terms, blog terms and more.'); ?>
<?php $this->assign('page_keywords','session terms, program terms, gift card terms, loyalty program terms, blog terms, web site terms'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Dream Dinners Policies & Terms</h1>
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
						<p class="mb-3 text-center"><a href="#website">Website</a> | <a href="#giftcards">Gift Cards</a> | <a href="#platepoints">PlatePoints</a> | <a href="#referral">Referral Program</a> | <a href="#contests">Contests</a> | <a href="#blog">Blog</a> | <a href="#sms">SMS</a></p>

					</div>
				</div>
				<div class="row">
					<div class="col-md-8 mx-auto">
						<h3 id="orders" class="mb-3 semibold-subtitle">Orders</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_session.tpl.php'); ?></div>
						<h3 id="website" class="mb-3 semibold-subtitle">Dream Dinners Website</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_website.tpl.php'); ?></div>
						<h3 id="giftcards" class="mb-3 semibold-subtitle">Gift Cards</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_giftcards.tpl.php'); ?></div>
						<h3 id="platepoints" class="mb-3 semibold-subtitle">PlatePoints</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_platepoints.tpl.php'); ?></div>
						<h3 id="referral" class="mb-3 semibold-subtitle">Referral Program</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_referrals.tpl.php'); ?></div>
						<h3 id="contests" class="mb-3 semibold-subtitle">Contests, Sweepstakes and Promotions</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_contests.tpl.php'); ?></div>
						<h3 id="blog" class="mb-3 semibold-subtitle">Blog</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_blogterms.tpl.php'); ?></div>
						<h3 id="sms" class="mb-3 semibold-subtitle">SMS</h3>
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_sms_terms.tpl.php'); ?></div>
						<h3 id="mpp" class="mb-3 semibold-subtitle">Meal Prep+ Membership Discontinued</h3>
						<!--<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_membership.tpl.php'); ?></div>-->

					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>