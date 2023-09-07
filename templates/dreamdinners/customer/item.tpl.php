<?php $this->setScriptVar('itemRecipeID = ' . $this->menuItemArray['entree']->DAO_recipe->id . ';'); ?>
<?php $this->assign('itemscope', 'Recipe'); ?>
<?php $this->assign('og_title', $this->menuItemArray['entree']->menu_item_name); ?>
<?php $this->assign('og_url', HTTPS_BASE . 'main.php?page=item&recipe=' . $this->menuItemArray['entree']->recipe_id); ?>
<?php $this->assign('og_image', HTTPS_BASE . RELATIVE_IMAGES_PATH . '/recipe/' . $this->menuItemArray['entree']->menuItemImagePath() . '/' . $this->menuItemArray['entree']->recipe_id . '.webp'); ?>
<?php $this->assign('og_description', trim($this->menuItemArray['entree']->menu_item_description)); ?>
<?php $this->assign('canonical_url', HTTPS_BASE . 'main.php?page=item&recipe=' . $this->menuItemArray['entree']->recipe_id); ?>
<?php $this->assign('page_title', $this->menuItemArray['entree']->menu_item_name); ?>
<?php $this->assign('page_description', trim($this->menuItemArray['entree']->menu_item_description)); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Dinner Details</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main>
		<div class="container">
			<div class="row mb-4">
				<div class="col-lg-6 col-md-12 p-0">
					<img class="img-fluid" alt="<?php echo htmlspecialchars($this->menuItemArray['entree']->menu_item_name); ?>" src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo $this->menuItemArray['entree']->menuItemImagePath(); ?>/<?php echo $this->menuItemArray['entree']->recipe_id; ?>.webp" />
					<?php if ($this->menuItemArray['entree']->icons['cooking_instruction_youtube_id']['meal_detail_enabled'] && $this->menuItemArray['entree']->icons['cooking_instruction_youtube_id']['show']) { ?>
						<div class="row mt-2">
							<div class="col-12 col-lg-6">
								<span class="btn btn-primary play-video"><i class="dd-icon icon-video font-size-medium align-middle pr-2"></i>Instructional video</span>
							</div>
							<div class="col-12 col-lg-6 pt-2">

							</div>
						</div>
					<?php } ?>
				</div>
				<div class="col-lg-6 col-md-12 pl-lg-5">

					<div class="row">
						<div class="col pt-3 pt-lg-0">
							<h3>
								<?php echo $this->menuItemArray['entree']->menu_item_name; ?>
							</h3>

							<p>
								<?php if (!empty($this->menuItemArray['entree']->menu_label)) { ?>
									<span class="font-weight-bold"><?php echo $this->menuItemArray['entree']->menu_label; ?></span>
								<?php } ?>
								<?php echo trim($this->menuItemArray['entree']->menu_item_description); ?>
							</p>
							<div class="row mb-3">
								<div class="col col-md-5 mb-2 text-center text-md-left">
									<?php foreach ($this->menuItemArray['entree']->icons AS $icon) { ?>
										<?php if ($icon['meal_detail_enabled'] && $icon['show']) { ?>
											<i class="font-size-medium-large dd-icon <?php echo $icon['css_icon']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $icon['tooltip']; ?>"></i>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>

					<div class="mb-4">
						<div class="col">
							<a href="/?page=session_menu" class="btn btn-primary btn-block btn-lg">Get Started</a>
						</div>
					</div>

				</div>

			</div>

			<?php if (!empty($this->menuItemArray['entree']->ltd_menu_item_supported) && !empty($this->menuItemArray['entree']->ltd_menu_item_value)) { ?>
				<?php include $this->loadTemplate('customer/subtemplate/item/item_ltd_program_info.tpl.php'); ?>
			<?php } ?>


			<nav class="row pt-4">
				<div class="col nav nav-pills nav-fill pr-0" id="dishDetails" role="tablist">
					<a class="nav-item nav-link col-6 text-uppercase font-weight-bold active" id="cooking-tab" data-urlpush="true" data-toggle="tab" data-target="#cooking" href="/?page=item&amp;recipe=<?php echo $this->menuItemArray['entree']->recipe_id; ?>&amp;tab=cooking" role="tab" aria-controls="cooking" aria-selected="true">Instructions</a>
					<a class="nav-item nav-link col-6 text-uppercase font-weight-bold" id="nutrition-tab" data-urlpush="true" data-toggle="tab" data-target="#nutrition" href="/?page=item&amp;recipe=<?php echo $this->menuItemArray['entree']->recipe_id; ?>&amp;tab=nutrition" role="tab" aria-controls="nutrition" aria-selected="true">Nutrition</a>
				</div>
			</nav>

			<div class="tab-content" id="dishDetailsContent">

				<div class="tab-pane fade show active" id="cooking" role="tabpanel" aria-labelledby="cooking-tab">

					<div class="row">
						<div class="col pt-4">
							<?php foreach ($this->menuItemArray['menu_item'] AS $DAO_menu_item) { ?>
								<?php if ($DAO_menu_item->instructions) { ?>
									<p><span class="font-weight-bold"><?php if (!$DAO_menu_item->isMenuItem_SidesSweets()) { ?><?php echo $DAO_menu_item->pricing_type_info['pricing_type_name']; ?><?php } ?> Prep time:</span> <?php echo $DAO_menu_item->prep_time; ?></p>
									<h4 class="mt-4"><?php if (!$DAO_menu_item->isMenuItem_SidesSweets()) { ?><?php echo $DAO_menu_item->pricing_type_info['pricing_type_name']; ?><?php } ?> Instructions</h4>
									<p><?php echo nl2br($DAO_menu_item->instructions); ?></p>
								<?php } ?>

								<?php if ($DAO_menu_item->instructions_air_fryer) { ?>
									<h4 class="mt-4"><i class="font-size-medium-large dd-icon icon-air-fryer text-gray-dark align-middle" data-toggle="tooltip" data-placement="top" title="Air Fryer Option"></i> <?php if (!$DAO_menu_item->isMenuItem_SidesSweets()) { ?><?php echo $DAO_menu_item->pricing_type_info['pricing_type_name']; ?><?php } ?> Air Fryer Instructions</h4>
									<p><?php echo nl2br($DAO_menu_item->instructions_air_fryer); ?></p>
								<?php } ?>

								<?php if ($DAO_menu_item->instructions_crock_pot) { ?>
									<h4 class="mt-4"><i class="font-size-medium-large dd-icon icon-instant-pot text-gray-dark align-middle" data-toggle="tooltip" data-placement="top" title="Crock-Pot/Instant Pot Option"></i> <?php if (!$DAO_menu_item->isMenuItem_SidesSweets()) { ?><?php echo $DAO_menu_item->pricing_type_info['pricing_type_name']; ?><?php } ?> Crock-Pot/Instant Pot Instructions</h4>
									<p><?php echo nl2br($DAO_menu_item->instructions_crock_pot); ?></p>
								<?php } ?>

								<?php if ($DAO_menu_item->instructions_grill) { ?>
									<h4 class="mt-4"><i class="font-size-medium-large dd-icon icon-grill text-gray-dark align-middle" data-toggle="tooltip" data-placement="top" title="Grill Option"></i> <?php if (!$DAO_menu_item->isMenuItem_SidesSweets()) { ?><?php echo $DAO_menu_item->pricing_type_info['pricing_type_name']; ?><?php } ?> Grill Instructions</h4>
									<p><?php echo nl2br($DAO_menu_item->instructions_grill); ?></p>
								<?php } ?>

							<?php } ?>

							<?php if (!empty($this->menuItemArray['entree']->serving_suggestions)) { ?>
								<h4 class="mt-4">Serving Suggestions</h4>
								<p><?php echo $this->menuItemArray['entree']->serving_suggestions; ?></p>
							<?php } ?>
						</div>
					</div>

				</div>

				<div class="tab-pane fade" id="nutrition" role="tabpanel" aria-labelledby="nutrition-tab">

					<div class="row pt-5">

						<?php foreach ($this->menuItemArray['nutrition_data'] as $thisCompNum => $thisComp) { ?>

							<div class="col-lg-4">

								<table class="table table-sm table-hover">
									<thead>
									<tr>
										<th colspan="2" scope="col"><span class="font-size-large">Nutrition Facts</span></th>
									</tr>
									<tr>
										<td colspan="2" scope="col">
											<div class="row font-weight-bold">
												<div class="col">
													Serving size
												</div>
												<div class="col text-right">
													<div><?php echo ucfirst($thisComp['info']['serving']); ?></div>
												</div>
											</div>
										</td>
									</tr>
									<tr>
										<th colspan="2" scope="col">
											<div class="row">
												<div class="col">
													<div>Amount per serving</div>
												</div>
											</div>
											<div class="row">
												<div class="col font-size-medium">
													<?php echo $thisComp['element']['Calories']['label']; ?>
												</div>
												<div class="col text-right font-size-large">
													<div><?php echo $thisComp['element']['Calories']['value']; ?></div>
												</div>
											</div>
										</th>
									</tr>
									<tr>
										<th colspan="2" scope="col" class="text-right">% Daily Value*</th>
									</tr>
									</thead>
									<tbody>
									<?php foreach ($thisComp['element'] as $nutriLabel => $nutriData) { ?>
										<?php if ($nutriLabel != 'Calories') { include $this->loadTemplate('customer/subtemplate/item/item_recipe_nutrition_element.tpl.php'); } ?>
									<?php } ?>
									</tbody>
									<tfoot>
									<tr>
										<td colspan="2" scope="col" class="font-size-small">
											* The % Daily Value (DV) tells you how much a nutrient in a serving of food contributes to a daily diet. 2,000 calories a day is used for general nutrition advice.
										</td>
									</tr>
									</tfoot>
								</table>

							</div>

						<?php } ?>

						<div class="col-lg-8">
							<?php if (!empty($this->menuItemArray['entree']->DAO_recipe->allergens)) { ?>
								<p class="mb-0">Contains: <?php echo $this->menuItemArray['entree']->DAO_recipe->allergens; ?></p>
							<?php } ?>
							<p>May Contain: Milk, Eggs, Fish, Shellfish, Tree Nuts, Peanuts, Wheat, Soybeans, Sesame.</p>

							<?php if(!empty($this->menuItemArray['entree']->DAO_recipe->ingredients)){ ?>
								<p>Ingredients: <?php echo $this->menuItemArray['entree']->DAO_recipe->ingredients; ?></p>
							<?php } ?>

							<p>Nutritional information is per serving unless otherwise stated and are based on standard formulations. For example, if one Large dinner was divided into 6 equal portions, the nutritional information is accurate for one portion. Variations in ingredients and preparation, as well as substitutions, will increase or decrease any stated nutritional values. Items vary by store, and are subject to change. Contact your local store for further assistance.</p>
						</div>

					</div>

				</div>

				<?php if ($this->menuItemArray['entree']->icons['cooking_instruction_youtube_id']['meal_detail_enabled'] && $this->menuItemArray['entree']->icons['cooking_instruction_youtube_id']['show']) { ?>
					<div class="tab-pane fade" id="video" role="tabpanel" aria-labelledby="video-tab">

						<div class="row pt-4">
							<div class="col">

								<div class="embed-responsive embed-responsive-16by9">
									<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/<?php echo $this->menuItemArray['entree']->icons['cooking_instruction_youtube_id']['value']; ?>?rel=0" allowfullscreen></iframe>
								</div>

							</div>
						</div>

					</div>
				<?php } ?>

			</div>

		</div>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>