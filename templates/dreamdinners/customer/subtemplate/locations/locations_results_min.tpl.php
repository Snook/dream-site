<?php if(!empty($this->store_results_array)) { ?>
	<?php foreach( $this->store_results_array as $szState => $arStores ) { ?>
		<div class="row">
			<div class="col">
				<h5 class="text-green mb-3"><?php echo $szState; ?> Assembly Kitchens</h5>

				<?php $counter = 0; foreach( $arStores as $id => $arStore ) { $counter++; ?>
					<div class="row">
						<div class="col pl-4">
							<div class="custom-control custom-radio">
								<input type="radio" name="store_id" id="store_id-<?php echo $id; ?>" class="custom-control-input" value="<?php echo $id; ?>" <?php echo ($arStore['checked']) ? 'checked="checked"' : ''; ?> />
								<label class="custom-control-label" for="store_id-<?php echo $id; ?>"><?php echo $arStore['store_name']; ?></label>
							</div>
							<div class="pl-4 mt-1">
								<p class="font-size-small"><i class="dd-icon icon-location font-size-small text-green-dark"></i> <a target="map_view" onclick="showMap('<?php echo urlencode($arStore["DAO_store"]->address_linear); ?>');event.preventDefault();" href="<?php echo $arStore['DAO_store']->generateMapLink(); ?>"><?php echo $arStore["DAO_store"]->address_linear; ?></a></p>
							</div>
						</div>
					</div>

				<?php } ?>
                <div class="row">
                    <div class="col pl-4">
                        <div class="custom-control custom-radio">
                            <input type="radio" name="store_id" id="store_id-no_store" class="custom-control-input" value="no_store"/>
                            <label class="custom-control-label" for="store_id-no_store">Do not select local store</label>
                        </div>
                    </div>
                </div>
			</div>
		</div>

	<?php } } else { ?>

	<h5>No stores found near the supplied address</h5>

<?php } ?>