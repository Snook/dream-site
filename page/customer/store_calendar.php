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
			$DAO_store->active = 1;

			if ($DAO_store->find_DAO_store(true))
			{
				$this->Template->assign('DAO_store', $DAO_store);
			}
			else
			{
				$this->Template->setErrorMsg('The requested store is unavailable.');
				CApp::bounce('main.php?page=locations');
			}
		}
		else
		{
			CApp::bounce('main.php?page=locations');
		}
	}
}
?>