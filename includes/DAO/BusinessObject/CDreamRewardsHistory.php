<?php
require_once 'DAO/Dream_rewards_history.php';

class CDreamRewardsHistory extends DAO_Dream_rewards_history
{
	static $DRStateMap = array(
		0 => 'neverInProgram',
		1 => 'active',
		2 => 'deactivated',
		3 => 'reactivated',
		4 => 'optedOut',
		5 => 'onHold'
	);

 	static $DRDescriptiveNameMap = array(
 		0 => 'Never in Program',
 		1 => 'Active',
 		2 => 'Deactivated',
 		3 => 'Reactivated',
 		4 => 'Opted Out',
		5 => 'On Hold'
 	);
	// removed option 0 since dropdown in not visible when 0 is selected and businss rules disallow selecting it

 	static $DRShortDescriptiveNameMap = array(
 		0 => 'N/A',
 		1 => 'Active',
 		2 => 'Deactivated',
 		3 => 'Reactivated',
		4 => 'Opted Out',
 		5 => 'On Hold'
 	);

	static $ActiveOptions = array(
		1 => 'Active',
		2 => 'Deactivate',
		4 => 'Opt Out'
	);

	static $InactiveOptionsV1 = array(
		2 => 'Deactivated',
		3 => 'Re-Activate in DR (V1)',
		4 => 'Opt Out'
	);

	static $InactiveOptionsV2 = array(
		2 => 'Deactivated',
		3 => 'Re-Activate in DR (V2)',
		4 => 'Opt Out'
	);

	static $InactiveOptionsWithResetV1 = array(
		2 => 'Deactivated',
		3 => 'Re-Activate in DR (V1)',
		10 => 'Re-Activate in DR (V2)',
		4 => 'Opt Out'
	);

	static $InactiveOptionsWithResetV2 = array(
		2 => 'Deactivated',
		3 => 'Re-Activate in DR (V2)',
		4 => 'Opt Out'
	);

	static $ReactivatedOptions = array(
		2 => 'Deactivate',
		3 => 'Reactivated',
		4 => 'Opt Out'
	);

	private static $rewardDataByLevelVersion1 = array(
		0 => array('type' => 'na', 'display_text' => 'This order is not applicable to the Dream Rewards Program.', 'value' => 0),
		1 => array('type' => 'no_reward', 'display_text' => '1st Order - No reward', 'value' => 0),
		2 => array('type' => 'percent', 'display_text' => '2nd Order - 5% off and defrost bin', 'value' => 5),
		3 => array('type' => 'percent', 'display_text' => '3rd Order - 10% off qualifying order', 'value' => 10),
		4 => array('type' => 'percent', 'display_text' => '4th Order - 15% off qualifying order', 'value' => 15),
		5 => array('type' => 'gift', 'display_text' => '5th Order - Dream Rewards Guest', 'value' => 0),
		6 => array('type' => 'certificate', 'display_text' => '6th Order - Dream Rewards Guest', 'value' => 0),
		7 => array('type' => 'gift', 'display_text' => '7th Order - Dream Rewards Guest', 'value' => 0),
		8 => array('type' => 'gift', 'display_text' => '8th Order - Dream Rewards Guest', 'value' => 0),
		9 => array('type' => 'percent', 'display_text' => '9th Order - 20% off qualifying order', 'value' => 20),
		10 => array('type' => 'gift', 'display_text' => '10th Order - Dream Rewards Guest', 'value' => 0),
		11 => array('type' => 'gift', 'display_text' => '11th Order - Dream Rewards Guest', 'value' => 0),
		12 => array('type' => 'percent', 'display_text' => '12th Order - 30% off qualifying order', 'value' => 30),
		13 => array('type' => 'percent', 'display_text' => 'Dream Rewards VIP - 10% off qualifying order', 'value' => 10)
		/*
		14 => array('type' => 'percent', 'display_text' => '14th (VIP) - 10% off qualifying order', 'value' => 10),
		15 => array('type' => 'percent', 'display_text' => '15th (VIP) - 10% off qualifying order', 'value' => 10),
		16 => array('type' => 'percent', 'display_text' => '16th (VIP) - 10% off qualifying order', 'value' => 10),
		17 => array('type' => 'percent', 'display_text' => '17th (VIP) - 10% off qualifying order', 'value' => 10),
		18 => array('type' => 'percent', 'display_text' => '18th (VIP) - 10% off qualifying order', 'value' => 10),
		19 => array('type' => 'percent', 'display_text' => '19th (VIP) - 10% off qualifying order', 'value' => 10),
		20 => array('type' => 'percent', 'display_text' => '20th (VIP) - 10% off qualifying order', 'value' => 10),
		21 => array('type' => 'percent', 'display_text' => '21st (VIP) - 10% off qualifying order', 'value' => 10),
		22 => array('type' => 'percent', 'display_text' => '22nd (VIP) - 10% off qualifying order', 'value' => 10),
		23 => array('type' => 'percent', 'display_text' => '23rd (VIP) - 10% off qualifying order', 'value' => 10),
		24 => array('type' => 'percent', 'display_text' => '24th (VIP) - 10% off qualifying order', 'value' => 10),
		25 => array('type' => 'percent', 'display_text' => '25th (VIP) - 10% off qualifying order', 'value' => 10),
		26 => array('type' => 'percent', 'display_text' => '26th (VIP) - 10% off qualifying order', 'value' => 10),
		27 => array('type' => 'percent', 'display_text' => '27th (VIP) - 10% off qualifying order', 'value' => 10),
		28 => array('type' => 'percent', 'display_text' => '28th (VIP) - 10% off qualifying order', 'value' => 10),
		29 => array('type' => 'percent', 'display_text' => '29th (VIP) - 10% off qualifying order', 'value' => 10),
		30 => array('type' => 'percent', 'display_text' => '30th (VIP) - 10% off qualifying order', 'value' => 10),
		31 => array('type' => 'percent', 'display_text' => '31st (VIP) - 10% off qualifying order', 'value' => 10),
		32 => array('type' => 'percent', 'display_text' => '32nd (VIP) - 10% off qualifying order', 'value' => 10),
		33 => array('type' => 'percent', 'display_text' => '33rd (VIP) - 10% off qualifying order', 'value' => 10),
		34 => array('type' => 'percent', 'display_text' => '34th (VIP) - 10% off qualifying order', 'value' => 10),
		35 => array('type' => 'percent', 'display_text' => '35th (VIP) - 10% off qualifying order', 'value' => 10),
		36 => array('type' => 'percent', 'display_text' => '36th (VIP) - 10% off qualifying order', 'value' => 10),
		*/
	);

