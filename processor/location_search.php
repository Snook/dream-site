<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once('DAO/CFactory.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');
require_once('includes/miscMath.inc');
require_once("includes/CPageProcessor.inc");

function locationCompare($a, $b)
{
	if ($a['distance'] == $b['distance'])
	{
		return 0;
	}

	return ($a['distance'] < $b['distance']) ? -1 : 1;
}

class processor_location_search extends CPage
{

	function runPublic()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		parent::runPublic();

		$req_compact = CGPC::do_clean((!empty($_REQUEST['compact']) ? $_REQUEST['compact'] : false), TYPE_BOOL);
		$req_latitude = CGPC::do_clean((!empty($_REQUEST['latitude']) ? $_REQUEST['latitude'] : false), TYPE_NUM);
		$req_longitude = CGPC::do_clean((!empty($_REQUEST['longitude']) ? $_REQUEST['longitude'] : false), TYPE_NUM);
		$req_zip = CGPC::do_clean((!empty($_REQUEST['zip']) ? $_REQUEST['zip'] : false), TYPE_STR);
		$req_state = CGPC::do_clean((!empty($_REQUEST['state']) ? $_REQUEST['state'] : false), TYPE_NOHTML, true);

		$Form = new CForm;
		$Form->Repost = false;

		$User = CUser::getCurrentUser();

		$this->Template->assign('compact', $req_compact);
		$this->Template->assign('cart_info', CUser::getCartIfExists());
		$this->Template->assign('store_results_array', false);

