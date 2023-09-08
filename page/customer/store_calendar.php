<?php
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_store_calendar extends CPage
{
	function runPublic()
	{
		if (!empty($_GET['id']) && (is_numeric($_GET['id']) || CTemplate::isAlphaNumHyphen($_GET['id'])))
		{
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->id = $_GET['id'];
			$DAO_store->show_on_customer_site = 1;

			if ($DAO_store->find_DAO_store(true))
			{
				$DAO_store->getActivePromoArray();

				$calendar = CSession::getSessionsForFullCalendarCustomer($DAO_store, true);
				$calendar['menus'] = CMenu::getActiveMenuArray();
				$calendarJS = (!empty($calendar) ? json_encode($calendar) : "{}");

				$sessionArray = $DAO_store->getCustomerCalendarArray(array(
					CSession::INTRO,
					CSession::ALL_STANDARD,
					CSession::EVENT
				), false, false, true);

				$this->Template->assign('DAO_store', $DAO_store);
				$this->Template->assign('has_meal_customization_sessions', ($calendar['info']['has_meal_customization_sessions'] && $DAO_store->supports_meal_customization));
				$this->Template->assign('sessionArray', $sessionArray);
				$this->Template->assign('calendar', $calendar);
				$this->Template->assign('calendarJS', $calendarJS);
				$this->Template->assign('canOrderIntro', CUser::getCurrentUser()->isEligibleForIntro($DAO_store));
			}
			else
			{
				$this->Template->setErrorMsg('The requested store is unavailable.');
				CApp::bounce('/locations');
			}
		}
		else
		{
			CApp::bounce('/locations');
		}
	}
}

?>