	private static $rewardDataByLevelVersion2 = array(
		0 => array('type' => 'na', 'display_text' => 'This order is not applicable to the Dream Rewards Program.', 'value' => 0),
		1 => array('type' => 'gift', 'display_text' => '1st Order - Defrost Bin', 'value' => 0),
		2 => array('type' => 'percent', 'display_text' => '2nd Order - 5% off qualifying order', 'value' => 5),
		3 => array('type' => 'percent', 'display_text' => '3rd Order - 5% off qualifying order', 'value' => 5),
		4 => array('type' => 'percent', 'display_text' => '4th Order - 5% off qualifying order', 'value' => 5),
		5 => array('type' => 'no_reward', 'display_text' => '5th Order - Dream Rewards Guest', 'value' => 0),
		6 => array('type' => 'percent', 'display_text' => '6th Order - 10% off qualifying order', 'value' => 10),
		7 => array('type' => 'no_reward', 'display_text' => '7th Order - Dream Rewards Guest', 'value' => 0),
		8 => array('type' => 'percent', 'display_text' => '8th Order - 10% off qualifying order', 'value' => 10),
		9 => array('type' => 'no_reward', 'display_text' => '9th Order - Dream Rewards Guest', 'value' => 0),
		10 => array('type' => 'percent', 'display_text' => '10th Order - 15% off qualifying order', 'value' => 15),
		11 => array('type' => 'no_reward', 'display_text' => '11th Order - Dream Rewards Guest', 'value' => 0),
		12 => array('type' => 'percent', 'display_text' => '12th Order - 20% off qualifying order', 'value' => 20),
		13 => array('type' => 'percent', 'display_text' => 'Dream Rewards VIP - 10% off qualifying order', 'value' => 10)
		/*
		14 => array('type' => 'percent', 'display_text' => '14th (VIP) - 10% off qualifying order', 'value' => 10),
		15 => array('type' => 'percent', 'display_text' => '15th (VIP) - 10% off qualifying order', 'value' => 10),
		16 => array('type' => 'percent', 'display_text' => '16th (VIP) - 10% off qualifying order', 'value' => 10),
		17 => array('type' => 'percent', 'display_text' => '17th (VIP) - 10% off qualifying order', 'value' => 10),
		18 => array('type' => 'percent', 'display_text' => '18th (VIP) - 10% off qualifying order', 'value' => 10),
		19 => array('type' => 'percent', 'display_text' => '19th (VIP) - 10% off qualifying order', 'value' => 10),
		20 => array('type' => 'percent', 'display_text' => '20th (VIP) - 10% off qualifying order', 'value' => 10),
		21 => array('type' => 'percent', 'display_text' => '21st (VIP) - 10% off qualifying order', 'value' => 10),
		22 => array('type' => 'percent', 'display_text' => '22nd (VIP) - 10% off qualifying order', 'value' => 10),
		23 => array('type' => 'percent', 'display_text' => '23rd (VIP) - 10% off qualifying order', 'value' => 10),
		24 => array('type' => 'percent', 'display_text' => '24th (VIP) - 10% off qualifying order', 'value' => 10),
		25 => array('type' => 'percent', 'display_text' => '25th (VIP) - 10% off qualifying order', 'value' => 10),
		26 => array('type' => 'percent', 'display_text' => '26th (VIP) - 10% off qualifying order', 'value' => 10),
		27 => array('type' => 'percent', 'display_text' => '27th (VIP) - 10% off qualifying order', 'value' => 10),
		28 => array('type' => 'percent', 'display_text' => '28th (VIP) - 10% off qualifying order', 'value' => 10),
		29 => array('type' => 'percent', 'display_text' => '29th (VIP) - 10% off qualifying order', 'value' => 10),
		30 => array('type' => 'percent', 'display_text' => '30th (VIP) - 10% off qualifying order', 'value' => 10),
		31 => array('type' => 'percent', 'display_text' => '31st (VIP) - 10% off qualifying order', 'value' => 10),
		32 => array('type' => 'percent', 'display_text' => '32nd (VIP) - 10% off qualifying order', 'value' => 10),
		33 => array('type' => 'percent', 'display_text' => '33rd (VIP) - 10% off qualifying order', 'value' => 10),
		34 => array('type' => 'percent', 'display_text' => '34th (VIP) - 10% off qualifying order', 'value' => 10),
		35 => array('type' => 'percent', 'display_text' => '35th (VIP) - 10% off qualifying order', 'value' => 10),
		36 => array('type' => 'percent', 'display_text' => '36th (VIP) - 10% off qualifying order', 'value' => 10),
		*/
	);

