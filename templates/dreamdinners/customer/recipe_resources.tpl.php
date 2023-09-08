<?php $this->assign('canonical_url', HTTPS_BASE . '?page=recipe_resources'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/recipe_resources.min.js'); ?>
<?php $this->assign('page_title', 'Recipe Resources' . ((!empty($this->search_results)) ? ' - Search Results' : '')); ?>
<?php $this->assign('page_description', 'Cooking instructions, nutritional information and watch step by step instruction on how to cook your Dream Dinners meals in your own kitchen.'); ?>
<?php $this->assign('page_keywords', 'cooking instructions, nutritional information, how to videos, dream dinners video, cooking videos, cooking tips'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Recipe Resources</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">
			</div>
		</div>
	</header>

	<main class="container">

		<div class="row mb-4">
			<div class="col col-lg-6 mx-auto">
				<div class="input-group">
					<input class="form-control p-4 dd-strip-tags" id="ci_search" type="text" maxlength="255" placeholder="Search"<?php if (!empty($this->search_query)) { ?>value="<?php echo $this->search_query; ?>"<?php } ?> />
					<?php if (false) { //video_disabled ?>
						<div class="input-group-append">
							<div class="input-group-text">
								<div class="custom-control custom-checkbox">
									<input id="ci_search_vids" name="ci_search_vids" type="checkbox" class="custom-control-input" <?php echo ($this->videos_only) ? 'checked="checked"' : ''; ?> />
									<label class="custom-control-label" for="ci_search_vids">Videos only</label>
								</div>
							</div>
						</div>
					<?php } ?>
					<button id="ci_search_submit" type="button" class="btn btn-sm btn-primary">Search</button>

				</div>
			</div>
		</div>

		<?php if (empty($this->search_query)) { ?>

			<div class="row mb-4">
				<div class="col-8 mx-auto">
					<p>Search our recipes for step by step  cooking instructions and nutritional information. Please type in the recipe name you are looking for or keyword to search for it.</p>

					<p>Common keywords:
						<a href="/recipe-resources?q=chicken">chicken</a>,
						<a href="/recipe-resources?q=turkey">turkey</a>,
						<a href="/recipe-resources?q=potatoes">potatoes</a>,
						<a href="/recipe-resources?q=pork">pork</a>,
						<a href="/recipe-resources?q=bread">bread</a>,
						<a href="/recipe-resources?q=tortellini">tortellini</a>,
						<a href="/recipe-resources?q=pretzel">pretzel</a>,
						<a href="/recipe-resources?q=shrimp">shrimp</a>,
						<a href="/recipe-resources?q=salmon">salmon</a>,
						<a href="/recipe-resources?q=steak">steak</a>
					</p>
					<p>If you do not find the recipe you are looking for, you can also <a href="/my-meals">login to your account</a> and click My Meals to find information on the dinners you have purchased.</p>
				</div>
			</div>

			<?php if (!empty($this->activeMenus)) { ?>

				<nav class="row mb-4">
					<div class="col nav nav-tabs nav-fill pr-0" id="dishDetails" role="tablist">
						<?php $tabcount = 0; foreach ($this->activeMenus AS $menu) { $tabcount++; ?>
							<a class="nav-item nav-link col-<?php echo floor(12 / count($this->activeMenus)); ?> text-uppercase font-weight-bold<?php if ($tabcount == 2) { echo ' active'; } ?>" id="m-<?php echo $menu['menu_id']; ?>-tab" data-urlpush="true" data-toggle="tab" data-target="#m-<?php echo $menu['menu_id']; ?>" href="/browse-menu&amp;tab=<?php echo $menu['menu_id']; ?>" role="tab" aria-controls="m-<?php echo $menu['menu_id']; ?>" aria-selected="<?php echo ($tabcount == 2) ? 'true' : 'false'; ?>"><?php echo $menu['menu_month']; ?></a>
						<?php } ?>
					</div>
				</nav>

				<div class="tab-content" id="dishDetailsContent">
					<?php $tabcount = 0; foreach ($this->activeMenus AS $menu) { $tabcount++; ?>
						<div class="tab-pane fade<?php if ($tabcount == 2) { echo ' show active'; } ?>" id="m-<?php echo $menu['menu_id']; ?>" role="tabpanel" aria-labelledby="m-<?php echo $menu['menu_id']; ?>-tab">
							<div class="card-deck">
								<?php $count = 0; foreach ($menu['menu_items'] AS $menu_item) {  ?>
									<div class="card m-md-2 p-0 col-md-3 border-0">
										<img class="card-img-top img-fluid" src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($menu_item['menu_image_override'])) ? 'default' : $menu_item['menu_image_override']; ?>/<?php echo $menu_item['recipe_id']; ?>.webp" alt="<?php echo $menu_item['menu_item_name']; ?>">
										<div class="card-body">
											<p class="card-title">
												<a href="/item?recipe=<?php echo $menu_item['recipe_id']; ?>"><?php echo $menu_item['menu_item_name']; ?></a>
											</p>
										</div>
									</div>
									<?php if (++$count % 2 === 0) { // allows card deck to wrap every two cards on small devices  ?>
										<div class="w-100 d-none d-sm-block d-md-none"></div>
									<?php } ?>
									<?php if ($count % 4 === 0) { // allows card deck to wrap every four cards on large devices  ?>
										<div class="w-100 d-none d-md-block"></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		<?php } ?>

		<?php if (!empty($this->search_query)) { ?>
			<?php if (empty($this->search_results)) { ?>
				<div class="row">
					<div class="col-6 mx-auto">
						<p>No results were found.</p>
						<p>Please check the recipe name spelling.</p>
					</div>
				</div>
			<?php } else { ?>
				<div class="card-deck">
					<?php $count = 0; foreach ($this->search_results AS $recipe_id => $recipe) { ?>
						<div class="card m-md-2 p-0 col-md-3 border-0">
							<img class="card-img-top img-fluid" src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($recipe['menu_image_override'])) ? 'default' : $recipe['menu_image_override']; ?>/<?php echo $recipe['recipe_id']; ?>.webp" alt="<?php echo $recipe['recipe_name']; ?>">
							<div class="card-body">
								<p class="card-title">
									<a href="/item?recipe=<?php echo $recipe['recipe_id']; ?><?php echo (!empty($recipe['cooking_instruction_youtube_id'])) ? '&amp;tab=video' : ''; ?>"><?php echo $recipe['recipe_name']; ?></a>
								</p>
							</div>
						</div>
						<?php if (++$count % 2 === 0) { // allows card deck to wrap every two cards on small devices  ?>
							<div class="w-100 d-none d-sm-block d-md-none"></div>
						<?php } ?>
						<?php if ($count % 4 === 0) { // allows card deck to wrap every four cards on large devices  ?>
							<div class="w-100 d-none d-md-block"></div>
						<?php } ?>
					<?php } ?>
				</div>


				<?php if (!empty($this->page_total)) { ?>
					<div class="row pt-4">
						<div class="col">
							<nav aria-label="Page navigation">
								<ul class="pagination">
									<li class="page-item<?php echo (empty($this->page_prev)) ? ' disabled' : '' ?>"><a class="page-link" href="/recipe-resources?q=<?php echo $this->search_query; ?><?php echo ((!empty($this->page_prev) && $this->page_prev != 1) ? '&amp;p=' . $this->page_prev : ''); ?><?php echo ($this->videos_only) ? '&amp;video=true' : ''; ?>">Previous Page</a></li>
									<li class="page-item<?php echo ($this->page_next > $this->page_total) ? ' disabled' : '' ?>"><a class="page-link" href="/recipe-resources?q=<?php echo $this->search_query; ?><?php echo (!empty($this->page_next) ? '&amp;p=' . $this->page_next : ''); ?><?php echo ($this->videos_only) ? '&amp;video=true' : ''; ?>">Next Page</a></li>
								</ul>
							</nav>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
		<?php } ?>

		<!-- Wine Pairing Section
		<section>
				<div class="container border-green border-top">
					<div class="row my-5">
						<div class="col-md-5 mb-6">
							<div>
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken_and_shrimp_carbonara_family_460x235.jpg" alt="Shrimp and Chicken Carbonara" class="img-fluid mb-4" />
							</div>
						</div>
						<div class="col-md-7 text-left">
						  <h2><strong>Want the perfect wine to pair with your Dream Dinners?</strong></h2>
							<p><strong><a href="https://blog.dreamdinners.com/tag/drink-pairings/" target="_blank">Visit our blog</a></strong> to see pairing suggestions from Chef Laura for 7Cellars Wine.</p>
							<p><strong><a href="https://www.7cellars.com/discount/DREAMDINNERS" target="_blank">Visit 7Cellars</a></strong> and use code DREAMDINNERS to get 40% off your purchase for a limited time.</p>
						</div>
				</div>
			  </div>
			</section> -->
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>