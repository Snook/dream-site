<?php

//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");

require_once("includes/CPageAdminOnly.inc");
require_once 'includes/CCalendar.inc';
require_once 'includes/DAO/BusinessObject/CSession.php';

/*
 *
 *  Delivery support:
 * Add DELIVERY Session type to session_weekly_template_item
 *
 *
 *
 */

function build24HourTime($hour, $minutes, $am_pm)
{
	if ($am_pm == "pm" && $hour < 12)
	{
		$hour += 12;
	}

	$asTS = mktime($hour, $minutes, 0, 12, 1, 2005);

	return date("H:i:s", $asTS);
}

function sort_by_relative_times($a, $b)
{
	$timeA = $a['time'];
	$timeB = $b['time'];

	if ($timeA == $timeB)
	{
		return 0;
	}

	return ($timeA < $timeB) ? -1 : 1;
}

class page_admin_session_template_mgr extends CPageAdminOnly
{

	private $currentStore = null;

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	static $DayNameToNumMap = array(
		"SUN" => 0,
		"MON" => 1,
		"TUE" => 2,
		"WED" => 3.,
		"THU" => 4,
		"FRI" => 5,
		"SAT" => 6
	);

	static function validateItemTime($setID, $day, $time, $duration, $type)
	{

		if ($type != CSession::STANDARD)
		{
			return array(
				'valid' => true,
				"type" => "",
				"amount" => 0
			);
		}

		$testDayNum = self::$DayNameToNumMap[$day];
		$testTime = explode(':', $time);
		$testTime = ($testTime[0] * 60) + ($testTime[1]) + ($testTime[2] / 60);
		$testTime = ($testDayNum * 1440) + $testTime;

		$testEndTime = $testTime + $duration;

		$curSetList = array();
		$curSet = new DAO();
		$curSet->query("select start_day, start_time, duration_minutes from session_weekly_template_item  where session_weekly_template_id = $setID and session_type = 'STANDARD' and is_deleted = 0");

		while ($curSet->fetch())
		{
			$dayNum = self::$DayNameToNumMap[$curSet->start_day];
			$time = explode(':', $curSet->start_time);
			$time = ($time[0] * 60) + ($time[1]) + ($time[2] / 60);
			$relTime = ($dayNum * 1440) + $time;

			$curSetList[] = array(
				"time" => $relTime,
				"duration" => $curSet->duration_minutes
			);
		}

		usort($curSetList, 'sort_by_relative_times');

		foreach ($curSetList as $thisSession)
		{
			$thisSessionEnd = $thisSession['time'] + $thisSession["duration"];

			if ($thisSessionEnd == $testEndTime && $thisSession['time'] == $testTime)
			{
				return array(
					'valid' => false,
					"type" => "dupe",
					"amount" => 0
				);
			}

			if ($testEndTime > $thisSession['time'] && $testEndTime < $thisSessionEnd)
			{
				// problem - ends during another session
				return array(
					'valid' => false,
					"type" => "tail",
					"amount" => $testEndTime - $thisSession['time']
				);
			}
			else if ($testTime > $thisSession['time'] && $testTime < $thisSessionEnd)
			{
				// problem - begins during another session
				return array(
					'valid' => false,
					"type" => "head",
					"amount" => $thisSession['time'] - $testTime
				);
			}
			else if ($testTime < $thisSession['time'] && $testEndTime > $thisSessionEnd)
			{
				// problem - engulfs another session
				return array(
					'valid' => false,
					"type" => "contains",
					"amount" => 0
				);
			}

			if ($thisSession['time'] > $testTime)
			{
				// in the clear
				break;
			}
		}

		return array(
			'valid' => true,
			"type" => "",
			"amount" => 0
		);
	}