	static function rewardDataByLevel($programVersion, $level, $downgradeCount = false)
	{
		if ($programVersion == 0)
		{
			return null;
		}


		if ($programVersion == 3)
		{
			return null;
		}


		if ($programVersion != 1 and $programVersion != 2)
		{
			throw new Exception('Invalid Dream Rewards program version');
		}

		if (!is_numeric($level) or $level < 0)
		{
			throw new Exception('Invalid Dream Rewards level');
		}


		if (!isset($level))
		{
			$tempLevel = 0;
		}
		else if ($level > 13)
		{
			$tempLevel = 13;
		}
		else
		{
			$tempLevel = $level;
		}

		if ($programVersion == 1)
		{
			return self::$rewardDataByLevelVersion1[$tempLevel];
		}
		else
		{
			$retVal =  self::$rewardDataByLevelVersion2[$tempLevel];

			if ($tempLevel == 13 and $downgradeCount)
			{
				if ($downgradeCount > 1)
					$retVal['display_text'] = "Dream Rewards VIP (5% off next $downgradeCount qualifying orders)";
				else
					$retVal['display_text'] = "Dream Rewards VIP (5% off next qualifying order)";
			}
			return $retVal;
		}
	}

	static function nextRewardDataByLevel($programVersion, $level, $downgradeCount = false)
	{
		$level = $level + 1;

		if($level > 13)
		{
			$level = 13;
		}

		return self::rewardDataByLevel($programVersion, $level, $downgradeCount);
	}

	private static $EditOptionsVersion1 = array(
		1 => '1st Order',
		2 => '2nd Order - 5% off',
		3 => '3rd Order - 10% off',
		4 => '4th Order - 15% off',
		5 => '5th Order',
		6 => '6th Order',
		7 => '7th Order',
		8 => '8th Order',
		9 => '9th Order - 20% off',
		10 => '10th Order',
		11 => '11th Order',
		12 => '12th Order - 30% off',
		13 => 'Dream Rewards VIP - 10% off'
		/*
		14 => '14th Order (VIP) - 10% off',
		15 => '15th Order (VIP) - 10% off',
		16 => '16th Order (VIP) - 10% off',
		17 => '17th Order (VIP) - 10% off',
		18 => '18th Order (VIP) - 10% off',
		19 => '19th Order (VIP) - 10% off',
		20 => '20th Order (VIP) - 10% off',
		21 => '21st Order (VIP) - 10% off',
		22 => '22nd Order (VIP) - 10% off',
		23 => '23rd Order (VIP) - 10% off',
		24 => '24th Order (VIP) - 10% off',
		25 => '25th Order (VIP) - 10% off',
		26 => '26th Order (VIP) - 10% off',
		27 => '27th Order (VIP) - 10% off',
		28 => '28th Order (VIP) - 10% off',
		29 => '29th Order (VIP) - 10% off',
		30 => '30th Order (VIP) - 10% off',
		31 => '31st Order (VIP) - 10% off',
		32 => '32nd Order (VIP) - 10% off',
		33 => '33rd Order (VIP) - 10% off',
		34 => '34th Order (VIP) - 10% off',
		35 => '35th Order (VIP) - 10% off',
		36 => '36th Order (VIP) - 10% off',
		*/
	);

	private static $EditOptionsVersion2 = array(
		0 => 'Not a Dream Rewards Order',
		1 => '1st Order',
		2 => '2nd Order - 5% off',
		3 => '3rd Order - 5% off',
		4 => '4th Order - 5% off',
		5 => '5th Order',
		6 => '6th Order - 10% off',
		7 => '7th Order',
		8 => '8th Order - 10% off',
		9 => '9th Order',
		10 => '10th Order - 15% off',
		11 => '11th Order',
		12 => '12th Order - 20% off',
		13 => 'Dream Rewards VIP - 10% off'
		/*
		14 => '14th Order (VIP) - 10% off',
		15 => '15th Order (VIP) - 10% off',
		16 => '16th Order (VIP) - 10% off',
		17 => '17th Order (VIP) - 10% off',
		18 => '18th Order (VIP) - 10% off',
		19 => '19th Order (VIP) - 10% off',
		20 => '20th Order (VIP) - 10% off',
		21 => '21st Order (VIP) - 10% off',
		22 => '22nd Order (VIP) - 10% off',
		23 => '23rd Order (VIP) - 10% off',
		24 => '24th Order (VIP) - 10% off',
		25 => '25th Order (VIP) - 10% off',
		26 => '26th Order (VIP) - 10% off',
		27 => '27th Order (VIP) - 10% off',
		28 => '28th Order (VIP) - 10% off',
		29 => '29th Order (VIP) - 10% off',
		30 => '30th Order (VIP) - 10% off',
		31 => '31st Order (VIP) - 10% off',
		32 => '32nd Order (VIP) - 10% off',
		33 => '33rd Order (VIP) - 10% off',
		34 => '34th Order (VIP) - 10% off',
		35 => '35th Order (VIP) - 10% off',
		36 => '36th Order (VIP) - 10% off',
		*/
	);

	private static $LevelNameArray = array(
		1 => '1st Order',
		2 => '2nd Order',
		3 => '3rd Order',
		4 => '4th Order',
		5 => '5th Order',
		6 => '6th Order',
		7 => '7th Order',
		8 => '8th Order',
		9 => '9th Order',
		10 => '10th Order',
		11 => '11th Order',
		12 => '12th Order',
		13 => 'Dream Rewards VIP'
		/*
		14 => '14th Order (VIP)',
		15 => '15th Order (VIP)',
		16 => '16th Order (VIP)',
		17 => '17th Order (VIP)',
		18 => '18th Order (VIP)',
		19 => '19th Order (VIP)',
		20 => '20th Order (VIP)',
		21 => '21st Order (VIP)',
		22 => '22nd Order (VIP)',
		23 => '23rd Order (VIP)',
		24 => '24th Order (VIP)',
		25 => '25th Order (VIP)',
		26 => '26th Order (VIP)',
		27 => '27th Order (VIP)',
		28 => '28th Order (VIP)',
		29 => '29th Order (VIP)',
		30 => '30th Order (VIP)',
		31 => '31st Order (VIP)',
		32 => '32nd Order (VIP)',
		33 => '33rd Order (VIP)',
		34 => '34th Order (VIP)',
		35 => '35th Order (VIP)',
		36 => '36th Order (VIP)',
		*/
	);

