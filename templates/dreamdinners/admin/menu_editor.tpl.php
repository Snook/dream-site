<?php $this->setScript('foot', SCRIPT_PATH . '/admin/menu_editor.min.js'); ?>
<?php $this->assign('page_title', 'Menu Editor'); ?>
<?php $this->assign('topnav', 'store'); ?>
<?php $this->setScriptVar('menu_id = ' . $this->DAO_menu->id . ';'); ?>
<?php $this->assign('helpLinkSection', 'ME'); ?>

<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<form name="menu_editor_form" id="menu_editor_form" method="post" class="needs-validation" novalidate>
			<input type="hidden" name="action" id="action" value="none">
			<?php echo $this->form['hidden_html']; ?>

			<div class="row my-4">
				<div class="col-4">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Menu
							</div>
						</div>
						<?php echo $this->form['menu_html']; ?>
					</div>
				</div>
				<div class="col-4 text-center">
					<h1><a href="/backoffice/menu-editor">Menu Editor</a></h1>
				</div>
				<div class="col-4 text-right">
					<a href="/backoffice/menu-inventory-mgr" class="btn btn-primary btn-sm">Inventory Manager</a>
				</div>
			</div>

			<div class="row mb-4">

				<div class="col-6">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Store link
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?><?php echo $this->CurrentBackOfficeStore->getPrettyUrl(); ?>/order" readonly>
					</div>
				</div>

				<div class="col-6">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Menu link
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?><?php echo $this->CurrentBackOfficeStore->getPrettyUrl(); ?>/order/<?php echo $this->DAO_menu->menu_name_abbr; ?>" readonly>
					</div>
				</div>

			</div>

			<div class="row menu-editor-unsaved-alert collapse">
				<div class="col">
					<div class="alert alert-danger">
						<div class="row">
							<div class="col-6 font-weight-bold font-size-medium-small">
								Your changes have not yet been saved.
							</div>
							<div class="col-6 text-right">
								<span class="btn btn-primary menu-editor-finalize">Finalize All Changes</span>
								<span class="btn btn-primary menu-editor-reset"><i class="fas fa-undo"></i> Reset to Current</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col">

					<nav>
						<div class="nav nav-tabs" id="nav-tab" role="tablist">
							<a class="nav-link active nav-tab" id="nav-specials-tab" data-nav="specials" data-toggle="tab" href="#nav-specials" role="tab" aria-controls="nav-specials" aria-selected="true">Core</a>
							<a class="nav-link nav-tab" id="nav-efl-tab" data-nav="efl" data-toggle="tab" href="#nav-efl" role="tab" aria-controls="nav-efl" aria-selected="false">Extended Fast Lane</a>
							<a class="nav-link nav-tab" id="nav-sides-tab" data-nav="sides" data-toggle="tab" href="#nav-sides" role="tab" aria-controls="nav-sides" aria-selected="false">Sides &amp; Sweets</a>
							<a class="nav-link nav-tab" id="nav-sides-hidden-tab" data-nav="sides-hidden" data-toggle="tab" href="#nav-sides-hidden" role="tab" aria-controls="nav-sides-hidden" aria-selected="false">Sides &amp; Sweets - Hidden</a>
							<a class="nav-link nav-tab" id="nav-pricing-tab" data-nav="pricing" data-toggle="tab" href="#nav-pricing" role="tab" aria-controls="nav-pricing" aria-selected="false">Pricing Reference</a>
						</div>
					</nav>

					<div class="tab-content bg-white" id="nav-tabContent">
						<div class="tab-pane fade show active" id="nav-specials" role="tabpanel" aria-labelledby="nav-specials-tab">
							<table class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on customer menu</th>
									<th class="align-middle">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Size</th>
									<th class="align-middle">Current Price</th>
									<th class="align-middle">Price</th>
									<th class="align-middle">Remaining Inventory</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->menuItemArray[CMenuItem::CORE])) { ?>

									<?php $lastEntreeID = false; foreach ($this->menuItemArray[CMenuItem::CORE] as $DAO_menu_item) { ?>

										<tr id="row_<?php echo $DAO_menu_item->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
											<td class="align-middle">
												<?php echo $this->form[$DAO_menu_item->id . '_vis_html']; ?>
											</td>

											<td class="align-middle text-left">
												<a href="/item?recipe=<?php echo $DAO_menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->DAO_menu->id; ?>" class="link-dinner-details" data-tooltip="Dinner Details"
												   data-recipe_id="<?php echo $DAO_menu_item->recipe_id; ?>" data-store_id="<?php echo $this->CurrentBackOfficeStore->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>" data-menu_id="<?php echo $this->DAO_menu->id; ?>" data-size="large" data-detailed="true"
												   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
												<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $DAO_menu_item->id . ' &bull; Recipe ID: ' . $DAO_menu_item->recipe_id . '"' : ''; ?>>
														<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id; ?>)
													</span>
												<?php if (!empty($DAO_menu_item->is_bundle)) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($DAO_menu_item->admin_notes)) ? $DAO_menu_item->admin_notes : 'Meal bundle' ?>"></i><?php } ?>
												<?php if (!empty($this->CurrentBackOfficeStore->supports_ltd_roundup) && $DAO_menu_item->ltd_menu_item_value) { ?><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/menu-icon07.png" class="img_valign" data-tooltip="$1 is added to price to be donated to DDF" /><?php } ?>
											</td>

											<td class="align-middle text-left">
												<?php echo $DAO_menu_item->pricing_type_info['pricing_type_name_short_w_qty'] ; ?>
											</td>

											<td class="align-middle <?php if (!empty($this->CurrentBackOfficeStore->supports_ltd_roundup) && $DAO_menu_item->ltd_menu_item_value) { ?>text-orange font-weight-bold" data-tooltip="$1 is added to price to be donated to DDF<?php } ?>">
												<?php echo CTemplate::moneyFormat($DAO_menu_item->getStorePrice()); ?>
											</td>

											<td class="align-middle">
												<div class="input-group flex-nowrap">
													<?php echo $this->form[$DAO_menu_item->id . '_ovr_html']; ?>
													<div class="input-group-append">
														<span class="ovr-alert-danger input-group-text collapse" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
															<i class="fas fa-exclamation-triangle text-danger" data-toggle="tooltip" data-placement="top" title="Price outside highest tier price <?php echo !empty($DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price) ? ' of ' . $DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price : ''; ?> and lowest tier price<?php echo !empty($DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price) ? '  of ' . $DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price : ''; ?>"></i>
														</span>
														<span class="ovr-alert-warning input-group-text collapse" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
															<i class="fas fa-exclamation-circle text-warning" data-toggle="tooltip" data-placement="top" title="Recommended pricing ends with .49 or .99"></i>
														</span>
													</div>
												</div>
											</td>

											<td class="align-middle">
												<?php echo $DAO_menu_item->override_inventory - $DAO_menu_item->number_sold ?>
											</td>
										</tr>
									<?php } ?>
								<?php } ?>

								</tbody>
							</table>

						</div>
						<div class="tab-pane fade" id="nav-efl" role="tabpanel" aria-labelledby="nav-efl-tab">

							<div class="col text-right py-2">
								<span id="add_past_menu_item" data-menu_id="<?php echo $this->DAO_menu->id; ?>" class="btn btn-primary">Add EFL Menu Item</span>
							</div>

							<table class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on customer menu</th>
									<th class="align-middle">Show on Sides &amp; Sweets Forms</th>
									<th class="align-middle">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Size</th>
									<th class="align-middle">Current Price</th>
									<th class="align-middle">Price</th>
									<th class="align-middle">Remaining Inventory</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->menuItemArray[CMenuItem::EXTENDED])) { ?>
									<?php $lastEntreeID = false; foreach ($this->menuItemArray[CMenuItem::EXTENDED] as $DAO_menu_item) { ?>

										<tr id="row_<?php echo $DAO_menu_item->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">

											<td class="align-middle">
												<?php echo $this->form[$DAO_menu_item->id . '_vis_html']; ?>
											</td>

											<td class="align-middle">
												<?php echo $this->form[$DAO_menu_item->id . '_pic_html']; ?>
											</td>

											<td class="align-middle text-left">
												<a href="/item?recipe=<?php echo $DAO_menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->DAO_menu->id; ?>" class="link-dinner-details" data-tooltip="Dinner Details"
												   data-recipe_id="<?php echo $DAO_menu_item->recipe_id; ?>" data-store_id="<?php echo $this->CurrentBackOfficeStore->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>" data-menu_id="<?php echo $this->DAO_menu->id; ?>" data-size="large" data-detailed="true"
												   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
												<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $DAO_menu_item->id . ' &bull; Recipe ID: ' . $DAO_menu_item->recipe_id . '"' : ''; ?>>
														<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id; ?>)
													</span>
												<?php if (!empty($DAO_menu_item->is_bundle)) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($DAO_menu_item->admin_notes)) ? $DAO_menu_item->admin_notes : 'Meal bundle' ?>"></i><?php } ?>
											</td>

											<td class="align-middle">
												<?php echo $DAO_menu_item->pricing_type_info['pricing_type_name_short_w_qty']; ?>
											</td>

											<td class="align-middle">
												<?php echo $DAO_menu_item->getStorePrice(); ?>
											</td>

											<td class="align-middle">
												<div class="input-group flex-nowrap">
													<?php echo $this->form[$DAO_menu_item->id . '_ovr_html']; ?>
													<div class="input-group-append">
														<span class="ovr-alert-danger input-group-text collapse" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
															<i class="fas fa-exclamation-triangle text-danger" data-toggle="tooltip" data-placement="top" title="Price outside highest tier price <?php echo !empty($DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price) ? ' of ' . $DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price : ''; ?> and lowest tier price<?php echo !empty($DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price) ? '  of ' . $DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price : ''; ?>"></i>
														</span>
														<span class="ovr-alert-warning input-group-text collapse" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
															<i class="fas fa-exclamation-circle text-warning" data-toggle="tooltip" data-placement="top" title="Recommended pricing ends with .49 or .99"></i>
														</span>
													</div>
												</div>
											</td>

											<td class="align-middle">
												<?php echo $DAO_menu_item->override_inventory - $DAO_menu_item->number_sold ?>
											</td>

										</tr>
									<?php } ?>
								<?php } else { ?>
									<tr id="empty_menu_message">
										<td colspan="12" class="text-center font-weight-bold py-4">
											No Extended Fast Lane items have been added to this menu. Click the "Add EFL Menu Item" button to add an item</span>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>

						</div>
						<div class="tab-pane fade" id="nav-sides" role="tabpanel" aria-labelledby="nav-sides-tab">
							<div class="col text-right py-2" id="CTS_default_price_control">
								<button type="button" class="btn btn-primary sides-sweets-save">Save Sides &amp; Sweets Defaults</button>
								<button type="button" class="btn btn-primary sides-sweets-retrieve">Retrieve Sides &amp; Sweets Defaults</button>
							</div>

							<table class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on customer menu</th>
									<th class="align-middle">Show on Sides &amp; Sweets Forms</th>
									<th class="align-middle">Hide Item Everywhere</th>
									<th class="align-middle">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Size</th>
									<th class="align-middle">Current Price</th>
									<th class="align-middle">Price</th>
									<th class="align-middle">Remaining Inventory</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->menuItemArray[CMenuItem::SIDE]['not_hidden'])) { ?>
									<?php $subcategory = false; foreach ($this->menuItemArray[CMenuItem::SIDE]["not_hidden"] as $id => $DAO_menu_item) { ?>
										<?php if ($subcategory != $DAO_menu_item->subcategory_label) { $subcategory = $DAO_menu_item->subcategory_label; ?>
											<tr>
												<td colspan="8" class="font-weight-bold py-3">
													<?php echo $DAO_menu_item->subcategory_label; ?>
												</td>
											</tr>
										<?php } ?>

										<?php include $this->loadTemplate('admin/subtemplate/menu_editor/menu_editor_row_side.tpl.php'); ?>

									<?php } ?>
								<?php } else { ?>
									<tr>
										<td colspan="11" class="text-center font-weight-bold py-4">There are no Sides &amp; Sweets items for this menu</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>

						</div>

						<div class="tab-pane fade" id="nav-sides-hidden" role="tabpanel" aria-labelledby="nav-sides-hidden-tab">
							<div class="col text-right py-2" id="CTS_default_price_control">
								<?php if ($this->DAO_menu->isEnabled_Add_Sides_and_EFL()) {?>
									<span id="add_past_menu_item_sides" data-menu_id="<?php echo $this->DAO_menu->id; ?>" class="btn btn-primary <?php if (!$this->DAO_menu->isEnabled_Add_Sides_and_EFL()) { ?>disabled<?php } ?>">Add Sides & Sweets Menu Item</span>
								<?php } ?>
								<button type="button" class="btn btn-primary sides-sweets-save">Save Sides &amp; Sweets Defaults</button>
								<button type="button" class="btn btn-primary sides-sweets-retrieve">Retrieve Sides &amp; Sweets Defaults</button>
							</div>

							<table class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on customer menu</th>
									<th class="align-middle">Show on Sides &amp; Sweets Forms</th>
									<th class="align-middle">Hide Item Everywhere</th>
									<th class="align-middle">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Size</th>
									<th class="align-middle">Current Price</th>
									<th class="align-middle">Price</th>
									<th class="align-middle">Remaining Inventory</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->menuItemArray[CMenuItem::SIDE]['hidden'])) { ?>
									<?php $subcategory = false; foreach ($this->menuItemArray[CMenuItem::SIDE]["hidden"] as $id => $DAO_menu_item) { ?>
										<?php if ($subcategory != $DAO_menu_item->subcategory_label) { $subcategory = $DAO_menu_item->subcategory_label; ?>
											<tr>
												<td colspan="8" class="font-weight-bold py-3">
													<?php echo $DAO_menu_item->subcategory_label; ?>
												</td>
											</tr>
										<?php } ?>

										<?php include $this->loadTemplate('admin/subtemplate/menu_editor/menu_editor_row_side.tpl.php'); ?>

									<?php } ?>
								<?php } else { ?>
									<tr>
										<td colspan="11" class="text-center font-weight-bold py-4">There are no Sides &amp; Sweets items for this menu</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>

						</div>

						<div class="tab-pane fade" id="nav-pricing" role="tabpanel" aria-labelledby="nav-pricing-tab">
							<br/>

							<table class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle text-left"></th>
									<th class="align-middle <?php echo (($this->CurrentBackOfficeStore->core_pricing_tier == 1) ? 'bg-green-light' : '' ); ?>" colspan="2">Tier 1</th>
									<th class="align-middle <?php echo (($this->CurrentBackOfficeStore->core_pricing_tier == 2) ? 'bg-green-light' : '' ); ?>" colspan="2">Tier 2</th>
									<th class="align-middle <?php echo (($this->CurrentBackOfficeStore->core_pricing_tier == 3) ? 'bg-green-light' : '' ); ?>" colspan="2">Tier 3</th>
								</tr>
								<tr>
									<th class="align-middle text-left">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Medium</th>
									<th class="align-middle">Large</th>
									<th class="align-middle">Medium</th>
									<th class="align-middle">Large</th>
									<th class="align-middle">Medium</th>
									<th class="align-middle">Large</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->pricingReferenceArray)) { $category = false; $sub_category = false; ?>
									<?php foreach ($this->pricingReferenceArray AS $DAO_menu_item) { ?>
										<?php if ($category != $DAO_menu_item->category_group) { $category = $DAO_menu_item->category_group; ?>
											<tr>
												<th colspan="7"><?php echo $DAO_menu_item->categoryGroupName(); ?></th>
											</tr>
										<?php } ?>
										<?php if ($DAO_menu_item->isMenuItem_SidesSweets() && $sub_category != $DAO_menu_item->subcategory_label) { $sub_category = $DAO_menu_item->subcategory_label; ?>
											<tr>
												<th colspan="7"><?php echo $DAO_menu_item->subcategory_label; ?></th>
											</tr>
										<?php } ?>
										<?php if (!empty($DAO_menu_item->pricing_tiers)) { ?>
											<tr>
												<td class="text-left">
													<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id; ?>)
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers['1'][CMenuItem::HALF])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['1'][CMenuItem::HALF]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers['1'][CMenuItem::FULL])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['1'][CMenuItem::FULL]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers['2'][CMenuItem::HALF])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['2'][CMenuItem::HALF]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers['2'][CMenuItem::FULL])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['2'][CMenuItem::FULL]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers['3'][CMenuItem::HALF])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['3'][CMenuItem::HALF]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers['3'][CMenuItem::FULL])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['3'][CMenuItem::FULL]->price; ?>
													<?php } ?>
												</td>
											</tr>
										<?php } ?>
									<?php } ?>
								<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="row menu-editor-unsaved-alert collapse">
				<div class="col">
					<div class="alert alert-danger">
						<div class="row">
							<div class="col-6 font-weight-bold font-size-medium-small">
								Your changes have not yet been saved.
							</div>
							<div class="col-6 text-right">
								<span class="btn btn-primary menu-editor-finalize">Finalize All Changes</span>
								<span class="btn btn-primary menu-editor-reset"><i class="fas fa-undo"></i> Reset to Current</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-7">
					<ul class="list-unstyled">
						<li><i class="fas fa-exclamation-triangle text-danger" data-toggle="tooltip" data-placement="top" title="Price outside highest and lowest tier prices"></i> Price outside highest and lowest tier prices</li>
						<li><i class="fas fa-exclamation-circle text-warning" data-toggle="tooltip" data-placement="top" title="Recommended pricing ends with .49 or .99"></i> Recommended pricing ends with .49 or .99</li>
					</ul>
				</div>
			</div>

		</form>
	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>