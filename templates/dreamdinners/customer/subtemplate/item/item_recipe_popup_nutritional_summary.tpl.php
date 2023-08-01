<div class="row mb-2">
	<div class="col text-right">
		<a class="btn btn-primary btn-sm" href="main.php?page=item&recipe=<?php echo $this->menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->menu_item->menu_id; ?>">Full details</a>
	</div>
</div>
<div class="row">
	<?php foreach ($this->menu_item->nutrition_array['component'] as $thisCompNum => $thisComp) { ?>
		<div class="col-12">
			<table class="table table-sm table-hover">
				<thead>
				<tr>
					<th colspan="2" scope="col"><span class="font-size-large">Nutrition Facts</span></th>
				</tr>
				<tr>
					<td colspan="2" scope="col">
						<div>3 servings per container (Medium)</div>
						<div>6 servings per container (Large)</div>
						<div class="row font-weight-bold">
							<div class="col">
								Serving size
							</div>
							<div class="col text-right">
								<div><?php echo ucfirst($thisComp['info']['serving']); ?></div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th colspan="2" scope="col">
						<div class="row">
							<div class="col">
								<div>Amount per serving</div>
							</div>
						</div>
						<div class="row">
							<div class="col font-size-medium">
								<?php echo $thisComp['element']['Calories']['label']; ?>
							</div>
							<div class="col text-right font-size-large">
								<div><?php echo $thisComp['element']['Calories']['value']; ?></div>
							</div>
						</div>
					</th>
				</tr>
				<tr>
					<th colspan="2" scope="col" class="text-right">% Daily Value*</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($thisComp['element'] as $nutriLabel => $nutriData) { ?>
					<?php if ($nutriLabel != 'Calories') { include $this->loadTemplate('customer/subtemplate/item/item_recipe_nutrition_element.tpl.php'); } ?>
				<?php } ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2" scope="col" class="font-size-small">
						* The % Daily Value (DV) tells you how much a nutrient in a serving of food contributes to a daily diet. 2,000 calories a day is used for general nutrition advice.
					</td>
				</tr>
				</tfoot>
			</table>
		</div>
	<?php } ?>

	<div class="col-12">
		<?php if (!empty($this->menu_item->DAO_recipe->allergens)) { ?>
			<p class="mb-0">Contains: <?php echo $this->menu_item->DAO_recipe->allergens; ?></p>
		<?php } ?>
		<p>May Contain: Milk, Eggs, Fish, Shellfish, Tree Nuts, Peanuts, Wheat, Soybeans, Sesame.</p>

		<?php if(!empty($this->menu_item->DAO_recipe->ingredients)){ ?>
			<p>Ingredients: <?php echo $this->menu_item->DAO_recipe->ingredients; ?></p>
		<?php } ?>

		<p>Nutritional information is per serving unless otherwise stated and are based on standard formulations. For example, if one Large dinner was divided into 6 equal portions, the nutritional information is accurate for one portion. Variations in ingredients and preparation, as well as substitutions, will increase or decrease any stated nutritional values. Items vary by store, and are subject to change. Contact your local store for further assistance.</p>
	</div>

</div>