	function levelName($level)
	{
		if (!is_numeric($level) or $level < 0)
		{
			throw new Exception('Invalid Dream Rewards level');
		}

		if ($level > 24)
		{
			return "VIP order# $level";
		}

		return self::$LevelNameArray[$level];
	}

	static $ShortLevelDescArray = array(
		0 => 'Not a Dream Rewards Order',
		1 => '1st Order',
		2 => '2nd Order',
		3 => '3rd Order',
		4 => '4th Order',
		5 => '5th Order',
		6 => '6th Order',
		7 => '7th Order',
		8 => '8th Order',
		9 => '9th Order',
		10 => '10th Order',
		11 => '11th Order',
		12 => '12th Order',
		13 => 'Dream Rewards VIP'
		/*
		14 => '14th Order (VIP)',
		15 => '15th Order (VIP)',
		16 => '16th Order (VIP)',
		17 => '17th Order (VIP)',
		18 => '18th Order (VIP)',
		19 => '19th Order (VIP)',
		20 => '20th Order (VIP)',
		21 => '21st Order (VIP)',
		22 => '22nd Order (VIP)',
		23 => '23rd Order (VIP)',
		24 => '24th Order (VIP)',
		25 => '25th Order (VIP)',
		26 => '26th Order (VIP)',
		27 => '27th Order (VIP)',
		28 => '28th Order (VIP)',
		29 => '29th Order (VIP)',
		30 => '30th Order (VIP)',
		31 => '31st Order (VIP)',
		32 => '32nd Order (VIP)',
		33 => '33rd Order (VIP)',
		34 => '34th Order (VIP)',
		35 => '35th Order (VIP)',
		36 => '36th Order (VIP)',
		37 => 'VIP Order',
		*/
	);

	static function shortLevelDesc($level)
	{
		if (!is_numeric($level) or $level < 0)
		{
			return "";
		}

		if ($level > 12)
		{
			return "VIP order# $level";
		}

		return self::$ShortLevelDescArray[$level];
	}

	private static $ShortLevelDescArrayWithDiscountVersion1 = array(0 => 'NA',
		1 => '1st Order',
		2 => '2nd Order (5% off)',
		3 => '3rd Order (10% off)',
		4 => '4th Order (15% off)',
		5 => '5th Order',
		6 => '6th Order',
		7 => '7th Order',
		8 => '8th Order',
		9 => '9th Order (20% off)',
		10 => '10th Order',
		11 => '11th Order',
		12 => '12th Order (30% off)',
		13 => 'Dream Rewards VIP (10% off)'
	);

	private static $ShortLevelDescArrayWithDiscountVersion2 = array(0 => 'NA',
		1 => '1st Order',
		2 => '2nd Order (5% off)',
		3 => '3rd Order (5% off)',
		4 => '4th Order (5% off)',
		5 => '5th Order',
		6 => '6th Order (10% off)',
		7 => '7th Order',
		8 => '8th Order (10% off)',
		9 => '9th Order',
		10 => '10th Order (15% off)',
		11 => '11th Order',
		12 => '12th Order (20% off)',
		13 => 'Dream Rewards VIP (10% off)'
	);

	static function shortLevelDescArrayWithDiscount($programVersion, $level)
	{
		if (!is_numeric($level) or $level < 0)
		{
			throw new Exception('Invalid Dream Rewards level');
		}

		$specific_level = "";
		if ($level > 12)
		{
			$specific_level = " (#" . $level . ")";
		}

		if ($level > 13)
		{
			$level = 13;
		}

		if ($programVersion != 1 and $programVersion != 2)
		{
			throw new Exception('Invalid Dream Rewards program version');
		}

		if ($programVersion == 1)
		{
			return self::$ShortLevelDescArrayWithDiscountVersion1[$level] . $specific_level;
		}
		else
		{
			return self::$ShortLevelDescArrayWithDiscountVersion2[$level] . $specific_level;
		}
	}

