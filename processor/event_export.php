<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CSession.php");

class processor_event_export extends CPageProcessor
{
	function runPublic()
	{
		$this->run();
	}

	function runCustomer()
	{
		$this->run();
	}

	function run()
	{
		if (isset($_REQUEST['sid']) && is_numeric($_REQUEST['sid']))
		{
			$sessionObj = DAO_CFactory::create('session');
			$sessionObj->id = $_REQUEST['sid'];

			if ($sessionObj->find(true))
			{
				$sessionObj->generateICSFile();
			}
		}
	}
}
?>