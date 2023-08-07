<?php $this->setScript('head', SCRIPT_PATH . '/admin/menu_editor.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.uitablefilter.js'); ?>
<?php $this->assign('page_title', 'Menu Editor'); ?>
<?php $this->assign('topnav', 'store'); ?>
<?php $this->assign('helpLinkSection', 'ME'); ?>

<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col-4 offset-4 text-center ">
				<h1><a href="main.php?page=admin_menu_editor">Menu Editor</a></h1>
			</div>
			<div class="col-4 text-right">
				<a href="main.php?page=admin_menu_inventory_mgr&tabs=menu.specials" class="btn btn-primary btn-sm" id="inv-nav-button">Inventory Manager</a>
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

		<form name="menu_editor_form" id="menu_editor_form" action="main.php?page=admin_menu_editor" method="post" class="needs-validation" novalidate>
			<input type="hidden" name="finalize" id="finalize" value="true">
			<?php echo $this->form['hidden_html']; ?>

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
			</div>

			<div class="form-row">

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Menu link
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?>/menu/<?php echo $this->DAO_store->id; ?>-<?php echo $this->DAO_menu->menu_name_abbr; ?>" readonly>
					</div>

				</div>

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Store link
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?>/menu/<?php echo $this->DAO_store->id; ?>" readonly>
					</div>

				</div>

			</div>

			<div class="form-row">

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Menu starter
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?>/menu/<?php echo $this->DAO_store->id; ?>-<?php echo $this->DAO_menu->menu_name_abbr; ?>-starter" readonly>
					</div>

				</div>

				<div class="form-group col-6">

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								Store starter
							</div>
						</div>
						<input type="text" class="form-control" value="<?php echo HTTPS_SERVER; ?>/menu/<?php echo $this->DAO_store->id; ?>-starter" readonly>
					</div>

				</div>

			</div>

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

					<?php if (false && CStore::isCoreTestStore($this->DAO_store->id, $this->DAO_menu->id)) { ?>
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

					<?php if (false && CStore::isCoreTestStore($this->DAO_store->id, $this->DAO_menu->id)) { ?>
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

				<?php if (!$this->DAO_order_minimum->isZeroDollarAssembly() && $this->DAO_menu->isEnabled_Assembly_Fee()) { ?>

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

			<div class="form-row alert alert-danger font-weight-bold text-center unsaved-message collapse">
				<div class="col">
					Your changes have not yet been saved. Finalize all changes to see updated values.
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col text-right">
					<input name="submit_changes" id="submit_changes" type="submit" value="Finalize All Changes" class="btn btn-primary" />
					<a href="main.php?page=admin_menu_editor" class="btn btn-primary">Reset to current</a>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col">

					<nav>
						<div class="nav nav-tabs" id="nav-tab" role="tablist">
							<a class="nav-link active nav-tab" id="nav-core-tab" data-urlpush="true" data-nav="core" data-toggle="tab" href="#nav-core" role="tab" aria-controls="nav-core" aria-selected="true">Core</a>
							<a class="nav-link nav-tab" id="nav-efl-tab" data-nav="efl" data-urlpush="true" data-toggle="tab" href="#nav-efl" role="tab" aria-controls="nav-efl" aria-selected="false">Extended Fast Lane</a>
							<a class="nav-link nav-tab" id="nav-sides-tab" data-nav="sides" data-urlpush="true" data-toggle="tab" href="#nav-sides" role="tab" aria-controls="nav-sides" aria-selected="false">Sides &amp; Sweets</a>
							<?php if (!empty($this->pricingReferenceArray)) { ?>
								<a class="nav-link nav-tab" id="nav-pricing-tab" data-nav="pricing" data-urlpush="true" data-toggle="tab" href="#nav-pricing" role="tab" aria-controls="nav-pricing" aria-selected="false">Pricing Reference</a>
							<?php } ?>
						</div>
					</nav>

					<div class="tab-content bg-white" id="nav-tabContent">
						<div class="tab-pane fade show active" id="nav-core" role="tabpanel" aria-labelledby="nav-core-tab">
							<?php if ($this->DAO_menu->isEnabled_MarkupRoundUp()) { ?>
								<div class="col text-right py-2">
									<span class="btn btn-primary" data-round_up_markup="CORE" data-tooltip="Set the Override price from the Markup price rounded-up to nearest 50 cents. This will overwrite the existing value, if any.">Round Markup Price</span>
								</div>
							<?php } ?>

							<table id="itemsTbl" class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on customer menu</th>
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
								<?php if (!empty($this->menuItemArray[CMenuItem::CORE])) { ?>

									<?php $lastEntreeID = false; $tabindex = 0; foreach ($this->menuItemArray[CMenuItem::CORE] as $DAO_menu_item) { ?>
										<?php if ($DAO_menu_item->pricing_type != CMenuItem::INTRO && !($DAO_menu_item->is_side_dish && $DAO_menu_item->pricing_type == CMenuItem::HALF)) { ?>
											<tr id="row_<?php echo $DAO_menu_item->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
												<td class="align-middle">
													<?php if ($DAO_menu_item->is_visibility_controllable) { ?>
														<?php echo $this->form['vis_' . $DAO_menu_item->id . '_html']; ?>
													<?php } ?>
												</td>

												<td class="align-middle text-left">
													<a href="main.php?page=item&amp;recipe=<?php echo $DAO_menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->DAO_menu->id;?>" class="link-dinner-details" data-tooltip="Dinner Details"
													   data-recipe_id="<?php echo $DAO_menu_item->recipe_id; ?>" data-store_id="<?php echo $this->DAO_store->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>" data-menu_id="<?php echo $this->DAO_menu->id; ?>"
													   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
													<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $DAO_menu_item->id . ' &bull; Recipe ID: ' . $DAO_menu_item->recipe_id . '"' : ''; ?>>
														<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id;?>)
													</span>
													<?php if (!empty($DAO_menu_item->is_bundle)) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($DAO_menu_item->admin_notes)) ? $DAO_menu_item->admin_notes : 'Meal bundle' ?>"></i><?php } ?>
													<?php if (!empty($this->DAO_store->supports_ltd_roundup) && $DAO_menu_item->ltd_menu_item_value) { ?><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/menu-icon07.png" class="img_valign" data-tooltip="$1 is added to price to be donated to DDF" /><?php } ?>
												</td>

												<td class="align-middle text-left">
													<?php echo $DAO_menu_item->pricing_type_info['pricing_type_name_short_w_qty'] ;?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle">
														<?php echo $DAO_menu_item->base_price; ?>
													</td>
												<?php } ?>

												<td class="align-middle <?php if (!empty($this->DAO_store->supports_ltd_roundup) && $DAO_menu_item->ltd_menu_item_value) { ?>text-orange font-weight-bold" data-tooltip="$1 is added to price to be donated to DDF<?php } ?>">
													<?php echo CTemplate::moneyFormat($DAO_menu_item->store_price); ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle markup-price" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
														<?php echo CTemplate::moneyFormat($DAO_menu_item->store_price); ?>
													</td>
												<?php } ?>

												<td class="align-middle">
													<?php if ($DAO_menu_item->is_price_controllable) { ?>
														<?php echo $this->form['ovr_' . $DAO_menu_item->id . '_html']; ?>
													<?php } ?>
												</td>

												<td class="align-middle preview-price font-weight-bold text-danger <?php if (!empty($this->DAO_store->supports_ltd_roundup) && $DAO_menu_item->ltd_menu_item_value) { ?>text-orange font-weight-bold" data-tooltip="$1 is added to price to be donated to DDF<?php } ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>"></td>

												<?php if ($lastEntreeID != $DAO_menu_item->entree_id) { $lastEntreeID = $DAO_menu_item->entree_id; ?>
													<td rowspan="<?php echo $DAO_menu_item->sub_entree_count; ?>" class="align-middle">
														<?php echo $DAO_menu_item->override_inventory - $DAO_menu_item->number_sold  ?>
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
								<span data-add_past_menu_item="efl" class="btn btn-primary">Add EFL Menu Item</span>
								<?php if ($this->DAO_menu->isEnabled_MarkupRoundUp()) { ?>
									<span class="btn btn-primary" data-round_up_markup="EXTENDED" data-tooltip="Set the Override price from the Markup price rounded-up to nearest 50 cents. This will overwrite the existing value, if any.">Round Markup Price</span>
								<?php } ?>
							</div>

							<table id="EFLitemsTbl" class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on customer menu</th>
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
								<?php if (!empty($this->menuItemArray[CMenuItem::EXTENDED])) { ?>
									<?php $lastEntreeID = false; $tabindex = 0; foreach ($this->menuItemArray[CMenuItem::EXTENDED] as $DAO_menu_item) { ?>
										<?php if ($DAO_menu_item->pricing_type != CMenuItem::INTRO && !($DAO_menu_item->is_side_dish && $DAO_menu_item->pricing_type == CMenuItem::HALF)) { ?>
											<tr id="row_<?php echo $DAO_menu_item->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">

												<td class="align-middle">
													<?php if ($DAO_menu_item->is_visibility_controllable) { ?>
														<?php echo $this->form['vis_' . $DAO_menu_item->id . '_html']; ?>
													<?php } ?>
												</td>

												<td class="align-middle">
													<?php if ($DAO_menu_item->is_store_special) { ?>
														<?php echo $this->form['pic_' . $DAO_menu_item->id . '_html']; ?>
													<?php } ?>
												</td>

												<td class="align-middle text-left">
													<a href="main.php?page=item&amp;recipe=<?php echo $DAO_menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->DAO_menu->id;?>" class="link-dinner-details" data-tooltip="Dinner Details"
													   data-recipe_id="<?php echo $DAO_menu_item->recipe_id; ?>" data-store_id="<?php echo $this->DAO_store->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>" data-menu_id="<?php echo $this->DAO_menu->id; ?>"
													   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
													<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $DAO_menu_item->id . ' &bull; Recipe ID: ' . $DAO_menu_item->recipe_id . '"' : ''; ?>>
														<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id;?>)
													</span>
													<?php if (!empty($DAO_menu_item->is_bundle)) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($DAO_menu_item->admin_notes)) ? $DAO_menu_item->admin_notes : 'Meal bundle' ?>"></i><?php } ?>
												</td>

												<td class="align-middle">
													<?php echo $DAO_menu_item->pricing_type_info['pricing_type_name_short_w_qty']; ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle">
														<?php echo $DAO_menu_item->base_price; ?>
													</td>
												<?php  }  ?>

												<td class="align-middle">
													<?php echo $DAO_menu_item->store_price; ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_Markup()) { ?>
													<td class="align-middle markup-price" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
														<?php if ((!empty($this->markupData['markup_value_4_serving']) && $DAO_menu_item->pricing_type == CMenuItem::FOUR) || (!empty($this->markupData['markup_value_2_serving']) && $DAO_menu_item->pricing_type == CMenuItem::TWO) || (!empty($this->markupData['markup_value_3_serving']) && $DAO_menu_item->pricing_type == CMenuItem::HALF) || (!empty($this->markupData['markup_value_6_serving']) && $DAO_menu_item->pricing_type == CMenuItem::FULL)) { ?>
															<?php echo $DAO_menu_item->store_price; ?>
														<?php  }  ?>
													</td>
												<?php  }  ?>

												<td class="align-middle">
													<?php if ($DAO_menu_item->is_price_controllable) { ?>
														<?php echo $this->form['ovr_' . $DAO_menu_item->id . '_html']; ?>
													<?php } ?>
												</td>

												<?php if ($this->DAO_menu->isEnabled_MarkDown()) { ?>
													<td class="align-middle">
														<?php if (isset($this->canAddEFLItems) && $this->canAddEFLItems) { ?>
															<?php if ($DAO_menu_item->markdown_id) { ?>
																<button class="btn btn-primary" id="mkdn_<?php echo $DAO_menu_item->id; ?>" data-markdown_id="<?php echo $DAO_menu_item->markdown_id?>" data-markdown_value="<?php echo $DAO_menu_item->markdown_value?>" data-org_val="<?php echo $DAO_menu_item->markdown_value?>" ><?php echo $DAO_menu_item->markdown_value?>%</button>
															<?php } else { ?>
																<button class="btn btn-primary" id="add-mkdn_<?php echo $DAO_menu_item->id; ?>">Add</button>
															<?php } ?>
														<?php } ?>
													</td>
												<?php } ?>

												<td class="align-middle preview-price font-weight-bold text-danger" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">

												</td>

												<?php if ($lastEntreeID != $DAO_menu_item->entree_id) { $lastEntreeID = $DAO_menu_item->entree_id; ?>
													<td rowspan="<?php echo $DAO_menu_item->sub_entree_count; ?>" class="align-middle">
														<?php echo $DAO_menu_item->override_inventory - $DAO_menu_item->number_sold  ?>
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
									<span data-add_past_menu_item="sides" class="btn btn-primary">Add Sides &amp; Sweets Menu Item</span>
									<span class="btn btn-primary sides-sweets-get-pricing" data-operation="default_pricing_save">Save Sides &amp; Sweets Defaults</span>
									<span class="btn btn-primary sides-sweets-get-pricing" data-operation="default_pricing_get">Retrieve Sides &amp; Sweets Defaults</span>
									<?php if ($this->DAO_menu->isEnabled_MarkupRoundUp()) { ?>
										<span class="btn btn-primary" data-round_up_markup="SIDE" data-tooltip="Set the Override price from the Markup price rounded-up to nearest 50 cents. This will overwrite the existing value, if any.">Round Markup Price</span>
									<?php } ?>
									<br />
									<span id="dp_error" class="warning_text"></span>
									<span id="dp_proc_mess" class="collapse"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/throbber_processing_noborder.gif" alt="Processing" /></span>
								</div>
							<?php } ?>

							<table id="ctsItemsTbl" class="table table-striped table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
								<thead class="text-center bg-white sticky-top ddtemp-z-index-0">
								<tr>
									<th class="align-middle">Show on customer menu</th>
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
								<?php if (!empty($this->menuItemArray[CMenuItem::SIDE])) { ?>
									<?php $subcategory = false; foreach ($this->menuItemArray[CMenuItem::SIDE] as $id => $DAO_menu_item) { ?>
										<?php if ($subcategory != $DAO_menu_item->subcategory_label)  { $subcategory = $DAO_menu_item->subcategory_label; ?>
											<tr>
												<td colspan="11" class="font-weight-bold py-3">
													<?php echo $DAO_menu_item->subcategory_label; ?>
												</td>
											</tr>
										<?php } ?>
										<tr id="row_<?php echo $DAO_menu_item->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
											<td class="align-middle">
												<?php if ($DAO_menu_item->is_visibility_controllable) { ?>
													<?php echo $this->form['vis_' . $DAO_menu_item->id . '_html']; ?>
												<?php } ?>
											</td>

											<td class="align-middle">
												<?php echo $this->form['form_' . $DAO_menu_item->id . '_html']; ?>
											</td>

											<td class="align-middle">
												<?php echo $this->form['hid_' . $DAO_menu_item->id . '_html']; ?>
											</td>

											<td class="align-middle text-left">
												<a href="main.php?page=item&amp;recipe=<?php echo $DAO_menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->DAO_menu->id;?>" class="link-dinner-details" data-tooltip="Dinner Details"
												   data-recipe_id="<?php echo $DAO_menu_item->recipe_id; ?>" data-store_id="<?php echo $this->DAO_store->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>" data-menu_id="<?php echo $this->DAO_menu->id; ?>"
												   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
												<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $DAO_menu_item->id . ' &bull; Recipe ID: ' . $DAO_menu_item->recipe_id . '"' : ''; ?>>
													<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id;?>)
												</span>
												<?php if (!empty($DAO_menu_item->is_bundle)) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($DAO_menu_item->admin_notes)) ? $DAO_menu_item->admin_notes : 'Meal bundle' ?>"></i><?php } ?>
												<div id="rec_id_<?php echo $DAO_menu_item->id; ?>" class="collapse"><?php echo $DAO_menu_item->recipe_id; ?></div>
											</td>

											<td class="align-middle">1 item</td>

											<td class="align-middle">
												<?php echo $DAO_menu_item->price; ?>
											</td>

											<td class="align-middle">
												<?php echo $DAO_menu_item->store_price; ?>
											</td>

											<td class="align-middle markup-price" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
												<?php if ((!empty($this->markupData['markup_value_2_serving']) && $DAO_menu_item->pricing_type == CMenuItem::TWO) || (!empty($this->markupData['markup_value_4_serving']) && $DAO_menu_item->pricing_type == CMenuItem::FOUR) || (!empty($this->markupData['markup_value_3_serving']) && $DAO_menu_item->pricing_type == CMenuItem::HALF) || (!empty($this->markupData['markup_value_6_serving']) && $DAO_menu_item->pricing_type == CMenuItem::FULL)) { ?>
													<?php echo $DAO_menu_item->store_price; ?>
												<?php } ?>
											</td>

											<td class="align-middle">
												<?php if ($DAO_menu_item->is_price_controllable) { ?>
													<?php echo $this->form['ovr_' . $DAO_menu_item->id . '_html']; ?>
												<?php } ?>
											</td>

											<td class="align-middle preview-price font-weight-bold text-danger" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">

											</td>

											<td class="align-middle">
												<?php echo $DAO_menu_item->override_inventory - $DAO_menu_item->number_sold; ?>
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
						<?php if (!empty($this->pricingReferenceArray)) { ?>
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
									<?php foreach ($this->pricingReferenceArray AS $DAO_menu_item) { ?>
										<?php if (!empty($DAO_menu_item->pricing_tiers)) { ?>
											<tr>
												<td class="text-left">
													<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id; ?>)
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers) && !empty($DAO_menu_item->pricing_tiers['1'][CMenuItem::HALF])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['1'][CMenuItem::HALF]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers) && !empty($DAO_menu_item->pricing_tiers['1'][CMenuItem::FULL])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['1'][CMenuItem::FULL]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers) && !empty($DAO_menu_item->pricing_tiers['2'][CMenuItem::HALF])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['2'][CMenuItem::HALF]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers) && !empty($DAO_menu_item->pricing_tiers['2'][CMenuItem::FULL])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['2'][CMenuItem::FULL]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers) && !empty($DAO_menu_item->pricing_tiers['3'][CMenuItem::HALF])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['3'][CMenuItem::HALF]->price; ?>
													<?php } ?>
												</td>
												<td>
													<?php if (!empty($DAO_menu_item->pricing_tiers) && !empty($DAO_menu_item->pricing_tiers['3'][CMenuItem::FULL])) { ?>
														<?php echo $DAO_menu_item->pricing_tiers['3'][CMenuItem::FULL]->price; ?>
													<?php } ?>
												</td>
											</tr>
										<?php } ?>
									<?php } ?>
									</tbody>
								</table>
							</div>
						<?php } ?>
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
						<input name="submit_changes" id="submit_changes" type="submit" value="Finalize All Changes" class="btn btn-primary" onclick="confirm_and_check_form()" />
						<a href="main.php?page=admin_menu_editor" class="btn btn-primary">Reset to current</a>
					</div>
					<div class="text-danger font-weight-bold unsaved-message collapse">Your changes have not yet been saved. Finalize all changes to see updated values.</div><br />
				</div>
			</div>

		</form>
	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>