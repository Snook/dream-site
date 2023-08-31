<?php
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_store extends CPage
{

	function runPublic()
	{
		$tpl = CApp::instance()->template();

		if (!empty($_GET['id']) && is_numeric($_GET['id']))
		{
			list($storeInfo, $ownerInfo, $StoreObj) = CStore::getStoreAndOwnerInfo($_GET['id']);
		}

		// in case someone tries to load a DC store, bounce to the parent store.
		if ($StoreObj->store_type == CStore::DISTRIBUTION_CENTER)
		{
			CApp::bounce('main.php?page=store&id=' . $StoreObj->parent_store_id);
		}

		if (!empty($storeInfo) && $storeInfo['show_on_customer_site'] == 1)
		{
			if (!CBrowserSession::getCurrentStore())
			{
				CBrowserSession::setLastViewedStore($storeInfo['id']);
			}

			$canOrderIntro = CUser::getCurrentUser()->isEligibleForIntro($StoreObj);

			$calendar = CSession::getSessionsForFullCalendarCustomer($StoreObj, true);
			$calendar['menus'] = CMenu::getActiveMenuArray();
			$calendarJS = (!empty($calendar) ? json_encode($calendar) : "{}");

			$storePromos = CStore::getActiveStorePromos($StoreObj);

			$storeOHEvents = array();

			// if the user is eligible for Open House, get the next two upcoming Open House
			if (!CUser::isLoggedIn() || (CUser::isLoggedIn() && CUser::getCurrentUser()->isEligibleForDreamTaste()))
			{
				$count = 0;

				foreach ($calendar['sessions'] AS $session)
				{
					if (count($storeOHEvents) < 2 && $session['extendedProps']['session_type_title_short'] == 'OH')
					{
						$storeOHEvents[$count++] = $session;
					}

					if ($count == 2)
					{
						continue;
					}
				}
			}


			$sessionArray = $StoreObj->getCustomerCalendarArray(array(
				CSession::INTRO,
				CSession::ALL_STANDARD,
				CSession::EVENT
			),false,false,true);

			$supportsCustomizatoin = ($calendar['info']['has_meal_customization_sessions'] && $StoreObj->supports_meal_customization);
			$tpl->assign('has_meal_customization_sessions', $supportsCustomizatoin);
			$tpl->assign('sessionArray', $sessionArray);
			$tpl->assign('calendar', $calendar);
			$tpl->assign('calendarJS', $calendarJS);
			$tpl->assign('canOrderIntro', $canOrderIntro);
			$tpl->assign('storePromos', $storePromos);
			$tpl->assign('storeOHEvents', $storeOHEvents);
			$tpl->assign('store_info', $storeInfo);
			$tpl->assign('owner_info', $ownerInfo);
		}
		else
		{
			$tpl->setErrorMsg('The requested store is unavailable.');
			CApp::bounce('main.php?page=locations');
		}
	}
}

?>