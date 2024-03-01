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
		<?php echo $this->form[$DAO_menu_item->id . '_ovr_html']; ?>
	</td>

	<td class="align-middle">
		<?php echo $DAO_menu_item->override_inventory - $DAO_menu_item->number_sold; ?>
	</td>

</tr>