		if (!empty($req_latitude) && !empty($req_longitude) && !empty($req_zip))
		{
			$Form->DefaultValues['postalcode'] = $req_zip;

			$this->Template->assign('zip_code', $req_zip);
			$this->Template->assign('request', 'for ' . $req_zip);

			$Zip = DAO_CFactory::create('zipcodes');
			$Zip->zip = $req_zip;

			if (true)
			{
				$Form->DefaultValues['state'] = $Zip->state;

				$DAO_store = DAO_CFactory::create('store', true);
				if ($req_compact)
				{
					$DAO_store->active = 1;
				}
				$DAO_store->show_on_customer_site = 1;
				$DAO_store->whereAdd("store.store_type <> '" . CStore::DISTRIBUTION_CENTER . "'");
				$DAO_store->whereAdd("store.address_latitude > '" . ($req_latitude - 5) . "' AND store.address_latitude  < '" . ($req_latitude + 5) . "'");
				$DAO_store->whereAdd("store.address_longitude > '" . ($req_longitude - 5) . "' AND store.address_longitude < '" . ($req_longitude + 5) . "'");
				$DAO_store->find_DAO_store();

				$rawList = array();
				$results = array();

				while ($DAO_store->fetch())
				{
					$distance = distance($req_latitude, $req_longitude, $DAO_store->address_latitude, $DAO_store->address_longitude);

					$allowed_distance = 30;

					if ($DAO_store->id == 99)
					{
						$allowed_distance = 45;
					}

					if ($distance < $allowed_distance)
					{
						$rawList[$DAO_store->id] = $DAO_store->toArray();
						$rawList[$DAO_store->id]['DAO_store'] = clone $DAO_store;
						$rawList[$DAO_store->id]['DAO_store_pickup_location'] = null;
						$rawList[$DAO_store->id]['type'] = 'STORE';
						$rawList[$DAO_store->id]['distance'] = $distance;
						$rawList[$DAO_store->id]['map_link'] = $DAO_store->generateMapLink();
						$rawList[$DAO_store->id]['image_name'] = $DAO_store->getStoreImageName();
						$rawList[$DAO_store->id]['coming_soon'] = $DAO_store->isComingSoon();
					}
				}

				$DAO_store_pickup_location = DAO_CFactory::create('store_pickup_location', true);
				$DAO_store_pickup_location->active = 1;
				$DAO_store_pickup_location->show_on_customer_site = 1;
				$DAO_store_pickup_location->whereAdd("store_pickup_location.address_latitude > '" . ($req_latitude - 5) . "' AND store_pickup_location.address_latitude  < '" . ($req_latitude + 5) . "'");
				$DAO_store_pickup_location->whereAdd("store_pickup_location.address_longitude > '" . ($req_longitude - 5) . "' AND store_pickup_location.address_longitude < '" . ($req_longitude + 5) . "'");
				$DAO_store = DAO_CFactory::create('store', true);
				if ($req_compact)
				{
					$DAO_store->active = 1;
				}
				$DAO_store->show_on_customer_site = 1;
				$DAO_store->whereAdd("store.store_type <> '" . CStore::DISTRIBUTION_CENTER . "'");
				$DAO_store->joinAddWhereAsOn(DAO_CFactory::create('short_url', true), 'LEFT');
				$DAO_store_pickup_location->joinAddWhereAsOn($DAO_store);
				$DAO_store_pickup_location->find();

				while ($DAO_store_pickup_location->fetch())
				{
					$distance = distance($req_latitude, $req_longitude, $DAO_store_pickup_location->address_latitude, $DAO_store_pickup_location->address_longitude);

					$allowed_distance = 15;

					if ($distance < $allowed_distance && !$DAO_store_pickup_location->DAO_store->isComingSoon())
					{
					$id = $DAO_store_pickup_location->DAO_store->id . '-' . $DAO_store_pickup_location->id;

						$rawList[$id] = $DAO_store_pickup_location->DAO_store->toArray();
						$rawList[$id]['DAO_store'] = clone $DAO_store_pickup_location->DAO_store;
						$rawList[$id]['DAO_store']->DAO_short_url = clone $DAO_store_pickup_location->DAO_short_url;
						$rawList[$id]['DAO_store_pickup_location'] = clone $DAO_store_pickup_location;
						$rawList[$id]['type'] = 'COMMUNITY_PICK_UP';
						$rawList[$id]['distance'] = $distance;
						$rawList[$id]['map_link'] = $DAO_store_pickup_location->DAO_store->generateMapLink();
						$rawList[$id]['image_name'] = $DAO_store_pickup_location->DAO_store->getStoreImageName();
						$rawList[$id]['coming_soon'] = $DAO_store_pickup_location->DAO_store->isComingSoon();
					}
				}

				usort($rawList, 'locationCompare');

				$count = 0;
				foreach ($rawList as $DAO_store)
				{
					if ($count++ > 4)
					{
						break;
					}

					$stateName = CStatesAndProvinces::GetName($DAO_store['state_id']);

					if ($DAO_store['type'] == 'COMMUNITY_PICK_UP')
					{
						$id = $DAO_store['DAO_store']->id . '-' . $DAO_store['DAO_store_pickup_location']->id;
					}
					else
					{
						$id = $DAO_store['id'];
					}

					if (!array_key_exists($stateName, $results))
					{
						$results[$stateName] = array();
					}

					$results[$stateName][$id] = $DAO_store;

					if ($count == 1)
					{
						$results[$stateName][$id]['checked'] = true;
					}
					else
					{
						$results[$stateName][$id]['checked'] = false;
					}
				}

				$this->Template->assign('store_results_array', $results);

				if (empty($results) && !empty($req_zip))
				{
					CLog::RecordNew(CLog::DEBUG, 'Location Search - No Stores Found: ' . $req_zip);
				}
				else if (!empty($results) && !empty($req_zip))
				{
					CLog::RecordNew(CLog::DEBUG, 'Location Search - Stores Found: ' . $req_zip);
				}
			}
			else
			{
				// show not found thing...
			}
		}
		else if (!empty($req_zip))
		{
			$Form->DefaultValues['postalcode'] = $req_zip;

			$this->Template->assign('zip_code', $req_zip);
			$this->Template->assign('request', 'for ' . $req_zip);

			$Zip = DAO_CFactory::create('zipcodes');
			$Zip->zip = $req_zip;

			if ($Zip->find(true))
			{
				$active_only = '';
				if (!empty($req_compact))
				{
					$active_only = "AND s.active = '1'";
				}

				$Form->DefaultValues['state'] = $Zip->state;

				$DAO_store = DAO_CFactory::create('store', true);
				if ($req_compact)
				{
					$DAO_store->active = 1;
				}
				$DAO_store->show_on_customer_site = 1;
				$DAO_store->whereAdd("store.store_type <> '" . CStore::DISTRIBUTION_CENTER . "'");

				$DAO_zipcodes = DAO_CFactory::create('zipcodes', true);
				$DAO_zipcodes->whereAdd("store.postal_code = zipcodes.zip");
				$DAO_zipcodes->whereAdd("zipcodes.latitude > '" . ($Zip->latitude - 5) . "' AND zipcodes.latitude  < '" . ($Zip->latitude + 5) . "'");
				$DAO_zipcodes->whereAdd("zipcodes.longitude > '" . ($Zip->longitude - 5) . "' AND zipcodes.longitude < '" . ($Zip->longitude + 5) . "'");
				$DAO_store->joinAddWhereAsOn($DAO_zipcodes, array(
					'joinType' => 'INNER',
					'useLinks' => false
				));
				$DAO_store->find_DAO_store();

				$rawList = array();
				$results = array();

				while ($DAO_store->fetch())
				{
					$distance = distance($Zip->latitude, $Zip->longitude, $DAO_store->DAO_zipcodes->latitude, $DAO_store->DAO_zipcodes->longitude);

					$allowed_distance = 30;

					if ($DAO_store->id == 99)
					{
						$allowed_distance = 45;
					}

					if ($distance < $allowed_distance)
					{
						$rawList[$DAO_store->id] = $DAO_store->toArray();
						$rawList[$DAO_store->id]['DAO_store'] = clone $DAO_store;
						$rawList[$DAO_store->id]['distance'] = $distance;
						$rawList[$DAO_store->id]['map_link'] = $DAO_store->generateMapLink();
						$rawList[$DAO_store->id]['type'] = 'STORE';
						//$rawList[$store->id]['linear_address'] = $store->generateLinearAddress();
						$rawList[$DAO_store->id]['image_name'] = $DAO_store->getStoreImageName();
						$rawList[$DAO_store->id]['coming_soon'] = $DAO_store->isComingSoon();
					}
				}

				$DAO_store_pickup_location = DAO_CFactory::create('store_pickup_location', true);
				$DAO_store_pickup_location->active = 1;
				$DAO_store_pickup_location->show_on_customer_site = 1;
				$DAO_store_pickup_location->whereAdd("store_pickup_location.address_latitude > '" . ($Zip->latitude - 5) . "' AND store_pickup_location.address_latitude  < '" . ($Zip->latitude + 5) . "'");
				$DAO_store_pickup_location->whereAdd("store_pickup_location.address_longitude > '" . ($Zip->longitude - 5) . "' AND store_pickup_location.address_longitude < '" . ($Zip->longitude + 5) . "'");
				$DAO_store = DAO_CFactory::create('store', true);
				if ($req_compact)
				{
					$DAO_store->active = 1;
				}
				$DAO_store->show_on_customer_site = 1;
				$DAO_store->whereAdd("store.store_type <> '" . CStore::DISTRIBUTION_CENTER . "'");
				$DAO_store->joinAddWhereAsOn(DAO_CFactory::create('short_url', true), 'LEFT');
				$DAO_store_pickup_location->joinAddWhereAsOn($DAO_store);
				$DAO_store_pickup_location->find();

				while ($DAO_store_pickup_location->fetch())
				{
					$distance = distance($Zip->latitude, $Zip->longitude, $DAO_store_pickup_location->address_latitude, $DAO_store_pickup_location->address_longitude);

					$allowed_distance = 15;

					if ($distance < $allowed_distance && !$DAO_store_pickup_location->DAO_store->isComingSoon())
					{
						$id = $DAO_store_pickup_location->DAO_store->id . '-' . $DAO_store_pickup_location->id;

						$rawList[$id] = $DAO_store_pickup_location->DAO_store->toArray();
						$rawList[$id]['DAO_store'] = clone $DAO_store_pickup_location->DAO_store;
						$rawList[$id]['DAO_store']->DAO_short_url = clone $DAO_store_pickup_location->DAO_short_url;
						$rawList[$id]['DAO_store_pickup_location'] = clone $DAO_store_pickup_location;
						$rawList[$id]['type'] = 'COMMUNITY_PICK_UP';
						$rawList[$id]['distance'] = $distance;
						$rawList[$id]['map_link'] = $DAO_store_pickup_location->DAO_store->generateMapLink();
						$rawList[$id]['image_name'] = $DAO_store_pickup_location->DAO_store->getStoreImageName();
						$rawList[$id]['coming_soon'] = $DAO_store_pickup_location->DAO_store->isComingSoon();
					}
				}

				usort($rawList, 'locationCompare');

				$count = 0;
				foreach ($rawList as $store_id => $DAO_store)
				{
					if ($count++ > 4)
					{
						break;
					}

					$stateName = CStatesAndProvinces::GetName($DAO_store['state_id']);

					if ($DAO_store['type'] == 'COMMUNITY_PICK_UP')
					{
						$id = $DAO_store['DAO_store']->id . '-' . $DAO_store['DAO_store_pickup_location']->id;
					}
					else
					{
						$id = $DAO_store['id'];
					}

					if (!array_key_exists($stateName, $results))
					{
						$results[$stateName] = array();
					}

					$results[$stateName][$id] = $DAO_store;

					if ($count == 1)
					{
						$results[$stateName][$id]['checked'] = true;
					}
					else
					{
						$results[$stateName][$id]['checked'] = false;
					}
				}

				$this->Template->assign('store_results_array', $results);

				if (empty($results) && !empty($req_zip))
				{
					CLog::RecordNew(CLog::DEBUG, 'Location Search - No Stores Found: ' . $req_zip);
				}
				else if (!empty($results) && !empty($req_zip))
				{
					CLog::RecordNew(CLog::DEBUG, 'Location Search - Stores Found: ' . $req_zip);
				}
			}
			else
			{
				// show not found thing...
			}
		}
		else if (!empty($req_state) && CStatesAndProvinces::IsValid($req_state))
		{
			$state_id = substr($req_state, 0, 2); // substr added for security measure
			$stateName = CStatesAndProvinces::GetName($state_id);

			$this->Template->assign('request', 'for ' . $stateName);

			$Form->DefaultValues['state'] = $state_id;

			if (!$stateName)
			{
				// TODO: error
			}

			$DAO_store = DAO_CFactory::create('store', true);
			if ($req_compact)
			{
				$DAO_store->active = 1;
			}
			$DAO_store->show_on_customer_site = 1;
			$DAO_store->state_id = $state_id;
			$DAO_store->whereAdd("store.store_type <> '" . CStore::DISTRIBUTION_CENTER . "'");
			$DAO_store->orderBy("store.city, store.store_name");
			$DAO_store->find_DAO_store();

			$results = array();

			while ($DAO_store->fetch())
			{
				$results[$DAO_store->id] = $DAO_store->toArray();
				$results[$DAO_store->id]['DAO_store'] = clone $DAO_store;
				$results[$DAO_store->id]['map_link'] = $DAO_store->generateMapLink();
				$results[$DAO_store->id]['type'] = 'STORE';
				// $results[$store->id]['linear_address'] = $store->generateLinearAddress();
				$results[$DAO_store->id]['image_name'] = $DAO_store->getStoreImageName();
				$results[$DAO_store->id]['coming_soon'] = $DAO_store->isComingSoon();
			}

			$this->Template->assign('state_has_delivered', CBox::quickCheckForBoxAvailableInState($state_id));

			if (!empty($results))
			{
				$this->Template->assign('store_results_array', array($stateName => $results));
			}
		}
		else
		{
			// show not found thing...
		}