	static function getCurrentStateForOrder($UserDAO, $StoreDAO, $orderDAO, $editable = false, $curOrderLevel = false, $forEmptyOrder = false)
	{
		$show = false;

		if ($StoreDAO->supports_dream_rewards)
		{
			$show = true;
		}

		$neverOptedIn = $UserDAO->dream_reward_status == 0;

		$orderLevel = 0;

		if ($forEmptyOrder)
		{
			if ($orderDAO->dream_rewards_level < 13)
			{
				$rewardData = self::rewardDataByLevel(2, $UserDAO->dream_reward_level);
			}
			else
			{
				if ($orderDAO->dr_downgraded_order_count == 0)
				{
					$rewardData = array(
							'type' => 'percent',
							'display_text' => "Order# {$UserDAO->dream_reward_level} (VIP) - 10% discount",
							'value' => 10,
					);
				}
				else
				{
					$downgradedCount = 3 - $UserDAO->dr_downgraded_order_count;
					$rewardData = array(
							'type' => 'percent',
							'display_text' => "Order# {$UserDAO->dream_reward_level} (VIP) - 5% discount (#$downgradedCount)",
							'value' => 5,
					);
				}
			}

			$orderLevel = $UserDAO->dream_reward_level;

		}
		else
		{
			if ($orderDAO->dream_rewards_level < 13)
			{
				$rewardData = self::rewardDataByLevel($UserDAO->dream_rewards_version, $orderDAO->dream_rewards_level);
			}
			else
			{
				if (!$orderDAO->is_dr_downgraded_order)
				{
					$rewardData = array(
						'type' => 'percent',
						'display_text' => "Order# {$orderDAO->dream_rewards_level} (VIP) - 10% discount",
						'value' => 10,
					);
				}
				else
				{
					$downgradedCount = 3 - $UserDAO->dr_downgraded_order_count;
					$rewardData = array(
						'type' => 'percent',
						'display_text' => "Order# {$orderDAO->dream_rewards_level} (VIP) - 5% discount (#$downgradedCount)",
						'value' => 5,
					);
				}
			}
			$orderLevel = $orderDAO->dream_rewards_level;
		}

		$retVal = array(
			'readOnly' => !$editable,
			'show' => $show,
			'neverOptedIn' => $neverOptedIn,
			'status' => $UserDAO->dream_reward_status,
			'is_ordering' => true,
			'program_version' => $UserDAO->dream_rewards_version,
			'level' => $orderLevel,
			'user_id' => $UserDAO->id,
			'store_id' => $StoreDAO->id,
			'user_name' => $UserDAO->firstname . " " . $UserDAO->lastname,
			'reward' => $rewardData,
			'status_text' => self::$DRDescriptiveNameMap[$UserDAO->dream_reward_status],
			'order_id' => $orderDAO->id
		);

		if ($editable)
		{
			$Form  = new CForm();

			$retVal['order_edit_view'] = !$neverOptedIn;

			// always use database
			unset($_POST['dr_level_for_order']);
			unset($_POST['dr_status']);
			unset($_POST['dr_level']);
			unset($_POST['dr_level_total']);

			$Form->DefaultValues['dr_status'] = $UserDAO->dream_reward_status;
			$Form->DefaultValues['dr_level'] = ($UserDAO->dream_reward_level > 12 ? 13 : $UserDAO->dream_reward_level);
			$Form->DefaultValues['dr_level_total'] = $UserDAO->dream_reward_level;

			if ($UserDAO->dream_reward_level > 12 and $UserDAO->dr_downgraded_order_count > 0)
			{
				$Form->addElement(array(CForm::type=> CForm::CheckBox,
					CForm::name => 'is_downgraded',
					CForm::checked => true,
					CForm::disabled => true));
			}

			$retVal['this_order_was_downgraded']= false;

			if ($orderDAO->is_dr_downgraded_order)
			{
				$Form->addElement(array(CForm::type=> CForm::CheckBox,
					CForm::name => 'this_order_downgraded',
					CForm::onClick => 'calculateTotal',
					CForm::checked => true));

				$retVal['this_order_was_downgraded'] = true;
			}

			$levelOptions = null;

			if ($UserDAO->dream_rewards_version == 1)
			{
				$levelOptions = &self::$EditOptionsVersion1;
			}
			else
			{
				$levelOptions = &self::$EditOptionsVersion2;
			}

			if ($curOrderLevel)
			{
				$Form->DefaultValues['dr_level_for_order'] = ($curOrderLevel > 12 ? 13 : $curOrderLevel);

				$Form->addElement(array(CForm::type=> CForm::DropDown,
					CForm::allowAllOption => false,
					CForm::name => 'dr_level_for_order',
					CForm::onChange => 'dr_level_change',
					CForm::disabled => false,
					CForm::options => $levelOptions));
			}
			else
			{
				$Form->DefaultValues['dr_level_for_order'] = ($orderLevel > 12 ? 13 : $orderLevel);

				$Form->addElement(array(CForm::type=> CForm::DropDown,
					CForm::allowAllOption => false,
					CForm::name => 'dr_level_for_order',
					CForm::onChange => 'dr_level_change',
					CForm::disabled => false,
					CForm::options => $levelOptions));
			}

			$statusOptions = null;

			if ($UserDAO->dream_reward_status == 1)
			{
				$statusOptions = &self::$ActiveOptions;
			}
			else if ($UserDAO->dream_reward_status == 2)
			{
				$statusOptions = &self::$InactiveOptionsV2;
			}
			else if ($UserDAO->dream_reward_status == 3)
			{
				$statusOptions = &self::$ReactivatedOptions;
			}
			else
			{
				$statusOptions = &self::$ActiveOptions;
			}

			$Form->addElement(array(CForm::type=> CForm::DropDown,
				CForm::allowAllOption => false,
				CForm::name => 'dr_status',
				CForm::disabled => true,
				CForm::options => $statusOptions));

			$Form->addElement(array(CForm::type=> CForm::DropDown,
				CForm::allowAllOption => false,
				CForm::name => 'dr_level',
				CForm::onChange => 'level_change',
				CForm::disabled => true,
				CForm::options => self::$LevelNameArray));

			$Form->addElement(array(CForm::type=> CForm::Text,
				CForm::name => 'dr_level_total',
				CForm::size => 2,
				CForm::onKeyUp => 'javascript:level_total_change(this.value)'));

			$FormArray = $Form->Render();
			$retVal['form'] = $FormArray;
		}
		return $retVal;
	}

	static function canOfferReactivationChoice($UserDAO)
	{
		$Events = DAO_CFactory::create('dream_rewards_history');
		$Events->query("select event_id from dream_rewards_history where event_id <> 4 and user_id = {$UserDAO->id} order by id desc limit 1");

		// if the last event for this user was a cron deactivation then we can offer the reactivatin choice so return true;
		if ($Events->fetch())
		{
			if ($Events->event_id == 7 or $Events->event_id == 11)
			{
				return true;
			}
		}
		return false;
	}

