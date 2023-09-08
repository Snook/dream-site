<?php $this->setScript('head', SCRIPT_PATH . '/admin/import_menu.min.js'); ?>
<?php $this->assign('page_title','Import Menu Items'); ?>
<?php $this->assign('topnav','import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col-lg-6 text-center mb-3 order-lg-2">
				<h1><a href="?page=admin_import_menu_reciprofity">Import Menu Items</a></h1>
			</div>
			<div class="col-8 col-lg-3 order-lg-1">

			</div>
			<div class="col-4 col-lg-3 text-center text-lg-right order-lg-3">

			</div>
		</div>

		<?php if (empty($this->import_success)) { ?>

			<form id="form_import_menu" method="post" enctype="multipart/form-data">

				<div class="row mb-3">
					<div class="col-12">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="menu">Menu</label>
							</div>
							<?php echo $this->ImportForm['menu_html']; ?>
							<div class="input-group-append">
								<?php echo $this->ImportForm['multi_store_select_html']; ?>
							</div>
						</div>
					</div>
					<div class="col-12">
						<div class="p-4">Default store selection is all stores that are set to "Active", "Show on Customer site" or "Enable Menu Builder". Importing new items will go to the selected stores. Updating a menu will update the existing menu items for all current stores, regardless of selection, and if new items are added they will only be deployed to the selected stores. If deploying menu items to a Distribution Center, choose the Distribution Center and the parent store.</div>
						<div class="px-4">Checkboxes are used for updating existing items. If the item being imported is a new addition, the field is required and the checkbox selection is ignored.</div>
						<div class="text-center my-2">
							<div id="checkbox_select_none" class="btn btn-primary">Select None</div>
							<div id="checkbox_select_all" class="btn btn-primary">Select All</div>
							<div id="checkbox_select_update_default" class="btn btn-primary">Select Typical Updates</div>
							<div id="checkbox_select_tier_pricing" class="btn btn-primary">Select Tier Pricing</div>
						</div>
					</div>
				</div>

				<div class="row mb-4">
					<div class="col">
						<ul class="list-group mb-3">
							<li class="list-group-item"><?php echo $this->ImportForm['import_menu_item_name_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_menu_item_description_html']; ?></li>
							<li class="list-group-item">Recipe ID (C) - Must be unique on a given menu across all menu categories</li>
							<li class="list-group-item">Size (C) - Determined from Recipe ID underscore flag, 123_L or 123_M for Large and Medium</li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_menu_class_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_sub_category_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_food_cost_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_price_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_servings_per_order_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_price_tier_1_md_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_price_tier_1_lg_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_price_tier_2_md_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_price_tier_2_lg_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_price_tier_3_md_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_price_tier_3_lg_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_serving_suggestions_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_station_number_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_initial_menu_html']; ?></li>
							<li class="list-group-item">Menu sub-sort (X) - Determines what order the menu items are displayed, does not change with updates</li>
							<li class="list-group-item">Sales mix (Y) - Core items should equal 100%, 8% or higher get auto labeled as Guest Favorite</li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_nutritional_serverings_per_container_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_prep_time_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_instructions_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_instructions_air_fryer_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_instructions_crock_pot_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_instructions_grill_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['import_best_prepared_by_html']; ?></li>
						</ul>

						<h3 class="mb-2">Options</h3>

						<ul class="list-group">
							<li class="list-group-item"><?php echo $this->ImportForm['option_update_menu_label_html']; ?></li>
							<li class="list-group-item"><?php echo $this->ImportForm['option_update_pricing_tier_override_price_html']; ?></li>
						</ul>
					</div>
				</div>

				<div class="row mb-4">
					<div class="col">
						<?php echo $this->ImportForm['base_menu_import_html']; ?>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<div class="input-group">
							<?php echo $this->ImportForm['submit_menu_import_html']; ?>
							<div class="input-group-append pl-4">
								<?php echo $this->ImportForm['testmode_html']; ?>
							</div>
						</div>

						<img id="processing_image" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" style="display: none;" alt="Processing" />
					</div>
				</div>

			</form>

		<?php } else if ($this->testmode) { ?>

			<table class="table">
				<thead>
				<tr>
					<th colspan="2" class="bgcolor_dark catagory_row">Dry Run Results</th>
				</tr>
				<tr>
					<th class="bgcolor_medium header_row">Item</th>
					<th class="bgcolor_medium header_row" style="width:300px;">Result</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->changelog AS $change) { ?>
					<tr>
						<td style="font-weight:bold; font-size:larger;"><?php echo $change['item']; ?></td>
						<td><span class="changelog_<?php echo $change['event']; ?>"><?php echo ucfirst($change['event']); ?></span></td>
					</tr>

					<?php if (isset($change['diff'])) { ?>
						<?php foreach($change['diff'] as $thisDiff) { ?>
							<?php if (isset($thisDiff['diff'])) { ?>
								<tr>
									<td class="bgcolor_light" colspan="2">
										<div class="font-weight-bold"><?php echo $thisDiff['name']; ?></div>
										<div><span class="bg-green">Diff:</span> <?php echo $thisDiff['diff']; ?></div>
									</td>
								</tr>
							<?php } else { ?>
								<tr>
									<td class="bgcolor_light" colspan="2">
										<div class="font-weight-bold"><?php echo $thisDiff['name']; ?></div>
										<div><span class="bg-danger text-white">Was:</span> <?php echo $thisDiff['org']; ?></div>
										<div><span class="bg-green">Now:</span> <?php echo $thisDiff['new']; ?></div>
									</td>
								</tr>
							<?php } ?>
						<?php } ?>

					<?php  } else if (isset($change['message'])) { ?>
						<tr>
							<td  class="bgcolor_light" colspan="2">
								<?php $change['message']; ?>
							</td>
						</tr>
					<?php } } // end foreach change (item) ?>

				</tbody>
			</table>

		<?php } else { ?>

			<p>Menu imported, view the <a href="?page=admin_menu_inspector&menus=<?php echo $this->menu_id; ?>" class="button">Menu Inspector</a> to verify.</p>

			<table style="width: 100%;">
				<thead>
				<tr>
					<th colspan="2" class="bgcolor_dark catagory_row">Change log</th>
				</tr>
				<tr>
					<th class="bgcolor_medium header_row">Title</th>
					<th class="bgcolor_medium header_row">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->changelog AS $change) { ?>
					<tr>
						<td class="bgcolor_light"><?php echo $change['message']; ?></td>
						<td class="bgcolor_light"><span class="changelog_<?php echo $change['event']; ?>"><?php echo ucfirst($change['event']); ?></span></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

		<?php } ?>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>