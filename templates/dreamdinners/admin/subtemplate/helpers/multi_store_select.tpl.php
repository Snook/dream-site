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
	<?php if (!$this->UserCurrent->isFranchiseAccess()) { ?>
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
	<?php } ?>
	<div class="row mt-4">
		<div class="col">
			<form id="multi_store_select_form">
				<ul class="list-unstyled">
					<?php foreach ($this->store_array AS $state => $stateArray) { ?>
						<li class="mb-2" data-state_name="<?php echo $state; ?>"><div class="font-weight-bold mb-2"><?php echo str_replace("_", " ", $state); ?></div>
							<ul class="list-unstyled ml-4">
								<?php foreach ($stateArray['stores'] AS $DAO_store) { ?>
									<li>
										<div class="custom-control custom-control-inline custom-checkbox">
											<input class="custom-control-input"
												   id="storeSelect[<?php echo $DAO_store->id; ?>]"
												   name="storeSelect[<?php echo $DAO_store->id; ?>]"
												   value="<?php echo $DAO_store->id; ?>"
												   data-franchise_id="<?php echo $DAO_store->franchise_id; ?>"
												   data-active="<?php echo $DAO_store->active; ?>"
												   data-show_on_customer_site="<?php echo $DAO_store->show_on_customer_site; ?>"
												   data-store_type="<?php echo $DAO_store->store_type; ?>"
												   type="checkbox"
											<?php echo (!empty($this->store_id_array) && in_array($DAO_store->id, $this->store_id_array)) ? 'checked="checked"' : ''; ?> />
											<label class="custom-control-label <?php if ($DAO_store->isActive()) { ?>font-weight-bold<?php } else { ?>text-muted<?php } ?>" for="storeSelect[<?php echo $DAO_store->id; ?>]">
												<?php echo $DAO_store->store_name; ?>
												<?php if ($DAO_store->isComingSoon()) { ?>
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