<?php
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_store_meet_the_owner extends CPage
{
	/**
	 * @throws Exception
	 */
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

				$this->Template->assign('DAO_store', $DAO_store);
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