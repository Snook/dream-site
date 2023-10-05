<?php
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/CCalendar.inc';
require_once 'includes/DAO/BusinessObject/CSession.php';

function dateComparePS($a, $b)
{
	$aTime = strtotime($a['time']);
	$bTime = strtotime($b['time']);

	if ($aTime == $bTime)
	{
		return 0;
	}

	return ($aTime < $bTime) ? -1 : 1;
}

function populateCallbackPS($Date)
{
	$retVal = array();

	$count = 0;

	$styleOverride = "";

	if (isset(page_admin_publish_sessions::$validMenuRangeMarkersArray[$Date]))
	{
		$styleOverride = page_admin_publish_sessions::$validMenuRangeMarkersArray[$Date];
	}

	$tsTemp = strtotime($Date);
	//CApp::instance()->template()->assign( 'AAA-'.$Date, $tsTemp );
	if (($tsTemp < page_admin_publish_sessions::$tsMenuBegin) || ($tsTemp > page_admin_publish_sessions::$tsMenuEnd))
	{
		$styleOverride = 'validForMenu="false"';
	}

	if (array_key_exists($Date, page_admin_publish_sessions::$sessionArray))
	{
		usort(page_admin_publish_sessions::$sessionArray[$Date], 'dateComparePS');

		foreach (page_admin_publish_sessions::$sessionArray[$Date] as $dayItem)
		{
			$image = "";
			$anchorStart = "";
			$anchorEnd = "";
			$editClick = "";

			if ($dayItem['isOpen'])
			{
				$linkClass = "calendar_on_text_on";
			}
			else
			{
				$linkClass = "calendar_on_text_off";
			}

			$id = 0;

			if ($dayItem['isWalkIn']){
				continue;
			}

			if (!$dayItem['isOpen'])
			{
				$image = "/session_closed.png";
				$anchorStart = "<a class='$linkClass' href = javascript:onSessionClick(" . $dayItem['id'] . ")>";
				$anchorEnd = "</a>";
				$editClick = "onMouseOver='hiliteIcon(" . $dayItem['id'] . ", 4);' onMouseOut='unHiliteIcon(" . $dayItem['id'] . ", 4);' onClick='onEditClick(" . $dayItem['id'] . ");'";
			}
			else if ($dayItem['state'] == "SAVED")
			{
				$image = "/session_saved.png";
				$anchorStart = "<a class='$linkClass' href = javascript:onSessionClick(" . $dayItem['id'] . ")>";
				$anchorEnd = "</a>";
				$editClick = "onMouseOver='hiliteIcon(" . $dayItem['id'] . ", 2);' onMouseOut='unHiliteIcon(" . $dayItem['id'] . ", 2);' onClick='onEditClick(" . $dayItem['id'] . ");'";
			}
			else if ($dayItem['state'] == "NEW")
			{
				$image = "/session_new.png";
				$id = $dayItem['id'];
				$editClick = "onClick='onDeleteClick(this);'";
				$anchorStart = "<span class='$linkClass'>";
				$anchorEnd = "</span>";
			}
			else if ($dayItem['state'] == "PUBLISHED")
			{
				$image = "/session_pub.png";
				$anchorStart = "<a class='$linkClass' href = javascript:onSessionClick(" . $dayItem['id'] . ")>";
				$anchorEnd = "</a>";
				$editClick = "onMouseOver='hiliteIcon(" . $dayItem['id'] . ", 1);' onMouseOut='unHiliteIcon(" . $dayItem['id'] . ", 1);' onClick='onEditClick(" . $dayItem['id'] . ");'";
			}
			else
			{
				$image = "/session_closed.png";
				$anchorStart = "<a class='$linkClass' href = javascript:onSessionClick(" . $dayItem['id'] . ")>";
				$anchorEnd = "</a>";
				$editClick = "onMouseOver='hiliteIcon(" . $dayItem['id'] . ", 4);' onMouseOut='unHiliteIcon(" . $dayItem['id'] . ", 4);' onClick='onEditClick(" . $dayItem['id'] . ");'";
			}

			$time12Hour = date("g:i a", strtotime($dayItem['time']));

			$type = "(std)";
			$color = "color='#000000'";
			if ($dayItem['isM4U'])
			{
				$type = "(M4U)";
				$color = "color='#00AA00'";
			}
			if ($dayItem['isDLVR'])
			{
				$type = "(DLVR)";
				$color = "color='#00AA00'";
			}

			$customizable = '';
			if($dayItem['allowedCustomization'])
			{
				if ($dayItem['isOpenForCustomization'])
				{
					$customizable = '<i class="dd-icon icon-customize text-orange" style="font-size: 65%;" data-tooltip="Session Open for Customization"></i>';
				}
				else
				{
					$customizable = '<i class="dd-icon icon-customize text-black" style="font-size: 65%;" data-tooltip="Session Closed for Customization"></i>';
				}
			}

			$retVal[$count++] = "<img name='" . $dayItem['time'] . "' id='" . $dayItem['id'] . "' src='" . ADMIN_IMAGES_PATH . $image . "' " . $editClick . ">$anchorStart<font size='1' $color>$time12Hour $type</font>$anchorEnd $customizable";
		}
	}

	return array(
		$retVal,
		$styleOverride
	);
}

