<div class="row">
	<div class="col">
		<div class="input-group mb-2">
			<input class="form-control dd-strip-tags" id="add_menu_item_filter" type="text" placeholder="Search" />
			<div class="input-group-append">
				<button id="add_menu_item_clear_filter" class="btn btn-primary btn-sm coupon-search">Clear</button>
			</div>
		</div>

	</div>
</div>

<div style="overflow-y: scroll; height:360px;">
	<table id="add_menu_item_recipe_list" class="table table-sm table-striped table-hover table-hover-cyan ddtemp-table-border-collapse" data-unsaved_entrees="{}">
		<?php foreach ($this->menuItems as $menu_id => $menuItems) { ?>
			<tr>
				<th colspan="3" class="text-center"><?php echo $menuItems['menu_info']['name']; ?></th>
			</tr>
			<?php foreach ($menuItems['items'] as $recipe_id => $menuItem) { ?>
				<tr>
					<td><span class="btn btn-primary w-100" data-add_menu_item_info="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>" data-entree_id="<?php echo $menuItem['id']; ?>">Info</span></td>
					<td>
						<div data-tooltip="Recipe Id: <?php echo $menuItem['recipe_id'] . '<br>Last Menu: ' .$menuItems['menu_info']['name']. '<br>Description: ' .htmlspecialchars($menuItem['description']); ?>"><?php echo htmlspecialchars($menuItem['name']); ?> (<?php echo $recipe_id; ?>)</div>
						<div class="add_menu_item_info" data-recipe_id="<?php echo $recipe_id; ?>" data-entree_id="<?php echo $menuItem['id']; ?>" data-fetched="false"></div>
					</td>
					<td><span class="btn btn-primary w-100 text-nowrap" data-add_menu_item="<?php echo $menuItem['id']; ?>" data-entree_id="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>">Add to menu</span></td>
				</tr>
			<?php } ?>
		<?php } ?>
	</table>
</div>

<hr />

<h3>Items Selected to Add</h3>

<div style="overflow-y: scroll; height:160px;">
	<table class="table table-sm table-striped table-hover table-hover-cyan ddtemp-table-border-collapse">
		<?php foreach ($this->menuItems as $menu_id => $menuItems) { ?>
			<?php foreach ($menuItems['items'] as $recipe_id => $menuItem) { ?>
				<tr class="row_menu_editor_add_item collapse" data-recipe_id="<?php echo $recipe_id; ?>">
					<td><span data-tooltip="Recipe Id: <?php echo $menuItem['recipe_id'] . '<br>Last Menu: ' .$menuItems['menu_info']['name']. '<br>Description: ' .htmlspecialchars($menuItem['description']); ?>"><?php echo htmlspecialchars($menuItem['name']); ?> (<?php echo $recipe_id; ?>)</span></td>
					<td><span class="btn btn-primary w-100 " data-add_menu_item_cancel="<?php echo $menuItem['id']; ?>" data-entree_id="<?php echo $menuItem['id']; ?>" data-recipe_id="<?php echo $recipe_id; ?>">Cancel</span></td>
				</tr>
			<?php } ?>
		<?php } ?>
	</table>
</div>