	static function getCurrentStateForUser($UserDAO, $StoreDAO, $readOnlyWhenNotHomeStore = false, $in_order_process = true)
	{
		$show = false;

		if ($StoreDAO->supports_dream_rewards)
		{
			$show = true;
		}

		$neverOptedIn = $UserDAO->dream_reward_status == 0;

		$readOnly = false;
		if ($readOnlyWhenNotHomeStore)
		{
			if (!empty($UserDAO->home_store_id) and $UserDAO->home_store_id != $StoreDAO->id)
			{
				$readOnly = true;
			}
		}

		//if ($UserDAO->dream_reward_status == 4)
		//	$readOnly = true;

		// TODO : detect preferred user status and shut out user from this progeam id present

		$Form  = new CForm();

		// always use database
		unset($_POST['dr_status']);
		unset($_POST['dr_level']);
		unset($_POST['dr_level_total']);

		$Form->DefaultValues['dr_status'] = $UserDAO->dream_reward_status;
		$Form->DefaultValues['dr_level'] = ($UserDAO->dream_reward_level > 12 ? 13 : $UserDAO->dream_reward_level);
		$Form->DefaultValues['dr_level_total'] = $UserDAO->dream_reward_level;

		$downgradeCount = $UserDAO->dr_downgraded_order_count;

		$statusOptions = null;

		if ($UserDAO->dream_reward_status == 1)
		{
			$statusOptions = &self::$ActiveOptions;
		}
		else if ($UserDAO->dream_reward_status == 2)
		{
			$statusOptions = &self::$InactiveOptionsV2;
		}
		else if ($UserDAO->dream_reward_status == 3)
		{
			$statusOptions = &self::$ReactivatedOptions;
		}
		else
		{
			$statusOptions = &self::$ActiveOptions;
		}

		if ($UserDAO->dream_reward_level > 12 and $UserDAO->dr_downgraded_order_count > 0)
		{
			$Form->addElement(array(CForm::type=> CForm::CheckBox,
				CForm::name => 'is_downgraded',
				CForm::checked => true,
				CForm::disabled => true));
		}

		$Form->addElement(array(CForm::type=> CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'dr_status',
			CForm::disabled => true,
			CForm::options => $statusOptions));

		$levelOptions = null;

		if ($UserDAO->dream_rewards_version == 1)
		{
			$levelOptions = &self::$EditOptionsVersion1;
		}
		else
		{
			$levelOptions = &self::$EditOptionsVersion2;
		}

		$Form->addElement(array(CForm::type=> CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'dr_level',
			CForm::onChange => 'level_change',
			CForm::disabled => true,
			CForm::options => $levelOptions));

		$Form->addElement(array(CForm::type=> CForm::Text,
			CForm::name => 'dr_level_total',
			CForm::size => 2,
			CForm::onKeyUp => 'javascript:level_total_change(this.value)'));

		$FormArray = $Form->Render();

		if ($UserDAO->dream_reward_level < 13)
		{
			$rewardData = self::rewardDataByLevel($UserDAO->dream_rewards_version, $UserDAO->dream_reward_level);
		}
		else
		{
			if (!$UserDAO->dr_downgraded_order_count)
			{
				$rewardData = array(
					'type' => 'percent',
					'display_text' => "Order# {$UserDAO->dream_reward_level} (VIP) - 10% discount",
					'value' => 10,
				);
			}
			else
			{
				$downgradedCount = 3 - $UserDAO->dr_downgraded_order_count;
				$rewardData = array(
					'type' => 'percent',
					'display_text' => "Order# {$UserDAO->dream_reward_level} (VIP) - 5% discount (#$downgradedCount)",
					'value' => 5,
				);
			}
		}

		$retVal = array(
			'readOnly' => $readOnly,
			'show' => $show,
			'neverOptedIn' => $neverOptedIn,
			'status' => $UserDAO->dream_reward_status,
			'is_ordering' => $in_order_process,
			'program_version' => $UserDAO->dream_rewards_version,
			'level' => $UserDAO->dream_reward_level,
			'user_id' => $UserDAO->id,
			'store_id' => $StoreDAO->id,
			'user_name' => $UserDAO->firstname . " " . $UserDAO->lastname,
			'form' => $FormArray,
			'reward' => $rewardData,
			'status_text' => self::$DRDescriptiveNameMap[$UserDAO->dream_reward_status],
			'downgrade_count' => $downgradeCount
		);

		return $retVal;
	}

	// For Customer facing display in myaccount and emails
	// if the customer is not currently active in the program return false
	static function getCurrentStateForUserShortForm($UserDAO, $isAdminView = false)
	{
		$retVal = array();

		$canShowData = (($UserDAO->dream_rewards_version == 1 || $UserDAO->dream_rewards_version == 2) && ($UserDAO->dream_reward_status == 1 || $UserDAO->dream_reward_status == 3 || $isAdminView));

		if ($canShowData)
		{
			$retVal['status'] = self::$DRDescriptiveNameMap[$UserDAO->dream_reward_status];

			if ($UserDAO->dream_reward_level < 13 and $UserDAO->dream_reward_level > 0)
			{
				$retVal['level'] = self::$LevelNameArray[$UserDAO->dream_reward_level];
			}
			else if ($UserDAO->dream_reward_level == 0)
			{
				$retVal['level'] = 'N/A';
			}
			else
			{
				$retVal['level'] = 'Order# ' . $UserDAO->dream_reward_level . ' (VIP)';
			}

			$retVal['program_version'] = $UserDAO->dream_rewards_version;

			return $retVal;
		}

		return false;
	}