class page_admin_publish_sessions extends CPageAdminOnly
{

	public static $sessionArray = array();

	// Added 11/14/2006 davib/carls (...)
	public static $tsMenuBegin = 0;
	public static $tsMenuEnd = 0;

	public static $validMenuRangeMarkersArray = array();

	private $currentStore = null;

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
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		//if no store is chosen, bounce to the choose store page
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{

		$tpl = CApp::instance()->template();

		//------------------------------------------------set up store and menu form
		$storeMenuForm = new CForm();
		$storeMenuForm->Repost = true;

		if ($this->currentStore)  //fadmins
		{
			$currentStore = $this->currentStore;
		}
		else //site admin
		{
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$storeMenuForm->DefaultValues['store'] = array_key_exists('store', $_GET) ? CGPC::do_clean($_GET['store'], TYPE_INT) : null;

			$storeMenuForm->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$currentStore = $storeMenuForm->value('store');
		}

		if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN)
		{
			CBrowserSession::instance()->setValue('default_store_id', $currentStore);
		}

		$Store = DAO_CFactory::create('store');
		$Store->id = $currentStore;
		$Store->find(true);

		//	dbg();

		CBrowserSession::instance()->setValue('sm_current_page', '/backoffice/publish_sessions');

		$Owner_id = CUser::getCurrentUser()->id;
		//-----------------------------------------determine the current set
		$set_id = 0;
		$setList = array();

