<?php $this->assign('page_title', 'Press Room | Awards and Honors');?>
<?php $this->assign('page_description','View a few awards and honors Dream Dinners has received. Including the Entrepreneur of the Year Award.'); ?>
<?php $this->assign('page_keywords','prsa award and honor, stevie award finalist, entrepreneur of the year, awards'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<a href="/?static=pressroom" class="btn btn-primary"><span class="pr-2">&#10094;</span> back</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h2>Awards and Honors</h2>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<div class="container">
			<div class="row">
				<div class="col-md-6 mb-4">
					<blockquote class="blockquote">
						<p class="mb-0">&quot;Dream Dinners Inc. took the top honor for Public Relations Special Event in 7‐days, for their 2011 “Supper Bowl” in Dallas‐the result of a partnership with the NFL wives club, “Off the Field” which benefitted the Children’s Hospital in Dallas.&quot;</p>
					</blockquote>
					<p><a href="<?php echo MEDIA_PATH; ?>/Dream Dinners Press Release-PRSA Awards and The View.pdf" class="btn btn-primary btn-sm" target="_blank">View PDF Article</a></p>
				</div>
				<div class="col-md-6 mb-4">
					<blockquote class="blockquote">
						<p class="mb-0">&quot;Dream Dinners, the country’s leading meal assembly service, has been named as a finalist in the prestigious Stevie Awards for Women in Business.&quot;</p>
						<footer class="blockquote-footer">Lia Bigano <cite>SnohomishTimes.com - Prestigious Stevie Awards and Dream Dinners</cite></footer>
					</blockquote>
					<p><a href="http://www.snohomishtimes.com/snohomishNEWS.cfm?inc=story&newsID=1962" class="btn btn-primary btn-sm" target="_blank">View Article</a></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 mb-4">
					<blockquote class="blockquote">
						<p class="mb-0">&quot;The Pacific Northwest Ernst & Young Entrepreneur Of The Year 2006 award recipients are: [&hellip;] Consumer Products - Stephanie Allen, CEO, and Tina Kuna, CFO, Dream Dinners&quot;</p>
						<footer class="blockquote-footer">Tania Villalonga <cite>Ernst &amp; Young - Award Recipients of Ernst &amp; Young Entrepreneur Of The YearÆ Award Announced in the Pacific Northwest</cite></footer>
					</blockquote>
					<p><a href="<?php echo MEDIA_PATH; ?>/eoy_2006.pdf" class="btn btn-primary btn-sm" target="_blank">View PDF Article</a></p>
				</div>
				<div class="col-md-6 mb-4">

				</div>
			</div>
			<div class="row">
				<div class="col-md-3 mx-auto">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">Media Relations</h5>
							<p class="card-text">360-804-2078</p>
							<p class="card-text"><a href="mailto:pr@dreamdinners.com">pr@dreamdinners.com</a></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>