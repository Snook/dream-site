
<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1 d-print-none">
			<a href="/main.php?page=box_select" class="btn btn-primary"><span class="pr-2">&#10094;</span> Boxes</a>
		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center col-print-12">
			<h2>Select your <span class="text-green font-weight-semi-bold">box</span> meals</h2>
		</div>
	</div>
</header>

<main class="container">

	<div class="session_menu_div">

		<div class="row">
			<div class="col-12 col-md-8 col-print-12">

				<div class="container-fluid px-0 sticky-top mobile-cart-div d-print-none">
					<div class="row bg-white d-md-none mb-4">
						<div class="col">
							<?php if (!empty($this->box_info->box_type) && $this->box_info->box_type == CBox::DELIVERED_FIXED) { ?>
								<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart_fixed.tpl.php'); ?>
							<?php } else { ?>
								<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart.tpl.php'); ?>
							<?php } ?>
						</div>
					</div>
				</div>

				<div class="card-deck m-md-auto">
					<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu.tpl.php'); ?>
				</div>
			</div>

			<div class="col-12 col-md-4 px-md-4 d-none d-md-block d-print-none">

				<div class="container p-0 sticky-top bg-white pt-md-2 desktop-cart-div">

					<?php if (!empty($this->box_info->box_type) && $this->box_info->box_type == CBox::DELIVERED_FIXED) { ?>
						<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart_fixed.tpl.php'); ?>
					<?php } else { ?>
						<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart.tpl.php'); ?>
					<?php } ?>

					<div class="row mt-4">
						<span class="col-6 text-center text-green-dark font-size-small font-weight-bold"><span class="text-uppercase">Medium</span> serves 2-3</span>
						<span class="col-6 text-center text-green font-size-small font-weight-bold"><span class="text-uppercase">Large</span> serves 4-6</span>
					</div>
					<div class="row mt-3 d-none d-md-block font-size-small">
						<?php foreach (CRecipe::getIconSchematic() AS $icon) { ?>
							<?php if ($icon['site_legend_enabled']) { ?>
								<div class="col-12">
									<?php if (!empty($icon['css_icon'])) { ?><i class="font-size-medium-large align-middle dd-icon <?php echo $icon['css_icon']; ?>"></i><?php } ?> <?php echo $icon['label']; ?>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-5">
		<p class="text-center"><i>Due to food shortages related to COVID-19, we have had to make substitutions to various ingredients. These substitutions can affect our nutritional information and may cause slight changes to what is listed for each recipe.</i></p>
	</div>
</main>