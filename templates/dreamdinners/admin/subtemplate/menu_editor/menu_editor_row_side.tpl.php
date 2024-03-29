<tr id="row_<?php echo $DAO_menu_item->id; ?>" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
	<td class="align-middle">
		<?php echo $this->form[$DAO_menu_item->id . '_vis_html']; ?>
	</td>

	<td class="align-middle">
		<?php echo $this->form[$DAO_menu_item->id . '_form_html']; ?>
	</td>

	<td class="align-middle">
		<?php echo $this->form[$DAO_menu_item->id . '_hid_html']; ?>
	</td>

	<td class="align-middle text-left">
		<a href="/item?recipe=<?php echo $DAO_menu_item->recipe_id; ?>&amp;ov_menu=<?php echo $this->DAO_menu->id; ?>" class="link-dinner-details" data-tooltip="Dinner Details"
		   data-recipe_id="<?php echo $DAO_menu_item->recipe_id; ?>"
		   data-store_id="<?php echo $this->CurrentBackOfficeStore->id; ?>"
		   data-menu_item_id="<?php echo $DAO_menu_item->id; ?>"
		   data-menu_id="<?php echo $this->DAO_menu->id; ?>"
		   data-size="large"
		   data-detailed="true"
		   target="_blank"><i class="fas fa-file-alt font-size-medium-small mr-1"></i></a>
		<span<?php echo ($this->form_login['user_type'] == CUser::SITE_ADMIN) ? ' data-tooltip="Menu ID: ' . $DAO_menu_item->id . ' &bull; Recipe ID: ' . $DAO_menu_item->recipe_id . '"' : ''; ?>>
			<?php echo $DAO_menu_item->menu_item_name; ?> (<?php echo $DAO_menu_item->recipe_id; ?>)
		</span>
		<?php if (!empty($DAO_menu_item->is_bundle)) { ?><i class="fas fa-layer-group font-size-small" data-tooltip="<?php echo (!empty($DAO_menu_item->admin_notes)) ? $DAO_menu_item->admin_notes : 'Meal bundle' ?>"></i><?php } ?>
		<div id="rec_id_<?php echo $DAO_menu_item->id; ?>" class="collapse"><?php echo $DAO_menu_item->recipe_id; ?></div>
	</td>

	<td class="align-middle">1 item</td>

	<td class="align-middle">
		<?php echo $DAO_menu_item->getStorePrice(); ?>
	</td>

	<td class="align-middle">
		<div class="input-group flex-nowrap">
			<?php echo $this->form[$DAO_menu_item->id . '_ovr_html']; ?>
			<div class="input-group-append">
				<span class="ovr-alert-danger input-group-text collapse" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
					<i class="fas fa-exclamation-triangle text-danger" data-toggle="tooltip" data-placement="top" title="Price outside highest tier price <?php echo !empty($DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price) ? ' of ' . $DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price : ''; ?> and lowest tier price<?php echo !empty($DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price) ? '  of ' . $DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price : ''; ?>"></i>
				</span>
				<span class="ovr-alert-warning input-group-text collapse" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>">
					<i class="fas fa-exclamation-circle text-warning" data-toggle="tooltip" data-placement="top" title="Recommended pricing ends with .49 or .99"></i>
				</span>
			</div>
		</div>
	</td>

	<td class="align-middle">
		<?php echo $DAO_menu_item->override_inventory - $DAO_menu_item->number_sold; ?>
	</td>

</tr>