<?php $this->assign('page_title', 'Press Room');?>
<?php $this->assign('page_description','Dream Dinners is in the news. Find us on TV, in magazines and online.'); ?>
<?php $this->assign('page_keywords','magazine articles, television, videos, awards, news articles'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h2>Press Room</h2>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-3 mb-4 text-center">
						<a href="/?static=pressroom_tv" data-gaq_cat="Landing" data-gaq_action="Television Spotlight" data-gaq_label="Press Room"><img src="<?php echo IMAGES_PATH; ?>/press/tv-spotlight-307x160.jpg" alt="Television Spotlight" class="img-fluid" /></a>
						<h3 class="text-green mt-2">Television Spotlight</h3>
						<p>Watch us on The View, Good Morning America and more.</p>
						<p><a href="/?static=pressroom_tv" data-gaq_cat="Landing" data-gaq_action="Television Spotlight" data-gaq_label="Press Room" class="btn btn-primary">Read More &gt;</a></p>
					</div>
					<div class="col-md-3 mb-4 text-center">
						<a href="/?static=pressroom_magazine_articles" data-gaq_cat="Landing" data-gaq_action="Magazine Articles" data-gaq_label="Press Room"><img src="<?php echo IMAGES_PATH; ?>/press/magazine-articles-307x160.jpg" alt="Magazine Articles" class="img-fluid" /></a>
						<h3 class="text-green mt-2">Magazine Articles</h3>
						<p>Great magazine articles about Dream Dinners in Redbook, &quot;O&quot;, Family Circle and more.</p>
						<p><a href="/?static=pressroom_magazine_articles" data-gaq_cat="Landing" data-gaq_action="Magazine Articles" data-gaq_label="Press Room" class="btn btn-primary">Read More &gt;</a></p>
					</div>
					<div class="col-md-3 mb-4 text-center">
						<a href="/?static=pressroom_online_articles" data-gaq_cat="Landing" data-gaq_action="Online Articles" data-gaq_label="Press Room"><img src="<?php echo IMAGES_PATH; ?>/press/online-articles-307x160.jpg" alt="Online Articles" class="img-fluid" /></a>
						<h3 class="text-green mt-2">Online Articles</h3>
						<p>Read articles from the Wall Street Journal, Time Magazine and more.</p>
						<p><a href="/?static=pressroom_online_articles" data-gaq_cat="Landing" data-gaq_action="Online Articles" data-gaq_label="Press Room" class="btn btn-primary">Read More &gt;</a></p>
					</div>
					<div class="col-md-3 mb-4 text-center">
						<a href="/?static=pressroom_awards" data-gaq_cat="Landing" data-gaq_action="Awards" data-gaq_label="Press Room"><img src="<?php echo IMAGES_PATH; ?>/press/awards-honors-307x160.jpg" alt="Awards" class="img-fluid" /></a>
						<h3 class="text-green mt-2">Awards &amp; Honors</h3>
						<p>Read articles about our awards and honors over the years.</p>
						<p><a href="/?static=pressroom_awards" data-gaq_cat="Landing" data-gaq_action="Awards" data-gaq_label="Press Room" class="btn btn-primary">Read More &gt;</a></p>
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>