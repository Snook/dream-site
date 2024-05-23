<?php $this->assign('page_title', 'Nutritionals'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Nutritionals</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main class="container-fluid">
		<div class="row mb-2 d-print-none">
			<div class="col">
				<?php echo $this->form['menus_dropdown_html']; ?>
			</div>
			<div class="col text-right">
				<a href="/?<?php echo $_SERVER['QUERY_STRING']; ?>&export=csv" class="btn btn-sm btn-primary">Download</a>
			</div>
		</div>

		<?php if (empty($this->nutritional_array)) {?>
			<p class="font-weight-bold text-center font-size-large">No nutritional information available for the date selected.</p>
		<?php } else { ?>

			<div class="table-responsive">
				<table class="table table-sm">
					<tr>
						<td colspan="2" class="title">Menu Nutritional Information<sup>1</sup><br /><?php echo $this->menu['menu_name']; ?></td>
						<td colspan="12">
							<?php if ($this->store) { ?>
								<div class="font-weight-bold"><?php echo $this->store['store_name']; ?></div>
								<div><?php echo $this->store['address_line1']; ?><?php if (!empty($this->store['address_line2'])) { ?>, <?php echo $this->store['address_line2']; ?><?php } ?></div>
								<div><?php echo $this->store['city']; ?>, <?php echo $this->store['state_id']; ?> <?php echo $this->store['postal_code']; ?></div>
								<div><?php echo $this->store['telephone_day']; ?></div>
							<?php } ?>
						</td>
					</tr>
					<?php foreach ($this->nutritional_array AS $category => $nutritional_array) { ?>
						<tr>
							<th colspan="2"><?php echo $nutritional_array['name']; ?></th>
							<th class="text-right">Time to Prep<sup>2</sup></th>
							<th class="text-right">Serving Size</th>
							<th>Cal.</th>
							<th>Fat</th>
							<th>Sat.<br/>Fat</th>
							<th>Chole-<br/>sterol</th>
							<th>Carbs</th>
							<th>Fiber</th>
							<th>Sugar</th>
							<th>Protein</th>
							<th>Sodium</th>
							<th>Notes</th>
						</tr>
						<?php foreach ($nutritional_array['nutritionals'] AS $recipe_id => $recipe) {

							if (isset($recipe['info']['has_inventory']) && $this->filter_zero_inventory && !$recipe['info']['has_inventory'])
								continue;
							?>

							<?php $component_count = 0; ?>
							<?php foreach ($recipe['component'] AS $component_id => $component) { $component_count++ ?>
								<tr>
									<?php if ($component_count == 1) { ?>
										<td rowspan="<?php echo count($recipe['component']); ?>" class="font-weight-bold"><a href="/item?recipe=<?php echo $recipe_id; ?>"><?php echo $recipe['info']['recipe_name']; ?></a></td>
										<td rowspan="<?php echo count($recipe['component']); ?>">
											<ul class="list-group list-group-horizontal list-inline">
											<?php if (false && $recipe['info']['flag_heart_healthy']) { // disabled 9-23-2020 ?><li><i class="font-size-medium-small dd-icon icon-health text-gray-dark" data-toggle="tooltip" data-placement="top" title="Heart Healthy"></i></li><?php } ?>
											<?php if ($recipe['info']['flag_grill_friendly']) { ?><li><i class="font-size-medium-small dd-icon icon-grill text-gray-dark" data-toggle="tooltip" data-placement="top" title="Grill Option"></i></li><?php } ?>
											<?php if ($recipe['info']['flag_cooks_from_frozen']) { ?><li><i class="font-size-medium-small dd-icon icon-frozen text-gray-dark" data-toggle="tooltip" data-placement="top" title="Cooks from Frozen"></i></li><?php } ?>
											<?php if ($recipe['info']['flag_crockpot']) { ?><li><i class="font-size-medium-small dd-icon icon-crock-pot text-gray-dark" data-toggle="tooltip" data-placement="top" title="Crock-Pot Option"></i></li><?php } ?>
											<?php if ($recipe['info']['flag_instant_pot']) { ?><li><i class="font-size-medium-small dd-icon icon-instant-pot text-gray-dark" data-toggle="tooltip" data-placement="top" title="Instant Pot Option"></i></li><?php } ?>
											<?php if ($recipe['info']['flag_under_400']) { ?><li><i class="font-size-medium-small dd-icon icon-calories text-gray-dark" data-toggle="tooltip" data-placement="top" title="Under 500 Calories"></i></li><?php } ?>
											<?php if ($recipe['info']['flag_no_added_salt']) { ?><li><i class="font-size-medium-small dd-icon icon-no-added-salt text-gray-dark" data-toggle="tooltip" data-placement="top" title="No Added Salt"></i></li><?php } ?>
											<?php if ($recipe['info']['flag_under_thirty']) { ?><li><i class="font-size-medium-small dd-icon icon-minutes_30 text-gray-dark" data-toggle="tooltip" data-placement="top" title="Cooks in under 30 Minutes"></i></li><?php } ?>
											</ul>
										</td>
										<td rowspan="<?php echo count($recipe['component']); ?>" class="text-right"><?php echo $recipe['info']['prep_time']; ?></td>
									<?php } ?>
									<td class="text-right"><?php echo ucfirst($component['serving']) . ((!empty($component['serving_weight'])) ? ' (' . $component['serving_weight'] . 'g)' : ''); ?></td>
									<td><?php echo CTemplate::formatDecimal($component['Calories']['value']); ?><?php echo $component['Calories']['measure_label']; ?></td>
									<td><?php echo $component['Fat']['prefix'] . CTemplate::formatDecimal($component['Fat']['value']); ?><?php echo $component['Fat']['measure_label']; ?></td>
									<td><?php echo $component['Sat Fat']['prefix'] . CTemplate::formatDecimal($component['Sat Fat']['value']); ?><?php echo $component['Sat Fat']['measure_label']; ?></td>
									<td><?php echo $component['Cholesterol']['prefix'] . CTemplate::formatDecimal($component['Cholesterol']['value']); ?><?php echo $component['Cholesterol']['measure_label']; ?></td>
									<td><?php echo $component['Carbs']['prefix'] . CTemplate::formatDecimal($component['Carbs']['value']); ?><?php echo $component['Carbs']['measure_label']; ?></td>
									<td><?php echo $component['Fiber']['prefix'] . CTemplate::formatDecimal($component['Fiber']['value']); ?><?php echo $component['Fiber']['measure_label']; ?></td>
									<td><?php echo $component['Sugars']['prefix'] . CTemplate::formatDecimal($component['Sugars']['value']); ?><?php echo $component['Sugars']['measure_label']; ?></td>
									<td><?php echo $component['Protein']['prefix'] . CTemplate::formatDecimal($component['Protein']['value']); ?><?php echo $component['Protein']['measure_label']; ?></td>
									<td><?php echo $component['Sodium']['prefix'] . CTemplate::formatDecimal($component['Sodium']['value']); ?><?php echo $component['Sodium']['measure_label']; ?></td>
									<?php if ($component_count == 1) { ?>
										<td rowspan="<?php echo count($recipe['component']); ?>"><?php echo $recipe['info']['notes']; ?></td>
									<?php } ?>
								</tr>
							<?php } // end foreach $component ?>
						<?php } // end foreach $recipe ?>
					<?php } ?>
				</table>
			</div>

			<p>With Dream Dinners, healthy eating is easy for everyone.</p>

			<div class="row">
				<div class="col">
					<i class="font-size-medium-large align-middle dd-icon icon-minutes_30 text-gray-dark"></i> Takes 30 minutes or less to prepare
				</div>
			</div>
			<div class="row">
				<div class="col">
					<i class="font-size-medium-large align-middle dd-icon icon-grill text-gray-dark"></i> Grill Option
				</div>
			</div>
			<div class="row">
				<div class="col">
					<i class="font-size-medium-large align-middle dd-icon icon-crock-pot text-gray-dark"></i> Crock-Pot Option
				</div>
			</div>
			<div class="row">
				<div class="col">
					<i class="font-size-medium-large align-middle dd-icon icon-instant-pot text-gray-dark"></i> Instant Pot Option
				</div>
			</div>
			<div class="row">
				<div class="col">
					<i class="font-size-medium-large align-middle dd-icon icon-calories text-gray-dark"></i> Under 500 Calories
				</div>
			</div>
			<?php if (false) { //video_disabled ?>
			<div class="row">
				<div class="col">
					<i class="font-size-medium-large dd-icon icon-video text-gray-dark"></i> Instructional Video
				</div>
			</div>
			<?php } ?>
			<div class="row">
				<div class="col">
					<i class="font-size-medium-large align-middle dd-icon icon-frozen text-gray-dark"></i> Cooks from Frozen
				</div>
			</div>
			<?php if (false) { // disabled 9-23-2020 ?>
			<div class="row">
				<div class="col">
					<i class="font-size-medium-large align-middle dd-icon icon-health text-gray-dark"></i>
					Dream Dinners "Heart Healthy" selection: each serving contains no more than 10 grams fat*, 7 grams saturated fat, 95 mg cholesterol &amp; 650 mg sodium (recommended limits for a 2,000 calorie daily diet are 20 grams of saturated fat and 2,300 milligrams of sodium)<br />
					<span>*Menu items containing heart healthy fats can receive the "heart healthy" designation even though fat grams exceed the 10 gram cut off.</span>
				</div>
			</div>
			<?php } ?>

			<p class="d-print-none">Sides &amp; Sweets Freezer Items - May not include all available items as selections vary by store. Contact your store if your item is not listed.</p>

			<p class="font-italic">
				<sup>1</sup>Nutritional information is per serving unless otherwise stated and are based on standard formulations. For example, if one Large dinner was divided into 6 equal portions, the nutritional information is accurate for one portion. Variations may occur due to manufacture/supplier alterations and individual assembly and preparation.<br />
				<sup>2</sup>Times indicated are for 6 serving dinners, some 3 serving dinners may vary slightly with less times.
			</p>

		<?php } ?>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>