	const FADMIN_UPDATE_EVENT_ID = 0;
	const CRON_TASK_DEACTIVATION_EVENT_ID = 1;
	const ELIGIBLE_ORDER_EVENT_ID = 2;
	const CANCELLED_ORDER_EVENT_ID = 3;
	const SKIPPED_ORDER_EVENT_ID = 4;
	const FIFTEEN_DAY_WARNING_SENT_EVENT_ID = 5;
	const THIRTY_DAY_WARNING_SENT_EVENT_ID = 6;
	const FORTY_FIVE_DAY_DEACTIVATION_EVENT_ID = 7;
	const VIP_WELCOME_EMAIL_EVENT_ID = 8;
	const VIP_EIGHTEENTH_VISIT_EMAIL_EVENT_ID = 9;
	const VIP_THIRTY_DAY_REMINDER_EMAIL_EVENT_ID = 10;
	const VIP_DEACTIVATION_EMAIL_EMAIL_EVENT_ID = 11;
	const VIP_FORTY_FIVE_DAY_EMAIL_EVENT_ID = 12;
	const CONVERT_FROM_VERSION_1_TO_VERSION_2 = 13;

	static $eventDescMap = array(
		0 => 'fadmin_update',
		1 => 'cron_task_demotion',
		2 => 'eligible_order',
		3 => 'cancelled_order',
		4 => 'skipped_order',
		5 => '15_day_warning_sent',
		6 => '30_day_warning_sent',
		7 => '45_day_deactivation',
		8 => 'VIP_welcome_email_sent',
		9 => 'VIP_18th_Visit_Email',
		10 => 'VIP_30_day_reminder_email',
		11 => 'VIP_90_day_deactivation_email',
		12 => 'VIP_45_day_downgrade_email'
	);

	static function incrementStatus($rewardsData, $OrderObj, $UserObj)
	{
		if ($rewardsData['status'] == 1 or  $rewardsData['status'] == 3)
		{
			// fadmin will have been warned about servings level - allow increment here

			$rewardsData['level']++;

			$userCopy = clone($UserObj);
			$previousLevel = $UserObj->dream_reward_level;

			if ($previousLevel == 18)
			{
				//send coupon email
				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->query("select id, email_address from store where id = {$OrderObj->store_id}");
				$StoreObj->fetch();

				self::send_18th_visit_email($UserObj, $StoreObj);
			}

			if ($previousLevel != $rewardsData['level'])
			{
				$UserObj->dream_reward_level = $rewardsData['level'];
				$UserObj->update($userCopy);

				CDreamRewardsHistory::recordDreamRewardsEvent($UserObj->id, $OrderObj->store_id, $OrderObj->id, self::ELIGIBLE_ORDER_EVENT_ID, $UserObj->dream_reward_status, $UserObj->dream_reward_level, $UserObj->dream_reward_status, $previousLevel, 'order increments level- : ' . CAppUtil::truncate($_SERVER['HTTP_REFERER'], 72));

				if ($rewardsData['level'] > 12 and !CDreamRewardsHistory::hasReceivedVIPWelcomeEmail($UserObj->id))
				{
					$StoreObj = DAO_CFactory::create('store');
					$StoreObj->query("select id, store_name, telephone_day, email_address from store where id = {$OrderObj->store_id}");
					$StoreObj->fetch();
					CDreamRewardsHistory::send_dr_vip_welcome_email($UserObj, $StoreObj);
				}
			}
			else
			{
				CDreamRewardsHistory::recordDreamRewardsEvent($UserObj->id, $OrderObj->store_id, $OrderObj->id, self::ELIGIBLE_ORDER_EVENT_ID, $UserObj->dream_reward_status, $UserObj->dream_reward_level, $UserObj->dream_reward_status, $previousLevel, 'eligible order - level not incremented : ' . CAppUtil::truncate($_SERVER['HTTP_REFERER'], 72));
			}

			if ($UserObj->dr_downgraded_order_count > 0)
			{
				CLog::Assert($OrderObj->is_dr_downgraded_order, 'The is_downgraded bit not set when user is VIP_downgraded');
				if ($UserObj->dr_downgraded_order_count == 1)
				{
					// TODO: third order reached so re-activate
					// otherwise just decrement count
					$tempUserObj = DAO_CFactory::create('user');
					$tempUserObj->query("update user set dr_downgraded_order_count = 0 where id = {$UserObj->id}");
				}
				else
				{
					// otherwise just decrement count
					$newDowngradedStatus = $UserObj->dr_downgraded_order_count - 1;
					$tempUserObj = DAO_CFactory::create('user');
					$tempUserObj->query("update user set dr_downgraded_order_count = $newDowngradedStatus where id = {$UserObj->id}");
				}
			}
		}
	}

	static function send_dr_vip_welcome_email($User, $Store)
	{
		try
		{
			$Mail = new CMail();

			$data = array(
				'store_name' => $Store->store_name,
				'store_contact_text' => $Store->telephone_day . ", " .  $Store->email_address,
			);

			$contentsText = CMail::mailMerge('dream_rewardsvip_notify.txt.php', $data);
			$contentsHtml = CMail::mailMerge('dream_rewardsvip_notify.html.php', $data);

			$Mail->send(null, $Store->email_address, $User->firstname.' '.$User->lastname, $User->primary_email, 'Welcome to Dream Rewards VIP!', $contentsHtml, $contentsText, '','', $User->id, 'dream_rewards_vip_welcome');
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
			return;
		}

		CDreamRewardsHistory::recordDreamRewardsEvent($User->id, $Store->id, '', self::VIP_WELCOME_EMAIL_EVENT_ID, $User->dream_reward_status, $User->dream_reward_level, $User->dream_reward_status, $User->dream_reward_level, 'Sent VIP welcome email');
	}

