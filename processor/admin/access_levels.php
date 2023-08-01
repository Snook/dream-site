<?php
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");

class processor_admin_access_levels extends CPageProcessor
{

	function __construct()
	{
		$this->doGenericInputCleaning = false;
	}

	function runSiteAdmin()
	{
		$this->runAccessLevels();
	}
	
	function runFranchiseManager()
	{
		$this->runAccessLevels();
	}
	
	function runOpsLead()
	{
		$this->runAccessLevels();
	}
	
	function runHomeOfficeManager()
	{
		$this->runAccessLevels();
	}

	function runFranchiseOwner()
	{
		$this->runAccessLevels();
	}

	function runAccessLevels()
	{
		$req_op = CGPC::do_clean((!empty($_REQUEST['op']) ? $_REQUEST['op'] : false), TYPE_NOHTML, true);
		$req_uts_id = CGPC::do_clean((!empty($_POST['uts_id']) ? $_POST['uts_id'] : false), TYPE_INT);

		if (!empty($req_op) && $req_op == 'do_display_on_site')
		{

			$req_display = CGPC::do_clean((!empty($_POST['display']) ? $_POST['display'] : false), TYPE_INT);

			if (is_numeric($req_uts_id) && is_numeric($req_display))
			{
				$UTS = DAO_CFactory::create('user_to_store');
				$UTS->id = $req_uts_id;

				if ($UTS->find())
				{
					$UTS->display_to_public = $req_display;
					$UTS->update();

					echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Display setting updated.',
							'dd_toasts' => array(
									array('message' => 'Display setting updated.', 'toast_id' => 'pref_updated')
							)
					));
				}

			}

		}

		if (!empty($req_op) && $req_op == 'do_display_text')
		{
			$req_display_text = CGPC::do_clean((!empty($_REQUEST['display_text']) ? $_REQUEST['display_text'] : false), TYPE_NOHTML, true);

			if (is_numeric($req_uts_id) && !empty($req_display_text))
			{
				$UTS = DAO_CFactory::create('user_to_store');
				$UTS->id = $req_uts_id;

				if ($UTS->find())
				{
					if ($req_display_text === '0')
					{
						$req_display_text = 'NULL';
					}

					$UTS->display_text = $req_display_text;
					$UTS->update();

					echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Display text updated.',
							'uts_id' => $req_uts_id,
							'dd_toasts' => array(
									array('message' => 'Display text updated.', 'toast_id' => 'pref_updated')
							)
					));
				}

			}

		}
	}
}
?>