	function runSiteAdmin()
	{
		$tpl = CApp:: instance()->template();

		//-----------------------------------------start setup of set management form
		$SetForm = new CForm();
		$SetForm->Repost = true;

		//$SetForm->DefaultValues['menus'] = $defaultMonth;
		// CES 11/12/14 - must be harmless
		$SetForm->DefaultValues['menus'] = false;

		$SetForm->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'menus'
		));

		if ($this->currentStore)
		{ //fadmins
			$store = $this->currentStore;
		}
		else //site admin
		{
			$SetForm->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $SetForm->value('store');
		}

		// HACK: Need Menus hidden field so the Create a Session link in 2nd Tier Nav bar will function
		// Would be better to pass the current menu along		ins
		$todaysMonth = date("n");
		$todaysYear = date("Y");
		$todaysDay = date("j");
		$currentMonthDate = date("Y-m-01");

		//TODO: how far back do we go
		$startMonth = $todaysMonth - 6;
		$startYear = $todaysYear;
		if ($startMonth <= 0)
		{
			$startMonth += 12;
			$startYear--;
		}

		// Get menu info
		$Menu = DAO_CFactory::create('menu');

		$Menu->selectAdd();
		$Menu->selectAdd("menu.*");

		$start_date = mktime(0, 0, 0, $startMonth, 1, $startYear);
		$start_date_sql = date("Y-m-d", $start_date);

		$Menu->whereAdd("menu.menu_start >= '$start_date_sql'");
		$Menu->find();

		$menu_array = array();
		$menu_months = array();

		$defaultMonth = "";
		$lastMonth = "";
		while ($Menu->fetch())
		{
			$displayString = CTemplate::dateTimeFormat($Menu->menu_start, "%B %Y");
			$menu_array[$Menu->id] = $displayString;
			$menu_months[$Menu->id] = $Menu->menu_start;
			if (strtotime($Menu->menu_start) == strtotime($currentMonthDate))
			{
				$defaultMonth = $Menu->id;
			}
			$lastMonth = $Menu->id;
		}

		if ($defaultMonth == "")
		{
			$defaultMonth = $lastMonth;
		}

		if (isset($_REQUEST['menu_id']) && $_REQUEST['menu_id'])
		{
			$defaultMonth = CGPC::do_clean($_REQUEST['menu_id'], TYPE_INT);
		}

		//-----------------------------------------determine the current set
		$set_id = 0;
		$setList = array();
		$Owner = null;

		//-----------------------------------------delete the current set
		if (isset($_POST['delete_set_submit']))
		{
			$deleteSet = DAO_CFactory::create('session_weekly_template');

			if (!isset($_POST['set_id']))
			{
				throw new exception ("No set supplied for set delete request.");
			}

			$deleteSet->id = $_POST['set_id'];
			$deleteSet->delete();

			$DeleteOwner = DAO_CFactory::create('session_template_owner');
			$DeleteOwner->session_weekly_template_id = $_REQUEST['set_id'];
			$DeleteOwner->find(true);
			$DeleteOwner->delete();

			$currentItems = DAO_CFactory::create('session_weekly_template_item');

			$currentItems->session_weekly_template_id = $_POST['set_id'];
			$currentItems->find();
			while ($currentItems->fetch())
			{
				$currentItems->delete();
			}

			unset($_POST['set_id']);
		}

		//-----------------------------------------create a new set based on the current one
		if (isset($_POST['set_submit']))
		{

			if (!isset($_POST['set_name']) || $_POST['set_name'] == "")
			{
				$tpl->setErrorMsg('Please supply a name for your new template');
			}
			else
			{
				// user has no templates so create an initial one
				$currentSet = DAO_CFactory::create('session_weekly_template');
				$currentSet->session_template_name = $_REQUEST['set_name'];
				$currentSet->is_global = 0;
				if (!empty($_REQUEST['set_store_template']))
				{
					$currentSet->store_id = $store;
				}
				$currentSet->id = 0;
				$currentSet->id = $currentSet->insert();
				$set_id = $currentSet->id;
				$setList[$currentSet->id] = $currentSet->session_template_name;

				$AddOwner = DAO_CFactory::create('session_template_owner');
				$AddOwner->session_weekly_template_id = $currentSet->id;
				$AddOwner->owner_id = CUser::getCurrentUser()->id;
				$AddOwner->insert();

				$copyItems = DAO_CFactory::create('session_weekly_template_item');

				$copyItems->session_weekly_template_id = $_POST['set_id'];
				$copyItems->find();

				while ($copyItems->fetch())
				{
					$copyItems->session_weekly_template_id = $currentSet->id;

					if (!isset($copyItems->introductory_slots))
					{
						$copyItems->introductory_slots = 0;
					}

					$copyItems->insert();
				}

				$_POST['set_id'] = $set_id;
			}

			unset($_POST['set_name']);
		}

		//-----------------------------------------clear the current set
		if (isset($_POST['clear_set_submit']))
		{
			if (!isset($_POST['set_id']))
			{
				throw new exception ("No set supplied for set delete request.");
			}

			$currentItems = DAO_CFactory::create('session_weekly_template_item');

			$currentItems->session_weekly_template_id = $_POST['set_id'];
			$currentItems->find();
			while ($currentItems->fetch())
			{
				$currentItems->delete();
			}
		}

		$Owner = DAO_CFactory::create('session_template_owner');
		$Owner->query("SELECT
			sto.id,
			sto.owner_id,
			sto.session_weekly_template_id,
			sto.timestamp_updated,
			sto.timestamp_created,
			sto.created_by,
			sto.updated_by,
			swt.session_template_name,
			swt.store_id
			FROM session_template_owner AS sto
			INNER JOIN session_weekly_template AS swt ON swt.id = sto.session_weekly_template_id
			WHERE (sto.owner_id = '" . CUser::getCurrentUser()->id . "' OR swt.store_id = '" . $store . "')
			AND sto.is_deleted = 0
			AND swt.is_deleted = 0");

		while ($Owner->fetch())
		{
			$set_id = $Owner->session_weekly_template_id;
			if (!empty($Owner->store_id))
			{
				$setList[$set_id] = 'Store - ';
			}
			else
			{
				$setList[$set_id] = 'Personal - ';
			}
			$setList[$set_id] .= $Owner->session_template_name;
		}

		if (isset($_POST['set_id']))
		{
			$set_id = $_POST['set_id'];
		}

		//-----------------------------------------instantiate the current set container
		$currentSet = DAO_CFactory::create('session_weekly_template');

		if ($set_id == 0)
		{
			// user has no templates so create an initial one
			$currentSet->session_template_name = "Default template";
			$currentSet->is_global = 0;
			$currentSet->store_id = 0;
			$currentSet->id = $currentSet->insert();
			$set_id = $currentSet->id;
			$setList[$currentSet->id] = $currentSet->session_template_name;
			$Owner->session_weekly_template_id = $currentSet->id;
			$Owner->owner_id = CUser::getCurrentUser()->id;
			$Owner->insert();
		}
		else
		{
			$currentSet->id = $set_id;
			$currentSet->find(true);
		}

		//-----------------------------------------add new item to the current set
		if (isset($_POST['item_submit']))
		{
			$newItem = DAO_CFactory::create('session_weekly_template_item');

			$newItem->session_weekly_template_id = $currentSet->id;
			$newItem->start_day = $_POST['start_day'];
			$newItem->start_time = date("H:i:s", strtotime($_POST["start_time"])); //Build24HourTime($_POST["time_hour"], $_POST["time_minutes"], $_POST["time_am_pm"]);

			$validationResult = self::validateItemTime($currentSet->id, $newItem->start_day, $newItem->start_time, $_POST['duration_minutes'], $_POST['session_type']);

			if ($validationResult['valid'])
			{
				$newItem->session_type = $_POST['session_type'];
				$newItem->duration_minutes = $_POST['duration_minutes'];
				$newItem->available_slots = $_POST['available_slots'];
				$newItem->introductory_slots = $_POST['introductory_slots'];
				$newItem->session_title = $_POST['session_title'];
				$newItem->close_interval_type = $_POST['close_interval_type'];
				$newItem->close_interval_hours = $_POST['close_interval_hours'];

				$newItem->meal_customization_close_interval_type = $_POST['meal_customization_close_interval_type'];
				$newItem->meal_customization_close_interval = $_POST['meal_customization_close_interval'];

				if (!isset($newItem->introductory_slots))
				{
					$newItem->introductory_slots = 0;
				}

				$newItem->id = $newItem->insert();
			}
			else
			{

				$tpl->setErrorMsg("The time or duration conflict with an existing session.");
			}
		}

		//------------------------------------------------set up proto-session editor form
		$ItemForm = new CForm();
		$ItemForm->Repost = true;

		$ItemForm->DefaultValues['set_id'] = $set_id;

		//-----------------------------------------cancel editing the item
		if (isset($_POST['item_update_cancel']))
		{
			$newItem = DAO_CFactory::create('session_weekly_template_item');
			unset($_POST['edited_item_id']);
		}

		//-----------------------------------------delete the currently edited item
		if (isset($_POST['item_delete']))
		{
			$deletedItem = DAO_CFactory::create('session_weekly_template_item');

			if (!isset($_REQUEST['edited_item_id']))
			{
				throw new exception ("No item_id supplied for item delete request.");
			}

			$deletedItem->id = $_REQUEST['edited_item_id'];
			$deletedItem->delete();
			unset($_POST['edited_item_id']);
		}

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $store;
		if (!$storeObj->find(true))
		{
			throw new Exception("Store not found for Template Manager.");
		}

		$tpl->assign('allowsMealCustomization', $storeObj->supports_meal_customization);

		//-----------------------------------------setup selected item for editing
		if (isset($_POST['action']) && $_POST['action'] == "edit")
		{

			$Item = DAO_CFactory::create('session_weekly_template_item');
			$Item->id = $_REQUEST['item_id'];

			$result = $Item->find(true);
			if ($result == 0)
			{
				throw new Exception("Invalid session template id.");
			}

			$ItemForm->DefaultValues = array_merge($ItemForm->DefaultValues, $Item->toArray());
			$timeAsTS = strtotime($Item->start_time);
			//	$ItemForm->DefaultValues['time_minutes'] = date('i', $timeAsTS);
			//	$ItemForm->DefaultValues['time_hour'] = date('g', $timeAsTS);
			//	$ItemForm->DefaultValues['time_am_pm'] = date('a', $timeAsTS);
			$ItemForm->DefaultValues['start_time'] = date('H:i:s', $timeAsTS);
			$ItemForm->DefaultValues['end_time'] = date('H:i:s', $timeAsTS + ($Item->duration_minutes * 60));

			$ItemForm->DefaultValues['close_interval_hours'] = $Item->close_interval_hours;
			$ItemForm->DefaultValues['meal_customization_close_interval'] = $Item->meal_customization_close_interval;

			$tpl->assign("editingItem", true);

			$ItemForm->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => 'edited_item_id',
				CForm::value => $_POST['item_id']
			));

			unset($_POST);
		}
		else
		{

			if ($storeObj->close_interval_type == CStore::HOURS)
			{
				$ItemForm->DefaultValues["close_interval_type"] = CStore::HOURS;
			}
			else
			{
				$ItemForm->DefaultValues["close_interval_type"] = CStore::ONE_FULL_DAY;
			}

			if ($storeObj->meal_customization_close_interval_type == CStore::HOURS)
			{
				$ItemForm->DefaultValues["meal_customization_close_interval_type"] = CStore::HOURS;
			}
			else
			{
				$ItemForm->DefaultValues["meal_customization_close_interval_type"] = CStore::FOUR_FULL_DAYS;
			}

			$ItemForm->DefaultValues["close_interval_hours"] = $storeObj->close_session_hours;
			$ItemForm->DefaultValues['close_interval_hours'] = 24;
			if (!empty($_REQUEST['meal_customization_close_interval']))
			{
				$ItemForm->DefaultValues['meal_customization_close_interval'] = $_REQUEST['meal_customization_close_interval'];
			}
			else
			{
				$ItemForm->DefaultValues['meal_customization_close_interval'] = is_null($storeObj->close_customization_session_hours) ? 24 : $storeObj->close_customization_session_hours;
			}
			$ItemForm->DefaultValues['session_type'] = CSession::STANDARD;
			$ItemForm->DefaultValues['introductory_slots'] = $storeObj->default_intro_slots;
			//	$ItemForm->DefaultValues['time_minutes'] = '00';
			//	$ItemForm->DefaultValues['time_am_pm'] = 'pm';
			$ItemForm->DefaultValues['start_time'] = '12:00:00';
			$ItemForm->DefaultValues['end_time'] = '12:00:00';
			$ItemForm->DefaultValues['duration_minutes'] = 0;

			$ItemForm->DefaultValues['start_day'] = 'WED';

			$tpl->assign("editingItem", false);
		}

		$ItemForm->AddElement(array(
			CForm::type => CForm::Time,
			CForm::required => true,
			CForm::name => 'start_time'
		));
		$ItemForm->AddElement(array(
			CForm::type => CForm::Time,
			CForm::required => true,
			CForm::name => 'end_time'
		));

		/*
				$ItemForm->AddElement(array(
					CForm::type => CForm::Text,
					CForm::maxlength => 2,
					CForm::size => 2,
					CForm::hour => true,
					CForm::required => true,
					CForm::name => 'time_hour'
				));

				$ItemForm->AddElement(array(
					CForm::type => CForm::Text,
					CForm::maxlength => 2,
					CForm::size => 2,
					CForm::minutes => true,
					CForm::required => true,
					CForm::name => 'time_minutes'
				));

				$ItemForm->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => "time_am_pm",
					CForm::required => true,
					CForm::value => 'am'
				));

				$ItemForm->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => "time_am_pm",
					CForm::required => true,
					CForm::value => 'pm'
				));

		*/

		$ItemForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::name => 'available_slots'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::name => 'introductory_slots'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::name => 'duration_minutes'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => false,
			CForm::maxlength => 50,
			CForm::name => 'session_title'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "close_interval_type",
			CForm::required => true,
			CForm::value => CSession::HOURS
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "close_interval_type",
			CForm::required => true,
			CForm::value => CSession::ONE_FULL_DAY
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 3,
			CForm::size => 3,
			CForm::name => 'close_interval_hours'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "meal_customization_close_interval_type",
			CForm::required => true,
			CForm::value => CSession::HOURS
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "meal_customization_close_interval_type",
			CForm::required => true,
			CForm::value => CSession::FOUR_FULL_DAYS
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 3,
			CForm::size => 3,
			CForm::name => 'meal_customization_close_interval'
		));

		//TODO: Added Delivered session type
		$session_type_array = array(
			CSession::STANDARD => "Assembly",
			CSession::SPECIAL_EVENT => "Made for You",
			CSession::DELIVERY => "Delivery"
		);

		$ItemForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::options => $session_type_array,
			CForm::name => 'session_type'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'action'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'set_id'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'item_id'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::addOnClick => true,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'item_submit',
			CForm::value => 'Create'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::addOnClick => true,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'item_update',
			CForm::value => 'Save'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::addOnClick => true,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'item_delete',
			CForm::value => 'Delete'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::addOnClick => true,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'item_update_cancel',
			CForm::value => 'Cancel'
		));

		$ItemForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::options => array(
				"SUN" => "Sunday",
				"MON" => "Monday",
				"TUE" => "Tuesday",
				"WED" => "Wednesday",
				"THU" => "Thursday",
				"FRI" => "Friday",
				"SAT" => "Saturday"
			),
			CForm::name => 'start_day'
		));

		//-----------------------------------------update the currently edited item
		if (isset($_POST['item_update']))
		{
			$updateItem = DAO_CFactory::create('session_weekly_template_item');

			if (!isset($_REQUEST['edited_item_id']))
			{
				throw new exception ("No item_id supplied for item update request.");
			}

			$updateItem->id = CGPC::do_clean($_REQUEST['edited_item_id'], TYPE_INT);
			$updateItem->setFrom($ItemForm->values());
			$updateItem->start_time = date("H:i:s", strtotime(CGPC::do_clean($_POST["start_time"], TYPE_STR))); //Build24HourTime($_POST["time_hour"], $_POST["time_minutes"], $_POST["time_am_pm"]);

			$updateItem->session_type = $ItemForm->value('session_type');
			$updateItem->session_weekly_template_id = $currentSet->id;

			$updateItem->update();

			unset($_POST['edited_item_id']);
		}

		//-----------------------------------------instantiate the current set items
		$items_array = array();
		$dayConversionArray = array(
			"SUN" => 0,
			"MON" => 1,
			"TUE" => 2,
			"WED" => 3,
			"THU" => 4,
			"FRI" => 5,
			"SAT" => 6
		);
		$currentItems = DAO_CFactory::create('session_weekly_template_item');

		$currentItems->session_weekly_template_id = $currentSet->id;
		$currentItems->orderBy("start_day, start_time, session_type");
		$currentItems->find();

		while ($currentItems->fetch())
		{
			$session_type = CCalendar::sessionTypeNote(CSession::STANDARD);
			if ($currentItems->session_type == CSession::SPECIAL_EVENT)
			{
				$session_type = CCalendar::sessionTypeNote(CSession::SPECIAL_EVENT);
			}
			else if ($currentItems->session_type == CSession::DELIVERY)
			{
				$session_type = CCalendar::sessionTypeNote(CSession::DELIVERY);
			}

			$asTS = strtotime($currentItems->start_time);
			$items_array[$dayConversionArray[$currentItems->start_day]][$currentItems->id] = array(
				'date' => date("g:i a", $asTS),
				'type' => $session_type
			);
		}

		//-------------------------------------------complete setup of set management form
		$SetForm->DefaultValues["set_id"] = "$currentSet->id";

		$SetForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'set_name'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'set_store_template'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'set_submit',
			CForm::value => 'Save as'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'delete_set_submit',
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::value => 'Delete'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'clear_set_submit',
			CForm::value => 'Clear'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => true,
			CForm::allowAllOption => true,
			CForm::options => $setList,
			CForm::name => 'set_id'
		));

		$formArray = $SetForm->render(true);
		$itemFormArray = $ItemForm->render(true);

		//----------------------------------------------------- assign to template
		$tpl->assign('DAO_menu', CMenu::getCurrentMenu());

		$tpl->assign('current_set', $currentSet->toArray());
		$tpl->assign('this_set_id', $set_id);
		$tpl->assign('items', $items_array);
		$tpl->assign('form_template_item', $itemFormArray);
		$tpl->assign('form_template_set', $formArray);

		$tpl->assign('page_title', 'Session Management');
	}
}

