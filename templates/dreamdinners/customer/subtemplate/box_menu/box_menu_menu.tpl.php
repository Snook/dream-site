<?php
foreach ($this->box_bundle_info->menu_item['items'] as $id => $item)
{
	$this->assignRef('curItem', $item);
	$mainItem = $this->curItem[key($this->curItem)];
	$this->itemArray = $this->curItem;
	if (!empty($mainItem->DAO_bundle_to_menu_item->current_offering))
	{
		include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item.tpl.php');
	}
}
?>