<?php
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_store_community_pick_up extends CPage
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
				$DAO_store->getAvailableJobsArray();

				$locationArray = array();

				foreach ($DAO_store->getStorePickupLocations() AS $location)
				{
					// Prime with active locations
					if ($location->is_Active() && $location->is_ShowOnCustomerSite())
					{
						$locationArray[$location->id] = array(
							'DAO_store_pickup_location' => clone $location,
							'sessionArray' => null
						);
					}
				}

				$DAO_session = DAO_CFactory::create('session', true);
				$DAO_session->store_id = $DAO_store->id;
				$DAO_session->session_publish_state = CSession::PUBLISHED;
				$DAO_session->whereAdd("menu.id IN (" . implode(',', CMenu::getActiveMenuArrayIDs()) . ")");
				$DAO_session->whereAdd("session_properties.store_pickup_location_id IS NOT NULL");
				$DAO_session->orderBy("store_pickup_location.state_id, store_pickup_location.city, session.session_start");

				$DAO_session->find_DAO_session();

				while ($DAO_session->fetch())
				{
					if ($DAO_session->isOpen() && !$DAO_session->isPrivate() && !empty($locationArray[$DAO_session->DAO_store_pickup_location->id]))
					{
						// Need to add location if there is a session active but the location itself is now inactive
						// An inactive location does not govern session availability
						if (empty($locationArray[$DAO_session->DAO_store_pickup_location->id]) && $DAO_session->DAO_store_pickup_location->is_ShowOnCustomerSite())
						{
							$locationArray[$location->id] = array(
								'DAO_store_pickup_location' => clone $DAO_session->DAO_store_pickup_location,
								'sessionArray' => null
							);
						}

						$locationArray[$DAO_session->DAO_store_pickup_location->id]['sessionArray'][$DAO_session->id] = clone $DAO_session;
					}
				}

				$this->Template->assign('DAO_store', $DAO_store);
				$this->Template->assign('locationArray', $locationArray);
			}
			else
			{
				$this->Template->setStatusMsg('Sorry, it appears the location you are looking for has permanently closed. Please enter your zip code below to another location near you or see if our shipping service is available.');
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