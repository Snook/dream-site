<?php
// $this->menu_items is the entire list of menu items, we need to determine which ones are shown case-by-case
foreach ($this->menu_items as $id => $item)
{
	$this->assignRef('curItem', $item);
	$mainItem = $this->curItem[key($this->curItem)];
	$this->itemArray = $this->curItem;
	if ($mainItem->isVisibleAndNotHiddenEverywhere())
	{
		if (!empty($this->DAO_bundle))
		{
			if ($mainItem->isInBundle($this->DAO_bundle))
			{
				include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item.tpl.php');
			}
		}
		else
		{
			if ($this->menu_view == 'session_menu' && $mainItem->isMenuItem_Core())
			{
				include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item.tpl.php');
			}
			else if ($this->menu_view == 'session_menu_freezer' && $mainItem->isFreezer())
			{
				include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item.tpl.php');
			}
		}
	}
}
?>