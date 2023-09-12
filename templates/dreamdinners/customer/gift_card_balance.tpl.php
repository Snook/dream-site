<?php $this->assign('page_title', 'Gift Card Balance Inquiry'); ?>
<?php $this->assign('page_description','Check the balance on your Dream Dinners gift card before purchasing your order.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Gift card balance</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">

		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-10 mx-auto">
						<p>Enter a Gift Card number, then click the Check Balance button to get the current balance on your Gift Card account.</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 mx-auto ">
						<div class="embed-responsive embed-responsive-4by3">
							<script>window.addEventListener('message', function(e) { var sts_iframe = document.getElementById("sts_balcheck_frame"); var data = e.data.split(':'); if (data[0] == 'resize') { var posted_size = data[1].split(','); sts_iframe.style.height	= (parseInt(posted_size[0]) + 5)	+ 'px'; sts_iframe.style.width	= (parseInt(posted_size[1]) + 20) 	+ 'px'; } }, false);</script>
							<iframe id="sts_balcheck_frame" class="embed-responsive-item" src="//smarttransactions.net/gb/dreamdinners"></iframe>
						</div>
					</div>
				</div>
			</div>
		</section>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>