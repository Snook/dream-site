<?php $this->setScript('head', SCRIPT_PATH . '/admin/menu_editor.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.uitablefilter.js'); ?>
<?php $this->assign('page_title', 'Menu Editor'); ?>
<?php $this->assign('topnav', 'store'); ?>
<?php $this->assign('helpLinkSection', 'ME'); ?>
<?php $this->setScriptVar('limitToInventoryControl = ' . ($this->limitToInventoryControl ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('storeSupportsPlatePoints = ' . ($this->storeSupportsPlatePoints ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('storeInfo = ' . json_encode($this->DAO_store->toArray()) . ';'); ?>
<?php $this->setScriptVar('markupData = ' . json_encode($this->markupData) . ';'); ?>
<?php $this->setScriptVar('menuInfo = ' . $this->menuInfoJS . ';'); ?>
<?php $this->setScriptVar('isEnabled_Markup = ' . ($this->DAO_menu->isEnabled_Markup() ? 'true' : 'false') . ';'); ?>

<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col-4 offset-4 text-center ">
				<h1><a href="/?page=admin_menu_editor">Menu Editor</a></h1>
			</div>
			<div class="col-4 text-right">
				<a href="/?page=admin_menu_inventory_mgr&tabs=menu.specials" class="btn btn-primary btn-sm" id="inv-nav-button">Inventory Manager</a>
			</div>
		</div>

		<div id="markdown_editor" class="collapse">
			<h3 id="item_title"></h3><br />
			Enter a percentage from 1% to 20% or a target price that is equivalent to 20% or less of the current price. The menu item will be marked down (from the marked up price) by the amount entered.<br /><br />

			<div style="float:left">
				<span class="font-weight-bold">Enter Percentage</span><br />
				<input style="margin-bottom:5px;" type="text" id="markdown_amount" name="markdown_amount"  value ="" /><br />
				Current Price: <span id="current_price"></span><br />
				Base Price: <span id="base_price"></span><br />
			</div>
			<div style="float:right">
				<span class="font-weight-bold">or Enter Price</span></br />
				<input style="margin-bottom:5px;" type="text" id="markdown_price" name="markdown_price"  value ="" /><br />
			</div>
		</div>

		<form name="menu_editor_form" id="menu_editor_form" method="post" class="needs-validation" novalidate>
			<input type="hidden" name="action" id="action" value="none">
			<?php echo $this->form['hidden_html' ]; ?>

			<div class="form-row">
				<div class="form-group col-4">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Menu
							</div>
						</div>
						<?php echo $this->form['menus_html']; ?>
					</div>
				</div>
				<?php if (isset($this->form['store_html'])) { ?>
					<div class="form-group col-8 pr-2 text-right">

						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									Store
								</div>
							</div>
							<?php echo $this->form['store_html']; ?>
						</div>

					</div>
				<?php } ?>
			</div>

			<div class="form-row">

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Menu link
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?><?php echo $this->DAO_store->getPrettyUrl(); ?>/order/<?php echo $this->menuInfo['menu_name_abbr']; ?>" readonly>
					</div>

				</div>

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Store link
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?><?php echo $this->DAO_store->getPrettyUrl(); ?>/order" readonly>
					</div>

				</div>

			</div>

			<?php if ($this->DAO_store->storeSupportsIntroOrders($this->DAO_menu->id)) { ?>
			<div class="form-row">

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Menu starter
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?><?php echo $this->DAO_store->getPrettyUrl(); ?>/order/<?php echo $this->menuInfo['menu_name_abbr']; ?>/starter" readonly>
					</div>

				</div>

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Store starter
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?><?php echo $this->DAO_store->getPrettyUrl(); ?>/order/starter" readonly>
					</div>

				</div>

			</div>
			<?php } ?>

			<div class="form-row">
				<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
					<div class="form-group col-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									Markup Lg (6) %
								</div>
							</div>
							<?php echo $this->form['markup_6_serving_html']; ?>
						</div>
					</div>

					<?php if (false && CStore::isCoreTestStore($this->DAO_store->id, $this->menuInfo['menu_id'])) { ?>
						<div class="form-group col-4">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text">
										Markup Md (4) %
									</div>
								</div>
								<?php echo $this->form['markup_4_serving_html']; ?>
							</div>
						</div>
					<?php } ?>

					<div class="form-group col-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									Markup Md (3) %
								</div>
							</div>
							<?php echo $this->form['markup_3_serving_html']; ?>
						</div>
					</div>

					<?php if (false && CStore::isCoreTestStore($this->DAO_store->id, $this->menuInfo['menu_id'])) { ?>
						<div class="form-group col-4">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text">
										Markup Sm (2) %
									</div>
								</div>
								<?php echo $this->form['markup_2_serving_html']; ?>
							</div>
						</div>
					<?php } ?>

				<?php } ?>

				<div class="form-group col-4">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Markup Sides %
							</div>
						</div>
						<?php echo $this->form['markup_sides_html']; ?>
					</div>
				</div>


			</div>

			<div class="form-row">

				<?php if (!$this->DAO_order_minimum->isZeroDollarAssembly() && $this->allow_assembly_fee) { ?>

					<div class="form-group col">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									Delivery Assembly Fee $
								</div>
							</div>
							<?php echo $this->form['delivery_assembly_fee_html']; ?>
						</div>
					</div>

					<div class="form-group col">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									Assembly Fee $
								</div>
							</div>
							<?php echo $this->form['assembly_fee_html']; ?>
						</div>
					</div>

				<?php } ?>

				<div class="form-group col-4">
					<div class="input-group">
						<div class="input-group-prepend mr-2">
							<div class="input-group-text">
								Save as Default
							</div>
						</div>
						<div class="form-check form-check-inline">
							<?php echo $this->form['is_default_markup_html']['yes']; ?>
							<label class="form-check-label" for="is_default_markupyes">Yes</label>
						</div>

						<div class="form-check form-check-inline">
							<?php echo $this->form['is_default_markup_html']['no']; ?>
							<label class="form-check-label" for="is_default_markupno">No</label>
						</div>
					</div>
				</div>

			</div>

			<div class="form-row">
				<div class="form-group col text-right">

					<input name="submit_changes" id="submit_changes" type="button" value="Finalize All Changes" class="btn btn-primary" onclick="confirm_and_check_form()" />
					<input type="button" value="Reset to Current" onclick="resetPage();" class="btn btn-primary" />
					<div id="saved_message" class="text-danger font-weight-bold collapse">Your changes have not yet been saved. Finalize all changes to see updated values.</div>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col">

					<nav>
						<div class="nav nav-tabs" id="nav-tab" role="tablist">
							<a class="nav-link active nav-tab" id="nav-specials-tab" data-nav="specials" data-toggle="tab" href="#nav-specials" role="tab" aria-controls="nav-specials" aria-selected="true">Core</a>
							<a class="nav-link nav-tab" id="nav-efl-tab" data-nav="efl" data-toggle="tab" href="#nav-efl" role="tab" aria-controls="nav-efl" aria-selected="false">Extended Fast Lane</a>
							<a class="nav-link nav-tab" id="nav-sides-tab" data-nav="sides" data-toggle="tab" href="#nav-sides" role="tab" aria-controls="nav-sides" aria-selected="false">Sides &amp; Sweets</a>
							<a class="nav-link nav-tab" id="nav-pricing-tab" data-nav="pricing" data-toggle="tab" href="#nav-pricing" role="tab" aria-controls="nav-pricing" aria-selected="false">Pricing Reference</a>
						</div>
					</nav>

					<div class="tab-content bg-white" id="nav-tabContent">
						<div class="tab-pane fade show active" id="nav-specials" role="tabpanel" aria-labelledby="nav-specials-tab">
							<?php if ($this->DAO_menu->isEnabled_MarkupRoundUp()) { ?>
								<div class="col text-right py-2">
									<input name="submit_rounding" id="submit_rounding" type="button" value="Round Markup Price" class="btn btn-primary" onclick="confirm_and_round_form('core')" data-tooltip="Set the Override price from the Markup price rounded-up to nearest 50 cents. This will overwrite the existing value, if any."/>
								</div>
							<?php } ?>

							<table id="itemsTbl" class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Hide on customer menu</th>
									<th class="align-middle">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Size</th>
									<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
										<th class="align-middle">Base Price</th>
									<?php } ?>
									<th class="align-middle">Current Price</th>
									<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
										<th class="align-middle">Markup Price</th>
									<?php } ?>
									<th class="align-middle">Override Price</th>
									<th class="align-middle">Preview Price</th>
									<th class="align-middle">Remaining Inventory</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->menuInfo['Specials'])) { ?>

									<?php $lastEntreeID = false; $tabindex = 0; foreach ($this->menuInfo['Specials'] as $planNode) { ?>
										<?php if (is_array($planNode) && $planNode['pricing_type'] != CMenuItem::INTRO && !($planNode['is_side_dish'] && $planNode['pricing_type'] == CMenuItem::HALF)) { ?>
											<tr id="row_<?php echo $planNode['id' ]; ?>" data-menu_item_id="<?php echo $planNode['id' ]; ?>">
												<td class="align-middle">
													<?php if ($planNode['is_visibility_controllable']) { ?>
														<input data-orgval="<?php echo (!$planNode['is_visible'] ? "CHECKED" : ""); ?>" data-menu_item_id="<?php echo $planNode['id' ]; ?>" id="vis_<?php echo $planNode['id' ]; ?>" name="vis_<?php echo $planNode['id' ]; ?>" type="checkbox" <?php echo (!$planNode['is_visible'] ? "CHECKED" : ""); ?> />
													<?php } ?>
												</td>

												<td class="align-middle text-left">
													<a href="?page=item&amp;recipe=<?php echo $planNode['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id'];?>" class="link-dinner-details" data-tooltip="Dinner Details"
													   data-recipe_id="<?php echo $planNode['recipe_id']; ?>" data-store_id="<?php echo $this->DAO_store->id; ?>" data-menu_item_id="<?php echo $planNode['id']; ?>" data-menu_id="<?php echo $this->menuInfo['menu_id']; ?>"
													   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
													<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $planNode['id'] . ' &bull; Recipe ID: ' . $planNode['recipe_id'] . '"' : ''; ?>>
														<?php echo $planNode['menu_item_name']; ?> (<?php echo $planNode['recipe_id'];?>)
													</span>
													<?php if (!empty($planNode['is_bundle'])) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($planNode['admin_notes'])) ? $planNode['admin_notes'] : 'Meal bundle' ?>"></i><?php } ?>
													<?php if (!empty($this->DAO_store->supports_ltd_roundup) && $planNode['ltd_menu_item_value']) { ?><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/menu-icon07.png" class="img_valign" data-tooltip="$1 is added to price to be donated to DDF" /><?php } ?>
												</td>

												<td class="align-middle text-left">
													<?php echo $planNode['pricing_type_info']['pricing_type_name_short_w_qty'] ;?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle">
														<?php echo $planNode['base_price' ]; ?>
													</td>
												<?php } ?>

												<td class="align-middle <?php if (!empty($this->DAO_store->supports_ltd_roundup) && $planNode['ltd_menu_item_value']) { ?>text-orange font-weight-bold" data-tooltip="$1 is added to price to be donated to DDF<?php } ?>">
													<?php echo CTemplate::moneyFormat($planNode['price']); ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle markup-price">
														<?php echo CTemplate::moneyFormat($planNode['price']); ?>
													</td>
												<?php } ?>

												<td class="align-middle">
													<?php if ($planNode['is_price_controllable']) { ?>
														<input class="form-control form-control-sm no-spin-button core-override-price-input"
															   data-orgval="<?php echo $planNode['override_price' ]; ?>"
															   data-menu_item_id="<?php echo $planNode['id' ]; ?>"
															   data-lowest_tier_price="<?php echo $planNode['pricing_tiers'][1][$planNode['pricing_type']]->price; ?>"
															   data-highest_tier_price="<?php echo $planNode['pricing_tiers'][3][$planNode['pricing_type']]->price; ?>"
															   id="ovr_<?php echo $planNode['id' ]; ?>"
															   <?php if ($this->limitToInventoryControl) { ?>readonly="readonly"<?php } ?>
															   name="ovr_<?php echo $planNode['id' ]; ?>" value="<?php echo $planNode['override_price' ]; ?>"
															   type="number" step="any" size="3" maxlength="6" tabindex="<?php echo ++$tabindex; ?>" />
													<?php } ?>
												</td>

												<td class="align-middle preview-price font-weight-bold text-danger <?php if (!empty($this->DAO_store->supports_ltd_roundup) && $planNode['ltd_menu_item_value']) { ?>text-orange font-weight-bold" data-tooltip="$1 is added to price to be donated to DDF<?php } ?>"></td>

												<?php if ($lastEntreeID != $planNode['entree_id']) { $lastEntreeID = $planNode['entree_id']; ?>
													<td rowspan="<?php echo $planNode['sub_entree_count']; ?>" class="align-middle">
														<?php echo $planNode['override_inventory'] - $planNode['number_sold']  ?>
													</td>
												<?php } ?>
											</tr>
										<?php } ?>
									<?php } ?>
								<?php } ?>
								</tbody>
							</table>

						</div>
						<div class="tab-pane fade" id="nav-efl" role="tabpanel" aria-labelledby="nav-efl-tab">


							<div class="col text-right py-2">
								<?php if (isset($this->canAddEFLItems) && $this->canAddEFLItems) {?>
									<span id="add_past_menu_item" class="btn btn-primary">Add EFL Menu Item</span>
								<?php } ?>
								<?php if ($this->DAO_menu->isEnabled_MarkupRoundUp()) { ?>
									<input name="submit_rounding" id="submit_rounding" type="button" value="Round Markup Price" class="btn btn-primary" onclick="confirm_and_round_form('efl')" data-tooltip="Set the Override price from the Markup price rounded-up to nearest 50 cents. This will overwrite the existing value, if any."/>
								<?php } ?>
							</div>

							<table id="EFLitemsTbl" class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Hide on Freezer page</th>
									<th class="align-middle">Show on Sides &amp; Sweets Forms</th>
									<th class="align-middle">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Size</th>
									<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
										<th class="align-middle">Base Price</th>
									<?php } ?>
									<th class="align-middle">Current Price</th>
									<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
										<th class="align-middle">Markup Price</th>
									<?php } ?>
									<th class="align-middle">Override Price</th>
									<?php if ($this->DAO_menu->isEnabled_MarkDown()) { ?>
										<th class="align-middle">Mark Down</th>
									<?php } ?>
									<th class="align-middle">Preview Price</th>
									<th class="align-middle">Remaining Inventory</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->menuInfo['Extended Fast Lane'])) { ?>
									<?php $lastEntreeID = false; $tabindex = 0; foreach ($this->menuInfo['Extended Fast Lane'] as $planNode) { ?>
										<?php if (is_array($planNode) && $planNode['pricing_type'] != CMenuItem::INTRO && !($planNode['is_side_dish'] && $planNode['pricing_type'] == CMenuItem::HALF)) { ?>
											<tr id="row_<?php echo $planNode['id' ]; ?>" data-menu_item_id="<?php echo $planNode['id' ]; ?>">

												<td class="align-middle">
													<?php if ($planNode['is_visibility_controllable']) { ?>
														<input data-orgval="<?php echo (!$planNode['is_visible'] ? "CHECKED" : ""); ?>" data-menu_item_id="<?php echo $planNode['id' ]; ?>" data-entree_id="<?php echo $planNode['entree_id' ]; ?>" id="vis_<?php echo $planNode['id' ]; ?>" name="vis_<?php echo $planNode['id' ]; ?>" type="checkbox" <?php echo (!$planNode['is_visible'] ? "CHECKED" : ""); ?> />
													<?php } ?>
												</td>

												<td class="align-middle">
													<?php if ($planNode['is_store_special']) { ?>
														<input data-orgval="<?php echo ($planNode['show_on_pick_sheet'] ? "CHECKED" : ""); ?>" id="pic_<?php echo $planNode['id' ]; ?>" name="pic_<?php echo $planNode['id' ]; ?>" type="checkbox" <?php echo ($planNode['show_on_pick_sheet'] ? "CHECKED" : ""); ?> />
													<?php } ?>
												</td>

												<td class="align-middle text-left">
													<a href="?page=item&amp;recipe=<?php echo $planNode['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id'];?>" class="link-dinner-details" data-tooltip="Dinner Details"
													   data-recipe_id="<?php echo $planNode['recipe_id']; ?>" data-store_id="<?php echo $this->DAO_store->id; ?>" data-menu_item_id="<?php echo $planNode['id']; ?>" data-menu_id="<?php echo $this->menuInfo['menu_id']; ?>"
													   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
													<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $planNode['id'] . ' &bull; Recipe ID: ' . $planNode['recipe_id'] . '"' : ''; ?>>
														<?php echo $planNode['menu_item_name']; ?> (<?php echo $planNode['recipe_id'];?>)
													</span>
													<?php if (!empty($planNode['is_bundle'])) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($planNode['admin_notes'])) ? $planNode['admin_notes'] : 'Meal bundle' ?>"></i><?php } ?>
												</td>

												<td class="align-middle">
													<?php echo $planNode['pricing_type_info']['pricing_type_name_short_w_qty']; ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle">
														<?php echo $planNode['base_price' ]; ?>
													</td>
												<?php  }  ?>

												<td class="align-middle">
													<?php echo $planNode['price' ]; ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle markup-price">
														<?php if ((!empty($this->markupData['markup_value_4_serving']) && $planNode['pricing_type'] == CMenuItem::FOUR) || (!empty($this->markupData['markup_value_2_serving']) && $planNode['pricing_type'] == CMenuItem::TWO) || (!empty($this->markupData['markup_value_3_serving']) && $planNode['pricing_type'] == CMenuItem::HALF) || (!empty($this->markupData['markup_value_6_serving']) && $planNode['pricing_type'] == CMenuItem::FULL)) { ?>
															<?php echo $planNode['price' ]; ?>
														<?php  }  ?>
													</td>
												<?php  }  ?>

												<td class="align-middle">
													<?php if ($planNode['is_price_controllable']) { ?>
														<input class="form-control form-control-sm no-spin-button efl-override-price-input" data-orgval="<?php echo $planNode['override_price' ]; ?>" id="ovr_<?php echo $planNode['id' ]; ?>" <?php if ($this->limitToInventoryControl) { ?>readonly="readonly"<?php } ?> name="ovr_<?php echo $planNode['id' ]; ?>" value="<?php echo $planNode['override_price' ]; ?>" type="number" step="any" size="3" maxlength="6" tabindex="<?php echo ++$tabindex; ?>" />
													<?php } ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_MarkDown()) { ?>
													<td class="align-middle">
														<?php if (isset($this->canAddEFLItems) && $this->canAddEFLItems) { ?>
															<?php if ($planNode['markdown_id']) { ?>
																<button class="btn btn-primary" id="mkdn_<?php echo $planNode['id']; ?>" data-markdown_id="<?php echo $planNode['markdown_id']?>" data-markdown_value="<?php echo $planNode['markdown_value']?>" data-org_val="<?php echo $planNode['markdown_value']?>" ><?php echo $planNode['markdown_value']?>%</button>
															<?php } else { ?>
																<button class="btn btn-primary" id="add-mkdn_<?php echo $planNode['id']; ?>">Add</button>
															<?php } ?>
														<?php } ?>
													</td>
												<?php } ?>

												<td class="align-middle preview-price font-weight-bold text-danger">

												</td>

												<?php if ($lastEntreeID != $planNode['entree_id']) { $lastEntreeID = $planNode['entree_id']; ?>
													<td rowspan="<?php echo $planNode['sub_entree_count']; ?>" class="align-middle">
														<?php echo $planNode['override_inventory'] - $planNode['number_sold']  ?>
													</td>
												<?php } ?>

											</tr>
										<?php } ?>
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
							<?php if (!$this->limitToInventoryControl) { ?>
								<div class="col text-right py-2" id="CTS_default_price_control">
									<?php if (isset($this->canAddEFLItems) && $this->canAddEFLItems) {?>
										<span id="add_past_menu_item_sides" class="btn btn-primary">Add Sides & Sweets Menu Item</span>
									<?php } ?>
									<button onclick="SavePricing(); return false;" class="btn btn-primary">Save Sides &amp; Sweets Defaults</button>
									<button onclick="RetrievePricing(); return false;" class="btn btn-primary">Retrieve Sides &amp; Sweets Defaults</button>
									<?php if ($this->DAO_menu->isEnabled_MarkupRoundUp()) { ?>
										<input name="submit_rounding" id="submit_rounding" type="button" value="Round Markup Price" class="btn btn-primary" onclick="confirm_and_round_form('side')" data-tooltip="Set the Override price from the Markup price rounded-up to nearest 50 cents. This will overwrite the existing value, if any."/>
									<?php } ?>
									<br />
									<span id="dp_error" class="warning_text"></span>
									<span id="dp_proc_mess" class="collapse"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/throbber_processing_noborder.gif" alt="Processing" /></span>
								</div>
							<?php } ?>

							<table id="ctsItemsTbl" class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on Freezer page</th>
									<th class="align-middle">Show on Sides &amp; Sweets Forms</th>
									<th class="align-middle">Hide Item Everywhere</th>
									<th class="align-middle">Item title <span class="font-weight-normal">(Recipe ID)</span></th>
									<th class="align-middle">Size</th>
									<th class="align-middle">Base Price</th>
									<th class="align-middle">Current Price</th>
									<th class="align-middle">Markup Price</th>
									<th class="align-middle">Override Price</th>
									<th class="align-middle">Preview Price</th>
									<th class="align-middle">Remaining Inventory</th>
								</tr>
								</thead>
								<tbody class="text-white-space-nowrap text-center">
								<?php if (!empty($this->CTSMenu)) { ?>
									<?php $subcategory = false; foreach ($this->CTSMenu as $id => $ctsItem) { ?>
										<?php if ($subcategory != $ctsItem['subcategory_label' ])  { $subcategory = $ctsItem['subcategory_label' ]; ?>
											<tr>
												<td colspan="11" class="font-weight-bold py-3">
													<?php echo $ctsItem['subcategory_label' ]; ?>
												</td>
											</tr>
										<?php } ?>
										<tr id="row_<?php echo $ctsItem['id' ]; ?>" data-menu_item_id="<?php echo $ctsItem['id' ]; ?>">
											<td class="align-middle">
												<?php if ($ctsItem['is_visibility_controllable']) { ?>
													<input data-orgval="<?php echo ($ctsItem['is_visible'] ? "CHECKED" : ""); ?>" data-menu_item_id="<?php echo $ctsItem['id' ]; ?>" id="vis_<?php echo $ctsItem['id' ]; ?>" name="vis_<?php echo $ctsItem['id' ]; ?>" type="checkbox" <?php echo ($ctsItem['is_visible'] ? "CHECKED" : ""); ?> />
												<?php } ?>
											</td>

											<td class="align-middle">
												<input data-orgval="<?php echo ($ctsItem['show_on_order_form'] ? "CHECKED" : ""); ?>" data-menu_item_id="<?php echo $ctsItem['id' ]; ?>" id="form_<?php echo $ctsItem['id' ]; ?>" name="form_<?php echo $ctsItem['id' ]; ?>" type="checkbox" <?php echo ($ctsItem['show_on_order_form'] ? "CHECKED" : ""); ?> />
											</td>

											<td class="align-middle">
												<input data-orgval="<?php echo ($ctsItem['is_hidden_everywhere'] ? "CHECKED" : ""); ?>" data-menu_item_id="<?php echo $ctsItem['id' ]; ?>" id="hid_<?php echo $ctsItem['id' ]; ?>" name="hid_<?php echo $ctsItem['id' ]; ?>" type="checkbox" <?php echo ($ctsItem['is_hidden_everywhere'] ? "CHECKED" : ""); ?>>
											</td>

											<td class="align-middle text-left">
												<a href="?page=item&amp;recipe=<?php echo $ctsItem['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id'];?>" class="link-dinner-details" data-tooltip="Dinner Details"
												   data-recipe_id="<?php echo $ctsItem['recipe_id']; ?>" data-store_id="<?php echo $this->DAO_store->id; ?>" data-menu_item_id="<?php echo $ctsItem['id']; ?>" data-menu_id="<?php echo $this->menuInfo['menu_id']; ?>"
												   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
												<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $ctsItem['id'] . ' &bull; Recipe ID: ' . $ctsItem['recipe_id'] . '"' : ''; ?>>
													<?php echo $ctsItem['menu_item_name']; ?> (<?php echo $ctsItem['recipe_id'];?>)
												</span>
												<?php if (!empty($ctsItem['is_bundle'])) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($planNode['admin_notes'])) ? $planNode['admin_notes'] : 'Meal bundle' ?>"></i><?php } ?>
												<div id="rec_id_<?php echo $ctsItem['id' ]; ?>" class="collapse"><?php echo $ctsItem['recipe_id' ]; ?></div>
											</td>

											<td class="align-middle">1 item</td>

											<td class="align-middle">
												<?php echo $ctsItem['base_price' ]; ?>
											</td>

											<td class="align-middle">
												<?php echo $ctsItem['price' ]; ?>
											</td>

											<td class="align-middle markup-price">
												<?php if ((!empty($this->markupData['markup_value_2_serving']) && $ctsItem['pricing_type'] == CMenuItem::TWO) || (!empty($this->markupData['markup_value_4_serving']) && $ctsItem['pricing_type'] == CMenuItem::FOUR) || (!empty($this->markupData['markup_value_3_serving']) && $ctsItem['pricing_type'] == CMenuItem::HALF) || (!empty($this->markupData['markup_value_6_serving']) && $ctsItem['pricing_type'] == CMenuItem::FULL)) { ?>
													<?php echo $ctsItem['price' ]; ?>
												<?php } ?>
											</td>

											<td class="align-middle">
												<?php if ($ctsItem['is_price_controllable']) { ?>
													<input class="form-control form-control-sm no-spin-button side-override-price-input" data-orgval="<?php echo $ctsItem['override_price' ]; ?>" id="ovr_<?php echo $ctsItem['id' ]; ?>" name="ovr_<?php echo $ctsItem['id' ]; ?>" value="<?php echo $ctsItem['override_price' ]; ?>" type="number" step="any" size="3" maxlength="6" tabindex="<?php echo $tabindex++; ?>" <?php if ($this->limitToInventoryControl) { ?>readonly="readonly"<?php } ?> />
												<?php } ?>
											</td>

											<td class="align-middle preview-price font-weight-bold text-danger">

											</td>

											<td class="align-middle">
												<?php echo $ctsItem['override_inventory'] - $ctsItem['number_sold' ]; ?>
											</td>

										</tr>
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

							<table id="pricingTbl" class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle text-left"></th>
									<th class="align-middle <?php echo (($this->DAO_store->core_pricing_tier == 1) ? 'bg-green-light' : '' ); ?>" colspan="2">Tier 1</th>
									<th class="align-middle <?php echo (($this->DAO_store->core_pricing_tier == 2) ? 'bg-green-light' : '' ); ?>" colspan="2">Tier 2</th>
									<th class="align-middle <?php echo (($this->DAO_store->core_pricing_tier == 3) ? 'bg-green-light' : '' ); ?>" colspan="2">Tier 3</th>
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
									<?php if (!empty($this->pricingReferenceArray)) { ?>
										<?php foreach ($this->pricingReferenceArray AS $DAO_menu_item) { ?>
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

			<div class="row">
				<div class="col-7">
					<ul class="list-unstyled">
						<li><span class="font-weight-bold">Base Price:</span> Pricing set by Home Office.</li>
						<li><span class="font-weight-bold">Current Price:</span> Current store pricing in effect based on markup settings saved.</li>
						<li><span class="font-weight-bold">Markup Price:</span> Price with mark up added.</li>
						<li><span class="font-weight-bold">Override Price:</span> Price as directly input by store, overrides and excludes mark up.</li>
						<li><span class="font-weight-bold">Preview Price:</span> Item price based on changes made, but not yet finalized.</li>
					</ul>
				</div>
				<div class="col-5 text-right">
					<div>
						<input name="submit_changes" id="submit_changes" type="button" value="Finalize All Changes" class="btn btn-primary" onclick="confirm_and_check_form()" />
						<input type="button" value="Reset to Current" onclick="resetPage();" class="btn btn-primary" />
					</div>
					<div id="saved_message_2" class="text-danger font-weight-bold collapse">Your changes have not yet been saved. Finalize all changes to see updated values.</div><br />
				</div>
			</div>

		</form>
	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>