<?php
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('includes/CLog.inc');

class processor_session_type extends CPage
{

	function runPublic()
	{
		$this->runSessionTypePage();
	}

	function runCustomer()
	{
		$this->runSessionTypePage();
	}

	function runSessionTypePage()
	{
		$req_store = CGPC::do_clean((!empty($_REQUEST['store']) ? $_REQUEST['store'] : false), TYPE_INT);
		$req_menu = CGPC::do_clean((!empty($_REQUEST['menu']) ? $_REQUEST['menu'] : false), TYPE_STR);
		$req_navigation = CGPC::do_clean((!empty($_REQUEST['type']) ? $_REQUEST['type'] : (($req_menu && $req_store) ? 'all_standard' : false)), TYPE_STR);

		$store_id = false;
		$CartObj = CCart2::instance();

		$curMenuID = $CartObj->getMenuId();

		if (!empty($curMenuID) && is_numeric($curMenuID) && !CMenu::isMenuIDCurrentInCustomerView($curMenuID))
		{
			// has old menu id in cart
			$CartObj->addMenuId(0);
			$curMenuID = false;
		}

		$DAO_store = $CartObj->getOrder()->getStore();

		if (isset($DAO_store))
		{
			$store_id = $DAO_store->id;
		}

		if ($req_store)
		{
			if ($req_store != $store_id)
			{
				$DAO_store = DAO_CFactory::create('store');
				$DAO_store->id = $req_store;
				$DAO_store->active = 1;

				if ($DAO_store->find(true))
				{
					$CartObj->storeChangeEvent($req_store);
					$store_id = $req_store;
				}
			}
		}
		else if (empty($store_id))
		{
			list($store_id, $methodStoreFound) = CUser::getCurrentUser()->getCurrentStoreViewed(false);

			if (empty($store_id))
			{
				if (CBrowserSession::getValue('last_viewed_store'))
				{
					$store_id = CBrowserSession::getValue('last_viewed_store');

					if (is_numeric($store_id))
					{
						$CartObj->storeChangeEvent($store_id);
					}
				}
			}

			if (!empty($store_id))
			{
				$DAO_store = DAO_CFactory::create('store');
				$DAO_store->id = $store_id;
				$DAO_store->active = 1;

				$DAO_store->find(true);
			}
		}

		// store doesn't support intro, and they are requesting intro, send them to standard
		if ($req_navigation == 'starter' && !$DAO_store->storeSupportsIntroOrders($req_menu))
		{
			$req_navigation = 'all_standard';
		}

		if ($req_navigation)
		{
			switch (strtolower($req_navigation))
			{
				case 'standard':
					$navigation_type = CTemplate::STANDARD;
					$CartObj->removeBundleId();
					break;
				case 'intro':
				case 'starter':
					$navigation_type = CTemplate::INTRO;
					$CartObj->clearMenuItems(); // always clear menu items when selecting a bundle based order since some
					// items in the cart may not be supported by the bundle

					$bundleID = $CartObj->getBundleID();

					if ($bundleID)
					{
						$DAO_bundle = DAO_CFactory::create('bundle');
						$DAO_bundle->id = $bundleID;
						$DAO_bundle->find(true);

						if (!$DAO_bundle->isStarterPack())
						{
							$CartObj->removeBundleId();
						}
					}

					if (empty($curMenuID))
					{
						if (!empty($store_id))
						{
							// this gets all sessions which have been created
							$DAO_session = DAO_CFactory::create('session');
							$DAO_session->store_id = $store_id;
							$sessionCalendarArray = $DAO_session->getSessionArrayByMenu(array(
								'menu_id_array' => CMenu::getActiveMenuArray(),
								'exclude_walk_in' => true
							));

							// this digests the session array and determines counts for various session types
							$parsedSessionCalendarArray = array(
								'all' => CSession::parseSessionArrayByMenu($sessionCalendarArray),
								'no_closed_walkin' => CSession::parseSessionArrayByMenu($sessionCalendarArray, array(
									'filter_closed' => true,
									'filter_walkin' => true
								))
							);

							// This is for if no menu is defined, select the first available month for starter pack
							if (!$req_menu)
							{
								foreach ($parsedSessionCalendarArray['no_closed_walkin']['menu'] as $menu_id => $menu_info)
								{
									if (!empty($menu_info['session_type'][CSession::INTRO]))
									{
										$curMenuID = $menu_id;
										break;
									}
								}
							}
							else
							{
								if (!is_numeric($req_menu))
								{
									$DAO_menu = CMenu::getMenuByMonthAbbr($req_menu);

									$curMenuID = $DAO_menu->id;
								}
								else
								{
									$curMenuID = $req_menu;
								}
							}
						}
					}

					if (!empty($curMenuID))
					{
						$CartObj->clearMenuItems(true, true);
						$NewBundle = CBundle::getActiveBundleForMenu($curMenuID, $store_id);
						$CartObj->addBundleId($NewBundle->id, true);
					}
					else
					{
						CApp::bounce('main.php?page=store&id=' . $store_id);
					}
					break;
				case 'event':
					$navigation_type = CTemplate::EVENT;
					$CartObj->clearMenuItems(); // always clear menu items when selecting a bundle based order since some
					// items in the cart may not be supported by the bundle
					$bundleID = $CartObj->getBundleID();

					$DAO_bundle = DAO_CFactory::create('bundle');
					$DAO_bundle->id = $bundleID;
					$DAO_bundle->find(true);

					if (!$DAO_bundle->isFundraiser() && $DAO_bundle->isDreamTaste())
					{
						$CartObj->removeBundleId();
					}
					break;
				case 'madeforyou':
					$navigation_type = CTemplate::MADE_FOR_YOU;
					$CartObj->removeBundleId();
					break;
				case 'delivery':
					$navigation_type = CTemplate::DELIVERY;
					$CartObj->removeBundleId();
					break;
				case 'allstandard':
				default:
					$navigation_type = CTemplate::ALL_STANDARD;
					$CartObj->removeBundleId();
					break;
			}

			$CartObj->addNavigationType($navigation_type, true, false);
		}

		if ($req_menu)
		{
			// if it's not numeric, it should be an abbreviated month string
			if (!is_numeric($req_menu))
			{
				$DAO_menu = CMenu::getMenuByMonthAbbr($req_menu);

				$req_menu = $DAO_menu->id;
			}

			if ($curMenuID != $req_menu)
			{
				$CartObj->clearMenuItems();
			}

			$CartObj->addMenuId($req_menu);
		}

		// Determine where to bounce based on cart

		// no store, send them to pick a store
		if (empty($store_id))
		{
			CApp::bounce('main.php?page=locations');
		}
		// store is set to distribution center they shouldn't be here
		else if ($DAO_store->store_type == CStore::DISTRIBUTION_CENTER)
		{
			CApp::bounce('main.php?page=locations');
		}

		// send them to session menu
		CApp::bounce('main.php?page=session_menu');
	}
}

?>