	static function send_18th_visit_email($User, $Store)
	{
		try
		{
			$Mail = new CMail();

			$data = array('customer_name' => $User->firstname . ' ' . $User->lastname);

			$contentsHtml = CMail::mailMerge('dream_rewardsvip_certificate.html.php', $data);

			$Mail->send(null, $Store->email_address,
				$User->firstname.' '.$User->lastname, $User->primary_email,
				'Dream Rewards VIP Special Gift', $contentsHtml,
				null, '','', $User->id, 'dream_rewardsvip_certificate');
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
			return;
		}

		// TODO: update this logging function

		CDreamRewardsHistory::recordDreamRewardsEvent($User->id, $Store->id, '', self::VIP_EIGHTEENTH_VISIT_EMAIL_EVENT_ID, $User->dream_reward_status, $User->dream_reward_level, $User->dream_reward_status, $User->dream_reward_level, 'Sent 18th Visit email');
	}

	// for example emails only...
	static function send_VIP_deactivation_email($User, $Store)
	{
		try
		{
			$Mail = new CMail();

			$data = array('store_name' => $Store->store_name,
				'store_contact_text' => $Store->telephone_day . ", " .  $Store->email_address,
				'session_time' => CTemplate::dateTimeFormat("2009-09-09 09:09:09"));

			$contentsText = CMail::mailMerge('dream_rewardsvip_expire.txt.php', $data);
			$contentsHtml = CMail::mailMerge('dream_rewardsvip_expire.html.php', $data);

			$Mail->send(null, $Store->email_address,
				$User->firstname.' '.$User->lastname, $User->primary_email,
				'Dream Rewards VIP Status Update', $contentsHtml,
				$contentsText, '','', $User->id, 'dream_rewards_vip_welcome');

		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
			return;
		}

		// TODO: update this logging function

		CDreamRewardsHistory::recordDreamRewardsEvent($User->id, $Store->id, '', self::VIP_DEACTIVATION_EMAIL_EMAIL_EVENT_ID, $User->dream_reward_status, $User->dream_reward_level, $User->dream_reward_status, $User->dream_reward_level, 'Sent VIP deactivation email');
	}

	// for example emails only...
	static function send_VIP_downgrade_email($User, $Store)
	{
		try
		{
			$Mail = new CMail();

			$data = array('store_name' => $Store->store_name,
				'store_contact_text' => $Store->telephone_day . ", " .  $Store->email_address,
				'session_time' => CTemplate::dateTimeFormat("2009-09-09 09:09:09"),
				'cutOff_time' => CTemplate::dateTimeFormat('2009-10-24 00:00:00', VERBOSE_DATE));

			$contentsText = CMail::mailMerge('dream_rewardsvip_status_change.txt.php', $data);
			$contentsHtml = CMail::mailMerge('dream_rewardsvip_status_change.html.php', $data);

			$Mail->send(null, $Store->email_address,
				$User->firstname.' '.$User->lastname, $User->primary_email,
				'Dream Rewards VIP Status Update', $contentsHtml,
				$contentsText, '','', $User->id, 'dream_rewards_vip_welcome');

		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
			return;
		}

		// TODO: update this logging function

		CDreamRewardsHistory::recordDreamRewardsEvent($User->id, $Store->id, '', self::VIP_FORTY_FIVE_DAY_EMAIL_EVENT_ID, $User->dream_reward_status, $User->dream_reward_level, $User->dream_reward_status, $User->dream_reward_level, 'Sent VIP 45 day downgrade email');
	}

	static function hasReceivedVIPWelcomeEmail($user_id)
	{
		$historyDAO = DAO_CFactory::create('dream_rewards_history');
		$historyDAO->user_id = $user_id;
		$historyDAO->event_id = self::VIP_WELCOME_EMAIL_EVENT_ID;

		if ($historyDAO->find())
		{
			return true;
		}

		return false;
	}

	static function recordDreamRewardsEvent($user_id, $store_id, $order_id, $event_id, $curStatus, $curLevel, $previousStatus, $previousLevel, $description = 'null')
	{
		$remote_address = 'localhost';
		if (isset($_SERVER['REMOTE_ADDR']))
		{
			$remote_address = $_SERVER['REMOTE_ADDR'];
		}

		$historyDAO = DAO_CFactory::create('dream_rewards_history');
		$historyDAO->user_id = $user_id;
		$historyDAO->store_id = $store_id;
		$historyDAO->event_id = $event_id;
		$historyDAO->order_id = $order_id;
		$historyDAO->previous_program_level = $previousLevel;
		$historyDAO->current_program_level = $curLevel;
		$historyDAO->previous_program_status = $previousStatus;
		$historyDAO->current_program_status = $curStatus;
		$historyDAO->description = $description;
		$historyDAO->ip_address = $remote_address;

		$historyDAO->insert();
	}

	static function getHistoryArray($user_id, $store_id, $showAllOverride = false)
	{
		$retVal = array();
		$historyDAO = DAO_CFactory::create('dream_rewards_history');

		$storeClause = "";
		if (!$showAllOverride)
		{
			$storeClause = " and drh.store_id = $store_id ";
		}

		$historyDAO->query("select drh.*, CONCAT(u.firstname, ' ', u.lastname) as admin_name from dream_rewards_history drh " .
				" join user u on drh.created_by = u.id where drh.user_id = $user_id $storeClause order by id asc");

		while($historyDAO->fetch())
		{
			$description = CAppUtil::truncate($historyDAO->description, 128);

			$retVal[$historyDAO->id] = array('event' => self::$eventDescMap[$historyDAO->event_id],
				'org_status' => self::$DRStateMap[$historyDAO->previous_program_status],
				'cur_status' => self::$DRStateMap[$historyDAO->current_program_status],
				'org_level' => self::shortLevelDesc($historyDAO->previous_program_level),
				'cur_level' => self::shortLevelDesc($historyDAO->current_program_level),
				'description' => $description,
				'datetime' => CTemplate::dateTimeFormat($historyDAO->timestamp_created),
				'admin' => $historyDAO->admin_name);
		}
		return $retVal;
	}
}
?>