function sort_by_relative_times_repair_mode($a, $b)
{
	$timeA = $a['rel_time'];
	$timeB = $b['rel_time'];

	if ($timeA == $timeB)
	{
		return 0;
	}

	return ($timeA < $timeB) ? -1 : 1;
}

/*
$setsGetter = new DAO();
$setsGetter->query("select id from session_weekly_template where is_deleted = 0");
while($setsGetter->fetch())
{
	$tempArray = array();

	$itemsGetter = new DAO();
	$itemsGetter->query("select id, start_day, start_time, duration_minutes from session_weekly_template_item  where session_weekly_template_id = {$setsGetter->id} and is_deleted = 0");
	while($itemsGetter->fetch())
	{
		$dayNum = page_admin_session_template_mgr::$DayNameToNumMap[$itemsGetter->start_day];
		$time = explode(':', $itemsGetter->start_time);
		$time = ($time[0]*60) + ($time[1]) + ($time[2]/60);
		$relTime = ($dayNum * 1440) +  $time;

		$tempArray[] = array("id" => $itemsGetter->id, "start_day" => $itemsGetter->start_day, "start_time" => $itemsGetter->start_time, "duration" => $itemsGetter->duration_minutes, 'rel_time' => $relTime);
	}

	usort($tempArray, 'sort_by_relative_times_repair_mode');

	foreach($tempArray as $thisSession)
	{
		$validationResult = page_admin_session_template_mgr::validateItemTime($setsGetter->id, $thisSession['start_day'], $thisSession['start_time'], $thisSession['duration'], true);
		if (!$validationResult['valid'])
		{

			$output = $setsGetter->id .  ", " . $validationResult['type'] . ", " . $validationResult['amount'] . ", " . $thisSession['start_day'] . ", " . $thisSession['start_time']  . ", " .  $thisSession['duration'] . ", ";

			if ($validationResult['type'] == 'tail')
			{
				$output .= "update session_weekly_template_item set duration_minutes = " . $thisSession['duration']- $validationResult['amount'] . " where id = " . $thisSession['id'] .";";
			}
			else if ($validationResult['type'] == 'head')
			{
				$output .= "update session_weekly_template_item set start_time = " .$thisSession['start_time']  . " where id = " . $thisSession['id'] .";";
			}
			else
			{
				$output .= "update session_weekly_template_item set is_deleted = 1 where id = " . $thisSession['id'] .";";
			}

			echo $output . "\r\n";
		}
	}
}
*/
?>