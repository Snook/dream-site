<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CMail.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CUserData.php");
require_once("includes/DAO/BusinessObject/CFoodTesting.php");
require_once("page/admin/main.php");
require_once("page/admin/create_session.php");
require_once("processor/admin/processMetrics.php");

class processor_admin_status extends CPageProcessor
{

	function runSiteAdmin()
	{
		$this->mainStatus();
	}

	function mainStatus()
	{
		if (!empty($_POST['op']))
		{
			// Get status
			if ($_POST['op'] == 'import_menu' && !empty($_POST['menu_id']) && is_numeric($_POST['menu_id']))
			{
				$Menu = DAO_CFactory::create('menu');
				$Menu->query("SELECT
					m.id,
					m.menu_name
					FROM menu AS m
					INNER JOIN menu_to_menu_item AS mtmi ON mtmi.menu_id = m.id
					WHERE m.is_deleted = '0'
					AND m.id = '" . $_POST['menu_id'] . "'
					GROUP BY m.id");

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'import_menu success',
					'status' => (($Menu->fetch()) ? true : false)
				));
				exit;
			}

			// Get status
			if ($_POST['op'] == 'import_inventory' && !empty($_POST['menu_id']) && is_numeric($_POST['menu_id']))
			{
				$Menu = DAO_CFactory::create('menu');
				$Menu->query("SELECT * FROM `menu_item_inventory` WHERE menu_id = '" . $_POST['menu_id'] . "' LIMIT 1");

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'import_inventory success',
					'status' => (($Menu->fetch()) ? true : false)
				));
				exit;
			}

			// Get status
			if ($_POST['op'] == 'import_nutritionals' && !empty($_POST['menu_id']) && is_numeric($_POST['menu_id']))
			{
				$Menu = DAO_CFactory::create('menu');
				$Menu->query("SELECT * FROM recipe WHERE override_menu_id = '" . $_POST['menu_id'] . "' LIMIT 1");

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'import_nutritionals success',
					'status' => (($Menu->fetch()) ? true : false)
				));
				exit;
			}

			// Get status
			if ($_POST['op'] == 'import_sidesmap' && !empty($_POST['menu_id']) && is_numeric($_POST['menu_id']))
			{
				$Menu = DAO_CFactory::create('menu');
				$Menu->query("SELECT
					menu.id
					FROM menu
					INNER JOIN menu_to_menu_item ON menu_to_menu_item.menu_id = menu.id AND menu_to_menu_item.is_deleted = '0'
					INNER JOIN entree_to_side ON entree_to_side.entree_id = menu_to_menu_item.menu_item_id AND entree_to_side.is_deleted = '0'
					WHERE menu.id = '" . $_POST['menu_id'] . "'
					AND menu.is_deleted = '0'
					LIMIT 1");

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'import_sidesmap success',
					'status' => (($Menu->fetch()) ? true : false)
				));
				exit;
			}

			// Get status
			if ($_POST['op'] == 'bundle_intro' && !empty($_POST['menu_id']) && is_numeric($_POST['menu_id']))
			{
				$Menu = DAO_CFactory::create('bundle');
				$Menu->query("SELECT
					b.id
					FROM bundle AS b
					INNER JOIN bundle_to_menu_item AS btmi ON btmi.bundle_id = b.id
					WHERE b.menu_id = '" . $_POST['menu_id'] . "'
					AND b.bundle_type = 'TV_OFFER'
					AND b.is_deleted = '0'
					LIMIT 1");

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'bundle_intro success',
					'status' => (($Menu->fetch()) ? true : false)
				));
				exit;
			}

			// Get status
			if ($_POST['op'] == 'bundle_dreamtaste' && !empty($_POST['menu_id']) && is_numeric($_POST['menu_id']))
			{
				$Menu = DAO_CFactory::create('bundle');
				$Menu->query("SELECT
					b.id
					FROM bundle AS b
					INNER JOIN bundle_to_menu_item AS btmi ON btmi.bundle_id = b.id
					INNER JOIN dream_taste_event_properties ON dream_taste_event_properties.bundle_id = b.id
					INNER JOIN dream_taste_event_theme ON dream_taste_event_theme.id = dream_taste_event_properties.dream_taste_event_theme
					WHERE b.menu_id = '" . $_POST['menu_id'] . "'
					AND b.bundle_type = 'DREAM_TASTE'
					AND b.is_deleted = '0'
					LIMIT 1");

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'bundle_dreamtaste success',
					'status' => (($Menu->fetch()) ? true : false)
				));
				exit;
			}


			if ($_POST['op'] == 'cron_status')
			{
			    $this->doCronStatusCheck();
			}

		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'No operation.'
			));
			exit;
		}
	}

	public function doCronStatusCheck($returnHTMLResults = false)
	{

	    try {

	    //      show yesterdays         show todays - in progress            - show todays
	    // |---------------------|--------------------------------|-------------------------------------------------------------------|
	    // midnight ET          4:00am - processing begins      ~8:00am ends                                                        midnight

	    $resultsArray = array('EXPIRE_STORE_CREDIT' => array(),'WARN_EXPIRE_STORE_CREDIT' => array(),'CONFIRM_PLATEPOINTS_ORDERS' => array(),'EXPIRE_PLATEPOINTS_CREDITS' => array(),
	        'WARN_EXPIRE_PLATEPOINTS_CREDITS' => array(),'BIRTHDAY_REWARDS' => array(), 'DELAYED_PAYMENTS' => array(),
	        'SESSION_REMINDERS' => array(),'PUSH_GIFT_CARD_REPORT' => array(),'REFERRAL_REWARDS' => array(),
	        'USER_RETENTION_REMOVE' => array(),'DASHBOARDCACHING' => array(), 'USER_RETENTION_NEW' => array(),
	    	'FOOD_TESTING_REMINDER_EMAIL' => array(), 'CACHE_STORE_CLASSES' => array(), 'DELETE_OLD_SAVED_ORDERS' => array(), 'CLEAR_STALE_CARTS' => array(), 'CACHE_GLOBAL_RECIPE_RATING' => array(), 'UPDATE_MEMBERSHIP_STATUS' => array());

	    // check that cron tasks have run
	    $db = CLog::instance()->connect();

	    $myTpl = new CTemplate();

	    $in_progress = false;

	    $time = time();
	    if (($time % 86400) < (3600 * 4))
	    {
	        $yesterday = date("Y-m-d", $time - 86400);
            // after midnight before 4am
	        $query = "select * from cron_log where DATE(timestamp_created) = '$yesterday')";
	    }
	    else if (($time % 86400) < (3600 * 8))
	    {
	        // after 4am before 8am
	        $query = "select * from cron_log where DATE(timestamp_created) = DATE(now())";
	        // check todays but don't regard incomplete as an error
	        $in_progress = true;
	    }
	    else
	    {
	        $query = "select * from cron_log where DATE(timestamp_created) = DATE(now())";

	    }

	    $res = mysqli_query($db, $query);

	    $someFailures = false;
	    $allFailed = true;

	    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
	    {

	        $allFailed = false;

	        if ($row['processing_status'] != CLog::SUCCESS && $row['cron_type'] != 'BIRTHDAY_REWARDS')
	        {
	            $someFailures = true;
	        }

            if(isset($resultsArray[$row['cron_type']]))
            {
                $resultsArray[$row['cron_type']][$row['id']] = array('success' => ($row['processing_status'] == CLog::SUCCESS),
                                    'items_processed' => $row['records_processed'], 'comments' => $row['processing_comments'],
                                    'timestamp' =>  $row['timestamp_created']);
            }
            else
            {
                throw new Exception("Unknown cron type");
            }

	    }


        // Birthday reward cron only runs onthe 1st and 24th so set to success for other days

	    $curDay = date("j");
	    if ($curDay != 1 && ($curDay < 24 || $curDay > 27))
        {

            $resultsArray['BIRTHDAY_REWARDS'][0] = array('success' => true,
                                    'items_processed' => 0, 'comments' => "Not scheduled to run today",
                                    'timestamp' =>  'n/a');

        }

        // Saved order deletion cron only runs onthe 7th so set to success for other days

        if ($curDay != 7)
        {

        	$resultsArray['DELETE_OLD_SAVED_ORDERS'][0] = array('success' => true,
        		'items_processed' => 0, 'comments' => "Not scheduled to run today",
        		'timestamp' =>  'n/a');

        }

        foreach($resultsArray as $type => $data)
        {

            if (empty($data))
            {
                $someFailures = true;

                $resultsArray[$type][0] = array('success' => false,
                    'items_processed' => 0, 'comments' => $type . ' has not run.',
                    'timestamp' =>  date("Y-m-d H:i:s"));
            }

        }

        if (isset($resultsArray['BIRTHDAY_REWARDS']))
        {
        	// Birthday rewards are different as it may fail once and later succeed.
        	// Look for any success and alter results to show oever all success

        	$anySuccess = false;
        	$anyFailure = false;

        	foreach($resultsArray['BIRTHDAY_REWARDS'] as $id => $data)
        	{
        		if ($data['success'])
        		{
        			$anySuccess = true;
        		}
        		else
        		{
        			$anyFailure = true;
        		}
        	}


        	if ($anySuccess && $anyFailure)
        	{
        		foreach($resultsArray['BIRTHDAY_REWARDS'] as $id => $data)
        		{
        			unset($resultsArray['BIRTHDAY_REWARDS'][$id]);
        		}
        	}
        	else if (!$anySuccess && $anyFailure)
        	{
        		$someFailures = true;
        	}

        }


	    $myTpl->assign('in_progress', $in_progress);
	    $myTpl->assign('statusList', $resultsArray);
	    $cron_status_view = $myTpl->fetch('admin/subtemplate/status_cron.tpl.php');

	    if ($returnHTMLResults)
	    {
	          return array($cron_status_view, ($allFailed || $someFailures));
	    }

	    echo json_encode(array(
	        'processor_success' => true,
	        'processor_message' => 'bundle_dreamtaste success',
	        'view' => $cron_status_view));
	    exit;


	    } catch (exception $e) {
	        CLog::RecordException($e);
	    }


	}

}
?>