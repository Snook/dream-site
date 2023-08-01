<div class="container-fluid">
	<h4 class="text-uppercase">Select stores</h4>
	<div class="row my-2">
		<div class="col text-center">
			<button type="button" class="btn btn-primary btn-sm storeSelector-select-all"><i class="far fa-check-square"></i> All</button>
			<button type="button" class="btn btn-primary btn-sm storeSelector-select-none"><i class="far fa-check-square"></i> None</button>
			<button type="button" class="btn btn-primary btn-sm storeSelector-select-corporate"><i class="far fa-check-square"></i> Corporate</button>
			<button type="button" class="btn btn-primary btn-sm storeSelector-select-not-corporate"><i class="far fa-check-square"></i> Not Corporate</button>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<form id="storeSelector">
				<ul class="list-unstyled">
					<?php foreach (CStore::getListOfStores(true) AS $state => $stateArray) { ?>
						<li class="mb-2" data-state_name="<?php echo $state; ?>"><div class="font-weight-bold mb-2"><?php echo str_replace("_", " ", $state); ?></div>
							<ul class="list-unstyled ml-4">
								<?php foreach ($stateArray['stores'] AS $store) { ?>
									<li>
										<div class="custom-control custom-control-inline custom-checkbox">
											<input class="custom-control-input" id="storeSelect[<?php echo $store['id']; ?>]" name="storeSelect[<?php echo $store['id']; ?>]" value="<?php echo $store['id']; ?>" data-storeSelect_franchise_id="<?php echo $store['franchise_id']; ?>" type="checkbox" <?php echo (!empty($this->store_id_array) && in_array($store['id'], $this->store_id_array)) ? 'checked="checked"' : ''; ?> />
											<label class="custom-control-label" for="storeSelect[<?php echo $store['id']; ?>]"><?php echo $store['store_name']; ?></label>
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