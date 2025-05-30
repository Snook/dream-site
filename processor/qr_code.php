<?php
require_once('phplib/phpqrcode/qrlib.php');

class processor_qr_code extends CPageProcessor
{

	function runPublic()
	{
		$this->mainProcessor();
	}

	function runFranchiseStaff()
	{
		$this->mainProcessor();
	}

	function runFranchiseLead()
	{
		$this->mainProcessor();
	}

	function runEventCoordinator()
	{
		$this->mainProcessor();
	}

	function runOpsLead()
	{
		$this->mainProcessor();
	}

	function runOpsSupport()
	{
		$this->mainProcessor();
	}

	function runFranchiseManager()
	{
		$this->mainProcessor();
	}

	function runHomeOfficeManager()
	{
		$this->mainProcessor();
	}

	function runFranchiseOwner()
	{
		$this->mainProcessor();
	}

	function runSiteAdmin()
	{
		$this->mainProcessor();
	}

	function mainProcessor()
	{
		if ($_REQUEST['op'] == 'referral' && !empty($_REQUEST['id']))
		{

			//args :
			//id - required - user id to include
			//s  - optional - size of qr code, numeric
			//d  - optional - download file if set to 1, if 0 or not included it will open in new window

			$size = 3;

			if (!empty($_REQUEST['s']) && is_numeric($_REQUEST['s']))
			{
				$size = $_REQUEST['s'];
			}

			if (!empty($_REQUEST['d']) && is_numeric($_REQUEST['d']) && $_REQUEST['d'] == 1)
			{
				QRcode::pngWeb(HTTPS_BASE . 'share/' . $_REQUEST['id'], $size, 'share-dream-dinners-qr.png');
			}
			else
			{
				QRcode::pngWeb(HTTPS_BASE . 'share/' . $_REQUEST['id'], $size);
			}
		}

		if ($_REQUEST['op'] == 'store_info' && !empty($_REQUEST['id']))
		{

			//args :
			//id - required - store id to include
			//s  - optional - size of qr code, numeric
			//d  - optional - download file if set to 1, if 0 or not included it will open in new window

			$size = 3;

			if (!empty($_REQUEST['s']) && is_numeric($_REQUEST['s']))
			{
				$size = $_REQUEST['s'];
			}

			if (!empty($_GET['id']) && (is_numeric($_GET['id']) || CTemplate::isAlphaNumHyphen($_GET['id'])))
			{
				$DAO_store = DAO_CFactory::create('store', true);
				$DAO_store->id = $_GET['id'];
				$DAO_store->find_DAO_store(true);
			}

			if (!empty($_REQUEST['d']) && is_numeric($_REQUEST['d']) && $_REQUEST['d'] == 1)
			{
				QRcode::pngWeb(HTTPS_SERVER . $DAO_store->getPrettyUrl(), $size, 'dream-dinners-store-qr.png');
			}
			else
			{
				QRcode::pngWeb(HTTPS_SERVER . $DAO_store->getPrettyUrl(), $size);
			}
		}

		if ($_REQUEST['op'] == 'render' && !empty($_REQUEST['data']))
		{

			//args :
			//data - required - url to convert to qr code
			//s  - optional - size of qr code, numeric
			//d  - optional - download file if set to 1, if 0 or not included it will open in new window

			$size = 3;

			if (!empty($_REQUEST['s']) && is_numeric($_REQUEST['s']))
			{
				$size = $_REQUEST['s'];
			}

			if (!empty($_REQUEST['d']) && is_numeric($_REQUEST['d']) && $_REQUEST['d'] == 1)
			{
				QRcode::pngWeb($_REQUEST['data'], $size, 'dream-dinners-qr.png');
			}
			else
			{
				QRcode::pngWeb($_REQUEST['data'], $size);
			}
		}
	}
}

?>