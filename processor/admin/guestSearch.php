<?php
/*
 * Created on June 11, 2012
 * project_name guestSearch
 *
 * Copyright 2012 DreamDinners
 * @author Carls
 */

require_once("includes/CPageProcessor.inc");

class processor_admin_guestSearch extends CPageProcessor
{

	private static $currentStore = null;


	function runFranchiseStaff()
	{
		$this->guestSearch();
	}

	function runFranchiseManager()
	{
		$this->guestSearch();
	}

	function runFranchiseLead()
	{
		$this->guestSearch();
	}

	function runEventCoordinator()
	{
		$this->guestSearch();
	}

	function runOpsLead()
	{
		$this->guestSearch();
	}


	function runFranchiseOwner()
	{
		$this->guestSearch();
	}

	function runHomeOfficeStaff()
	{
		$this->guestSearch();
	}

	function runHomeOfficeManager()
	{
		$this->guestSearch();
	}

	function runSiteAdmin()
	{
		$this->guestSearch();
	}

	function guestSearch()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$search_type = CGPC::do_clean((!empty($_REQUEST['search_type']) ? $_REQUEST['search_type'] : false), TYPE_NOHTML);
		$searchValue = CGPC::do_clean((!empty($_REQUEST['q']) ? $_REQUEST['q'] : false), TYPE_NOHTML);
		$resultsType = CGPC::do_clean((!empty($_REQUEST['results_type']) ? $_REQUEST['results_type'] : false), TYPE_NOHTML);
		$store_id = CGPC::do_clean((!empty($_REQUEST['store']) ? $_REQUEST['store'] : false), TYPE_INT);

		if (empty($store_id))
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}

		$all_stores = !empty($_REQUEST['filter']);

		if (!empty($_POST['op']) && $_POST['op'] == 'get_search_form')
		{
			$tpl = new CTemplate();

			$all_stores_checked = CGPC::do_clean($_REQUEST['data']['all_stores_checked'], TYPE_BOOL);

			$tpl->assign('all_stores_checked', $all_stores_checked);

			$search_form = $tpl->fetch('admin/subtemplate/guest_search_form.tpl.php');

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved guest search form.',
				'search_form' => $search_form
			));

			exit;
		}
		else if (strlen($searchValue) < 3)
		{
			echo json_encode(array('processor_success' => false, 'result_code' => 50000, 'processor_message' => 'Please provide a search string of at least 3 characters.'));
			exit;
		}
		else if ($search_type && $searchValue)
		{
			switch($resultsType)
			{
				case 'compact_list':
					$select = "select u.* from user u ";
					break;
				default:
					$select = "select u.* from user u ";
			}

			//$searchValue = mysqli_real_escape_string($searchValue);
			$searchValue = trim($searchValue);

			switch($search_type)
			{
				case 'firstname':
					$where = "where u.firstname like '%$searchValue%'";
					break;
				case 'lastname':
					$where = "where u.lastname like '%$searchValue%'";
					break;
				case 'firstlast':
					$parts = explode(" ", $searchValue);
					$first = $parts[0];

					if (count($parts) > 1)
						$last = $parts[1];
					else
						$last = "";

					$where = "where ( u.lastname LIKE '%".$last."%' AND u.firstname LIKE '%". $first. "%') ";
					break;
				case 'email':
					$where = "where (u.primary_email like '%$searchValue%' OR u.secondary_email like '%$searchValue%')";
					break;
				case 'id':
					$where = "where id = $searchValue";
					break;
				default:

					break;
			}

			if (!$all_stores && $store_id)
			{
				$where .= " and u.home_store_id = $store_id";
			}

			$where .= " and u.is_deleted = 0 order by u.lastname, u.firstname limit 10000";

			$User = DAO_CFactory::create('user');
			$User->query($select . $where);


			$rows = array();

			while($User->fetch())
			{
				$rows[] = $User->toArray();
			}

			$tpl = new CTemplate();

			$select_button_title = CGPC::do_clean($_REQUEST['select_button_title'], TYPE_STR);

			$tpl->assign('select_button_title', $select_button_title);
			$tpl->assign('rows', $rows);
			$tpl->assign('rowcount', count($rows));

			if ($resultsType == 'inline_search')
			{
				$html = $tpl->fetch('admin/subtemplate/guest_search_result_row.tpl.php');
			}
			else
			{
				$html = $tpl->fetch('admin/list_users_plugin_results_compact.tpl.php');
			}

			echo json_encode(array('processor_success' => true, 'result_code' => 1,  'data' => $html));
		}
		else
		{
		    echo json_encode(array('processor_success' => false, 'result_code' => 50001, 'processor_message' => 'Please provide a search string of at least 3 characters.'));
		}
	}

	static function initSeachPanel($tpl, $store_id)
	{
		self::$currentStore = $store_id;

		$Form = new CForm();
		$Form->Repost = TRUE;

		$AdminUser = CUser::getCurrentUser();

		if ( self::$currentStore )
		{
			//fadmins
			$store = self::$currentStore;
		}
		else
		{
			//site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
					CForm::onChangeSubmit => true,
					CForm::allowAllOption => true,
					CForm::showInactiveStores => true,
					CForm::name => 'store'));

			$store = $Form->value('store');
		}

		$search_types = array('firstname' => 'First Name', 'lastname' => 'Last Name', 'firstlast' => 'First Name[space]Last Name', 'email' => 'Email Address', 'id' => 'Customer ID');
		$Form->addElement(array(CForm::type=> CForm::DropDown,
				CForm::onChangeSubmit => false,
				CForm::onChange => 'searchTypeChange',
				CForm::allowAllOption => true,
				CForm::options => $search_types,
				CForm::name => 'search_type'));

		$Form->DefaultValues['currentStore'] = $store;

		$Form->addElement(array(CForm::type => CForm::Hidden, CForm::name => "currentStore"));

		$tpl->assign('form_list_users', $Form->render());
	}
}
?>