<?php $this->assign('page_title', 'Dream Dinners Video');?>
<?php $this->assign('page_description','Watch for more information'); ?>
<?php $this->assign('page_keywords','video, watch video'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>How To Video</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<!--<img src="<?php echo IMAGES_PATH; ?>/landing_pages/holidays-1400x475.jpg" alt="holiday dinner" class="img-fluid" />-->
				<div class="row my-5 text-center">
                	<div class="col mb-4">
						<h3>Learn how to make the braided bread kit with Chef Laura.</h3>
					</div>
					<div class="embed-responsive embed-responsive-16by9 text-center">
						<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/9eQZ6TKStZY" allowfullscreen></iframe>
						<!--<iframe class='hippo-embed-frame hippo-embed-frame-inline-9305135' loading="lazy" width='100%' height='100%' scrolling='no' frameborder=0 marginwidth=0 marginheight=0 src='https://dreamdinners2bklo3.hippovideo.io/video/embed/e7pqu8QjXO9oTWLuSpcxhtXNAk2ZxATBqHqIlNEZvxo?autoplay=false' allowfullscreen ></iframe><script>window.hippoEmbedSeo = "";</script><script src="https://hippo-embed-scripts.s3.amazonaws.com/video-delivery-embed.js" async></script><script>var hippoResponsiveInline9305135 = function() {var frames = document.querySelectorAll('.hippo-embed-frame-inline-9305135');for(var i = 0; i < frames.length; i++) {frames[i].style.height = (frames[i].offsetWidth/1.777) + 'px';}};document.addEventListener('DOMContentLoaded', function() { hippoResponsiveInline9305135(); });window.addEventListener('resize', function() { setTimeout(hippoResponsiveInline9305135, 10); });hippoResponsiveInline9305135();</script>-->
					</div>
				</div>
			</div>
		</section>
		
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>