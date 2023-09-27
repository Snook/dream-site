
<?php $this->setCSS(CSS_PATH . '/admin/inventory_mgr.css'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/menu_inv_mgr.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.stickyTableHeaders.js'); ?>

<?php $this->assign('page_title', 'Inventory Manager'); ?>
<?php $this->assign('topnav', 'store'); ?>
<?php $this->assign('helpLinkSection', 'ME'); ?>
<?php $this->setScriptVar('storeSupportsPlatePoints = ' . ($this->storeSupportsPlatePoints ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('storeInfo = ' . json_encode($this->storeInfo->toArray()) . ';'); ?>
<?php $this->setScriptVar('menu_id = ' . $this->menuInfo['menu_id'] . ';'); ?>
<?php $this->setScriptVar('menu_anchor_date = \'' . $this->menuInfo['menu_anchor_date'] . '\';'); ?>
<?php $this->setScriptVar('gStore_ID = ' . $this->store_id . ';'); ?>
<?php $this->setScriptVar('storeSpecialsItems = ' . $this->storeSpecialsItems . ';'); ?>
<?php $this->setScriptVar('weekInfo = ' . json_encode($this->weeks_inv) . ';'); ?>
<?php $this->setScriptVar('menuState = ' . json_encode($this->menu_state) . ';'); ?>


<?php $this->setOnLoad("menu_editor_init();"); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<script src="<?php echo SCRIPT_PATH; ?>/admin/vendor/calendarDateInput.js" type="text/javascript"></script>

<style>
	table.sticky tbody td:nth-child(3) {
		position: sticky;
		left: 0;
		z-index: 2;
		}
	select.calendarDateInput{
		padding: 0rem 0rem 0rem 0rem !important;
		height: calc(1rem + 2px);
		}
	input.calendarDateInput{
		padding: 0rem 0rem 0rem 0rem !important;
		height: calc(1rem + 2px);
		}
	legend {
		text-align: left !important;
		line-height: normal;
		}
</style>
<form name="menu_editor_form" id="menu_editor_form" method="post">

	<table class="ME_menu_editor_table" style="width:100%;">
		<input type="hidden" name="action" id="action" value="none">

		<!-- Header area -->
		<!-- Store Row : for site admin only -->
		<tr>
			<td align="center" colspan="3" style="padding: 0px;">
				<h1>Inventory Manager</h1>
			</td>
		</tr>


		<!-- Menu Selector Row -->
		<tr>
			<?php if (isset($this->form['store_html']))
			{ ?>
				<!-- Store Row : for site admin only -->
				<td align="left" style="padding: 5px;">
					<b>Selected Store:</b> <?= $this->form['store_html']; ?> </td>
			<?php } ?>

			<td align="left" style="padding-left: 10px;"><b>Selected Menu:</b> <?= $this->form['menus_html']; ?></td>
			<td align="left" style="padding-left: 10px;">
				<a href="/?page=admin_menu_editor&tabs=menu.specials" class="btn btn-primary btn-sm" id="inv_mgr">Menu Editor</a>
			</td>
		</tr>
	</table>
	<div class="row" >
		<div class="col-12" style="padding-left: 80%;">
			<fieldset>
				<legend class="expand_export"><span class="button">Export By Date</span></legend>
				<div id="export_by_dates" style="display:none;">
					Select a range of dates:
					<?php
					$rangestart = NULL;
					$rangeend = NULL;
					if (isset($this->range_day_start_set) && isset($this->range_day_end_set)) {
						echo "<script>DateInput('range_day_start', false, 'YYYY-MM-DD','" . $this->range_day_start_set .  "')</script>";
						echo "<script>DateInput('range_day_end', false, 'YYYY-MM-DD','" . $this->range_day_end_set .  "')</script>";
					}
					else {
						echo "<script>DateInput('range_day_start', true, 'YYYY-MM-DD')</script>";
						echo "<script>DateInput('range_day_end', true, 'YYYY-MM-DD')</script>";
					}
					?><span id="export_by_range" class="button">Export</span>
				</div>
			</fieldset>

		</div>
	</div>
	<div class="tabbed-content" data-tabid="menu">
		<div class="tabs-container">
			<ul class="tabs">
				<!-- data-tabid can be any string, required on all elements -->
				<li data-tabid="specials" data-nav="menu.specials" class="tab selected">Core</li>
				<li data-tabid="efl" data-nav="menu.efl" class="tab">Extended Fast Lane</li>
				<li data-tabid="sides" data-nav="menu.sides" class="tab">Sides &amp; Sweets</li>
			</ul>
		</div>

		<div class="tabs-content">

			<div data-tabid="specials">

				<div class="mb-1">
					<table style="width:100%;">
						<tr><td colspan="2" style="font-weight:bold;">
								Projected Guest Counts:</td>
							<td><?php echo $this->form['regular_guest_count_goal_html'];?></td><td style="font-weight:normal; margin-right:20px;">Regular Guests</td><td>&nbsp;&nbsp;&nbsp;</td>

							<td><?php echo $this->form['taste_guest_count_goal_html'];?></td><td style="font-weight:normal; margin-right:20px;">Event Guests</td><td>&nbsp;&nbsp;&nbsp;</td>

							<td><?php echo $this->form['intro_guest_count_goal_html'];?></td><td style="font-weight:normal; margin-right:20px;">Starter Pack Guests</td><td>&nbsp;&nbsp;&nbsp;</td>

							<td><span id="save_guest_counts" class="button">Save Projected Guest Counts</span><span style="font-weight:bold; font-size: 18pt;">&#9312;</span>&nbsp;&nbsp;
								<span id="calc_inv" class="button">Calculate Projected Inventory</span><span style="font-weight:bold; font-size: 18pt;">&#9314;</span>
								<span id="show_actuals" class="button ">View Servings Sold</span>
								<span id="reset_to_current" class="button float-right">Reset to Current Stored Values</span>
							</td>

						</tr>
					</table>

				</div>

				<table class="table" style="width:100%;">
					<!-- data display area -->
					<tr>
						<td colspan="2">

							<table id="itemsTbl" class="table  table-sm sticky" style="width: 100%;">
								<thead>
								<tr scope="row" class="header_top">
									<th colspan="2" scope="col" class="ME_header_zebra_even IM_header"><span id="reset_sales_mix" class="button mb-1">Reset to National Sales Mix</span>
										<span id="save_sales_mix" class="button">Save Sales Mix</span><span style="font-weight:bold; font-size: 18pt;">&#9313;</span></th>
									<th scope="col" class="ME_header_zebra_odd IM_header"><span class="warning_text" id="page_error_display"></span>
										<div class="float-left">
											<div class="font-size-extra-small">
												<span class="mr-2 ml-2" style="font-weight:bold;">Steps to Initialize New Menu:</span><br />
												<span style="font-weight:bold; font-size: 11pt;">&#9312;</span><span class="font-size-extra-small">Review, Adjust and Save Projected Guest counts</span>
												<a href="" data-toggle="collapse" data-target="#init_steps" aria-expanded="false" aria-controls="collapse-MFYD-One" class="mr-2">Show/Hide All Steps ...</a>
												<div id="init_steps" class="collapse" aria-labelledby="init_steps">
													<div><span style="font-weight:bold; font-size: 11pt;">&#9313;</span><span>Review, Adjust and Save Store Sales Mix.  [Note: The total must equal 100%.]</span></div>
													<div><span style="font-weight:bold; font-size: 11pt;">&#9314;</span><span>Calculate projected Inventory. Values are not saved until the Preorder is finalized.</span></div>
													<div><span style="font-weight:bold; font-size: 11pt;">&#9315;</span><span>Finalize the Preorder.  This saves the PreOrder and Weekly Inventory to the database.</span></div>
													<div><span style="font-weight:bold; font-size: 11pt;">&#9316;</span><span>Export the Preorder for manually importing into Reciprofity </span></div>
													<div><span style="font-weight:bold; font-size: 11pt;">&#9317;</span><span>Finalizing Weekly Inventory – The inventory for individual weeks can be edited at any time after finalizing the pre-order. To save changes, click the Finalize button for each week where inventory was edited.</span></div>
												</div>
											</div>
										</div>
									</th>
									<th scope="col" colspan="3" class="ME_header_zebra_even IM_header thick_left_border"><h3>Preorder</h3><span style="margin-bottom:2px;" class="button" id="finalize_preorder" >Finalize</span><span style="font-weight:bold; font-size: 18pt;">&#9315;</span><br />
										<span id="export_menu_sales" class="button" data-menu_start="<?php echo $this->menu_start; ?>" data-menu_interval="<?php echo $this->menu_interval; ?>">Export</span><span style="font-weight:bold; font-size: 18pt;">&#9316;</span></th>
									<?php
									$evenColumn = false;
									$colClass = 'ME_header_zebra_even';
									foreach ($this->weeks_inv as $weekNum => $weekData)
									{
										if ($evenColumn)
										{
											$colClass = 'ME_header_zebra_even';
										}
										else
										{
											$colClass = 'ME_header_zebra_odd';
										}

										$evenColumn = !$evenColumn;
										?>
										<th scope="col" colspan="3" class="<?php echo  $colClass ; ?> IM_header thick_left_border"><h3>Week <?php echo  $weekNum; ?></h3>
											<span style="margin-bottom:2px;" class="button" id="finalize_week_<?php echo  $weekNum; ?>">Finalize</span><span style="font-weight:bold; font-size: 12pt;">&#9317;</span><br /><span
													data-week_start="<?php echo $weekData['week_start']; ?>"
													id="export_week_<?php echo  $weekNum; ?>_sales" class="button">Export</span></th>
									<?php } ?>
									<th scope="col" colspan="2" class="ME_header_zebra_odd IM_header thick_left_border"><h3>Available to Promise</h3></th>

								</tr>

								<tr scope="row" class="header_bottom">
									<th scope="col" class="ME_header_zebra_odd IM_header" data-help="inv_mgr-national_sales_mix">National Sales Mix</th>
									<th scope="col" class="ME_header_zebra_even IM_header" data-help="inv_mgr-store_sales_mix">Store Sales Mix</th>
									<th scope="col" class="ME_header_zebra_odd IM_header">Item title <span style="font-weight: 100;">(Recipe ID)</span></th>
									<th scope="col" style="width:40px;" class="ME_header_zebra_even IM_header thick_left_border" data-help="inv_mgr-projected_inventory">Projected Inventory</th>
									<th scope="col" class="ME_header_zebra_even IM_header" data-help="inv_mgr-sum_servings_remaining">Sum of Total Servings Remaining</th>
									<th scope="col" class="ME_header_zebra_even IM_header" data-help="inv_mgr-sum_weeks_inventory">Sum of Adj Weeks Inventory</th>
									<?php
									$evenColumn = false;
									$colClass = 'ME_header_zebra_even';

									foreach ($this->weeks_inv as $weekNum => $weekData)
									{
										if ($evenColumn)
										{
											$colClass = 'ME_header_zebra_even';
										}
										else
										{
											$colClass = 'ME_header_zebra_odd';
										}

										$evenColumn = !$evenColumn;
										?>
										<th scope="col" class="<?php echo  $colClass ; ?> IM_header thick_left_border"  data-help="inv_mgr-proj_weeks_inventory">Proj Weeks Inv</th>
										<th scope="col" class="<?php echo  $colClass ; ?> IM_header " data-help="inv_mgr-servings_remaining_<?php echo $weekNum;?>">Servings Remaining</th>
										<th scope="col" class="<?php echo  $colClass ; ?> IM_header" data-help="inv_mgr-adj_weeks_invntory_<?php echo $weekNum;?>">Adj Weeks Inv</th>
									<?php } ?>

									<!--      <th colspan="1" scope="col" class="ME_header_zebra_odd IM_header thick_left_border">Remaining Inv (new)</th> -->
									<th colspan="1" scope="col" class="ME_header_zebra_odd IM_header thick_left_border"  data-help="inv_mgr-bottom_line">Sum of Adj Weeks Inventory/Remaining Available to Promise</th>
								</tr>

								</thead>
								<?php
								$lastEntreeID = false;
								$groupCount = 0;
								$tabindex = 0;
								foreach ($this->menuInfo['Specials'] as $planNode)
								{
									if (is_array($planNode) && $planNode['pricing_type'] == CMenuItem::FULL)
									{
										$introAttr = "";
										if (!empty($planNode['is_intro']))
										{
											$introAttr = ' data-is_intro="true" ';
										}

										$tasteAttr = "";
										if (!empty($planNode['is_taste']))
										{
											$tasteAttr = ' data-is_taste="true" ';
										}

										?>
										<tr id="row_<?= $planNode['id'] ?>" data-menu_item_id="<?= $planNode['id'] ?>" data-ltd_menu_item_value="<?= $planNode['ltd_menu_item_value'] ?>">

											<!-- National Sales Mix -->
											<td nowrap class="ME_zebra_odd" style="padding: 2px;">
												<span id="nsm_<?= $planNode['recipe_id'] ?>"><?php echo sprintf("%01.2f", $planNode['sales_mix'] * 100.0); ?></span>%
											</td>

											<!-- Store Adjusted Mix -->
											<?php $store_sales_mix = (!empty($planNode['store_sales_mix']) ? sprintf("%01.2f", $planNode['store_sales_mix'] * 100.0) : sprintf("%01.2f", $planNode['sales_mix'] * 100.0)); ?>
											<td nowrap class="ME_zebra_odd" style="padding: 2px;">
												<input  <?php echo $introAttr .$tasteAttr; ?> id="ssmx_<?php echo $planNode['recipe_id']; ?>" class="sales_mix_input <?php echo ($this->menu_state['hasSavedStoreMix'] ? "saved" : "unsaved") ?>" type="number" value="<?php echo $store_sales_mix;?>" data-org_val="<?php echo $store_sales_mix;?>" data-is_bundle="<?php echo $planNode['is_bundle']; ?>" <?php echo !empty($planNode['is_bundle']) ? 'readonly' : ''; ?> step="0.5" min="0" max="100" />
											</td>

											<!-- Title -->
											<td nowrap align="left" class="ME_zebra_even" style="padding: 2px;">
												<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $planNode['id'] . ' &bull; Recipe ID: ' . $planNode['recipe_id'] . '"' : ''; ?> id="rname_<?= $planNode['recipe_id'] ?>"><?= $planNode['display_title'] ?> (<?php echo $planNode['recipe_id'];?>)</span>
												<a href="/?page=item&amp;recipe=<?php echo $planNode['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id'];?>" data-tooltip="Item Page" target="_blank">&gt;</a>
												<?php if (!empty($this->storeInfo->supports_ltd_roundup) && $planNode['ltd_menu_item_value']) { ?><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/menu-icon07.png" class="img_valign" data-tooltip="$1 is added to price to be donated to DDF" /><?php } ?>
											</td>


											<!-- Preorder Projected-->
											<td  id="td_poi_<?php echo $planNode['recipe_id']; ?>" nowrap class="ME_zebra_even thick_left_border <?php echo ($this->menu_state['hasSavedPreOrder'] ? "static_saved_even" : "static_unsaved_even") ?>" style="padding:4px;"><span id="poi_<?php echo $planNode['recipe_id']; ?>" ><?php echo (empty($planNode['initial_inventory']) ? "0": $planNode['initial_inventory'] )?></span></td>

											<td  id="td_totso_<?php echo $planNode['recipe_id']; ?>" nowrap class="ME_zebra_even" style="padding:4px;"><?php echo ($planNode['initial_inventory'] - $planNode['number_sold']); ?></td>

											<!-- Preorder Sum of Weeks -->
											<td  id="td_pwt_<?php echo $planNode['recipe_id']; ?>" nowrap class="ME_zebra_odd <?php echo ($this->menu_state['hasSavedPreOrder'] ? "static_saved_odd" : "static_unsaved_odd") ?>" style="padding:4px;"><span id="pwt_<?php echo $planNode['recipe_id']; ?>" >0</span></td>

											<?php foreach ($this->weeks_inv as $weekNum => $weekData)
											{
												$index = "week" . $weekNum . "_projection";
												$projection = (empty($planNode[$index]) ? 0 : $planNode[$index]);
												?>
												<!-- Preorder Week N Projected-->
												<td id="td_wt<?php echo $weekNum;?>_<?php echo $planNode['recipe_id']; ?>" nowrap class="ME_zebra_even thick_left_border <?php echo ($this->menu_state['hasSavedPreOrder'] ? "static_saved_odd" : "static_unsaved_odd") ?>" style="padding:4px;"><span id="wt<?php echo $weekNum;?>_<?php echo $planNode['recipe_id']; ?>" ><?php echo $projection; ?></span></td>
												<!-- Preorder Week N Final-->
												<td nowrap class="ME_zebra_even" style="padding:4px; text-align:center;">
													<?php
													$halfVal = (!empty($this->sales_summary[$planNode['recipe_id']][CMenuItem::HALF][$weekNum]) ? $this->sales_summary[$planNode['recipe_id']][CMenuItem::HALF][$weekNum] : 0);
													$fullVal = (!empty($this->sales_summary[$planNode['recipe_id']][CMenuItem::FULL][$weekNum]) ? $this->sales_summary[$planNode['recipe_id']][CMenuItem::FULL][$weekNum] : 0);
													echo $projection - ($halfVal + $fullVal); ?>
												</td>

												<td id="td_wta<?php echo $weekNum;?>_<?php echo $planNode['recipe_id']; ?>" nowrap class="ME_zebra_odd" style="padding:4px;">
													<input id="wta<?php echo $weekNum;?>_<?php echo $planNode['recipe_id']; ?>" name="wta<?php echo $weekNum;?>_<?php echo $planNode['recipe_id']; ?>" class="inventory_input <?php echo ($this->menu_state['hasSavedPreOrder'] ? "saved" : "unsaved") ?>" length="4" type="text"
														   value="<?php echo $projection ?>"  data-org_val="<?php echo $projection;?>" /></td>
											<?php } ?>

											<!-- Promise to Sell Control Value-->
											<?php  $override =  (empty($planNode['override_inventory']) ? 0: $planNode['override_inventory']);
											$numSold = (empty($planNode['number_sold']) ? 0: $planNode['number_sold']);
											$remaining = $override - $numSold;?>
											<input id="ovi_<?php echo $planNode['recipe_id']; ?>" data-numsold="<?php echo $numSold; ?>"
												   type="hidden" value="<?php echo (empty($planNode['override_inventory']) ? "0": $planNode['override_inventory'] )?>"
												   data-org_val="<?php echo (empty($planNode['override_inventory']) ? "0": $planNode['override_inventory'] ) ?>" />
											<!-- Remaining Inventory-->
											<!--  <td nowrap class="ME_zebra_odd static_saved_odd thick_left_border" style="padding:4px;"><span  id="ri_<?php echo $planNode['recipe_id']; ?>" ><?php echo $remaining; ?></span></td> -->
											<td id="pts-td_<?php echo $planNode['recipe_id']; ?>" nowrap class="static_saved_odd thick_left_border" style="padding:4px;" data-tooltip="<?php echo 'Stored inventory: ' . $planNode['override_inventory'] . ' Remaining: ' . ($planNode['override_inventory'] - $planNode['number_sold']); ?>">
												<span data-default_inventory="<?php echo $planNode['override_inventory']; ?>" id="cur_adj_inv_<?php echo $planNode['recipe_id']; ?>"><?php echo $planNode['override_inventory']; ?></span>
												/
												<span id="cur_rmg_<?php echo $planNode['recipe_id']; ?>"><?php echo $planNode['override_inventory'] - $planNode['number_sold']; ?></span>
											</td>
										</tr>


										<tr id="tr_act_<?php echo $planNode['recipe_id']; ?>" class="collapse">
											<!-- Title -->
											<td colspan="3" nowrap align="left" class="actuals_cell" style="padding: 4px;text-align: right">Servings Sold</td>

											<td colspan="3" id="td_act_poi_<?php echo $planNode['recipe_id']; ?>" nowrap class="actuals_cell thick_left_border" style="padding:4px;">

												<?php $actualStr = "";
												if (empty($this->sales_summary[$planNode['recipe_id']]['TOTAL'][CMenuItem::FULL]) &&  empty($this->sales_summary[$planNode['recipe_id']]['TOTAL'][CMenuItem::HALF]))
												{
													$actualStr = "0 Svg";
												}
												else
												{
													$total = $this->sales_summary[$planNode['recipe_id']]['TOTAL'][CMenuItem::FULL] + $this->sales_summary[$planNode['recipe_id']]['TOTAL'][CMenuItem::HALF];
													$actualStr = "<b>" . $total . " Svg</b> (M:" . $this->sales_summary[$planNode['recipe_id']]['TOTAL'][CMenuItem::HALF] / 3 . "&nbsp;L:" . $this->sales_summary[$planNode['recipe_id']]['TOTAL'][CMenuItem::FULL] / 6 . ")";
												}
												echo $actualStr;
												?>
											</td>


											<?php foreach ($this->weeks_inv as $weekNum => $weekData)
											{
												$index = "week" . $weekNum . "_projection";
												$projection = (empty($planNode[$index]) ? 0 : $planNode[$index]);
												?>
												<td colspan="3" id="td_act_wt<?php echo $weekNum;?>_<?php echo $planNode['recipe_id']; ?>" nowrap class="actuals_cell thick_left_border <?php echo ($this->menu_state['hasSavedPreOrder'] ? "static_saved_odd" : "static_unsaved_odd") ?>" style="padding:4px;">
													<?php $actualStr = "";
													if (empty($this->sales_summary[$planNode['recipe_id']][CMenuItem::FULL][$weekNum]) &&  empty($this->sales_summary[$planNode['recipe_id']][CMenuItem::HALF][$weekNum]))
													{
														$actualStr = "0 Svg";
													}
													else
													{
														$halfNum = (empty($this->sales_summary[$planNode['recipe_id']][CMenuItem::HALF][$weekNum]) ? 0 : $this->sales_summary[$planNode['recipe_id']][CMenuItem::HALF][$weekNum]);
														$fullfNum = (empty($this->sales_summary[$planNode['recipe_id']][CMenuItem::FULL][$weekNum]) ? 0 : $this->sales_summary[$planNode['recipe_id']][CMenuItem::FULL][$weekNum]);

														$total = $halfNum + $fullfNum;
														$actualStr = "<b>" . $total . " Svg</b> (M:" . $halfNum /  3
															. "&nbsp;L:" . $fullfNum / 6 . ")";
													}
													echo $actualStr;
													?>
												</td>
											<?php } ?>


											<!-- Promise to Sell Control Value-->
											<td colspan="2" nowrap class="static_saved_odd" style="padding:4px;"></td>

										</tr>


									<?php }
								} ?>
							</table>
						</td>
					</tr>
				</table>
			</div>

			<div data-tabid="efl">
				<span id="export_EFL_sales" class="button float-right m-1">Export EFL Sales</span><span style="font-weight:bold; font-size: 18pt;"></span>
				<span class="button float-right m-1" id="save_efl_inv" >Save EFL Inventory</span>


				<table id="EFLitemsTbl" style="width: 100%;">
					<thead>
					<tr style="outline: 1px solid #A5A5A5;">
						<th class="ME_header_zebra_odd" style="width:20px;">Hidden on Web Menu</th>
						<th class="ME_header_zebra_odd" style="width:20px;">Shown on Sides and Sweets Forms</th>
						<th class="ME_header_zebra_even">Item title <span style="font-weight: 100;">(Recipe ID)</span></th>
						<th class="ME_header_zebra_odd">Size</th>
						<th class="ME_header_zebra_inventory_alt">Override Inventory</th>
						<th class="ME_header_zebra_inventory">Number Sold</th>
						<th class="ME_header_zebra_inventory_alt">Remaining Inventory</th>
					</tr>
					</thead>
					<?php
					$lastEntreeID = false;
					$groupCount = 0;
					$tabindex = 0;

					if (!empty($this->menuInfo['Extended Fast Lane'])) {
						foreach ($this->menuInfo['Extended Fast Lane'] as $categoryName => $planNode)
						{
							if (is_array($planNode) && $planNode['pricing_type'] != CMenuItem::INTRO && !($planNode['is_side_dish'] && $planNode['pricing_type'] == CMenuItem::HALF))
							{ ?>
								<tr id="row_<?= $planNode['id'] ?>" data-menu_item_id="<?= $planNode['id'] ?>" data-ltd_menu_item_value="<?= $planNode['ltd_menu_item_value'] ?>">

								<td nowrap class="ME_zebra_odd text-center" style="padding: 2px;">
									<?php if (!$planNode['is_visible']) {  echo "Yes"; } ?>
								</td>

								<td nowrap class="ME_zebra_odd text-center" style="padding: 2px;">
									<?php if ($planNode['show_on_pick_sheet']) {  echo "Yes"; } ?>
								</td>

								<td nowrap align="left" class="ME_zebra_even" style="padding: 2px;">
									<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $planNode['id'] . ' &bull; Recipe ID: ' . $planNode['recipe_id'] . '"' : ''; ?>><?= $planNode['display_title'] ?> (<?php echo $planNode['recipe_id'];?>)</span>
									<a href="/?page=item&amp;recipe=<?php echo $planNode['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id'];?>" data-tooltip="Item Page" target="_blank">&gt;</a>
								</td>
							<?php } ?>

							<?php if ($categoryName == "Sides")
						{ ?>
							<td nowrap class="ME_zebra_odd" style="padding:4px;">-</td>
						<?php }
						else if (isset($planNode['servings_per_item']))
						{

							$sizeStr = "Lrg";
							if ($planNode['servings_per_item'] < 4)
							{
								$sizeStr = "Med";
							}?>
							<td nowrap class="ME_zebra_odd" style="padding:4px;"><?php echo $sizeStr . " (" . $planNode['servings_per_item'] . ")" ;?></td>
						<?php }
						else
						{ ?>
							<td nowrap class="ME_zebra_odd" style="padding:4px;"><?= ($planNode['pricing_type'] == CMenuItem::HALF ? "Med (3)" : "Lrg (6)") ?></td>
						<?php } ?>


							<!-- preview -->

							<?php
							if (true)
							{
								if ($lastEntreeID != $planNode['entree_id'])
								{
									$lastEntreeID = $planNode['entree_id'];
									$groupCount = $planNode['sub_entree_count'];
								}
								if ($groupCount > 1)
								{
									?>

									<td nowrap rowspan="<?= $groupCount ?>" class="ME_zebra_inventory_alt" style="text-align:center; padding: 2px;">
										<input data-numSold="<?= $planNode['number_sold'] ?>" onkeyup="calculatePage();" data-base_inv="<?= $planNode['initial_inventory'] ?>" type="text" id="ofi_<?= $planNode['recipe_id'] ?>" name="ofi_<?= $planNode['recipe_id'] ?>" size="4" maxlength="4" data-orgval="<?= $planNode['override_inventory'] ?>" value="<?= $planNode['override_inventory'] ?>" />
										srv
									</td>
									<td nowrap rowspan="<?= $groupCount ?>" class="ME_zebra_inventory" style="text-align:center; padding: 2px;"><?php echo $planNode['number_sold'] ?>
										servings
									</td>

									<td nowrap rowspan="<?= $groupCount-- ?>" class="ME_zebra_inventory_alt" style="text-align:center; padding: 2px;">
										<span id="atos_<?= $planNode['recipe_id'] ?>"><?= ($planNode['override_inventory'] - $planNode['number_sold']) ?></span>
										servings
									</td>

									<?php
								}
								else if ($planNode['sub_entree_count'] == 1)
								{
									?>

									<td nowrap class="ME_zebra_inventory_alt" style="text-align:center; padding: 2px;">
										<input data-numSold="<?= $planNode['number_sold'] ?>" onkeyup="calculatePage();" data-base_inv="<?= $planNode['initial_inventory'] ?>" type="text" id="ofi_<?php echo $planNode['recipe_id']; ?>" name="ofi_<?= $planNode['recipe_id'] ?>" size="4" maxlength="4" data-orgval="<?= $planNode['override_inventory'] ?>" value="<?= $planNode['override_inventory'] ?>" />
										srv
									</td>
									<td nowrap rowspan="<?= $groupCount ?>" class="ME_zebra_inventory" style="text-align:center; padding: 2px;"><?php echo $planNode['number_sold'] ?>
										servings
									</td>

									<td nowrap class="ME_zebra_inventory_alt" style="text-align:center; padding: 2px;"><span id="atos_<?= $planNode['recipe_id'] ?>"><?= ($planNode['override_inventory'] - $planNode['number_sold']) ?></span>
										<br />servings
									</td>

									<?php
								}
							} ?>
							</tr>
						<?php }
					}   else { ?>
						<tr id="empty_menu_message"><td colspan="12" style="text-align:center;"><span style="font-style: italic;">No Extended Fast Lane items have been added to this menu.  Click the "Add EFL Menu Item" button to add an item.</span></td></tr>
					<?php } ?>

				</table>

			</div>
			<?php if (!defined('USE_GLOBAL_SIDES_MENU') || !USE_GLOBAL_SIDES_MENU) { ?>
				<div data-tabid="sides">

					<span style="margin-bottom:2px; float:right;" class="button" id="save_sides_inv" >Save Sides Inventory</span>

					<div class="clear"></div>

					<table id="ctsItemsTbl" style="width: 100%;">
						<thead>
						<tr style="outline: 1px solid #A5A5A5;">
							<th class="ME_header_zebra_odd">Is Shown on Web Menu</th>
							<th class="ME_header_zebra_odd">Shown on Sides and Sweet Forms</th>
							<th class="ME_header_zebra_odd">Is Hidden Everywhere</th>
							<th class="ME_header_zebra_even">Item title <span style="font-weight: 100;">(Recipe ID)</span></th>
							<th class="ME_header_zebra_odd">Size</th>
							<th class="ME_header_zebra_inventory" style="text-align:center;">Number Sold</th>
							<th class="ME_header_zebra_inventory_alt" style="text-align:center;">Override Inventory</th>
							<th class="ME_header_zebra_inventory" style="text-align:center;">Available to Sell</th>
						</tr>
						</thead>
						<?php
						if (!empty($this->CTSMenu))
						{
							foreach ($this->CTSMenu as $id => $ctsItem)
							{ ?>
								<tr id="row_<?= $ctsItem['id'] ?>" data-menu_item_id="<?= $ctsItem['id'] ?>" data-ltd_menu_item_value="<?= $ctsItem['ltd_menu_item_value'] ?>">
									<td nowrap class="ME_zebra_odd text-center" style="padding: 2px;">
										<?php if ($ctsItem['is_visible']) {  echo "Yes"; } ?>
									</td>

									<td nowrap class="ME_zebra_odd text-center" style="padding: 2px;">
										<?php if ($ctsItem['show_on_order_form']) {  echo "Yes"; } ?>
									</td>

									<td nowrap class="ME_zebra_odd text-center" style="padding: 2px;">
										<?php if ($ctsItem['is_hidden_everywhere']) {  echo "Yes"; } ?>
									</td>

									<td nowrap align="left" class="ME_zebra_even" style="padding: 2px;">
										(<?= $ctsItem['subcategory_label'] ?>)&nbsp;

										<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $ctsItem['id'] . ' &bull; Recipe ID: ' . $ctsItem['recipe_id'] . '"' : ''; ?>><?= $ctsItem['display_title'] ?> (<?php echo $ctsItem['recipe_id'];?>)</span>
										<a href="/?page=item&amp;recipe=<?php echo $ctsItem['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id'];?>" data-tooltip="Item Page" target="_blank">&gt;</a>

										<div id="rec_id_<?= $ctsItem['id'] ?>" style="display:none"><?= $ctsItem['recipe_id'] ?></div>
									</td>

									<td nowrap class="ME_zebra_odd" style="text-align:center; padding: 4px;">1 item</td>

									<!-- preview -->
									<td nowrap class="ME_zebra_inventory" style="text-align:center; padding: 2px;"><?= $ctsItem['number_sold'] ?><br />items</td>
									<td nowrap class="ME_header_zebra_inventory_alt" style="text-align:center; padding: 2px;">
										<input data-number_sold="<?= $ctsItem['number_sold'] ?>" onkeyup="calculatePage();"
											   data-base_inv="<?= $ctsItem['initial_inventory'] ?>" type="text" id="ori_<?= $ctsItem['recipe_id'] ?>"
											   name="ori_<?= $ctsItem['recipe_id'] ?>" size="4" maxlength="4" data-orgval="<?= $ctsItem['override_inventory'] ?>" value="<?= $ctsItem['override_inventory'] ?>" />
										items
									</td>
									<td nowrap class="ME_zebra_inventory" style="text-align:center; padding: 2px;">
										<span id="atos_<?= $ctsItem['recipe_id'] ?>"><?= ($ctsItem['override_inventory'] - $ctsItem['number_sold']) ?></span><br />items
									</td>
								</tr>
							<?php }
						}
						else
						{ ?>
							<tr>
								<td colspan="9" align="center"><i>There are no Sides &amp; Sweets items for this Menu</i></td>
							</tr>
						<?php } ?>
					</table>

				</div>
			<?php } else { ?>
				<div data-tabid="sides">

					<span style="margin-bottom:2px; float:right;" class="button" id="save_sides_inv" >Save Sides Inventory</span>

					<div class="clear"></div>

					<table id="ctsItemsTbl" style="width: 100%;">
						<thead>
						<tr style="outline: 1px solid #A5A5A5;">
							<th class="ME_header_zebra_even">Item title <span style="font-weight: 100;">(Recipe ID)</span></th>
							<th class="ME_header_zebra_odd">Size</th>
							<th class="ME_header_zebra_inventory" style="text-align:center;">Sold &mdash; awaiting pickup</th>
							<th class="ME_header_zebra_inventory_alt" style="text-align:center;">Inventory On Hand</th>
							<th class="ME_header_zebra_inventory" style="text-align:center;">Available to Sell</th>
						</tr>
						</thead>
						<?php
						if (!empty($this->CTSMenu))
						{
							foreach ($this->CTSMenu as $id => $ctsItem)
							{ ?>
								<tr id="row_<?= $ctsItem['id'] ?>" data-menu_item_id="<?= $ctsItem['id'] ?>" data-ltd_menu_item_value="<?= $ctsItem['ltd_menu_item_value'] ?>">

									<td nowrap align="left" class="ME_zebra_even" style="padding: 2px;">
										(<?= $ctsItem['subcategory_label'] ?>)&nbsp;

										<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $ctsItem['id'] . ' &bull; Recipe ID: ' . $ctsItem['recipe_id'] . '"' : ''; ?>><?= $ctsItem['display_title'] ?> (<?php echo $ctsItem['recipe_id'];?>)</span>
										<a href="/?page=item&amp;recipe=<?php echo $ctsItem['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id'];?>" data-tooltip="Item Page" target="_blank">&gt;</a>

										<div id="rec_id_<?= $ctsItem['id'] ?>" style="display:none"><?= $ctsItem['recipe_id'] ?></div>
									</td>

									<td nowrap class="ME_zebra_odd" style="text-align:center; padding: 4px;">1 item</td>

									<!-- preview -->
									<td nowrap class="ME_zebra_inventory" style="text-align:center; padding: 2px;"><?= $ctsItem['future_pickups'] ?><br />items</td>
									<td nowrap class="ME_header_zebra_inventory_alt" style="text-align:center; padding: 2px;">
										<input data-futurePickups="<?= $ctsItem['future_pickups'] ?>" onkeyup="calculatePage();" data-base_inv="<?= $ctsItem['initial_inventory'] ?>"
											   type="text" id="ori_<?= $ctsItem['recipe_id'] ?>" name="ori_<?= $ctsItem['recipe_id'] ?>" size="4" maxlength="4" data-orgval="<?= $ctsItem['override_inventory'] ?>" value="<?= $ctsItem['override_inventory'] ?>" />
										items
									</td>
									<td nowrap class="ME_zebra_inventory" style="text-align:center; padding: 2px;">
										<span id="atos_<?= $ctsItem['recipe_id'] ?>"><?= ($ctsItem['override_inventory'] - $ctsItem['future_pickups']) ?></span><br />items
									</td>
								</tr>
							<?php }
						}
						else
						{ ?>
							<tr>
								<td colspan="9" align="center"><i>There are no Sides &amp; Sweets items for this Menu</i></td>
							</tr>
						<?php } ?>
					</table>

				</div>

			<?php } ?>
		</div>



	</div>

	</div>
	<!--
				<table style="width:100%;">
					<tr align="right">
						<td>
						</td>
						<td><span id="saved_message_2" style="color:red; font-weight:bold; display:none">Your changes have not yet been saved. Finalize all changes to see updated values.</span><br />
							<input name="submit_changes" id="submit_changes" type="button" value="Finalize All Changes" class="button" onclick="confirm_and_check_form()" />
							<input type="button" value="Reset to Current" onclick="resetPage();" class="button" />
						</td>
					</tr>
				</table>

	-->
</form>

</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>