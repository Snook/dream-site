<table id="recipe_list_yes" style="width: 100%;">
	<thead>
	<tr>
		<th colspan="3" style="text-align: right;"><input id="filter" name="filter" type="text" placeholder="Filter" /> <span id="clear_filter" class="btn btn-primary btn-sm">Clear</span></th>
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
				<th colspan="4" class="bgcolor_medium header_row"><?php echo $menuItems['menu_info']['name']; ?></th>
			</tr>
			<?php foreach ($menuItems['items'] as $recipe_id => $menuItem) { ?>
				<tr class="bgcolor_light" data-recipe_id_row="<?php echo $recipe_id; ?>">
					<td style="width:50px;"><button class="btn btn-primary btn-sm" data-info_menu_item="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>" data-entree_id="<?php echo $menuItem['id']; ?>">Info</button></td>
					<td style="width: 416px;"><span data-tooltip="Recipe Id: <?php echo $menuItem['recipe_id'] . '<br>Last Menu: ' .$menuItems['menu_info']['name']. '<br>Description: ' .htmlspecialchars($menuItem['description']); ?>"><?php echo htmlspecialchars($menuItem['name']); ?></span></td>
					<td style="text-align: right; width: 90px;">
						<span data-add_menu_item="<?php echo $menuItem['id']; ?>" data-entree_id="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>" class="btn btn-primary btn-sm">Add to EFL</span>
						<span style="display:none;" data-rmv_menu_item="<?php echo $menuItem['id']; ?>" data-entree_id="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>" class="btn btn-primary btn-sm">Cancel</span>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
	</table>
</div>

<hr />
<h3>Items Selected to Add</h3>

<div id="sel_list" style="overflow-y: scroll; height:160px;">
	<table id="selected_items" style="width: 100%;">
		<tbody>
		</tbody>
	</table>
</div>