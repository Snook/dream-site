<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/Address.php");

class page_admin_list_users extends CPageAdminOnly
{

	private $currentStore = null;
	private $restrictToCustomers = false;
	private $canPlaceOrder = true;
	private $support_corporate_crate_search = false;

	function runManufacturerStaff()
	{
		$this->canPlaceOrder = false;
		$this->runFranchiseStaff();
	}

	function runFranchiseStaff()
	{
		$this->canPlaceOrder = true;
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->canPlaceOrder = true;
		$this->runFranchiseOwner();
	}
	function runEventCoordinator()
	{
		$this->canPlaceOrder = true;
		$this->runFranchiseOwner();
	}
	function runOpsLead()
	{
		$this->canPlaceOrder = true;
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->canPlaceOrder = true;
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
		$this->canPlaceOrder = false;
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->canPlaceOrder = true;
		$this->runSiteAdmin();

	}

	function runFranchiseOwner()
	{
		//if no store is chosen, bounce to the choose store page
		$this->currentStore = CApp::forceLocationChoice();
		$this->restrictToCustomers = false;
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();
		//we actually want to always use a GET instead of a post for reports
		//not sure if we'll use much of the CForm functionality
		$Form = new CForm();
		$Form->Repost = false;

		$AdminUser = CUser::getCurrentUser();

		if ($this->currentStore)
		{
			//fadmins
			$store = $this->currentStore;

			$storeTestObj = DAO_CFactory::create('store');
			$storeTestObj->query("select id from store where id = $store and supports_corporate_crate = 1");
			if ($storeTestObj->N > 0)
			{
				$this->support_corporate_crate_search = true;
			}

		}
		else
		{
			//site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$_GET_store = false;
			if (array_key_exists('store', $_GET))
			{
				$_GET_store = CGPC::do_clean($_GET['store'], TYPE_INT);
			}

			$Form->DefaultValues['store'] = (CGPC::do_clean($_GET_store ,TYPE_STR)? $_GET_store : null);

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');

			$this->support_corporate_crate_search = true;

		}

		$tpl->assign('support_corporate_crate_search', $this->support_corporate_crate_search);

		if (isset($_REQUEST['edit_last_for']) && is_numeric($_REQUEST['edit_last_for']))
		{
			$canEdit = true;
			$lo_user_id = $_REQUEST['edit_last_for'];
			$LastOrder = DAO_CFactory::create('orders');
			$LastOrder->query("SELECT o.id, s.session_start FROM orders o
				JOIN booking b ON b.order_id = o.id AND status = 'ACTIVE'
				JOIN session s ON s.id = b.session_id
				WHERE o.user_id = $lo_user_id AND o.store_id = $store
				ORDER BY s.session_start DESC LIMIT 1");

			if ($LastOrder->fetch())
			{
				$canEdit = true;
				/// check to see if editing period has expired

				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->id = $store;
				$StoreObj->find(true);

				$now = CTimezones::getAdjustedServerTime($StoreObj);
				$sessionTS = strtotime($LastOrder->session_start);

				// check for orders in previous month if current day is greater than 6
				$day = date("j", $now);
				$month = date("n", $now);
				$year = date("Y", $now);

				if ($day > 6)
				{
					$cutOff = mktime(0, 0, 0, $month, 1, $year);
					if ($sessionTS < $cutOff)
					{
						$canEdit = false;
					}
				}
				else
				{
					$cutOff = mktime(0, 0, 0, $month - 1, 1, $year);
					if ($sessionTS < $cutOff)
					{
						$canEdit = false;
					}
				}

				if (!$canEdit)
				{
					$tpl->setErrorMsg('The editing period for this order has expired.');
				}
				else
				{
					CApp::bounce("?page=admin_order_mgr&order=" . $LastOrder->id);
				}
			}
			else
			{
				$tpl->setErrorMsg('This user does not have a current order that can be edited.');
			}
		}

		//check out these query params:
		//
		//letter_select
		//store
		//q (string || id)
		//
		//send these guys to the form again
		$q = array_key_exists('q', $_GET) ? trim($_GET['q']) : null;
		$letter_select = array_key_exists('letter_select', $_GET) ? $_GET['letter_select'] : null;

		//no letter sorting if searching
		if (isset($q))
		{
			if ($_REQUEST['search_type'] != 'id')
			{
				$q = CGPC::do_clean($q, TYPE_STR, true);
			}
			else
			{
				$q = CGPC::do_clean($q, TYPE_INT, true);
			}

			$letter_select = null;
		}

		if ($letter_select != "etc" && $letter_select != "all" && isset($letter_select) && strlen($letter_select) > 1)
		{
			$letter_select = substr($letter_select, 0, 1);
		}

		$tpl->assign('q', $q);
		$tpl->assign('letter_select', $letter_select);
		$tpl->assign('labels', null);
		$tpl->assign('rows', null);
		$tpl->assign('rowcount', null);
		$tpl->assign('phone_mask_css', false);

		$search_type = isset($_REQUEST['search_type']) ? $_REQUEST['search_type'] : false;

		if ($search_type)
		{
			$Form->DefaultValues['search_type'] = $search_type;
			if ($search_type == 'phone')
			{
				$tpl->assign('phone_mask_css', true);
			}
		}
		else
		{
			$Form->DefaultValues['search_type'] = 'lastname';
		}

		$search_types = array(
			'firstname' => 'First Name',
			'lastname' => 'Last Name',
			'firstlast' => 'First [space] Last Name',
			'email' => 'Email Address',
			'phone' => 'Phone Number',
			'id' => 'Customer ID'
		);
		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::onChange => 'searchTypeChange',
			CForm::allowAllOption => true,
			CForm::options => $search_types,
			CForm::name => 'search_type'
		));