		if (!empty($Zip))
		{
			$ckzip = DAO_CFactory::create('zipcodes');
			$ckzip->zip = $Zip->zip;
			$ckzip->joinAddWhereAsOn(DAO_CFactory::create('store'));
			$ckzip->whereAdd("zipcodes.distribution_center IS NOT NULL AND store.show_on_customer_site = 1");

			if ($ckzip->find(true))
			{
				// yes there is a distribution center for this zip
				// check if there are active boxes for this distribution center
				$this->Template->assign('delivered', $ckzip);
				$this->Template->assign('shipping_has_inventory', CBox::quickCheckForBoxAvailable($ckzip->distribution_center));
			}
		}

		if (!empty($User->firstname))
		{
			$Form->DefaultValues['first_name'] = $User->firstname;
		}
		if (!empty($User->lastname))
		{
			$Form->DefaultValues['last_name'] = $User->lastname;
		}
		if (!empty($User->primary_email))
		{
			$Form->DefaultValues['email_address'] = $User->primary_email;
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "first_name",
			CForm::required => true,
			CForm::placeholder => "*First Name",
			CForm::required_msg => "Please enter your first name.",
			CForm::maxlength => 80,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "last_name",
			CForm::required => true,
			CForm::placeholder => "*Last Name",
			CForm::required_msg => "Please enter your last name.",
			CForm::maxlength => 80,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "email_address",
			CForm::required => true,
			CForm::placeholder => "*Email Address",
			CForm::required_msg => "Please enter your email address.",
			CForm::maxlength => 80,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'state',
			CForm::required_msg => "Please select a state.",
			CForm::required => true,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "postalcode",
			CForm::required => true,
			CForm::placeholder => "*Postal Code",
			CForm::required_msg => "Please enter your postal code.",
			CForm::maxlength => 20,
			CForm::css_class => "form-control"
		));

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'shipping')
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Search results returned.',
				'delivered_store' => (!empty($this->Template->delivered->id) ? $this->Template->delivered->id : false),
				'shipping_has_inventory' => (!empty($this->Template->shipping_has_inventory) ? $this->Template->shipping_has_inventory : false)
			));
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'json')
		{
			$this->Template->assign('vresp', $Form->Render());

			$location_results = $this->Template->fetch('customer/subtemplate/locations/locations_results.tpl.php');

			$this->Template->assign('location_results', $location_results);

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Search results returned.',
				'html' => $location_results,
				'delivered_store' => (!empty($this->Template->delivered->id) ? $this->Template->delivered->id : false),
				'shipping_has_inventory' => (!empty($this->Template->shipping_has_inventory) ? $this->Template->shipping_has_inventory : false)
			));
		}
		else
		{
			$this->Template->assign('vresp', $Form->Render());

			$location_results = $this->Template->fetch('customer/subtemplate/locations/locations_results.tpl.php');

			$this->Template->assign('location_results', $location_results);

			return $location_results;
		}
	}
}

?>