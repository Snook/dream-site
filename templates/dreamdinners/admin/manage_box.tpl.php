<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col-lg-6 text-center mb-3 order-lg-2">
				<h1><a href="main.php?page=admin_manage_box">Manage Boxes</a></h1>
			</div>
			<div class="col-8 col-lg-3 order-lg-1">
				<?php if (!$this->singleStore && empty($this->editBox) && empty($this->createBox)) { ?>
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<div class="input-group-text"><i class="fas fa-filter"></i></div>
						</div>
						<select class="form-control form-control-sm store-select-filter">
							<option value="">Show All</option>
							<option value="base">Base Boxes</option>
							<!--<option value="310">Mobile - Dist. Ctr.</option>
							<option value="311">Salt Lake - Dist. Ctr.</option>-->
							<option value="312">Rochester Hills - Dist. Ctr.</option>
						</select>
					</div>
				<?php } ?>
			</div>
			<div class="col-4 col-lg-3 text-center text-lg-right order-lg-3">
				<a  href="main.php?page=admin_manage_box&amp;create=true" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i> Create New</a>
			</div>
		</div>

		<?php if (!empty($this->boxArray)) { ?>

			<?php foreach ($this->boxArray['boxArray']['all'] AS $box) { ?>
				<div class="row border mb-2 bg-white" data-box_store_id="<?php echo ((!empty($box->store_id)) ? $box->store_id : 'base'); ?>">
					<div class="col-md-6 py-2">
						<div class="row">
							<div class="col-12 mb-4">
								<h2><?php echo ((!empty($box->store_obj->store_name)) ? $box->store_obj->store_name : 'Base Box'); ?></h2>
							</div>
							<div class="col-12 mb-4 text-center pl-4">
								<div class="row font-weight-bold">
									<div class="col-4 bg-gray py-2">Menu</div>
									<div class="col-4 bg-gray py-2">From</div>
									<div class="col-4 bg-gray py-2">Through</div>
								</div>
								<div class="row">
									<div class="col-4"><?php echo $box->menu_obj->menu_name; ?></div>
									<div class="col-4">
										<div><?php echo CTemplate::formatDateTime('M d, Y', $box->availability_date_start, $box->store_obj); ?></div>
										<div><?php echo CTemplate::formatDateTime('h:i A T', $box->availability_date_start, $box->store_obj); ?></div>
									</div>
									<div class="col-4">
										<div><?php echo CTemplate::formatDateTime('M d, Y', $box->availability_date_end, $box->store_obj); ?></div>
										<div><?php echo CTemplate::formatDateTime('h:i A T', $box->availability_date_end, $box->store_obj); ?></div>
									</div>
								</div>
								<div class="row font-weight-bold">
									<div class="col-3 bg-gray py-2">Sort</div>
									<div class="col-3 bg-gray py-2"># Orders</div>
									<div class="col-3 bg-gray py-2"># Sold</div>
									<div class="col-3 bg-gray py-2">On web</div>
								</div>
								<div class="row">
									<div class="col-3"><?php echo $box->sort; ?></div>
									<div class="col-3"><?php echo $box->orders_n; ?></div>
									<div class="col-3"><?php echo $box->number_sold_n; ?></div>
									<div class="col-3"><?php echo (($box->is_visible_to_customer) ? 'Yes' : 'No'); ?></div>
								</div>
							</div>
							<div class="col-6 mb-2">
								<a href="main.php?page=admin_manage_box&amp;edit=<?php echo $box->id; ?>" class="btn btn-primary btn-block">Edit box</a>
							</div>
							<div class="col-6 mb-2">
								<?php if ($box->availability_date_end > CTemplate::formatDateTime()) { ?>
									<span class="btn btn-primary btn-block box-expire" data-box_id="<?php echo $box->id; ?>">Expire box</span>
								<?php } ?>
							</div>
							<?php if (empty($box->store_id)) { ?>
								<div class="col-12">
									<span class="btn btn-primary btn-block box-deploy" data-box_id="<?php echo $box->id; ?>">Deploy to stores</span>
								</div>
							<?php } ?>
						</div>
					</div>
					<div class="col-md-6 py-2">
						<div class="col-12">
							<h2>Preview</h2>
						</div>
						<div class="card">
							<div class="card-body text-center">
								<div class="row mt-2">
									<div class="col">
										<i class="dd-icon <?php echo $box->css_icon; ?> font-size-extra-extra-large text-green"></i>
									</div>
								</div>
								<h5 class="card-title font-size-medium">
									<?php echo $box->title; ?>
								</h5>
								<p class="card-text mb-3">
									<?php echo $box->description; ?>
								</p>
							</div>
							<div class="card-footer border-0">
								<div class="row">
									<?php if (!empty($box->box_bundle_1_active)) { ?>
										<div class="col-lg-<?php echo ((!empty($box->box_bundle_2_active)) ? '6' : '12'); ?>">
											<div class="btn btn-primary btn-block disabled">
												$<?php echo $box->box_bundle_1_obj->price; ?> <?php echo $box->box_bundle_1_obj->bundle_name; ?>
											</div>
											<div class="text-muted font-size-small text-center"><?php echo $box->box_bundle_1_obj->menu_item_description; ?></div>
										</div>
									<?php } ?>
									<?php if (!empty($box->box_bundle_2_active)) { ?>
										<div class="col-lg-<?php echo ((!empty($box->box_bundle_1_active)) ? '6' : '12'); ?>">
											<div class="btn btn-primary btn-block disabled">
												$<?php echo $box->box_bundle_2_obj->price; ?> <?php echo $box->box_bundle_2_obj->bundle_name; ?>
											</div>
											<div class="text-muted font-size-small text-center"><?php echo $box->box_bundle_2_obj->menu_item_description; ?></div>
										</div>
									<?php } ?>

								</div>
							</div>
						</div>
					</div>
				</div>

			<?php } ?>

		<?php } ?>

		<?php if (!empty($this->editBox) || !empty($this->createBox)) { ?>


			<form id="BoxForm" name="BoxForm" data-processor="admin_manage_box" data-processor_method="post" class="needs-validation" novalidate>
				<?php echo $this->BoxForm['hidden_html']; ?>

				<div class="form-row">
					<div class="form-group col-md-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="store_id">Store</label>
							</div>
							<?php echo $this->BoxForm["store_id_html"]; ?>
						</div>
					</div>
					<div class="form-group col-md-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="menu_id">Menu</label>
							</div>
							<?php echo $this->BoxForm["menu_id_html"]; ?>
						</div>
					</div>
					<div class="form-group col-md-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="box_type">Box Type</label>
							</div>
							<?php echo $this->BoxForm["box_type_html"]; ?>
						</div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-5">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="title">Title</label>
							</div>
							<?php echo $this->BoxForm["title_html"]; ?>
						</div>
					</div>
					<div class="form-group col-md-3">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small css_icon_preview" data-toggle="tooltip" data-placement="top" data-html="true" title="" data-original-title="<i class='dd-icon font-size-extra-extra-large <?php echo $this->BoxForm["css_icon"]; ?>'></i>" for="css_icon"><i class="dd-icon <?php echo $this->BoxForm["css_icon"]; ?>"></i></label>
							</div>
							<?php echo $this->BoxForm["css_icon_html"]; ?>
						</div>
					</div>
					<div class="form-group col-md-2">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="sort">Sort</label>
							</div>
							<?php echo $this->BoxForm["sort_html"]; ?>
						</div>
					</div>
					<div class="form-group col-md-2">
						<div class="input-group-text font-size-small text-white-space-wrap"><?php echo $this->BoxForm["is_visible_to_customer_html"]; ?></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-6 mb-md-0">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="availability_date_start">Date Start</label>
							</div>
							<?php echo $this->BoxForm["availability_date_start_html"]; ?>
						</div>
					</div>
					<div class="form-group col-md-6 mb-0">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="availability_date_end">Date End</label>
							</div>
							<?php echo $this->BoxForm["availability_date_end_html"]; ?>
						</div>
					</div>
					<div class="form-group col">
						<div class="text-muted text-center">
							Based on Eastern timezone, current time is <span class="notice-current-time"><?php echo CTemplate::formatDateTime('h:m A T'); ?></span>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col">
						<div class="input-group">
							<div class="input-group-prepend">
								<label class="input-group-text font-size-small" for="description">Description</label>
							</div>
							<?php echo $this->BoxForm["description_html"]; ?>
						</div>
					</div>
				</div>

			</form>

			<?php if (!empty($this->editBox)) { ?>

				<div class="row">
					<div class="col-md-6">
						<form id="Bundle1Form" name="Bundle1Form" data-processor="admin_manage_box" data-processor_method="post" class="needs-validation" novalidate>
							<h5><?php echo $this->BoxForm["box_bundle_1_active_html"]; ?></h5>
							<?php echo $this->Bundle1Form['hidden_html']; ?>

							<div class="form-row">
								<div class="form-group col">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Button Title</label>
										</div>
										<?php echo $this->Bundle1Form["bundle_name_html"]; ?>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Box Note</label>
										</div>
										<?php echo $this->Bundle1Form["menu_item_description_html"]; ?>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Required Items</label>
										</div>
										<?php echo $this->Bundle1Form["number_items_required_html"]; ?>
									</div>
								</div>
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Req. Servings</label>
										</div>
										<?php echo $this->Bundle1Form["number_servings_required_html"]; ?>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Price</label>
										</div>
										<?php echo $this->Bundle1Form["price_html"]; ?>
									</div>
								</div>
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Shipping</label>
										</div>
										<?php echo $this->Bundle1Form["price_shipping_html"]; ?>
									</div>
								</div>
							</div>

						</form>

					</div>
					<div class="col-md-6">
						<form id="Bundle2Form" name="Bundle2Form" data-processor="admin_manage_box" data-processor_method="post" class="needs-validation" novalidate>
							<h5><?php echo $this->BoxForm["box_bundle_2_active_html"]; ?></h5>
							<?php echo $this->Bundle2Form['hidden_html']; ?>
							<div class="form-row">
								<div class="form-group col">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Button Title</label>
										</div>
										<?php echo $this->Bundle2Form["bundle_name_html"]; ?>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Box Note</label>
										</div>
										<?php echo $this->Bundle2Form["menu_item_description_html"]; ?>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Required Items</label>
										</div>
										<?php echo $this->Bundle2Form["number_items_required_html"]; ?>
									</div>
								</div>
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Req. Servings</label>
										</div>
										<?php echo $this->Bundle2Form["number_servings_required_html"]; ?>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Price</label>
										</div>
										<?php echo $this->Bundle2Form["price_html"]; ?>
									</div>
								</div>
								<div class="form-group col-md-6">
									<div class="input-group">
										<div class="input-group-prepend">
											<label class="input-group-text font-size-small">Shipping</label>
										</div>
										<?php echo $this->Bundle2Form["price_shipping_html"]; ?>
									</div>
								</div>
							</div>

						</form>

					</div>
				</div>

			<?php } ?>

			<div class="row mb-3">
				<div class="col">
					<?php echo $this->BoxForm["BoxFormSubmit_html"]; ?>
				</div>
			</div>

			<?php if (!empty($this->editBox)) { ?>

				<div class="row">
					<div class="col-6">
						<div id="bundle_1_menu_items"<?php echo (empty($this->BoxForm["box_bundle_1_active"])) ? ' class="collapse"' : ''; ?>>

							<table class="table table-sm table-striped">
								<?php if (!empty($this->menuItems['size'][CMenuItem::HALF])) { ?>
									<?php foreach ($this->menuItems['size'][CMenuItem::HALF] AS $category_id => $menuItems) { $subcat_label = false; ?>
										<tr>
											<th colspan="3"><?php echo $this->menuItems['category_titles'][$category_id]; ?></th>
										</tr>
										<?php foreach ($menuItems AS $menuItem) { ?>
											<?php if (!empty($menuItem->subcategory_label) && $subcat_label != $menuItem->subcategory_label) { $subcat_label = $menuItem->subcategory_label; ?>
												<tr>
													<th colspan="3" class="text-center"><?php echo $menuItem->subcategory_label; ?></th>
												</tr>
											<?php } ?>
											<tr>
												<td><?php echo $menuItem->recipe_id; ?></td>
												<td><label for="bundle_1_check_<?php echo $menuItem->id; ?>" class="text-decoration-hover-underline"><?php echo $menuItem->menu_item_name; ?></label></td>
												<td><?php echo $this->Bundle1Form["bundle_1_check_" . $menuItem->id . "_html"]; ?></td>
											</tr>
										<?php } ?>
									<?php } ?>
								<?php } ?>
							</table>

						</div>
					</div>
					<div class="col-6">
						<div id="bundle_2_menu_items"<?php echo (empty($this->BoxForm["box_bundle_2_active"])) ? ' class="collapse"' : ''; ?>>

							<table class="table table-sm table-striped">
								<?php if (!empty($this->menuItems['size'][CMenuItem::FULL])) { ?>
									<?php foreach ($this->menuItems['size'][CMenuItem::FULL] AS $category_id => $menuItems) { $subcat_label = false; ?>
										<tr>
											<th colspan="3"><?php echo $this->menuItems['category_titles'][$category_id]; ?></th>
										</tr>
										<?php foreach ($menuItems AS $menuItem) { ?>
											<?php if (!empty($menuItem->subcategory_label) && $subcat_label != $menuItem->subcategory_label) { $subcat_label = $menuItem->subcategory_label; ?>
												<tr>
													<th colspan="3" class="text-center"><?php echo $menuItem->subcategory_label; ?></th>
												</tr>
											<?php } ?>
											<tr>
												<td><?php echo $menuItem->recipe_id; ?></td>
												<td><label for="bundle_2_check_<?php echo $menuItem->id; ?>" class="text-decoration-hover-underline"><?php echo $menuItem->menu_item_name; ?></label></td>
												<td><?php echo $this->Bundle2Form["bundle_2_check_" . $menuItem->id . "_html"]; ?></td>
											</tr>
										<?php } ?>
									<?php } ?>
								<?php } ?>
							</table>

						</div>
					</div>
				</div>
			<?php } ?>

		<?php } ?>

	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>