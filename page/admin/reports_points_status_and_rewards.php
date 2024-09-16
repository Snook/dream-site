<?php // page_user_details.php

require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStatesAndProvinces.php");
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CEnrollmentPackage.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CUser.php');
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CPointsCredits.php');

require_once('fpdf/class_multicelltag.php');

class page_admin_reports_points_status_and_rewards extends CPageAdminOnly
{

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runManufacturerStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}
	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function getStatusDisplayString($inStatus, $User)
	{
		$retVal = $inStatus;

		if ($inStatus == "in_DR2")
		{
			$retVal = "Dream Rewards 2 ";
			if ($User->dream_reward_level > 12)
			{
				$retVal .= " (VIP)";
			}

			$retVal .= " Level " . $User->dream_reward_level;
		}

		return $retVal;
	}

	function runFranchiseOwner()
	{
		$tpl = CApp::instance()->template();
		$currentStore = CBrowserSession::getCurrentFadminStore();

		$sessionArray = array();

		$session_id = (isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : false);

		$reportDate = false;

		if ($session_id && is_numeric($session_id))
		{
			$sessionArray[] = $session_id;
			CLog::RecordReport("PlatePoints Status and Rewards Report per Session", "Session:$session_id");
		}
		else
		{

			$reportDate = (isset($_REQUEST['report_date']) ? $_REQUEST['report_date'] : false);
			$storeID = (isset($_REQUEST['store_id']) ? $_REQUEST['store_id'] : false);

			if ($reportDate)
			{
				$reportDate = CGPC::do_clean($reportDate, TYPE_DATE_YYYYMMDD);
			}

			if ($reportDate)
			{
				$dateValues = explode("-", $reportDate);

				$Session = DAO_CFactory::create("session");
				$Session->findDailySessions($storeID, $dateValues[2], $dateValues[1], $dateValues[0]);

				while ($Session->fetch())
				{
					$sessionArray[] = $Session->id;
				}

				CLog::RecordReport("PlatePoints Status and Rewards Report per Day", "Date:$reportDate");
			}
			else
			{
				// problem
				$tpl->setErrorMsg("There was a problem retrieving the PLATEPOINTS data for this day/session.");
				CApp::bounce('/backoffice');
			}
		}

		$sections = array();

		if ($reportDate)
		{
			$tpl->assign('date_header', "for Date " . $reportDate);
		}

		foreach ($sessionArray as $this_session_id)
		{

			$sessionDateGetter = DAO_CFactory::create('session');
			$sessionDateGetter->query("select session_start from session where id = $this_session_id");
			$sessionDateGetter->fetch();
			$SectionHeader = "Session: " . CTemplate::dateTimeFormat($sessionDateGetter->session_start);

			$Users = DAO_CFactory::create('user');
			$Users->query("select u.*, b.order_id from booking b
					join user u on u.id = b.user_id
					where b.session_id = $this_session_id and b.status = 'ACTIVE'
					group by u.id");

			$rows = array();

			while ($Users->fetch())
			{
				$platePointsData = $Users->getPlatePointsSummary(false, true);

				$platePointsData['status_display_str'] = $this->getStatusDisplayString($platePointsData['status'], $Users);

				if ($platePointsData['status'] == 'active' || ($platePointsData['status'] == 'inactive' && $platePointsData['userIsOnHold']))
				{

					if ($platePointsData['userIsOnHold'])
					{
						$platePointsData['status_display_str'] = "On Hold";
					}

				    if ($platePointsData['current_level']['level'] == 'enrolled')
                    {

                        // when a member (enrolled) the gifts are driven by order
                        $streakData = CPointsUserHistory::getOrdersSequenceStatus($Users->id, $Users->order_id);

                        if ($streakData['focusOrderInOriginalStreak'])
                        {
                            $platePointsData['habitStreakOrderNumberForThisOrder'] = $streakData['focusOrderStreakOrderNumber'];
                            $platePointsData['habitStreakOrderCount'] = $streakData['InitialStreakOrderCount'];
                        }

                        $platePointsData['orderBasedGiftData'] = CPointsUserHistory::getOrderBasedGiftData($Users->id, $Users->order_id, $streakData);

                        $platePointsData['receivedGifts'] = CPointsUserHistory::getReceivedOrderBasedGifts($Users->id);


                    }
                    else
                    {
						if (!$platePointsData['due_reward_for_current_level'])
						{
							$giftReceivedID = CPointsUserHistory::getGiftIDReceivedForLevel($platePointsData['current_level'], $Users->id);

							if ($giftReceivedID)
							{
								$platePointsData['gift_display_str'] = CPointsUserHistory::getGiftDisplayString($giftReceivedID);
							}
							else
							{
								$platePointsData['gift_display_str'] = CPointsUserHistory::getGiftDisplayString($platePointsData['current_level']['rewards']['gift_id']);
							}
						}
						else
						{
							if (is_array($platePointsData['current_level']))
							{
								$platePointsData['gift_display_str'] = CPointsUserHistory::getGiftDisplayString($platePointsData['current_level']['rewards']['gift_id']);
							}
						}
					}

				}

				$rows[$Users->id] = array_merge($platePointsData, array('guest_name' => $Users->firstname . " " . $Users->lastname, 'user_id' => $Users->id, 'order_id' => $Users->order_id));
			}

			$sections[$SectionHeader] = $rows;
		}

		$tpl->assign('sections', $sections);
	}
}

?>