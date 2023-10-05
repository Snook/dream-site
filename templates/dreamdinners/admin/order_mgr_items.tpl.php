<?php
function createSectionHeader($tpl, $categoryName)
{
	if ($categoryName == "Fast Lane")
	{
		return "</tbody><tr><th class='bg-green' colspan='15'>Extended Fast Lane</th></tr><tbody>";
	}
	else if ($categoryName == "Specials")
	{
		if ($tpl->storeSupportsMembership && $tpl->orderIsEligibleForMembershipDiscount && $tpl->storeSupportsDefaultOrder)
		{
			return "</tbody><tr>
				<th class='bg-green'>Core Items</th>
				<th colspan='3'><button class='btn btn-primary btn-sm' id='selectMediumStarterPackEntrees'>Select Default</button></th>
				<th colspan='2'><button class='btn btn-primary btn-sm' id='selectLargeStarterPackEntrees'>Select Default</button></th>
			</tr><tbody>";
		}
		else
		{
			return "</tbody><tr><th class='bg-green' colspan='7'>Core Items</th></tr><tbody>";
		}
	}
	else if ($categoryName == "Chef Touched Selections")
	{
		return "</tbody><tr><th class='bg-cyan' colspan='15'>Sides &amp; Sweets</th></tr><tbody>";
	}
	else
	{
		return "</tbody><tr><th class='bg-green' colspan='15'>" . $categoryName . "</th></tr><tbody>";
	}
}

//try to find entree id by menu item then by all the various sizes of that menu items
function determineEntreeId($tpl, $categoryName, $planNode, $menuItemId)
{
	$entree_id = null;
	if (array_key_exists($menuItemId, $tpl->menuInfo[$categoryName]))
	{
		$entree_id = $tpl->menuInfo[$categoryName][$menuItemId]['entree_id'];
		if (!is_null($entree_id))
		{
			return $entree_id;
		}
	}

	foreach ($planNode as $type => $menuItemId)
	{
		return determineEntreeId($tpl, $categoryName, $planNode, $menuItemId);
	}

	return $entree_id;
}