		switch ($AdminUser->user_type)
		{
			case CUser::SITE_ADMIN :
				$tpl->assign('can_export', true);
				break;
			case CUser::FRANCHISE_OWNER :
				$tpl->assign('can_export', false);
				break;
			case CUser::FRANCHISE_STAFF :
				$tpl->assign('can_export', false);
				break;
			case CUser::GUEST_SERVER :
				$tpl->assign('can_export', false);
				break;
			case CUser::FRANCHISE_MANAGER :
				$tpl->assign('can_export', false);
				break;
			case CUser::OPS_LEAD :
				$tpl->assign('can_export', false);
				break;
			case CUser::HOME_OFFICE_STAFF :
				$tpl->assign('can_export', true);
				break;
			case CUser::HOME_OFFICE_MANAGER :
				$tpl->assign('can_export', true);
				break;
			default:
				$tpl->assign('can_export', false);
				break;
		}

		if ($q || $letter_select)
		{
			$User = DAO_CFactory::create('user');

			//filter by store id
			$all_stores = false;
			if (!empty($_REQUEST['all_stores']))
			{
				$all_stores = true;
			}
			if ($all_stores)
			{
				$tpl->assign('all_stores', 'CHECKED');
			}

			if ((!$all_stores) && $store && ($store != 'all'))
			{
				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->id = $store;
				$User->joinAdd($StoreObj);
			}

			//filter by a letter
			if ($letter_select && ($letter_select != 'all'))
			{

				if ($letter_select == 'etc')
				{
					$User->whereAddFirstCharLike('user.lastname', "'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'", 'AND', true);
				}
				else
				{
					$User->whereAddFirstCharLike('user.lastname', "'$letter_select'");
				}
			}

			//filter by a query string
			if ($q && is_numeric($q) && $search_type === 'id')
			{
				$User->id = $q;
			}
			else if ($q)
			{
				$q = addslashes($q);

				switch ($search_type)
				{
					case 'firstname':
						$whereClause = "( user.firstname LIKE '%" . $q . "%') ";
						break;
					case 'lastname':
						$whereClause = "( user.lastname LIKE '%" . $q . "%') ";
						break;
					case 'firstlast':

						$parts = explode(" ", $q);
						$first = $parts[0];

						if (count($parts) > 1)
						{
							$last = $parts[1];
						}
						else
						{
							$last = "";
						}

						$whereClause = "( user.lastname LIKE '%" . $last . "%' AND user.firstname LIKE '%" . $first . "%') ";

						break;
					case 'email':
						if ($this->support_corporate_crate_search)
						{
							$whereClause = "( user.primary_email LIKE '%" . $q . "%' OR  user.secondary_email LIKE '%" . $q . "%') ";
						}
						else
						{
							$whereClause = "( user.primary_email LIKE '%" . $q . "%') ";
						}
						break;
					case 'phone':
							$whereClause = "( user.telephone_1 LIKE '%" . $q . "%' OR user.telephone_2 LIKE '%" . $q . "%' ) ";
						break;
				}

				$User->whereAdd($whereClause, 'AND');
			}

			$User->selectAdd();
			// LHOOK, added address details per FADMIN request 03/20/06

			if ($this->support_corporate_crate_search)
			{
				$fieldlist = "user.id, is_partial_account, user.primary_email, user.secondary_email, user.firstname, user.lastname, user.telephone_1, user.telephone_2,
								user.telephone_1_call_time, address.address_line1, address.address_line2,  address.city, address.state_id, address.postal_code";
			}
			else
			{
				$fieldlist = "user.id, is_partial_account, user.primary_email, user.firstname, user.lastname, user.telephone_1, user.telephone_2,
								user.telephone_1_call_time, address.address_line1, address.address_line2,  address.city, address.state_id, address.postal_code";
			}


			$User->selectAdd($fieldlist);
			$User->joinAdd(DAO_CFactory::create('address'), 'LEFT');

			if ($this->restrictToCustomers)
			{
				$User->user_type = CUser::CUSTOMER;
			}

			$User->groupBy('user.id');

			$User->orderBy('user.lastname, user.firstname');

			// site admins can see everyone

			// franchise owners can see franchise staff and manager but not other franchise owners

			// home office managers can see franchise staff, owners and managers

			// home office staff can only see customers

			//if ($AdminUser->primary_email !== "admin@dreamdinners.com")
			$User->whereAdd("user.id != 1");
			/// we can't look for "admin@dreamdinners.com".. this is wrong.. because folks have null addresses...
			// we don't ever want the admin account to show up .. for that matter.. probably any user account of type SITE ADMIN...

			$CountMe = clone($User);
			$rowcount = $CountMe->count('user.id');

			if ($rowcount > 10000)
			{
				$tpl->assign('rowcount', 0);
				$tpl->setErrorMsg('too many results: ' . $rowcount . " rows");

				return;
			}

			//we could get more than one address record
			$rowcount = $User->find();

			$tpl->assign('show_todays_guests', false);
		}
		else
		{
			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->id = $store;
			$StoreObj->find(true);
			// Get adjusted date for store timezone
			$todays_date = date("Y-m-d", CTimezones::getAdjustedServerTime($StoreObj));

			if ($this->support_corporate_crate_search)
			{
					$fieldlist = "user.id, is_partial_account, user.primary_email, user.secondary_email, user.firstname, user.lastname, user.telephone_1, user.telephone_2, user.telephone_1_call_time, address.address_line1, address.address_line2,  address.city, address.state_id, address.postal_code, s.session_start";
			}
			else
			{
				$fieldlist = "user.id, is_partial_account, user.primary_email, user.firstname, user.lastname, user.telephone_1, user.telephone_2, user.telephone_1_call_time, address.address_line1, address.address_line2,  address.city, address.state_id, address.postal_code, s.session_start";
			}


			$User = DAO_CFactory::create('user');
			$User->query("SELECT
				" . $fieldlist . "
				FROM `session` AS `s`
				INNER JOIN `store` AS `st` ON `s`.`store_id` = `st`.`id`
				INNER JOIN `booking` ON `s`.`id` = `booking`.`session_id` and `booking`.`is_deleted` = 0
				INNER JOIN `user` ON `booking`.`user_id` = `user`.`id` and `user`.`is_deleted` = 0
				LEFT JOIN `address` ON `address`.`user_id` = `user`.`id` and  `address`.`is_deleted` = 0
				WHERE booking.is_deleted = 0
					AND `booking`.status = 'ACTIVE'
					AND s.session_start LIKE '" . $todays_date . "%'
					AND s.store_id = '" . $store . "'
					AND user.id != '1'
					GROUP BY user.id
					ORDER BY user.lastname, user.firstname ASC");

			$rowcount = $User->N;

			if ($rowcount > 10000)
			{
				$tpl->assign('rowcount', 0);

				return;
			}

			$tpl->assign('show_todays_guests', true);
		}

		//up the memory limit for large result sets

		$labels = $User->getFieldLabels($fieldlist);
		$tpl->assign('labels', $labels);

		if (isset($_REQUEST['export']))
		{

			if ($_REQUEST['export'] == 'xlsx' && $rowcount > 250)
			{
				$_REQUEST['export'] = 'csv';
				$_GET['export'] = 'csv';
			}

			if ($_REQUEST['export'] == 'xlsx')
			{
				if ($rowcount > 0)
				{
					$mb = ($rowcount / 5 + 36);
					//ini_set('memory_limit', $mb.'M' );
					ini_set('max_execution_time ', 600);

					$rows = array();
					while ($User->fetch())
					{
						$rows[] = $User->toArray();
					}

					$tpl->assign('rows', $rows);
				}

				else
				{
					unset($_REQUEST['export']);
					unset($_GET['export']);
					$tpl->assign('rows', array());
				}
			}
			else //csv
			{
				$mb = ($rowcount / 50 + 16);
				ini_set('memory_limit', intval($mb) . 'M');
				ini_set('max_execution_time ', 600);

				$tpl->assign('rows', $User);
			}
		}
		else
		{
			$mb = ($rowcount / 50 + 36);
			ini_set('memory_limit', intval($mb) . 'M');
			ini_set('max_execution_time ', 600);

			$tpl->assign('rows', $User);
		}

		$tpl->assign('rowcount', $rowcount);

		$currentMonth = date('m', time());
		$currentMonthStr = date('M', time());
		$currentYear = date('Y', time());
		$nextMonthTS = mktime(0, 0, 0, $currentMonth + 1, 1, $currentYear);

		$tpl->assign('nextMonthTimestamp', $nextMonthTS);
		$tpl->assign('thisMonthStr', $currentMonthStr);
		$tpl->assign('nextMonthStr', date('M', $nextMonthTS));
		$tpl->assign('canPlaceOrder', $this->canPlaceOrder);
		$tpl->assign('store', $store);
		$tpl->assign('form_list_users', $Form->render());
	}
}

?>