		$SetOwner = DAO_CFactory::create('session_template_owner');
		$SetOwner->query("SELECT
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
			WHERE (sto.owner_id = '" . CUser::getCurrentUser()->id . "' OR swt.store_id = '" . $currentStore . "')
			AND sto.is_deleted = 0
			AND swt.is_deleted = 0");

		while ($SetOwner->fetch())
		{
			$set_id = $SetOwner->session_weekly_template_id;
			if (!empty($SetOwner->store_id) && $SetOwner->store_id == $currentStore)
			{
				$setList[$set_id] = 'Store - ';
			}
			else
			{
				$setList[$set_id] = 'Personal - ';
			}
			$setList[$set_id] .= $SetOwner->session_template_name;
		}

		if (isset($_POST['set_id']))
		{
			$set_id = CGPC::do_clean($_POST['set_id'], TYPE_INT);
		}

		$currentSet = DAO_CFactory::create('session_weekly_template');

		if ($set_id == 0)
		{
			// TODO: redirect to the template mananger since they don't have one yet
		}
		else
		{
			$currentSet->id = $set_id;
			$currentSet->find();
		}

		if (!$currentSet->id)
		{
			$tpl->assign("noValidSet", true);

			return;
		}
		else
		{
			$tpl->assign("noValidSet", false);
		}

		//-----------------------------------------set up set form
		$SetForm = new CForm();
		$SetForm->Repost = true;

		$SetForm->DefaultValues["set_id"] = "$currentSet->id";

		$SetForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => true,
			CForm::allowAllOption => true,
			CForm::options => $setList,
			CForm::name => 'set_id'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'save_window',
			CForm::value => 'Save'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'publish window',
			CForm::value => 'Publish'
		));

		$SetForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'fill_window',
			CForm::value => 'Fill from template'
		));

		$SetForm->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'start'
		));

		$SetForm->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'end'
		));

		$SetForm->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'operation'
		));

		$currentMenu = CBrowserSession::instance()->getValue('pm_current_menu');

		$todaysMonth = date("n");
		$todaysYear = date("Y");
		$todaysDay = date("j");

		//TODO: how far back do we go
		$startMonth = $todaysMonth - 2;
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
		$Menu->orderBy("menu.menu_start");

		$Menu->find(true);

		$menu_array = array();
		$menu_months = array();

		$defaultMonth = "";
		$lastMonth = "";
		while ($Menu->fetch())
		{
			$displayString = CTemplate::dateTimeFormat($Menu->menu_start, "%B %Y");
			$menu_array[$Menu->id] = $displayString;
			$menu_months[$Menu->id] = $Menu->menu_start;

			if ($defaultMonth == "")
			{
				if ($currentMenu == $Menu->id)
				{
					$defaultMonth = $Menu->id;
				}
			}
			$lastMonth = $Menu->id;
		}

		if ($defaultMonth == "")
		{
			$defaultMonth = $lastMonth;
		}

		if ($defaultMonth == "")
		{
			throw new Exception("Unable to load a valid menu");
		}

		if (isset($_POST["menus"]))
		{
			$storeMenuForm->DefaultValues['menus'] = CGPC::do_clean($_POST["menus"], TYPE_STR);
		}
		else
		{
			$storeMenuForm->DefaultValues['menus'] = $defaultMonth;
		}

		$storeMenuForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => true,
			CForm::allowAllOption => true,
			CForm::options => $menu_array,
			CForm::name => 'menus'
		));

		// the $currentMenu from the cookie will match the default month unless someone selected a menu with the popup
		if ($currentMenu != $storeMenuForm->value('menus'))
		{
			CBrowserSession::instance()->setValue('pm_current_menu', $storeMenuForm->value('menus'));
			$currentMenu = $storeMenuForm->value('menus');
		}

		$currentMenuTS = strtotime($menu_months[$currentMenu]);
		$currentMonth = date("n", $currentMenuTS);
		$currentYear = date("Y", $currentMenuTS);

		$tmpMenu = DAO_CFactory::create('menu');
		$tmpMenu->menu_start = $menu_months[$currentMenu];
		if ($tmpMenu->find(true))
		{
			// determine menu range
			$tmpMenu->getValidMenuRange(self::$tsMenuBegin, self::$tsMenuEnd);

			//$tpl->assign( 'AAA', date( 'm/d/Y', $tsMenuEnd ) );

			$currentMonthSuffix = '_' . strtolower(date('M', $currentMenuTS));
			//$currentMonth = date("n", $currentMenuTS);
			//$currentYear = date("Y", $currentMenuTS);

			$firstCurrentDate = Date('n', self::$tsMenuBegin) . '/' . Date('j', self::$tsMenuBegin) . '/' . Date('Y', self::$tsMenuBegin);
			self::$validMenuRangeMarkersArray[$firstCurrentDate] = "style=\"background-image: url(" . ADMIN_IMAGES_PATH . "/calendar/cal_menu_start$currentMonthSuffix.gif);" . " background-repeat: no-repeat; background-position:left 24px top 1px;\"";

			$lastCurrentDate = Date('n', self::$tsMenuEnd) . '/' . Date('j', self::$tsMenuEnd) . '/' . Date('Y', self::$tsMenuEnd);
			self::$validMenuRangeMarkersArray[$lastCurrentDate] = "style=\"background-image: url(" . ADMIN_IMAGES_PATH . "/calendar/cal_menu_end$currentMonthSuffix.gif);" . " background-repeat: no-repeat; background-position:right -40px top 0px;\"";
		}
		else
		{
			throw new Exception('blah blah blah');
		}

		$storeMenuFormArray = $storeMenuForm->render(true);

		//--------------------------------------------------set up the fill window

		$timeSpanStart = $SetForm->value('start');
		$timeSpanEnd = $SetForm->value('end');

		if (!$timeSpanEnd && $timeSpanStart)
		{
			$timeSpanEnd = $timeSpanStart;
		}

		if ($timeSpanEnd && !$timeSpanStart)
		{
			$timeSpanStart = $timeSpanEnd;
		}

		if (isset($timeSpanStart) && $timeSpanStart)
		{
			$timeSpanStart = strtotime($timeSpanStart);
		}

		if (isset($timeSpanEnd) && $timeSpanEnd)
		{
			$timeSpanEnd = strtotime($timeSpanEnd);
		}

		//--------------------------------------------------either convert new session template items
		// or echo them back to the browser
		if (isset($_POST['item_ids']) && !empty($_POST['item_ids']))
		{
			$saveItems = explode("|", CGPC::do_clean($_POST['item_ids'], TYPE_STR));

			if (isset($_POST['operation']) && $_POST['operation'] == 'save')
			{

				foreach ($saveItems as $item)
				{
					$thisItem = explode("^", $item);

					// TODO: can be alot more efficient
					if ($thisItem != 0)
					{

						$currentItem = DAO_CFactory::create('session_weekly_template_item');
						$currentItem->id = $thisItem[0] * -1;
						$currentItem->find(true);

						$sessionStartTime = $thisItem[1] . " " . $currentItem->start_time;
						$sessionStartTimeStamp = strtotime($sessionStartTime);

						$savedSession = DAO_CFactory::create('session');
						$savedSession->store_id = $currentStore;
						$savedSession->menu_id = $currentMenu;
						$savedSession->available_slots = $currentItem->available_slots;
						$savedSession->duration_minutes = $currentItem->duration_minutes;
						$savedSession->introductory_slots = $currentItem->introductory_slots;
						$savedSession->session_title = $currentItem->session_title;

						if (!$this->CurrentStore->storeSupportsIntroOrders($currentMenu))
						{
							$savedSession->introductory_slots = 0;
						}

						/*
						 *  TODO: Handle Delivery
						 *
						 *  if (session type is delivery)
						 *  set session_type = SPECIAL_EVENT
						 *  AND set session_type_subtype = DELIVERY
						 * 	else $savedSession->session_type = $currentItem->session_type;
						 *
						 */

						if ($currentItem->session_type == 'DELIVERY')
						{
							$savedSession->session_type = 'SPECIAL_EVENT';
							$savedSession->session_type_subtype = 'DELIVERY';
							$savedSession->session_class = 'SPECIAL_EVENT';
						}
						else
						{
							$savedSession->session_type = $currentItem->session_type;
							$savedSession->session_class = $currentItem->session_type;
						}

						$savedSession->sneak_peak = 0;

						$savedSession->session_publish_state = 'SAVED';
						$savedSession->close_interval_type = $currentItem->close_interval_type;

						$savedSession->session_start = date("Y-m-d H:i:s", $sessionStartTimeStamp);
						$savedSession->setCloseSchedulingTime($savedSession->close_interval_type, $currentItem->close_interval_hours);


						if ($currentItem->session_type== CSession::SPECIAL_EVENT || $currentItem->session_type== CSession::DELIVERY){
							$savedSession->setMealCustomizationCloseSchedulingTime($currentItem->meal_customization_close_interval_type, $currentItem->meal_customization_close_interval);
						}else{
							$savedSession->setMealCustomizationCloseSchedulingTime($currentItem->meal_customization_close_interval_type, -1);
						}

						$savedSession->insert();
					}
				}
				unset($_POST['item_ids']);
			}
			else
			{
				foreach ($saveItems as $item)
				{

					$thisItem = explode("^", $item);

					// TODO: can be alot more efficient
					if ($thisItem != 0)
					{
						$currentItem = DAO_CFactory::create('session_weekly_template_item');
						$currentItem->id = $thisItem[0] * -1;
						$currentItem->find(true);

						self::$sessionArray[$thisItem[1]][$thisItem[2]] = array(
							"time" => $thisItem[2],
							'isM4U' => $currentItem->session_type == CSession::SPECIAL_EVENT ? true : false,
							'isDLVR' => $currentItem->session_type == CSession::DELIVERY ? true : false,
							'isWalkIn' => $currentItem->session_type_subtype == CSession::WALK_IN ? true : false,
							"state" => "NEW",
							"id" => $thisItem[0],
							"isOpen" => true
						);
					}
				}
			}
		}

		if (isset($_POST['operation']) && $_POST['operation'] == 'publish')
		{
			if (isset($_POST['item_ids']) && !empty($_POST['item_ids']))
			{

				self::$sessionArray = array();

				$saveItems = explode("|", CGPC::do_clean($_POST['item_ids'], TYPE_STR));

				foreach ($saveItems as $item)
				{
					$thisItem = explode("^", $item);

					// TODO: can be much more efficient
					if ($thisItem != 0)
					{

						$currentItem = DAO_CFactory::create('session_weekly_template_item');
						$currentItem->id = $thisItem[0] * -1;
						$currentItem->find(true);

						$sessionStartTime = $thisItem[1] . " " . $currentItem->start_time;
						$sessionStartTimeStamp = strtotime($sessionStartTime);

						$savedSession = DAO_CFactory::create('session');
						$savedSession->store_id = $currentStore;
						$savedSession->menu_id = $currentMenu;
						$savedSession->available_slots = $currentItem->available_slots;
						$savedSession->duration_minutes = $currentItem->duration_minutes;
						$savedSession->session_title = $currentItem->session_title;
						$savedSession->introductory_slots = $currentItem->introductory_slots;

						if (!$this->CurrentStore->storeSupportsIntroOrders($currentMenu))
						{
							$savedSession->introductory_slots = 0;
						}

						// TODO: same as saved state
						if ($currentItem->session_type == 'DELIVERY')
						{
							$savedSession->session_type = 'SPECIAL_EVENT';
							$savedSession->session_type_subtype = 'DELIVERY';
							$savedSession->session_class = 'SPECIAL_EVENT';
						}
						else
						{
							$savedSession->session_type = $currentItem->session_type;
							$savedSession->session_class = $currentItem->session_type;
						}

						$savedSession->sneak_peak = 0;

						$savedSession->session_publish_state = 'PUBLISHED';
						$savedSession->close_interval_type = $currentItem->close_interval_type;
						$savedSession->session_start = date("Y-m-d H:i:s", $sessionStartTimeStamp);
						$savedSession->setCloseSchedulingTime($savedSession->close_interval_type, $currentItem->close_interval_hours);

						$savedSession->meal_customization_close_interval_type = $currentItem->meal_customization_close_interval_type;
						if ($savedSession->session_type== CSession::SPECIAL_EVENT || $savedSession->session_type== CSession::DELIVERY){
							$savedSession->setMealCustomizationCloseSchedulingTime($currentItem->meal_customization_close_interval_type, $currentItem->meal_customization_close_interval);
						}else{
							$savedSession->setMealCustomizationCloseSchedulingTime($currentItem->meal_customization_close_interval_type, -1);
						}

						$savedSession->insert();
					}
				}
			}
			// Also publish any previously saved sessions
			$timeSpanStartasSQLDate = date("Y-m-d H:i:s", $timeSpanStart);
			$timeSpanEndasSQLDate = date("Y-m-d H:i:s", $timeSpanEnd + 86400);
			$Sessions = DAO_CFactory::create('session');
			$Sessions->whereAdd("session_publish_state = 'SAVED' and $currentMenu = menu_id and store_id = $currentStore and session_start >= '$timeSpanStartasSQLDate' and session_start <= '$timeSpanEndasSQLDate'");
			$Sessions->find();

			while ($Sessions->fetch())
			{
				$Sessions->session_publish_state = 'PUBLISHED';
				$Sessions->update();
			}

			unset($_POST['item_ids']);
		}

		// Add this after processing it so we can clear it
		$SetForm->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'item_ids'
		));

		//-------------------------------------------------read in existing sessions
		//	$Sessions = DAO_CFactory::create('session');
		//	$Sessions->whereAdd("$currentMenu = menu_id and store_id = $currentStore");
		//	$Sessions->find();

		$Sessions = DAO_CFactory::create('session');
		$Sessions->query("SELECT s.*, dtet.fadmin_acronym FROM session s
									LEFT JOIN session_properties sp on sp.session_id = s.id and sp.is_deleted = 0
									LEFT JOIN dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id and dtep.is_deleted = 0
									LEFT JOIN dream_taste_event_theme dtet on dtet.id = dtep.dream_taste_event_theme# and fadmin_acronym not in 
									WHERE s.menu_id = $currentMenu
									AND s.store_id = $currentStore 
									AND s.is_deleted = 0");
		//AND s.session_type <> 'SPECIAL_EVENT' AND (isnull(dtet.fadmin_acronym) OR dtet.fadmin_acronym not in ('MPWC', 'OHC', 'FC')

		$count = 0;
		while ($Sessions->fetch())
		{
			$asTime = strtotime($Sessions->session_start);
			$dateOnly = Date("n", $asTime) . "/" . Date("j", $asTime) . "/" . Date("Y", $asTime);
			$timeOnly = Date("G", $asTime) . ":" . Date("i", $asTime);
			$isOpen = $Sessions->isOpen($Store);

			//TODO: need means of recognizing new type : Delivery

			$isOpenForCustomization = $Sessions->isOpenForCustomization($Store);
			$allowedCustomization = $Sessions->allowedCustomization($Store);

			self::$sessionArray[$dateOnly][$Sessions->id] = array(
				'time' => $timeOnly,
				'state' => $Sessions->session_publish_state,
				'isM4U' => $Sessions->session_type == CSession::SPECIAL_EVENT && $Sessions->session_type_subtype != CSession::DELIVERY ? true : false,
				'isDLVR' => $Sessions->session_type == CSession::SPECIAL_EVENT && $Sessions->session_type_subtype == CSession::DELIVERY ? true : false,
				'isWalkIn' => $Sessions->session_type_subtype == CSession::WALK_IN ? true : false,
				'isOpenForCustomization' => $isOpenForCustomization,
				'allowedCustomization' => $allowedCustomization,
				'id' => $Sessions->id,
				'isOpen' => $isOpen,
				'can_overlap' => $this->can_overlap($Sessions),
				'duration' => $Sessions->duration_minutes
			);
		}

		//----------------------------------------- fill window with template sessions
		$conflictArray = array();

		if (isset($_POST['fill_window']))
		{

			if (!$timeSpanStart && !$timeSpanEnd)
			{
				$tpl->setErrorMsg('You must define a date range before selecting the fill function.');
			}
			else
			{
				if (!$currentSet)
				{
					throw new Exception("fill_window called with no template set selected.");
				}

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
				$currentItems->find();
				while ($currentItems->fetch())
				{
					$formattedTime = date("G:i", strtotime($currentItems->start_time));
					$items_array[$dayConversionArray[$currentItems->start_day]][$currentItems->id] = array(
						'time' => $formattedTime,
						'isM4U' => $currentItems->session_type == CSession::SPECIAL_EVENT ? true : false,
						'isDLVR' => $currentItems->session_type == CSession::DELIVERY ? true : false,
						'duration' => $currentItems->duration_minutes
					);
				}

				$thisDay = $timeSpanStart;
				while ($thisDay <= $timeSpanEnd)
				{
					$dateOnly = date("n", $thisDay) . "/" . Date("j", $thisDay) . "/" . Date("Y", $thisDay);
					$dayNum = date("w", $thisDay);

					if (array_key_exists($dayNum, $items_array))
					{

						foreach ($items_array[$dayNum] as $k => $v)
						{
							if ($this->timeConflicts($dateOnly, $v['time'], $v['duration'], $v['isM4U'] || $v['isDLVR']))
								//if (isset(self::$sessionArray[$dateOnly][$v['time']]))
							{
								$conflictArray[$dateOnly . " " . $v['time']] = "Could not create new session at $dateOnly " . date("g:i a", strtotime($v['time'])) . ": Time Conflicts with an existing session.";
							}
							else
							{
								$timeIndex = $v['time'];
								if (isset(self::$sessionArray[$dateOnly][$v['time']]))
								{
									$timeIndex .= "a";

								}

								// CES: same start time overwrites
								self::$sessionArray[$dateOnly][$timeIndex] = array(
									'time' => $v['time'],
									'state' => "NEW",
									'id' => $k * -1,
									'isM4U' => $v['isM4U'],
									'isDLVR' => $v['isDLVR'],
									'isWalkIn' => $v['isWalkIn'],
									'isOpen' => true,
									'can_overlap' => true,
									'duration' => $v['duration']
								);
							}
						}
					}
					$thisDay += 86400;
				}
			}
		}

		$calendar = new CCalendar();
		$calendarRows = $calendar->generateDayArray($currentMonth, $currentYear, "populateCallbackPS", $timeSpanStart, $timeSpanEnd, true);

		$CurMonthText = date("F", $currentMenuTS);

		$SetFormArray = $SetForm->render(true);

		if (isset($conflictArray) && !empty($conflictArray))
		{
			$tpl->setErrorMsg(implode("<br />", $conflictArray));
		}

		$tpl->assign('page_title', 'Publish Sessions');
		$tpl->assign('rows', $calendarRows);
		$tpl->assign('calendarName', 'publishCalendar');
		$tpl->assign('calendarTitle', $CurMonthText . " " . $currentYear);
		$tpl->assign('storeAndMenu', $storeMenuFormArray);
		$tpl->assign('templateSetForm', $SetFormArray);
		$tpl->assign('calWidth', 636);
		$tpl->assign('support_cell_selection', true);
	}

	function timeConflicts($date, $time, $duration, $canOverlap)
	{
		if ($canOverlap)
		{
			return false;
		}

		$timeParts = explode(":", $time);
		$timeInMinutes = ($timeParts[0] * 60) + $timeParts[1];
		$endTimeInMinutes = $timeInMinutes + $duration;
		if (isset(self::$sessionArray[$date]))
		{
			foreach (self::$sessionArray[$date] as $id => $data)
			{
				if ($data['can_overlap'])
				{
					continue;
				}

				$ExSessionTimeParts = explode(":", $data['time']);
				$ExSessionTimeInMinutes = ($ExSessionTimeParts[0] * 60) + $ExSessionTimeParts[1];
				$ExSessionEndTimeInMinutes = $ExSessionTimeInMinutes + $data['duration'];

				if ($endTimeInMinutes < $ExSessionTimeInMinutes)
				{
					// ends before ex begins
					continue;
				}
				else if ($timeInMinutes > $ExSessionEndTimeInMinutes)
				{
					// begins after ex ends
					continue;
				}
				else if ($timeInMinutes >= $ExSessionTimeInMinutes && $timeInMinutes < $ExSessionEndTimeInMinutes)
				{
					return true;
				}
				else if ($endTimeInMinutes <= $ExSessionEndTimeInMinutes && $endTimeInMinutes > $ExSessionTimeInMinutes)
				{
					return true;
				}
			}

			return false;
		}
		else
		{
			return false;
		}
	}

	function can_overlap($session)
	{
		if ($session->session_type == CSession::STANDARD)
		{
			return false;
		}

		if ($session->session_type == CSession::SPECIAL_EVENT)
		{
			return true;
		}

		if (!empty($session->fadmin_acronym) && in_array($session->fadmin_acronym, array(
				'MPWC',
				'OHC',
				'FC'
			)))
		{
			return true;
		}

		return false;
	}

}

?>