?>

	<ul class="nav nav-tabs" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="main_menu-tab" data-toggle="tab" data-target="#main_menu" type="button" role="tab" aria-controls="main_menu" aria-selected="true">Main Menu</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="sides-tab" data-toggle="tab" data-target="#sides" type="button" role="tab" aria-controls="sides" aria-selected="false">Sides &amp; Sweets Menu</button>
		</li>
	</ul>

	<div class="tab-content bg-white">
		<div class="tab-pane fade show active" id="main_menu" role="tabpanel" aria-labelledby="main_menu-tab">
			<table id="itemsTbl" class="table table-striped-2 table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
				<thead>
				<tr class="sticky-top text-center bg-white">
					<th class="text-right">
						<?php if ($this->hasBundle) { ?>
							<div id="bundle_header_div">
								<?php echo $this->bundleInfo['bundle_name'] ?>

								<?php if ($this->userIsNewToBundle) { ?>
									<?php echo $this->form_direct_order['selectedBundle_html'] ?>
								<?php } else { ?>
									<br /><span class="text-danger">Ineligible due to previous orders</span>
								<?php } ?>

							</div>
						<?php } ?>
					</th>
					<th>Size</th>
					<?php if ($this->hasBundle) { ?>
						<th <?php if (!$this->userIsNewToBundle) { ?>class="text-danger"<?php } ?>>MPSP</th>
					<?php } ?>
					<th>Price</th>
					<th style="width: 140px; min-width: 140px;">Quantity</th>
					<th>Remaining<br />Inventory</th>
				</tr>
				</thead>
				<tbody>
				<?php

				if (!empty($this->planArray))
				{
					foreach ($this->planArray as $categoryName => $subArray)
					{
						if (is_array($subArray))
						{
							$printedName = false;

							foreach ($subArray as $planNode)
							{
								if (!$printedName)
								{
									$printedName = true;
									echo createSectionHeader($this, $categoryName);
								}

								$amountRemaining = 0;
								$printedQty = false;
								$menuItemCount = 1;

								//Loop through each size and create row in table
								foreach ($planNode as $type => $menuItemId)
								{
									$thisItem = $this->menuInfo[$categoryName][$menuItemId];

									$isDreamy = false;
									$isStarterPackItem = false;
									$rowBackgroundClause = '';

									//Do setup for all items sizes for same entree id
									$introId = (isset($planNode[CMenuItem::INTRO]) ? $planNode[CMenuItem::INTRO] : null);
									$halfId = (isset($planNode[CMenuItem::HALF]) ? $planNode[CMenuItem::HALF] : null);
									$fullId = (isset($planNode[CMenuItem::FULL]) ? $planNode[CMenuItem::FULL] : null);
									$twoId = (isset($planNode[CMenuItem::TWO]) ? $planNode[CMenuItem::TWO] : null);
									$fourId = (isset($planNode[CMenuItem::FOUR]) ? $planNode[CMenuItem::FOUR] : null);

									if ($introId && $this->menuInfo[$categoryName][$introId]['is_preassembled'])
									{
										$introId = null;
									}
									if ($introId && $this->menuInfo[$categoryName][$introId]['is_side_dish'])
									{
										$introId = null;
									}
									if ($introId && $this->menuInfo[$categoryName][$introId]['is_kids_choice'])
									{
										$introId = null;
									}
									if ($introId && $this->menuInfo[$categoryName][$introId]['is_menu_addon'])
									{
										$introId = null;
									}
									if ($introId && $this->menuInfo[$categoryName][$introId]['is_chef_touched'])
									{
										$introId = null;
									}

									if ($halfId && $this->menuInfo[$categoryName][$halfId]['is_side_dish'])
									{
										$halfId = null;
									}
									if ($halfId && $this->menuInfo[$categoryName][$halfId]['is_kids_choice'])
									{
										$halfId = null;
									}
									if ($halfId && $this->menuInfo[$categoryName][$halfId]['is_menu_addon'])
									{
										$halfId = null;
									}
									if ($halfId && $this->menuInfo[$categoryName][$halfId]['is_chef_touched'])
									{
										$halfId = null;
									}

									$amountRemaining = ($thisItem['override_inventory'] - $thisItem['number_sold']);

									if (isset($this->orgQuantities[$introId]))
									{
										$amountRemaining += ($this->orgQuantities[$introId] * 3);
									}

									if (isset($this->orgQuantities[$halfId]))
									{
										$amountRemaining += ($this->orgQuantities[$halfId] * CMenuItem::translatePricingTypeToNumeric(CMenuItem::HALF));
									}

									if (isset($this->orgQuantities[$fullId]))
									{
										$amountRemaining += ($this->orgQuantities[$fullId] * CMenuItem::translatePricingTypeToNumeric(CMenuItem::FULL));
									}

									if (isset($this->orgQuantities[$twoId]))
									{
										$amountRemaining += ($this->orgQuantities[$fullId] * CMenuItem::translatePricingTypeToNumeric(CMenuItem::TWO));
									}

									if (isset($this->orgQuantities[$fourId]))
									{
										$amountRemaining += ($this->orgQuantities[$fullId] * CMenuItem::translatePricingTypeToNumeric(CMenuItem::FOUR));
									}

									if ($this->isDreamTaste && in_array($thisItem['id'], explode(',', $this->dreamTasteProperties->menu_items)))
									{
										$isDreamy = true;
									}

									if ($this->isFundraiser && in_array($thisItem['id'], explode(',', $this->fundraiserProperties->menu_items)))
									{
										$isDreamy = true;
									}

									if (($this->isDreamTaste || $this->isFundraiser) && !($isDreamy) && !($thisItem['is_chef_touched']))
									{
										$rowBackgroundClause = 'collapse';
									}

									if ($isDreamy && !$thisItem['is_visible'])
									{
										continue;
									}

									?>
									<tr class="inventory-row <?php echo $rowBackgroundClause ?>" data-orig-remaining="<?php echo $amountRemaining ?>" data-entree="<?php echo $thisItem['entree_id'] ?>" data-servings="<?php echo $thisItem['servings_per_item'] ?>">

										<td class="font-weight-bold">
											<a href="javascript:toggleId('desc<?php echo $thisItem['id'] ?>');" class="text-white-space-nowrap <?php if (($this->sessionInfo['session_type'] == CSession::STANDARD || $this->sessionInfo['session_type'] == CSession::SPECIAL_EVENT) && !empty($this->storeInfo['supports_ltd_roundup']) && !empty($thisItem['ltd_menu_item_value'])) { ?> ltd_menu_item<?php } ?>">
												<?php echo $thisItem['menu_item_name']; ?>
												<?php echo ($thisItem['is_preassembled']) ? '(Pre-assembled)' : ''; ?>
												<?php echo ($thisItem['is_bundle']) ? '(Bundle)' : ''; ?>
											</a>
										</td>

										<td class="text-white-space-nowrap">
											<?php echo $thisItem['pricing_type_info']['pricing_type_name_short_w_qty']; ?>
										</td>

										<?php if ($this->hasBundle) { ?>
											<td id="bnd_div_<?php echo $thisItem['id'] ?>">
												<?php
												if (isset($this->bundleItems['bundle']) && array_key_exists($thisItem['id'], $this->bundleItems['bundle']))
												{
													$isStarterPackItem = true;
													echo $this->form_direct_order['bnd_' . $thisItem['id'] . '_html'];
												}

												if (!isset($this->bundleItems['bundle']) || (!array_key_exists($thisItem['id'], $this->bundleItems['bundle'])))
												{
													?>
												<?php } ?>
											</td>
										<?php } ?>

										<?php if ($isDreamy) { ?>
											<td>
												<span>$<span id="prc_<?php echo $thisItem['id']; ?>"><?php echo CTemplate::moneyFormat($thisItem['price']); ?></span></span>
											</td>
											<td>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="btn btn-primary btn-sm btn-dec_qty" data-menu_item_id="<?php echo $thisItem['id']; ?>" data-entree_id="<?php echo $thisItem['entree_id']; ?>" data-servings_per_item="<?php echo $thisItem['servings_per_item']; ?>" data-box_type="bnd_"><i class="fas fa-minus-square font-size-medium-small"></i></span>
													</div>
													<?php echo $this->form_direct_order['bnd_' . $thisItem['id'] . '_html']; ?>
													<div class="input-group-append">
														<span class="btn btn-primary btn-sm btn-inc_qty" data-menu_item_id="<?php echo $thisItem['id']; ?>" data-entree_id="<?php echo $thisItem['entree_id']; ?>" data-servings_per_item="<?php echo $thisItem['servings_per_item']; ?>" data-box_type="bnd_"><i class="fas fa-plus-square font-size-medium-small"></i></span>
													</div>
												</div>
											</td>
										<?php } else if (!$this->isDreamTaste && !$this->isFundraiser) { ?>
											<td class="text-right">
												<span>$<span id="prc_<?php echo $thisItem['id']; ?>"><?php echo CTemplate::moneyFormat($thisItem['price']); ?></span></span>
											</td>
											<td>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="btn btn-primary btn-sm btn-dec_qty" data-menu_item_id="<?php echo $thisItem['id']; ?>" data-entree_id="<?php echo $thisItem['entree_id']; ?>" data-servings_per_item="<?php echo $thisItem['servings_per_item']; ?>" data-box_type="qty_"><i class="fas fa-minus-square font-size-medium-small"></i></span>
													</div>
													<?php echo $this->form_direct_order['qty_' . $thisItem['id'] . '_html']; ?>
													<div class="input-group-append">
														<span class="btn btn-primary btn-sm btn-inc_qty" data-menu_item_id="<?php echo $thisItem['id']; ?>" data-entree_id="<?php echo $thisItem['entree_id']; ?>" data-servings_per_item="<?php echo $thisItem['servings_per_item']; ?>" data-box_type="qty_"><i class="fas fa-plus-square font-size-medium-small"></i></span>
													</div>
												</div>
											</td>
										<?php } else { ?>
											<td></td>
											<td></td>
											<td></td>
										<?php } ?>

										<td class="text-center">
											<span data-entree_inventory_remaining="<?php echo $thisItem['entree_id'] ?>"><?php echo $amountRemaining ?></span>
										</td>
									</tr>

									<tr id="desc<?php echo $thisItem['id'] ?>" class="collapse">
										<td colspan="7">
											<div>
												<?php echo $thisItem['menu_item_description'] ?>

												<?php if ($thisItem['is_bundle']) { ?>
													<?php
													// Batali
													if ($thisItem['id'] == 7707)
													{
														include $this->loadTemplate('admin/subtemplate/order_mgr/order_mgr_menu_item_bundle_batali.tpl.php');
													}
													else
													{
														include $this->loadTemplate('admin/subtemplate/order_mgr/order_mgr_menu_item_bundle.tpl.php');
													}
													?>
												<?php } ?>
											</div>
										</td>
									</tr>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane fade" id="sides" role="tabpanel" aria-labelledby="sides-tab">
			<table id="sidesTbl" class="table table-striped-2 table-bordered table-hover table-hover-cyan ddtemp-table-border-collapse">
				<thead>
				<tr class="sticky-top text-center bg-white">
					<th class="text-right">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="hide-zero-sides">
							<label class="custom-control-label pt-1" for="hide-zero-sides">Available only</label>
						</div>
					</th>
					<th>Price</th>
					<th style="width: 140px; min-width: 140px;">Quantity</th>
					<th>Remaining<br />Inventory</th>
				</thead>
				<tbody>
				<?php if (!empty($this->isTODD)) { ?>
					<tr>
						<td colspan="4" class="text-center"><h3>Taste of Dream Dinners Session</h3></td>
					</tr>
				<?php } ?>
				<?php if ($this->isDreamTaste) { ?>
					<tr>
						<td colspan="4" class="text-center"><h3>Meal Prep Workshop Session</h3></td>
					</tr>
				<?php } ?>
				<?php if ($this->isFundraiser) { ?>
					<tr>
						<td colspan="4" class="text-center"><h3>Fundraiser Session</h3></td>
					</tr>
				<?php } ?>

				<?php if (!empty($this->FinishingTouchesArray))
				{

				$subcat = false;

				foreach ($this->FinishingTouchesArray as $id => $thisItem)
				{

				if (isset($thisItem['is_hidden_everywhere']) && $thisItem['is_hidden_everywhere'] && empty($this->orgQuantities[$thisItem['id']]))
				{
					continue;
				}

				$amountRemainingFt = $thisItem['override_inventory'] - $thisItem['number_sold'];
				$borderClause = "";
				$matches = "";
				if (isset($this->matched_FT_items[$thisItem['id']]) && $this->matched_FT_items[$thisItem['id']]['has_match'])
				{
					$borderClause = ' border:purple 2px solid;';
					$filterArray = array();
					$numMatches = count($this->matched_FT_items[$thisItem['id']]['matches']);
					$match_counter = 0;
					foreach ($this->matched_FT_items[$thisItem['id']]['matches'] as $thisMatch)
					{
						if (!in_array($thisMatch['name'], $filterArray))
						{
							$filterArray[] = $thisMatch['name'];
						}
					}

					$matches .= implode(", ", $filterArray);
				}

				if ($subcat <> $thisItem['subcategory_label'])
				{
				?>
				</tbody>
				<tr>
					<td colspan="4" class="bg-cyan font-weight-bold"><?php echo $thisItem['subcategory_label']; ?></td>
				</tr>
				<tbody>
				<?php
				$subcat = $thisItem['subcategory_label'];
				}
				?>

				<tr class="inventory-row" data-orig-remaining="<?php echo $amountRemainingFt ?>" data-entree="<?php echo $thisItem['id'] ?>" data-servings="<?php echo $thisItem['servings_per_item'] ?>">
					<td class="font-weight-bold" <?php if (!empty($matches)) { echo 'data-tooltip="Compliments the ' . $matches . '"'; } ?>>
						<a href="javascript:toggleId('desc<?php echo $thisItem['id'] ?>');">
							<?php echo $thisItem['menu_item_name'] ?>
							<?php echo ($thisItem['is_bundle']) ? '(Bundle)' : ''; ?>
						</a>
					</td>

					<td class="text-right">
						$<span id="prc_<?php echo $thisItem['id']; ?>"><?php echo $thisItem['price']; ?></span>
					</td>
					<td>
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="btn btn-primary btn-sm btn-dec_qty" data-menu_item_id="<?php echo $thisItem['id']; ?>" data-entree_id="<?php echo $thisItem['entree_id']; ?>" data-servings_per_item="<?php echo $thisItem['servings_per_item']; ?>" data-box_type="qty_"><i class="fas fa-minus-square font-size-medium-small"></i></span>
							</div>
							<?php echo $this->form_direct_order['qty_' . $thisItem['id'] . '_html']; ?>
							<div class="input-group-append">
								<span class="btn btn-primary btn-sm btn-inc_qty" data-menu_item_id="<?php echo $thisItem['id']; ?>" data-entree_id="<?php echo $thisItem['entree_id']; ?>" data-servings_per_item="<?php echo $thisItem['servings_per_item']; ?>" data-box_type="qty_"><i class="fas fa-plus-square font-size-medium-small"></i></span>
							</div>
						</div>
					</td>

					<td class="text-center">
						<span data-entree_inventory_remaining="<?php echo $thisItem['entree_id'] ?>"><?php echo $amountRemaining ?></span>
					</td>
				</tr>

				<tr id="desc<?php echo $thisItem['id'] ?>" class="collapse">
					<td colspan="4">
						<?php echo $thisItem['menu_item_description'] ?>

						<?php if ($thisItem['is_bundle']) { ?>
							<?php include $this->loadTemplate('admin/subtemplate/order_mgr/order_mgr_menu_item_bundle.tpl.php');?>
						<?php } ?>
					</td>
				</tr>
				<?php } } ?>
				</tbody>
			</table>
		</div>
	</div>

<?php if (!isset($this->show_misc_food_field) || !$this->show_misc_food_field) { ?>
	<input type="hidden" name="misc_food_subtotal" id="misc_food_subtotal" value="0" />
	<input type="hidden" name="misc_food_subtotal_desc" id="misc_food_subtotal_desc" value="" />
<?php } ?>
<?php if (!isset($this->show_misc_non_food_field) || !$this->show_misc_non_food_field) { ?>
	<input type="hidden" name="misc_nonfood_subtotal" id="misc_nonfood_subtotal" value="0" />
	<input type="hidden" name="misc_nonfood_subtotal_desc" id="misc_nonfood_subtotal_desc" value="" />
<?php } ?>