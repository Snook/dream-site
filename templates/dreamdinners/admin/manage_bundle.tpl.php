<?php $this->setScript('head', SCRIPT_PATH . '/admin/manage_bundle.min.js'); ?>
<?php $this->setScriptVar('editable = ' . ($this->editable ? 'true' : 'false') . ';'); ?>
<?php $this->setOnload('manage_bundle_init();'); ?>
<?php $this->assign('page_title','Manage Bundles'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1><a href="/backoffice/manage_bundle">Manage Bundles</a></h1>
			</div>
		</div>

		<?php if (!empty($this->editBundle) || !empty($this->createBundle)) { ?>

			<form method="post">
				<?php echo $this->form['hidden_html']; ?>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label class="font-weight-bold" for="menu">Menu</label>
						<?php echo $this->form['menu_id_html']; ?>
					</div>
					<div class="form-group col-md-6">
						<label class="font-weight-bold">Bundle Type</label>
						<?php echo $this->form['bundle_type_html']; ?>
					</div>
					<div class="form-group col-md-6">
						<label class="font-weight-bold">Bundle Name</label>
						<?php echo $this->form['bundle_name_html']; ?>
					</div>
					<div class="form-group col-md-6">
						<label class="font-weight-bold">Bundle Description</label>
						<?php echo $this->form['menu_item_description_html']; ?>
					</div>
					<div class="master_menu_item_option form-group col-md-6 <?php echo (!empty($this->editBundle) && $this->editBundle->isMasterItem()) ? '' : 'collapse'; ?>">
						<label class="font-weight-bold">Master Menu Item ID</label>
						<?php echo $this->form['master_menu_item_html']; ?>
					</div>
					<div class="master_menu_item_option form-group col-md-3 <?php echo (!empty($this->editBundle) && $this->editBundle->isMasterItem()) ? '' : 'collapse'; ?>">
						<label class="font-weight-bold">Servings Per Item</label>
						<?php echo $this->form['servings_per_item_html']; ?>
					</div>
					<div class="master_menu_item_option form-group col-md-3 <?php echo (!empty($this->editBundle) && $this->editBundle->isMasterItem()) ? '' : 'collapse'; ?>">
						<label class="font-weight-bold">Item Count Per Item</label>
						<?php echo $this->form['item_count_per_item_html']; ?>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-3">
						<label class="font-weight-bold">Price</label>
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									$
								</div>
							</div>
							<?php echo $this->form['price_html']; ?>
						</div>
					</div>
					<div class="form-group col-md-3">
						<label class="font-weight-bold">Number of Items Required</label>
						<?php echo $this->form['number_items_required_html']; ?>
					</div>
					<div class="form-group col-md-3">
						<label class="font-weight-bold">Number of Servings Required</label>
						<?php echo $this->form['number_servings_required_html']; ?>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-12 text-center">
						<?php if ($this->editable) { ?>
							<?php echo $this->form['submit_html']; ?>
							<?php echo array_key_exists('delete_html',$this->form )? $this->form['delete_html']:''; ?>
						<?php } else { ?>
							<span class="btn btn-primary btn-sm disabled">Orders have been placed against his bundle, editing is now disabled.</span>
						<?php } ?>
					</div>
				</div>
			</form>

			<id id="menu_items">
				<?php if (!empty($this->editBundle)) { ?>
					<table class="table table-hover table-hover-cyan table-sm table-striped bg-white ddtemp-table-border-collapse">
						<tr class="text-center">
							<th colspan="4">Bundle Menu Items</th>
						</tr>
						<?php foreach ($this->menuItems AS $category => $menuItems) { ?>
							<?php if (is_array($menuItems)) { ?>
								<tr class="text-center">
									<th>Recipe ID</th>
									<th><?php echo $category; ?></th>
									<th></th>
								</tr>
								<?php foreach ($menuItems AS $entree_id => $DAO_menu_item) { ?>
									<tr>
										<td class="text-center"><?php echo $DAO_menu_item['menu_item_info']['recipe_id']; ?></td>
										<td><?php echo $DAO_menu_item['menu_item_info']['menu_item_name']; ?></td>
										<td class="text-center">
											<?php foreach ($DAO_menu_item['menu_item'] AS $DAO_menu_item) { ?>
												<?php if (!$DAO_menu_item->isBundle()) { ?>
													<div class="custom-control custom-checkbox custom-control-inline">
														<input class="custom-control-input" type="checkbox"
															   id="menu_item-<?php echo $DAO_menu_item->id; ?>"
															   data-bundle_id="<?php echo $this->editBundle->id; ?>"
															   data-bundle_menu_item_id="<?php echo $DAO_menu_item->id; ?>"
															   <?php if (in_array($DAO_menu_item->id, $this->bundleArray[$this->editBundle->id]->menu_item_ids)) { ?>checked="checked"<?php } ?>
															   <?php if (!$this->editable) { ?>disabled="disabled"<?php } ?> />
														<label class="custom-control-label" for="menu_item-<?php echo $DAO_menu_item->id; ?>"> <?php echo $DAO_menu_item->pricing_type_info['pricing_type_name_short_w_qty']; ?></label>
													</div>
												<?php } ?>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</table>
				<?php } ?>
			</id>

		<?php } else { ?>

			<table class="table table-hover table-hover-cyan table-striped-2 bg-white ddtemp-table-border-collapse">
				<thead>
				<tr class="text-center">
					<th>Menu</th>
					<th>Type</th>
					<th>Bundle Name</th>
					<th># of Items</th>
					<th>Servings Req</th>
					<th>Price</th>
					<th><a href="/backoffice/manage_bundle?create" class="btn btn-primary btn-sm">Create Bundle</a></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($this->bundleArray AS $bundle) { ?>
					<tr>
						<td><?php echo $bundle->DAO_menu->menu_name; ?></td>
						<td><?php echo $bundle->bundle_type; ?></td>
						<td><?php echo $bundle->bundle_name; ?></td>
						<td class="text-right"><?php echo $bundle->menu_item_count; ?></td>
						<td class="text-right"><?php echo $bundle->number_servings_required; ?></td>
						<td class="text-right">$<?php echo $bundle->price; ?></td>
						<td class="text-right">
							<span data-bundle_id="<?php echo $bundle->id; ?>" class="view_items button">Items</span>
							<a href="/backoffice/manage_bundle?edit=<?php echo $bundle->id; ?>" data-bundle_id="<?php echo $bundle->id; ?>" class="edit_bundle button">Edit</a>
						</td>
					</tr>
					<tr data-bundle_menu_items="<?php echo $bundle->id; ?>" class="collapse">
						<td colspan="7">
							<ul>
								<?php foreach($bundle->menu_item_ids AS $bundle_item) { ?>
									<li><?php echo $this->menuItemsArray[$bundle_item]->menu_item_name; ?> - <?php echo $this->menuItemsArray[$bundle_item]->pricing_type_info['pricing_type_name_short_w_qty']; ?></li>
								<?php } ?>
							</ul>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

		<?php } ?>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>