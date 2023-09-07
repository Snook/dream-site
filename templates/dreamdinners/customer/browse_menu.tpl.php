<?php $this->assign('canonical_url', HTTPS_BASE . 'main.php?page=browse_menu'); ?>
<?php $this->assign('page_title', 'Menu Preview'); ?>
<?php $this->assign('page_description','At Dream Dinners you make homemade meals for your family in our store, then freeze, thaw and cook when you are ready at home. We are your dinnertime solution.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col text-center">
				<h1>Menu Preview</h1>
				<p>Preview 12 of our 17 menu items for each month below. If you are hungry to view pricing and see the entire menu, <a href="/?page=locations">get started by entering your zip code</a> to find a location or see if we can ship to you.</p>

			</div>
		</div>
	</header>

	<main>
		<div class="container">

			<nav class="row mb-4">
				<div class="col-sm-8 mx-auto nav nav-pills nav-fill pr-0" id="dishDetails" role="tablist">
					<?php $count = 0; foreach ($this->activeMenus AS $menu) { $count++; ?>
						<a class="nav-item nav-link col-<?php echo floor(12 / count($this->activeMenus)); ?> text-uppercase font-weight-bold<?php if ($count == 1) { echo ' active'; } ?><?php echo (!empty($menu['menu_tab_css'])) ? ' ' . $menu['menu_tab_css'] : '' ; ?>" id="m-<?php echo $menu['menu_id']; ?>-tab" data-urlpush="true" data-toggle="tab" data-target="#m-<?php echo $menu['menu_id']; ?>" href="/?page=browse_menu&amp;tab=m-<?php echo $menu['menu_id']; ?>" role="tab" aria-controls="<?php echo $menu['menu_id']; ?>" aria-selected="<?php echo ($count == 1) ? 'true' : 'false'; ?>">

							<?php
							if(strtoupper($menu['menu_month']) == 'DELIVERED'){
								echo 'Ship to Home Menu';
							}else{
								echo $menu['menu_month'] . ' Store Menu';
							} ?> </a>
					<?php } ?>
				</div>
			</nav>

			<div class="tab-content" id="dishDetailsContent">
				<?php $LIMIT = 12; $count = 0; $pagItemCount = 0;foreach ($this->activeMenus AS $menu) { $count++; ?>
					<div class="tab-pane fade<?php if ($count == 1) { echo ' show active'; } ?>" id="m-<?php echo $menu['menu_id']; ?>" role="tabpanel" aria-labelledby="m-<?php echo $menu['menu_id']; ?>-tab">
						<div class="card-deck">
							<?php $count = 0; $total = count($menu['menu_items']);
								foreach ($menu['menu_items'] AS $menu_item) {
									$pagItemCount++;
									if($pagItemCount > $LIMIT){ $pagItemCount = 0; break;}?>
									<div class="card m-md-2 p-0 col-md-3">
										<img loading="lazy" class="card-img-top" src="<?php echo IMAGES_PATH; ?>/recipe/default/<?php echo $menu_item['recipe_id']; ?>.webp" alt="<?php echo $menu_item['menu_item_name']; ?>" />
										<div class="card-body">
											<h5 class="card-title font-size-small"><?php echo $menu_item['menu_item_name']; ?></h5>
											<a href="/?page=item&amp;recipe=<?php echo $menu_item['recipe_id']; ?>" class="card-link text-uppercase stretched-link">Dinner details</a>
										</div>
									</div>
									<?php if (++$count % 2 === 0) { // allows card deck to wrap every two cards on small devices  ?>
										<div class="w-100 d-none d-sm-block d-md-none"></div>
									<?php } ?>
									<?php if ($count % 4 === 0) { // allows card deck to wrap every four cards on large devices  ?>
										<div class="w-100 d-none d-md-block"></div>
									<?php } ?>
									<?php if ($count >= $total || $count >= $LIMIT) { // show get started button after 8 cards  ?>
										<div class="w-100 d-print-none">
											<a href="/?page=locations" class="btn btn-primary btn-block btn-spinner col-sm-8 mt-2 mb-4 my-sm-2 mx-auto">View Full Menu & Pricing</a>
										</div>
									<?php } ?>
								<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>