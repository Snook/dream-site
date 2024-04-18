<?php
// $this->menu_items is the entire list of menu items, we need to determine which ones are shown case-by-case
$coreEflCount = 0;
foreach ($this->menu_items as $id => $item)
{
	$this->assignRef('curItem', $item);
	$mainItem = $this->curItem[key($this->curItem)];
	$this->itemArray = $this->curItem;
	if ($mainItem->isVisible())
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
			if ($this->menu_view == 'session_menu' && ($mainItem->isMenuItem_Core() || $mainItem->isMenuItem_EFL()))
			{
				// only show 8 EFL items
				if ($mainItem->isMenuItem_EFL())
				{
					if (++$coreEflCount > 8)
					{
						continue;
					}
				}

				include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item.tpl.php');
			}
			else if ($this->menu_view == 'session_menu_freezer' && $mainItem->isMenuItem_SidesSweets())
			{
				include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_item.tpl.php');
			}
		}
	}
}
?>