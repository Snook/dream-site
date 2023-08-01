<?php
$showCategoryLabel = false;
foreach ($this->menu_items as $id => $item)
{
	$this->assignRef('curItem', $item);
	$mainItem = $this->curItem[key($this->curItem)];
	$otherSizeItem = $this->curItem[$id];
	if ($mainItem->isVisible() || $otherSizeItem->isVisible())
	{
		if ($this->menu_view == 'session_menu_freezer' && $mainItem->isFreezer())
		{
			$this->itemArray = $this->curItem;
			$categoryLabel = (!empty($mainItem->is_store_special)) ? 'Pre-Assembled Add On Dinners' : $mainItem->subcategory_label;
			?>
			<?php if ($showCategoryLabel != $categoryLabel) { $showCategoryLabel = $categoryLabel; ?>
			<div class="col-12">
				<div class="row p-1">
					<div class="col p-0 text-center bg-cyan-extra-dark">
						<p class="text-white text-uppercase font-weight-bold my-0 py-2"><i class="dd-icon icon-ss-<?php echo str_replace(' ', '_', preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($categoryLabel))); ?>"></i> <?php echo $categoryLabel; ?></p>
					</div>
				</div>
			</div>
		<?php } ?>
			<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item.tpl.php'); ?>
		<?php } ?>
	<?php } ?>
<?php } ?>