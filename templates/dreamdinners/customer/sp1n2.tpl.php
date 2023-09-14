<?php CBrowserSession::nofollow(); ?>
<?php $this->assign('page_title', 'Spin to Win July 2023');?>
<?php $this->assign('page_description','Spin to win a special prize.'); ?>
<?php $this->assign('page_keywords','seasonal gift'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Summer Spin to Win July</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
			
			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container text-center">
				<!--embed will auto display here based on wisepop settings-->
				<div id="wisepopdd"></div>
				<p class="text-center font-weight-bold">This promotion ends July 31, 2023. Each guest gets 2 chances to view the prize wheel for an entry.</p>
				<p class="text-center text-small">*Offer is only valid at participating locations. One prize per an order and not transferrable; this code may only be used by the account associated with this email address. No cash redemption permitted. Not available for guests that have a preferred guest account. Not valid combined with any other offers or promotions. All prizes must be redeemed in conjunction with a qualifying order in the valid redemption month. Coupon code cannot be used towards events or promotional orders including the Meal Prep Starter Pack.<br><br>
				Guests must bring a printed copy or show a screenshot of the email at their next pick up or assembly session to redeem their prize. For home delivery orders, guest should forward a copy of their prize email to redeem their prize.
				</p>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>