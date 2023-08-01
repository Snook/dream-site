<?php

class page_start_new_test_order extends CPage
{

	function getRandomStandardSessionNoFail($menu_id, $store_id)
	{

		$options = array();

		$sessions = DAO_CFactory::create('session');
		$sessions->query("select * from session where  menu_id = $menu_id and store_id = $store_id
			and session_type = 'STANDARD' and session_publish_state = 'PUBLISHED' and is_deleted = 0 and session_start > now()");

		while ($sessions->fetch())
		{
			if ($sessions->getRemainingSlots() > 0)
			{
				$options[] = $sessions->id;
			}
		}

		if (count($options))
		{
			shuffle($options);

			return array_shift($options);
		}

		return false;
	}

	function fillCartWithRandomItemsNoFail($CartObj, $menu_id, $store_id)
	{
		$items = DAO_CFactory::create('menu_item');
		$items->query("select mi.recipe_id, GROUP_CONCAT(mi.id) as mids, GROUP_CONCAT(mi.servings_per_item) as servings, mii.override_inventory - mii.number_sold as avail from menu_to_menu_item mmi
			join menu_item mi on mmi.menu_item_id = mi.id and mi.menu_item_category_id < 5 and mi.is_deleted = 0
			join menu_item_inventory mii on mi.recipe_id = mii.recipe_id and mii.store_id = $store_id and mii.menu_id = $menu_id
			where mmi.store_id = $store_id and mmi.menu_id = $menu_id and mmi.is_visible = 1 and mii.is_deleted = 0 group by mi.recipe_id");

		$options = array();

		while ($items->fetch())
		{
			$options[$items->recipe_id] = array(
				'mids' => explode(",", $items->mids),
				'servings' => explode(",", $items->servings),
				'avail' => $items->avail
			);
		}

		shuffle($options);

		$totalServings = 0;

		foreach ($options as $rid => $data)
		{

			if ($data['avail'] > 6)
			{
				if (count($data['mids']) == 2)
				{

					if (rand(0, 100) > 50)
					{
						$selectedMID = $data['mids'][1];
						$numServings = $data['servings'][1];
					}
					else
					{
						$selectedMID = $data['mids'][0];
						$numServings = $data['servings'][0];
					}
				}
				else
				{
					$selectedMID = $data['mids'][0];
					$numServings = $data['servings'][0];
				}

				$totalServings += $numServings;
				$CartObj->updateMenuItem($selectedMID, 1, $menu_id, true);

				if ($totalServings > 36)
				{
					break;
				}
			}
		}
	}

	function setUpCartForTests()
	{

		CCart2::instance()->emptyCartCompletely();

		$CartObj = CCart2::instance();
		$CartObj->storeChangeEvent(244); // hard coded for now
		$CartObj->addNavigationType(CTemplate::STANDARD);
		$menu_id = CMenu::getCurrentMenuId(); // current month for now
		$session_id = $this->getRandomStandardSessionNoFail($menu_id, 244);

		$CartObj->addSessionId($session_id, true);
		$CartObj->addMenuId($menu_id, true);

		$this->fillCartWithRandomItemsNoFail($CartObj, $menu_id, 244);
		//   $CartObj->flush();

	}

	function runPublic()
	{
		CApp::forceLogin('main.php?page=start_new_test_order');
	}

	function runCustomer()
	{

		$this->setUpCartForTests();

		CApp::bounce("/main.php?page=checkout");
	}
}

?>