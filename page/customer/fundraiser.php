<?php
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_fundraiser extends CPage
{

	function runPublic()
	{
		if (!empty($_GET['id']) && is_numeric($_GET['id']))
		{
			list($storeInfo, $ownerInfo, $DAO_store) = CStore::getStoreAndOwnerInfo($_GET['id']);
			$showOrgSpecific = false;

			if (empty($DAO_store) || !$DAO_store->isOpen())
			{
				CApp::bounce('?page=locations');
			}

			// in case someone tries to load a DC store, bounce to the parent store.
			if ($DAO_store->isDistributionCenter())
			{
				CApp::bounce('?page=store&id=' . $DAO_store->parent_store_id);
			}

			$DAO_session = DAO_CFactory::create('session');
			$DAO_session->store_id = $DAO_store->id;
			$DAO_session->session_type = CSession::FUNDRAISER;
			$DAO_session->session_publish_state = CSession::PUBLISHED;

			$DAO_session->selectAdd();
			$DAO_session->selectAdd('session.*');

			$DAO_session->whereAdd("session.menu_id IN ('" . implode("','", array_keys(CMenu::getActiveMenuArray())) . "')");

			$DAO_session_properties = DAO_CFactory::create('session_properties');

			$DAO_dream_taste_event_properties = DAO_CFactory::create('dream_taste_event_properties');
			$DAO_dream_taste_event_properties->joinAddWhereAsOn(DAO_CFactory::create('dream_taste_event_theme'));
			$DAO_dream_taste_event_properties->joinAddWhereAsOn(DAO_CFactory::create('bundle'));

			$DAO_session_properties->joinAddWhereAsOn($DAO_dream_taste_event_properties);

			$DAO_fundraiser = DAO_CFactory::create('fundraiser');

			if (!empty($_GET['fid']) && is_numeric($_GET['fid']))
			{
				$DAO_fundraiser->id = $_GET['fid'];

				$showOrgSpecific = true;
			}

			$DAO_session_properties->joinAddWhereAsOn($DAO_fundraiser);

			$DAO_session->joinAddWhereAsOn($DAO_session_properties);
			$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('menu'));
			$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('store'));

			$DAO_session->orderBy("session.menu_id ASC, fundraiser.fundraiser_name ASC, session.session_start ASC");

			$DAO_session->find();

			$fundraiserArray = array();

			while ($DAO_session->fetch())
			{
				if ($DAO_session->isOpen($DAO_store))
				{
					$fundraiserArray[$DAO_session->DAO_menu->id]['menu'] = $DAO_session->DAO_menu->cloneObj();
					$fundraiserArray[$DAO_session->DAO_menu->id]['fundraisers'][$DAO_session->DAO_fundraiser->id]['fundraiser'] = $DAO_session->DAO_fundraiser->cloneObj();
					$fundraiserArray[$DAO_session->DAO_menu->id]['fundraisers'][$DAO_session->DAO_fundraiser->id]['sessions'][CTemplate::dateTimeFormat($DAO_session->session_start, YEAR_MONTH_DAY)][$DAO_session->id] = $DAO_session->cloneObj();
				}
			}

			$this->Template->assign('fundraiserArray', $fundraiserArray);
			$this->Template->assign('DAO_store', $DAO_store);
			$this->Template->assign('showOrgSpecific', $showOrgSpecific);
		}
		else
		{
			CApp::bounce('?page=locations');
		}
	}
}
?>