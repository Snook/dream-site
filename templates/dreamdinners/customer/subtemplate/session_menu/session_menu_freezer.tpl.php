
<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1 d-print-none">
			<a href="/session-menu" class="btn btn-primary"><span class="pr-2">&#10094;</span> Menu</a>
		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center col-print-12">
			<h2>This month's <span class="text-cyan font-weight-semi-bold">Store specials</span></h2>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right d-print-none">
			<a href="/checkout" class="btn btn-primary disabled continue-btn">Continue <span class="pl-2">&#10095;</span></a>
		</div>
	</div>
</header>

<main class="container">

	<div class="row mb-4">
		<div class="col-md-8 offset-md-2 text-center">
			These seasonal sides, breakfast options and other delicious treats are only available at your store for a limited time. Items listed below are not customizable. Add them to your order today. 
		</div>
	</div>

	<div class="row">
		<div class="col-12 col-md-8">

			<div class="container-fluid px-0 sticky-top mobile-cart-div d-print-none">
				<div class="row bg-white d-md-none mb-4">
					<div class="col">
						<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_cart.tpl.php'); ?>
					</div>
				</div>
			</div>

			<div class="card-deck m-md-auto">
				<?php if(defined('SHOW_SIDES_CATEGORY_ON_MENU') && SHOW_SIDES_CATEGORY_ON_MENU ){?>
					<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_freezer.tpl.php'); ?>
				<?php }else{ ?>
					<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu.tpl.php'); ?>
				<?php } ?>
			</div>
		</div>

		<div class="col-12 col-md-4 px-md-4 d-none d-md-block">

			<div class="container p-0 sticky-top bg-white pt-md-2 desktop-cart-div d-print-none">

				<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_cart.tpl.php'); ?>

				<div class="row mt-4">
					<span class="col-6 text-center text-green-dark font-size-small font-weight-bold"><span class="text-uppercase">Medium</span> serves 2-3</span>
					<span class="col-6 text-center text-green font-size-small font-weight-bold"><span class="text-uppercase">Large</span> serves 4-6</span>
				</div>
				<div class="row mt-3 d-none d-md-block font-size-small">
					<?php foreach (CRecipe::getIconSchematic($this->cart_info["menuObj"]) AS $icon) { ?>
						<?php if ($icon['site_legend_enabled']) { ?>
							<div class="col-12">
								<?php if (!empty($icon['css_icon'])) { ?><i class="font-size-medium-large align-middle dd-icon <?php echo $icon['css_icon']; ?>"></i><?php } ?> <?php echo $icon['label']; ?>
							</div>
						<?php } ?>
					<?php } ?>

					<?php if ($this->order_type == COrders::STANDARD || $this->order_type == COrders::INTRO) { ?>
						<div class="col-12 mt-4">
							<a class="btn btn-green-dark-extra btn-sm btn-block" href="/print?store=<?php echo $this->cart_info['store_info']['id']; ?>&amp;menu=<?php echo $this->cart_info['menu_info']['id']; ?><?php echo (($this->order_type == COrders::INTRO) ? '&amp;intro=true' : ''); ?>" target="_blank"><i class="dd-icon icon-print mr-2"></i> Print <?php echo $this->cart_info['menu_info']['menu_name']; ?> Menu</a>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-5 d-print-none">
		<div class="col p-0 text-right">
			<a href="/checkout" class="btn btn-primary disabled continue-btn">Continue <span class="pl-2">&#10095;</span></a>
		</div>
	</div>

</main>