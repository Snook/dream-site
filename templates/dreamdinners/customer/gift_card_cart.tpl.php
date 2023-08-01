<?php $this->assign('no_cache', true); ?>
<?php $this->assign('page_title', 'Gift Card Cart');?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<a href="/main.php?page=gift_card_order" class="btn btn-primary btn-sm btn-md-lg p-2"><span class="pr-2">&#10094;</span> Gift cards</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Gift card cart</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">
				<a href="/main.php?page=checkout_gift_card" class="btn btn-primary btn-sm btn-md-lg p-2 <?php if (empty($this->cart_info['cart_info_array']['has_gift_cards']))  { ?> disabled<?php } ?>">Checkout <span class="pl-2">&#10095;</span></a>
			</div>
		</div>
	</header>

	<main class="container">

		<div class="row">
			<div class="col">
				<?php if ($this->cart_info['cart_info_array']['has_gift_cards']) { ?>
					<?php foreach ($this->cart_info['gift_card_info'] as $id => $giftCard) { ?>
						<?php include $this->loadTemplate('customer/subtemplate/checkout/checkout_cart_gift_card_item.tpl.php'); ?>
					<?php } ?>
				<?php } else { ?>
					<div class="text-center">
						<h3>Your cart is empty</h3>
						<a href="/main.php?page=gift_card_order" class="btn btn-primary mt-5">Order gift cards</a>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php if ($this->cart_info['cart_info_array']['has_gift_cards']) { ?>
			<div class="row mt-5">
				<div class="col text-center">
					<div class="btn btn-secondary clear-cart-gc">Clear cart</div>
				</div>
			</div>
		<?php } ?>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>