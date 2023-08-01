<?php if (empty($this->menuItemArray)) { ?>

	<p>No results.</p>

<?php } else { ?>

	<p class="font-size-small">Reminder, Sides &amp; Sweets are only available in the system as <?php echo CMenuItem::translatePricingType('FULL'); ?>. If you would like the coupon to apply a Sides &amp; Sweets item, select the <?php echo CMenuItem::translatePricingType('FULL'); ?> version.</p>

	<table class="table table-striped table-sm ddtemp-table-border-collapse">
		<?php foreach ($this->menuItemArray AS $menuItem) { ?>
			<tr>
				<td><?php echo $menuItem->recipe_id; ?></td>
				<td><?php echo $menuItem->menu_item_name; ?></td>
				<td><?php echo $menuItem->pricing_type_info['pricing_type_name']; ?></td>
				<td><button class="btn btn-primary w-100 select-recipe-id" data-menu_item_name="<?php echo $menuItem->menu_item_name; ?>" data-pricing_type_name_short="<?php echo $menuItem->pricing_type_info['pricing_type_name_short']; ?>" data-recipe_id="<?php echo $menuItem->recipe_id; ?>" data-pricing_type="<?php echo $menuItem->pricing_type; ?>">Select</button></td>
			</tr>
		<?php } ?>
	</table>

<?php } ?>