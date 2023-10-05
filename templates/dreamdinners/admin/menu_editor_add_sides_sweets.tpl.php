<table id="recipe_list_yes" style="width: 100%;">
	<thead>
	<tr>
		<th colspan="4" style="text-align: right;"><input id="filter" name="filter" type="text" placeholder="Filter" /> <span id="clear_filter" class="btn btn-primary btn-sm">Clear</span></th>
	</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<div id="list_div" style="overflow-y: scroll; height:360px;">
	<table id="recipe_list" style="width: 100%;">
		<tbody>
		<?php foreach ($this->menuItems as $menu_id => $menuItems) { ?>
			<tr>
				<th colspan="5" class="bgcolor_medium header_row"><?php echo $menuItems['menu_info']['name']; ?></th>
			</tr>
			<?php foreach ($menuItems['items'] as $recipe_id => $menuItem) { ?>
				<tr class="bgcolor_light" data-recipe_id_row="<?php echo $recipe_id; ?>">
					<td style="width:50px;"><button class="btn btn-primary btn-sm" data-info_menu_side="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>" data-entree_id="<?php echo $menuItem['id']; ?>">Info</button></td>
					<td style="width: 416px;"><span data-tooltip="Recipe Id: <?php echo $menuItem['recipe_id'] . '<br>Last Menu: ' .$menuItems['menu_info']['name']. '<br>Description: ' .htmlspecialchars($menuItem['description']); ?>"><?php echo htmlspecialchars($menuItem['name']); ?></span></td>

					<?php if ($menuItem['category_locked'] ) { ?>
						<td style="width: 416px;"><span data-tooltip="Category for this item has already been set for this menu." data-recipe_category="<?php echo $menuItem['id']; ?>"><?php echo htmlspecialchars($menuItem['category']); ?></span></td>
					<?php } else { ?>
						<td style="width: 416px;">
							<select style="display:none;" data-recipe_category="<?php echo $menuItem['id']; ?>">
								<option value="Breakfast">Breakfast</option>
								<option value="Bundles">Bundles</option>
								<option value="Dessert">Dessert</option>
								<option value="Sides">Sides</option>
								<option value="Soup & Sandwiches">Soup & Sandwiches</option>
								<option value="Veggies">Veggies</option>
							</select>
							<span data-old_category="<?php echo $menuItem['id']; ?>" class=""><?php echo htmlspecialchars($menuItem['category']); ?></span>
						</td>

					<?php } ?>
					<td style="width: 60px;">$<?php echo $menuItem['price']; ?></td>
					<td style="text-align: right; width: 90px;">
						<span data-add_menu_side="<?php echo $menuItem['id']; ?>" data-entree_id="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>" class="btn btn-primary btn-sm">Add</span>
						<span style="display:none;" data-rmv_menu_side="<?php echo $menuItem['id']; ?>" data-entree_id="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>" class="btn btn-primary btn-sm">Cancel</span>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
	</table>
</div>

<hr />
<h3>Sides and Sweets Selected to Add</h3>

<div id="sel_list" style="overflow-y: scroll; height:160px;">
	<table id="selected_items" style="width: 100%;">
		<tbody>
		</tbody>
	</table>
</div>