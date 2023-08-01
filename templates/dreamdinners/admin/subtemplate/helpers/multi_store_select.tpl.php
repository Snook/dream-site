<div class="container-fluid">
	<h4 class="text-uppercase">Select stores</h4>
	<div class="row my-2">
		<div class="col-3 align-self-center font-weight-bold text-right">
			All
		</div>
		<div class="col-9">
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="all"><i class="far fa-check-square"></i> All</button>
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="none"><i class="far fa-check-square"></i> None</button>
		</div>
	</div>
	<div class="row my-2">
		<div class="col-3 align-self-center font-weight-bold text-right">
			Active
		</div>
		<div class="col-9">
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="active-all"><i class="far fa-check-square"></i> All</button>
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="active-corporate"><i class="far fa-check-square"></i> Corporate</button>
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="active-non-corporate"><i class="far fa-check-square"></i> Non Corporate</button>
		</div>
	</div>
	<div class="row my-2">
		<div class="col-3 align-self-center text-muted text-right">
			Inactive
		</div>
		<div class="col-9">
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="inactive-all"><i class="far fa-check-square"></i> All</button>
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="inactive-corporate"><i class="far fa-check-square"></i> Corporate</button>
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="inactive-non-corporate"><i class="far fa-check-square"></i> Non Corporate</button>
		</div>
	</div>
	<div class="row my-2">
		<div class="col-3 align-self-center font-weight-bold text-right">
			Dist. Ctr.
		</div>
		<div class="col-9">
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="dist-all"><i class="far fa-check-square"></i> All</button>
			<button type="button" class="btn btn-primary btn-sm" data-multi_store_select_filter="dist-none"><i class="far fa-check-square"></i> None</button>
		</div>
	</div>
	<div class="row mt-4">
		<div class="col">
			<form id="multi_store_select_form">
				<ul class="list-unstyled">
					<?php foreach (CStore::getListOfStores(false) AS $state => $stateArray) { ?>
						<li class="mb-2" data-state_name="<?php echo $state; ?>"><div class="font-weight-bold mb-2"><?php echo str_replace("_", " ", $state); ?></div>
							<ul class="list-unstyled ml-4">
								<?php foreach ($stateArray['stores'] AS $store) { ?>
									<li>
										<div class="custom-control custom-control-inline custom-checkbox">
											<input class="custom-control-input"
												   id="storeSelect[<?php echo $store['id']; ?>]"
												   name="storeSelect[<?php echo $store['id']; ?>]"
												   value="<?php echo $store['id']; ?>"
												   data-franchise_id="<?php echo $store['franchise_id']; ?>"
												   data-active="<?php echo $store['active']; ?>"
												   data-show_on_customer_site="<?php echo $store['show_on_customer_site']; ?>"
												   data-store_type="<?php echo $store['store_type']; ?>"
												   type="checkbox"
											<?php echo (!empty($this->store_id_array) && in_array($store['id'], $this->store_id_array)) ? 'checked="checked"' : ''; ?> />
											<label class="custom-control-label <?php if (empty($store['active'])) { ?>text-muted<?php } else { ?>font-weight-bold<?php } ?>" for="storeSelect[<?php echo $store['id']; ?>]">
												<?php echo $store['store_name']; ?>
												<?php if (empty($store['active']) && !empty($store['show_on_customer_site'])) { ?>
													<span class="text-orange font-weight-bold">(Coming soon)</span>
												<?php } ?>
											</label>
										</div>
									</li>
								<?php } ?>
							</ul>
						</li>
					<?php } ?>
				</ul>
			</form>
		</div>
	</div>
</div>