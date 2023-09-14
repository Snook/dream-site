<?php $this->assign('page_title', 'Share Your Story');?>
<?php $this->assign('page_description','We want to hear about Dream Dinners in your life.'); ?>
<?php $this->assign('page_keywords','testimonial, video testimonial, dream dinners testimonial'); ?>
<?php $this->assign('no_navigation', false); ?>
<?php $this->assign('no_footer', false); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Share Your Story</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<div class="row">
					<div class="col text-center mb-5">
						<h3>We would love for you to give us a review! Leave a review on Google or Facebook about how Dream Dinners has helped you.</h3>
						<!--<p>Submit a short 30 - 60 second video answering ONE of the following questions.</p>
						<ul class="list-unstyled">
							<li>1. How has Dream Dinners helped you and your family during quarantine?</li>
							<li>2. What are the top 3 ways Dream Dinners has changed your dinnertime?</li>
							<li>3. What impact has Dream Dinners had on your family?</li>
						</ul>-->
					</div>
				</div>
			</div>
		</section>

		<!--<section>
			<div class="container">
				<div class="row mb-5">
					<div class="col-md-5 p-4 mb-5 text-left bg-green-light">
						<h4>Video Tips</h4>
						<ul>
							<li>Record your video HORIZONTALLY.</li>
							<li>Speak loudly and clearly.</li>
							<li>Make sure you are in a well lit environment.</li>
							<li>Make sure you are in a quiet place with no background noise.</li>
							<li>Record your video from your Camera or web cam to ensure audio is recording, then upload the video via the form below.</li>
							<li>If possible, hold your camera steady and don't move around while filming.</li></ul>
						<p class="font-italic">Note: By submitting your video you are giving Dream Dinners exclusive and unlimited rights to reuse your name, content, and video provided on our marketing platforms.</p>
					</div>
					<div class="col-md-7 text-center">
					<iframe src="https://widgets.boast.io/current/iframe-embed.html?boast-component=boast-form&form-id=11f47ca5-dd0d-4db7-9d1d-789fe1a0d562" frameBorder="0" style="width:100%;height:800px;" allow="microphone; camera; encrypted-media" allowusermedia></iframe>
						<script async defer src="https://widgets.boast.io/current/components.js"></script>
						<div data-boast-component="boast-form" data-form-id="11f47ca5-dd0d-4db7-9d1d-789fe1a0d562"></div>
						
				</div>
			</div>
		</section>-->
		<!--<section id="faq">
			<div class="container faq">
				<div class="row">
					<div class="col-md-8 mx-auto">
						<div class="mb-4"><?php include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_contests.tpl.php'); ?></div>
					</div>
				</div>
			</div>
		</section>-->
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>