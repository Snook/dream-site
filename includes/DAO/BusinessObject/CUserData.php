<?php
require_once 'DAO/User_data.php';

/* ------------------------------------------------------------------------------------------------
*	Class: CUserData
*
*	Data:
*
*	Methods:
*		Create()
*
*	Properties:
*
*
*	Description:
*
*
*	Requires:
*
* -------------------------------------------------------------------------------------------------- */

define('BIRTH_MONTH_FIELD_ID', 1); // 4500 total, 1054 no store, 8 duplicates to clean up
define('NUMBER_KIDS_FIELD_ID', 2); // 4100 total,  990 no store, 10 dupes to clean up
define('FAVORITE_MEAL_FIELD_ID', 3); // 436 total, 10 no store, no dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
define('WHY_WORKS_FIELD_ID', 4);        // 1312 total, 4 no store, no dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
define('GUEST_EMPLOYER_FIELD_ID', 5);    // 2346 total, 8 no store, 1 dupe -xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
define('UPCOMING_EVENTS_FIELD_ID', 6);  // 727 total, 9 no store, 7 dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
define('MISC_NOTES_FIELD_ID', 7);    // 12,741 total, 93 no store, 28 dupes
define('FAMILY_SIZE_FIELD_ID', 10);    // 4305 total, 1002 no store, 9 dupes
define('SPOUSE_EMPLOYER_FIELD_ID', 11);  // 1099 total, 6 no store, no dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
define('BIRTH_YEAR_FIELD_ID', 15);        //	1340 total, 981 no store, 1 dupe - must delete the "none" answers
define('GUEST_CARRY_OVER_NOTE', 16);    // 15,807 total, 0 no store, a few dupes
define('CONTRIBUTE_INCOME', 17); // 343 total, all no store, no dupes
define('USE_LISTS', 18);  // 326 total, all no store, no dupes
define('NUMBER_NIGHTS_OUT', 20); // (per week) 348 total, all no store, no dupes
define('PREFER_DAYTIME_SESSIONS_FIELD_ID', 21);
define('PREFER_EVENING_SESSIONS_FIELD_ID', 22);
define('PREFER_WEEKEND_SESSIONS_FIELD_ID', 23);
define('NUMBER_NIGHTS_OUT_PER_MONTH', 24); // (per month)
define('HOW_MANY_PEOPLE_FEEDING_FIELD_ID', 25);
define('DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID', 26);

define('DD_ANNIVERSARY_FIELD_ID', 9);  // not used
define('COOK_AT_HOME', 19); // OBSOLETE

define('AAA_REFERRED_FIELD_ID', 8);
define('EXPORTED_FOR_REDBOOK', 12);
define('REFERRAL_SOURCE_NOTES', 13);
define('TV_OFFER_REFERRAL_SOURCE', 14);

class CUserData extends DAO_User_data
{
	const BIRTH_MONTH_FIELD_ID = 1; // 4500 total, 1054 no store, 8 duplicates to clean up
	const NUMBER_KIDS_FIELD_ID = 2; // 4100 total,  990 no store, 10 dupes to clean up
	const FAVORITE_MEAL_FIELD_ID = 3; // 436 total, 10 no store, no dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	const WHY_WORKS_FIELD_ID = 4;        // 1312 total, 4 no store, no dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	const GUEST_EMPLOYER_FIELD_ID = 5;    // 2346 total, 8 no store, 1 dupe -xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	const UPCOMING_EVENTS_FIELD_ID = 6;  // 727 total, 9 no store, 7 dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	const MISC_NOTES_FIELD_ID = 7;    // 12,741 total, 93 no store, 28 dupes
	const FAMILY_SIZE_FIELD_ID = 10;    // 4305 total, 1002 no store, 9 dupes
	const SPOUSE_EMPLOYER_FIELD_ID = 11;  // 1099 total, 6 no store, no dupes -xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	const BIRTH_YEAR_FIELD_ID = 15;        //	1340 total, 981 no store, 1 dupe - must delete the "none" answers
	const GUEST_CARRY_OVER_NOTE = 16;    // 15,807 total, 0 no store, a few dupes
	const CONTRIBUTE_INCOME = 17; // 343 total, all no store, no dupes
	const USE_LISTS = 18;  // 326 total, all no store, no dupes
	const NUMBER_NIGHTS_OUT = 20; // (per week) 348 total, all no store, no dupes
	const PREFER_DAYTIME_SESSIONS_FIELD_ID = 21;
	const PREFER_EVENING_SESSIONS_FIELD_ID = 22;
	const PREFER_WEEKEND_SESSIONS_FIELD_ID = 23;
	const NUMBER_NIGHTS_OUT_PER_MONTH = 24; // (per month)
	const HOW_MANY_PEOPLE_FEEDING_FIELD_ID = 25;
	const DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID = 26;

	const DD_ANNIVERSARY_FIELD_ID = 9;  // not used
	const COOK_AT_HOME = 19; // OBSOLETE

	const AAA_REFERRED_FIELD_ID = 8;
	const EXPORTED_FOR_REDBOOK = 12;
	const REFERRAL_SOURCE_NOTES = 13;
	const TV_OFFER_REFERRAL_SOURCE = 14;

	static function monthArray()
	{
		return array(
			'1' => 'January',
			'2' => 'February',
			'3' => 'March',
			'4' => 'April',
			'5' => 'May',
			'6' => 'June',
			'7' => 'July',
			'8' => 'August',
			'9' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);
	}

	static function reverseMonthArray()
	{
		return array(
			'January' => 1,
			'February' => 2,
			'March' => 3,
			'April' => 4,
			'May' => 5,
			'June' => 6,
			'July' => 7,
			'August' => 8,
			'September' => 9,
			'October' => 10,
			'November' => 11,
			'December' => 12
		);
	}

	static function dayArray()
	{
		$array = array();

		for ($day = 1; $day <= 31; $day++)
		{
			$array[$day] = $day;
		}

		return $array;
	}

	static function yearArray($limit = 100)
	{
		$curYear = intval(date("Y"));
		$curYear -= 17;

		$array = array();

		for ($year = $curYear; $year >= $curYear - $limit; $year--)
		{
			$array[$year] = $year;
		}

		return $array;
	}

	static function getUserDataArray($user_ids, $store_id = null)
	{
		$userData = DAO_CFactory::create('user_data');
		$userData->query("SELECT *
				FROM user_data
				WHERE user_id IN (" . $user_ids . ")
				AND store_id = '" . $store_id . "'
				AND is_deleted = '0'");

		$user_data_array = array();

		while ($userData->fetch())
		{
			$user_data_array[$userData->user_id][$userData->user_data_field_id] = $userData->user_data_value;
		}

		return $user_data_array;
	}

	static function filterUserCarryoverNote($note)
	{
		return str_replace(array(
			"\r",
			"\r\n",
			"\n"
		), ' ', strip_tags($note));
	}

	// $user_id and $store_id to retrieve note from db, add $set_note to set note
	static function userCarryoverNote($user_id, $store_id, $set_note = false)
	{
		$guestnote = DAO_CFactory::create('user_data');
		$guestnote->query("SELECT ud.*,
				st.hide_carryover_notes
				FROM user_data AS ud
				INNER JOIN store AS st ON st.id = ud.store_id
				WHERE ud.user_id = '" . $user_id . "'
				AND ud.store_id = '" . $store_id . "'
				AND ud.user_data_field_id = '" . GUEST_CARRY_OVER_NOTE . "'
				AND ud.is_deleted = '0'
				ORDER BY ud.id DESC
				LIMIT 1");
		$guestnote->fetch();

		if ($set_note !== false)
		{
			require_once('includes/class.inputfilter_clean.php');

			$xssFilter = new InputFilter();
			$set_note = $xssFilter->process($set_note);

			$note = self::filterUserCarryoverNote($set_note);
			//$note = strip_tags($set_note);

			$guestnote->user_data_value = $note;

			if (!empty($guestnote->id))
			{
				$guestnote->updated_by = CUser::getCurrentUser()->id;
				$guestnote->update();
			}
			else
			{
				$guestnote->user_id = $user_id;
				$guestnote->store_id = $store_id;
				$guestnote->user_data_field_id = GUEST_CARRY_OVER_NOTE;
				$guestnote->insert();
			}
		}

		return $guestnote;
	}

	static function buildSFIFormElementsNew(&$Form, $User, $isAdmin = false)
	{
		$ddRequired = false;

		if ($User->hasEnrolledInPlatePoints() && !$isAdmin)
		{
			$ddRequired = true;
		}

		// current data
		$currentSFIData = array();

		if (!empty($User->id))
		{
			$UData = DAO_CFactory::create('user_data');

			$UData->query("SELECT ud.user_data_field_id, ud.user_data_value FROM user_data ud WHERE user_id = " . $User->id . " AND ud.user_data_field_id IN (1,2,10,15,17,18,19,20,21,22,23,24,25,26) AND is_deleted = 0");

			while ($UData->fetch())
			{
				$currentSFIData[$UData->user_data_field_id] = $UData->user_data_value;
			}
		}

		if (isset($currentSFIData[BIRTH_MONTH_FIELD_ID]) && !empty($currentSFIData[BIRTH_MONTH_FIELD_ID]))
		{
			if (!is_numeric($currentSFIData[BIRTH_MONTH_FIELD_ID]))
			{
				$months = self::reverseMonthArray();
				$currentSFIData[BIRTH_MONTH_FIELD_ID] = $months[$currentSFIData[BIRTH_MONTH_FIELD_ID]];
			}

			$Form->DefaultValues['birthday_month'] = $currentSFIData[BIRTH_MONTH_FIELD_ID];
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "birthday_month",
			CForm::dd_type => "dd_user_data",
			CForm::dd_required => $ddRequired,
			CForm::css_class => "custom-select",
			CForm::required_msg => "Please select birth month.",
			CForm::options => array('null' => 'Month') + CUserData::monthArray()
		));

		if (isset($currentSFIData[BIRTH_YEAR_FIELD_ID]) && !empty($currentSFIData[BIRTH_YEAR_FIELD_ID]) && $currentSFIData[BIRTH_YEAR_FIELD_ID] != 'none')
		{
			$Form->DefaultValues['birthday_year'] = $currentSFIData[BIRTH_YEAR_FIELD_ID];
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "birthday_year",
			CForm::dd_type => "dd_user_data",
			CForm::dd_required => $ddRequired,
			CForm::css_class => "custom-select",
			CForm::required_msg => "Please select birth year.",
			CForm::options => array('null' => 'Year') + CUserData::yearArray()
		));

		// number of kids at home
		if (isset($currentSFIData[HOW_MANY_PEOPLE_FEEDING_FIELD_ID]) && !empty($currentSFIData[HOW_MANY_PEOPLE_FEEDING_FIELD_ID]))
		{
			$Form->DefaultValues['number_feeding'] = $currentSFIData[HOW_MANY_PEOPLE_FEEDING_FIELD_ID];
		}
		else if (CBrowserSession::getValue('number_feeding'))
		{
			$Form->DefaultValues['number_feeding'] = CBrowserSession::getValue('number_feeding');
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'number_feeding',
			CForm::dd_type => "dd_user_data",
			CForm::css_class => "custom-select",
			CForm::required => $ddRequired,
			CForm::required_msg => "Please select number of people you are feeding.",
			CForm::options => array(
				'' => 'Number of People',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7 or more'
			)
		));

		// number of kids at home
		if (isset($currentSFIData[DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID]) && !empty($currentSFIData[DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID]))
		{
			$Form->DefaultValues['desired_homemade_meals_per_week'] = $currentSFIData[DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID];
		}
		else if (CBrowserSession::getValue('desired_homemade_meals_per_week'))
		{
			$Form->DefaultValues['desired_homemade_meals_per_week'] = CBrowserSession::getValue('desired_homemade_meals_per_week');
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'desired_homemade_meals_per_week',
			CForm::dd_type => "dd_user_data",
			CForm::css_class => "custom-select",
			CForm::required => $ddRequired,
			CForm::required_msg => "Please select number of desired meals.",
			CForm::options => array(
				'' => 'Number of Meals',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7'
			)
		));

		/*


		// number of kids at home
		if (isset($currentSFIData[NUMBER_KIDS_FIELD_ID]) && !empty($currentSFIData[NUMBER_KIDS_FIELD_ID]))
		{
			$Form->DefaultValues['number_of_kids'] = $currentSFIData[NUMBER_KIDS_FIELD_ID];
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'number_of_kids',
			CForm::dd_type => "dd_user_data",
			CForm::css_class => "custom-select",
			CForm::dd_required => $ddRequired,
			CForm::required_msg => "Please select number of kids.",
			CForm::options => array(
				'' => 'Number of kids',
				'none' => 'None',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6 or more'
			)
		));



		// number of adults at home
		if (isset($currentSFIData[FAMILY_SIZE_FIELD_ID]) && !empty($currentSFIData[FAMILY_SIZE_FIELD_ID]))
		{
			$Form->DefaultValues['number_of_adults'] = $currentSFIData[FAMILY_SIZE_FIELD_ID];
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'number_of_adults',
			CForm::dd_required => $ddRequired,
			CForm::dd_type => "dd_user_data",
			CForm::css_class => "custom-select",
			CForm::required_msg => "Please select number of adults.",
			CForm::options => array(
				'' => 'Number of adults',
				'none' => 'None',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7',
				'8' => '8',
				'9' => '9',
				'10' => '10',
				'11' => '11',
				'12' => '12',
				'13' => '13',
				'14' => '14',
				'15' => '15',
				'16' => '16',
				'17' => '17',
				'18' => '18',
				'19' => '19',
				'20' => '20 or more'
			)
		));


		*/

		// use lists
		if (isset($currentSFIData[USE_LISTS]) && !empty($currentSFIData[USE_LISTS]))
		{
			$Form->DefaultValues['use_lists'] = $currentSFIData[USE_LISTS];
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'use_lists',
			CForm::dd_type => "dd_user_data",
			CForm::css_class => "custom-select",
			CForm::dd_required => $ddRequired,
			CForm::required_msg => "Please select use of lists.",
			CForm::options => array(
				'' => 'use lists',
				'yes' => 'Yes',
				'no' => 'No'
			)
		));

		// weekly dine out
		if (isset($currentSFIData[NUMBER_NIGHTS_OUT]) && !empty($currentSFIData[NUMBER_NIGHTS_OUT]))
		{
			$Form->DefaultValues['number_weekly_dine_outs'] = $currentSFIData[NUMBER_NIGHTS_OUT];
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'number_weekly_dine_outs',
			CForm::dd_type => "dd_user_data",
			CForm::css_class => "custom-select",
			CForm::dd_required => $ddRequired,
			CForm::required_msg => "Please select weekly dine-outs.",
			CForm::options => array(
				'' => 'Weekly dine-outs',
				'none' => 'None',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7'
			)
		));

		// monthly dine out
		if (isset($currentSFIData[NUMBER_NIGHTS_OUT_PER_MONTH]) && !empty($currentSFIData[NUMBER_NIGHTS_OUT_PER_MONTH]))
		{
			$Form->DefaultValues['number_monthly_dine_outs'] = $currentSFIData[NUMBER_NIGHTS_OUT_PER_MONTH];
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'number_monthly_dine_outs',
			CForm::dd_type => "dd_user_data",
			CForm::css_class => "custom-select",
			CForm::dd_required => $ddRequired,
			CForm::required_msg => "Please select monthly dine-outs.",
			CForm::options => array(
				'' => 'Monthly dine-outs',
				'none' => 'None',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7',
				'8' => '8',
				'9' => '9',
				'10' => '10+'
			)
		));

		// prefer daytime sessions
		$daytime_checked = false;
		if (isset($currentSFIData[PREFER_DAYTIME_SESSIONS_FIELD_ID]) && !empty($currentSFIData[PREFER_DAYTIME_SESSIONS_FIELD_ID]))
		{
			$daytime_checked = true;
		}

		$Form->addElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'prefer_daytime_sessions',
			CForm::dd_type => "dd_user_data",
			CForm::label => 'Daytime',
			CForm::css_class => 'custom-control-input',
			CForm::label_css_class => 'custom-control-label',
			CForm::checked => $daytime_checked,
			CForm::attribute => array(
				'data-checkbox_group' => 'preferred_sessions',
				'data-checkbox_group_required' => (($ddRequired) ? 'true' : 'false')
			)
		));

		// prefer evening sessions
		$evening_checked = false;
		if (isset($currentSFIData[PREFER_EVENING_SESSIONS_FIELD_ID]) && !empty($currentSFIData[PREFER_EVENING_SESSIONS_FIELD_ID]))
		{
			$evening_checked = true;
		}

		$Form->addElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'prefer_evening_sessions',
			CForm::dd_type => "dd_user_data",
			CForm::label => 'Evening',
			CForm::css_class => 'custom-control-input',
			CForm::label_css_class => 'custom-control-label',
			CForm::checked => $evening_checked,
			CForm::attribute => array(
				'data-checkbox_group' => 'preferred_sessions',
				'data-checkbox_group_required' => (($ddRequired) ? 'true' : 'false')
			)
		));

		// prefer weekend sessions
		$weekend_checked = false;
		if (isset($currentSFIData[PREFER_WEEKEND_SESSIONS_FIELD_ID]) && !empty($currentSFIData[PREFER_WEEKEND_SESSIONS_FIELD_ID]))
		{
			$weekend_checked = true;
		}

		$Form->addElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'prefer_weekend_sessions',
			CForm::dd_type => "dd_user_data",
			CForm::label => 'Weekend',
			CForm::css_class => 'custom-control-input',
			CForm::label_css_class => 'custom-control-label',
			CForm::checked => $weekend_checked,
			CForm::attribute => array(
				'data-checkbox_group' => 'preferred_sessions',
				'data-checkbox_group_required' => (($ddRequired) ? 'true' : 'false')
			)
		));

		return $currentSFIData;
	}

	static function buildSFIFormElements(&$Form, $User, $fadminStore = false)
	{
		// The Sales Force Inititive Fields are known to be IDs 1 - through 6 so cheat and grab values based on id

		// current data
		$currentSFIData = array();
		$UData = DAO_CFactory::create('user_data');
		if ($fadminStore)
		{
			$UData->query("select ud.user_data_field_id, ud.user_data_value from user_data ud where user_id = " . $User->id . " and ud.user_data_field_id in (1,2,3,4,5,6,7,9,10,11,15) and store_id = $fadminStore and is_deleted = 0");
		}
		else
		{
			$UData->query("SELECT ud.user_data_field_id, ud.user_data_value FROM user_data ud WHERE user_id = " . $User->id . " AND ud.user_data_field_id IN (1,2,3,4,5,6,7,9,10,11,15) AND store_id IS NULL AND is_deleted = 0");
		}

		while ($UData->fetch())
		{
			$currentSFIData[$UData->user_data_field_id] = $UData->user_data_value;
		}

		// 1 - Birthday Month
		$monthOptions = array(
			"" => 'Select a Month:',
			'January' => 'January',
			'February' => 'February',
			'March' => 'March',
			'April' => 'April',
			'May' => 'May',
			'June' => 'June',
			'July' => 'July',
			'August' => 'August',
			'September' => 'September',
			'October' => 'October',
			'November' => 'November',
			'December' => 'December'
		);

		if (isset($currentSFIData[BIRTH_MONTH_FIELD_ID]) && !empty($currentSFIData[BIRTH_MONTH_FIELD_ID]))
		{
			$Form->DefaultValues['birthday_month'] = $currentSFIData[BIRTH_MONTH_FIELD_ID];
		}
		else
		{
			$Form->DefaultValues['birthday_month'] = "";
		}

		if (isset($currentSFIData[BIRTH_YEAR_FIELD_ID]) && !empty($currentSFIData[BIRTH_YEAR_FIELD_ID]))
		{
			$Form->DefaultValues['birthday_year'] = $currentSFIData[BIRTH_YEAR_FIELD_ID];
		}
		else
		{
			$Form->DefaultValues['birthday_year'] = "none";
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'birthday_month',
			CForm::options => $monthOptions
		));

		$yearOptions = array();

		$curYear = intval(date("Y"));

		for ($year = $curYear; $year >= $curYear - 100; $year--)
		{
			$yearOptions[$year] = $year;
			if ($year == $curYear - 30)
			{
				$yearOptions["none"] = 'Select a Year';
			}
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'birthday_year',
			CForm::options => $yearOptions
		));

		// 2 number of kids at home
		$kidOptions = array(
			'' => 'Select:',
			'none' => 'None',
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6 or more'
		);

		if (isset($currentSFIData[NUMBER_KIDS_FIELD_ID]) && !empty($currentSFIData[NUMBER_KIDS_FIELD_ID]))
		{
			$Form->DefaultValues['number_of_kids'] = $currentSFIData[NUMBER_KIDS_FIELD_ID];
		}
		else
		{
			$Form->DefaultValues['number_of_kids'] = "";
		}

		// create store popup to allow setting home store id
		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'number_of_kids',
			CForm::options => $kidOptions
		));

		// 3
		if (isset($currentSFIData[FAVORITE_MEAL_FIELD_ID]) && !empty($currentSFIData[FAVORITE_MEAL_FIELD_ID]))
		{
			$Form->DefaultValues['favorite_meals'] = $currentSFIData[FAVORITE_MEAL_FIELD_ID];
		}

		$mealOptions = array(
			"" => "None Selected",
			"Arroz Con Pollo" => "Arrozo Con Pollo",
			"Baked Almond Chicken" => "Baked Almond Chicken",
			"Canadian Bacon Stuff French Bread" => "Canadian Bacon Stuff French Bread",
			"Cheese Lover&apos;s Manicotti" => "Cheese Lover&apos;s Manicotti",
			"Chicken Enchiladas" => "Chicken Enchiladas",
			"Chicken Mirabella" => "Chicken Mirabella",
			"Chicken Paella" => "Chicken Paella",
			"Chicken Parmesean. with Garlic Bread" => "Chicken Parmesean. with Garlic Bread",
			"Chicken w Honey Garlic &amp; Orange" => "Chicken w Honey Garlic &amp; Orange",
			"Chicken w Sesame Honey Butter" => "Chicken w Sesame Honey Butter",
			"Cider Braised Pork Chop" => "Cider Braised Pork Chop",
			"Coconut Shrimp" => "Coconut Shrimp",
			"Creamy Chipolte Ravioli" => "Creamy Chipolte Ravioli",
			"Creamy Risotto with Chicken" => "Creamy Risotto with Chicken",
			"Hawaiian Chicken" => "Hawaiian Chicken",
			"Herb Crusted Flank Steak" => "Herb Crusted Flank Steak",
			"Herb Dijon Chicken" => "Herb Dijon Chicken",
			"Homestyle Chicken &amp; Dumplings" => "Homestyle Chicken &amp; Dumplings",
			"Italian Stuffed Pasta Shells" => "Italian Stuffed Pasta Shells",
			"Lemon Chicken Piccata" => "Lemon Chicken Piccata",
			"Orangey Asian Chicken" => "Orangey Asian Chicken",
			"Pesto Cheese Ravioli with Chicken &amp; Walnuts" => "Pesto Cheese Ravioli with Chicken &amp; Walnuts",
			"Seafood Cioppino" => "Seafood Cioppino",
			"Southwest Chicken" => "Southwest Chicken",
			"Sweet Cider BBQ Chicken" => "Sweet Cider BBQ Chicken"
		);

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::options => $mealOptions,
			CForm::name => 'favorite_meals'
		));

		// 4
		if (isset($currentSFIData[WHY_WORKS_FIELD_ID]) && !empty($currentSFIData[WHY_WORKS_FIELD_ID]))
		{
			$Form->DefaultValues['why_works'] = $currentSFIData[WHY_WORKS_FIELD_ID];
		}

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '4',
			CForm::cols => '40',
			CForm::name => 'why_works'
		));

		// 5
		if (isset($currentSFIData[GUEST_EMPLOYER_FIELD_ID]) && !empty($currentSFIData[GUEST_EMPLOYER_FIELD_ID]))
		{
			$Form->DefaultValues['guest_employer'] = $currentSFIData[GUEST_EMPLOYER_FIELD_ID];
		}

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '2',
			CForm::cols => '40',
			CForm::name => 'guest_employer'
		));

		// 6
		if (isset($currentSFIData[UPCOMING_EVENTS_FIELD_ID]) && !empty($currentSFIData[UPCOMING_EVENTS_FIELD_ID]))
		{
			$Form->DefaultValues['upcoming_events'] = $currentSFIData[UPCOMING_EVENTS_FIELD_ID];
		}

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '4',
			CForm::cols => '40',
			CForm::name => 'upcoming_events'
		));

		// 7
		if (isset($currentSFIData[MISC_NOTES_FIELD_ID]) && !empty($currentSFIData[MISC_NOTES_FIELD_ID]))
		{
			$Form->DefaultValues['misc_notes'] = $currentSFIData[MISC_NOTES_FIELD_ID];
		}

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '4',
			CForm::cols => '40',
			CForm::name => 'misc_notes'
		));

		// 9 is anniversary date: this is system generated

		// 2 number of kids at home
		$familySizeOptions = array(
			'' => 'Select:',
			'none' => 'None',
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'7' => '7',
			'8' => '8',
			'9' => '9',
			'10' => '10',
			'11' => '11',
			'12' => '12',
			'13' => '13',
			'14' => '14',
			'15' => '15',
			'16' => '16',
			'17' => '17',
			'18' => '18',
			'19' => '19',
			'20' => '20 or more'
		);

		if (isset($currentSFIData[FAMILY_SIZE_FIELD_ID]) && !empty($currentSFIData[FAMILY_SIZE_FIELD_ID]))
		{
			$Form->DefaultValues['family_size'] = $currentSFIData[FAMILY_SIZE_FIELD_ID];
		}
		else
		{
			$Form->DefaultValues['family_size'] = "";
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'family_size',
			CForm::options => $familySizeOptions
		));

		// 5
		if (isset($currentSFIData[SPOUSE_EMPLOYER_FIELD_ID]) && !empty($currentSFIData[SPOUSE_EMPLOYER_FIELD_ID]))
		{
			$Form->DefaultValues['spouse_employer'] = $currentSFIData[SPOUSE_EMPLOYER_FIELD_ID];
		}

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '2',
			CForm::cols => '40',
			CForm::name => 'spouse_employer'
		));

		return $currentSFIData;
	}

	static function getSFICustomerProvideData($User)
	{
		$retVal = array();
		$UData = DAO_CFactory::create('user_data');
		$UData->query("select user_data_value, user_data_field_id from user_data where user_data_field_id in (1,2,10,15) and is_deleted = 0 and store_id is null and user_id = {$User->id}");
		while ($UData->fetch())
		{
			switch ($UData->user_data_field_id)
			{
				case 1:
					$retVal['birthday_month'] = $UData->user_data_value;
					break;
				case 2:
					$retVal['how_many_under_18_at_home'] = $UData->user_data_value;
					break;
				case 10:
					$retVal['how_many_at_home'] = $UData->user_data_value;
					break;
				case 15:
					$retVal['birthday_year'] = $UData->user_data_value;
					break;
			}
		}

		return $retVal;
	}

	static function saveBirthdayForPlatePoint($month, $year, $User)
	{
		if (isset($month) && isset($year))
		{

			//			$fieldNames = array(
			//				1 => 'birthday_month',
			//				15 => 'birthday_year'
			//			);

			$UData = DAO_CFactory::create('user_data');
			$UData->user_id = $User->id;
			$UData->user_data_field_id = 1;
			$UData->store_id = 'null';
			if ($UData->find(true))
			{
				if (isset($formVal))
				{
					$UData->query("UPDATE user_data SET user_data_value = '" . htmlentities($month, ENT_QUOTES) . "' WHERE id = " . $UData->id);
				}
				else
				{
					$UData->delete();
				}
			}
			else
			{
				$UData->user_data_value = $month;
				$UData->insert();
			}

			$UData = DAO_CFactory::create('user_data');
			$UData->user_id = $User->id;
			$UData->user_data_field_id = 15;
			$UData->store_id = 'null';
			if ($UData->find(true))
			{
				if (isset($formVal))
				{
					$UData->query("UPDATE user_data SET user_data_value = '" . htmlentities($year, ENT_QUOTES) . "' WHERE id = " . $UData->id);
				}
				else
				{
					$UData->delete();
				}
			}
			else
			{
				$UData->user_data_value = $year;
				$UData->insert();
			}
		}
	}

	static function saveSFIFormElementsCustomerProvided($Form, $User)
	{
		if ($_POST && isset($_POST['submit_account']))
		{

			$fieldNames = array(
				1 => 'birthday_month',
				2 => 'how_many_under_18_at_home',
				10 => 'how_many_at_home',
				15 => 'birthday_year'
			);

			foreach ($fieldNames as $num => $name)
			{

				$formVal = $Form->value($name);
				$UData = DAO_CFactory::create('user_data');
				$UData->user_id = $User->id;
				$UData->user_data_field_id = $num;
				$UData->store_id = 'null';
				if ($UData->find(true))
				{
					if (isset($formVal))
					{
						$UData->query("UPDATE user_data SET user_data_value = '" . htmlentities($Form->value($name), ENT_QUOTES) . "' WHERE id = " . $UData->id);
					}
					else
					{
						$UData->delete();
					}
				}
				else
				{
					if (isset($formVal))
					{
						$UData->user_data_value = $Form->value($name);
						$UData->insert();
					}
				}
			}
		}
	}

	static function saveSFIFormElementsNew($Form, $User, $SFICurrentValues, $arrValues = false)
	{
		$fieldNames = array(
			1 => 'birthday_month',
			15 => 'birthday_year',
			17 => 'contribute_income',
			18 => 'use_lists',
			20 => 'number_weekly_dine_outs',
			21 => 'prefer_daytime_sessions',
			22 => 'prefer_evening_sessions',
			23 => 'prefer_weekend_sessions',
			24 => 'number_monthly_dine_outs',
			25 => 'number_feeding',
			26 => 'desired_homemade_meals_per_week'
		);

		foreach ($fieldNames as $num => $name)
		{

			if (!empty($arrValues))
			{
				$formVal = $arrValues['$name'];
			}
			else
			{
				$formVal = $Form->value($name);
			}

			if (!empty($formVal) || (isset($SFICurrentValues[$num])))
			{
				$UData = DAO_CFactory::create('user_data');
				$UData->user_id = $User->id;
				$UData->user_data_field_id = $num;
				if ($UData->find(true))
				{
					if (!empty($formVal))
					{

						//$org = clone($UData);
						//TODO : calling update generates large where clause that causes update to fail. Need to understand this happens
						// but just do simple query for now
						//$UData->user_data_value = $Form->value($name);
						//$UData->update($org);
						$UData->query("UPDATE user_data SET user_data_value = '" . htmlentities($formVal, ENT_QUOTES) . "' WHERE id = " . $UData->id);
					}
					else
					{
						$UData->delete();
					}
				}
				else
				{
					if (!empty($formVal))
					{
						$UData->user_data_value = $formVal;
						$UData->insert();
					}
				}

				if (CUser::getCurrentUser()->id == $User->id && $num == 25)
				{
					CUser::getCurrentUser()->number_feeding = $formVal;
				}
				else if (CUser::getCurrentUser()->id == $User->id && $num == 26)
				{
					CUser::getCurrentUser()->desired_homemade_meals_per_week = $formVal;
				}
			}
		}
	}

	static function saveSFIFormElements($Form, $User, $SFICurrentValues, $fadminStoreID)
	{
		if ($_POST && isset($_POST['submit_account']))
		{

			$fieldNames = array(
				1 => 'birthday_month',
				2 => 'number_of_kids',
				3 => 'favorite_meals',
				4 => 'why_works',
				5 => 'guest_employer',
				6 => 'upcoming_events',
				7 => 'misc_notes',
				10 => 'family_size',
				11 => 'spouse_employer',
				15 => 'birthday_year'
			);

			foreach ($fieldNames as $num => $name)
			{

				$formVal = $Form->value($name);
				if (!empty($formVal) || (isset($SFICurrentValues[$num])))
				{
					$UData = DAO_CFactory::create('user_data');
					$UData->user_id = $User->id;
					if ($fadminStoreID)
					{
						$UData->store_id = $fadminStoreID;
					}
					$UData->user_data_field_id = $num;
					if ($UData->find(true))
					{
						if (!empty($formVal))
						{

							//$org = clone($UData);
							//TODO : calling update generates large where clause that causes update to fail. Need to understand this happens
							// but jsut do simple query for now
							//$UData->user_data_value = $Form->value($name);
							//$UData->update($org);
							$UData->query("UPDATE user_data SET user_data_value = '" . htmlentities($Form->value($name), ENT_QUOTES) . "' WHERE id = " . $UData->id);
						}
						else
						{
							$UData->delete();
						}
					}
					else
					{
						if (!empty($formVal))
						{
							$UData->user_data_value = $Form->value($name);
							$UData->insert();
						}
					}
				}
			}
		}
	}

	static function getSFIDataForDisplay($user_id)
	{
		// The Sales Force Inititive Fields are known to be IDs 1 - through 6 so cheat and grab values based on id

		// current data
		$currentSFIData = array();
		$UData = DAO_CFactory::create('user_data');

		$UData->query("select ud.user_data_field_id, ud.user_data_value, ud.store_id, s.store_name from user_data ud
			left join store s on ud.store_id = s.id
			where ud.user_id = $user_id and ud.user_data_field_id in (1,2,3,4,5,6,7,9,10,11,15,17,18,19,20,21,22,23,24,25,26) and ud.is_deleted = 0");

		while ($UData->fetch())
		{
			$currentSFIData[(isset($UData->store_name) ? $UData->store_name : 'admin')][$UData->user_data_field_id] = $UData->user_data_value;
		}

		return $currentSFIData;
	}

	static function getSFICurrentValues($user_id)
	{

		$currentSFIData = array();
		$UData = DAO_CFactory::create('user_data');

		$UData->query("select ud.user_data_field_id, ud.user_data_value, ud.store_id, s.store_name from user_data ud
	        where ud.user_id = $user_id and ud.user_data_field_id in (1,2,3,4,5,6,7,9,10,11,15,17,18,19,20,21,22,23,24,25,26) and ud.is_deleted = 0 order by id asc");

		while ($UData->fetch())
		{
			$currentSFIData[$UData->user_data_field_id] = $UData->user_data_value;
		}

		return $currentSFIData;
	}

	static function getSFIDataForDisplayNew($user_id, $data_id_array = null, $Store = false)
	{
		// The Sales Force Inititive Fields are known to be IDs 1 - through 6 so cheat and grab values based on id
		if (!$data_id_array)
		{
			$data = '1,2,10,15,17,18,19,20,21,22,23,24,25,26';
		}
		else
		{
			$data = implode(',', $data_id_array);
		}

		$storeSpecific_query = "";
		if ($Store)
		{
			$store_id = false;

			if (is_object($Store))
			{
				$store_id = $Store->id;
			}
			else if (is_numeric($Store))
			{
				$store_id = $Store;
			}

			$storeSpecific_query = " AND (ud.store_id = " . $store_id . " OR ud.store_id IS NULL OR ud.store_id = '') ";
		}

		// current data
		$currentSFIData = array();
		$UData = DAO_CFactory::create('user_data');

		$UData->query("SELECT ud.user_data_field_id, ud.user_data_value, ud.store_id, s.store_name FROM user_data ud
				LEFT JOIN store s ON ud.store_id = s.id
				WHERE ud.user_id = '" . $user_id . "'
				" . $storeSpecific_query . "
				AND ud.user_data_field_id IN (" . $data . ") 
				AND ud.is_deleted = 0 ORDER BY ud.id");

		while ($UData->fetch())
		{
			if ($UData->user_data_field_id == 1 && is_numeric($UData->user_data_value))
			{
				$monthArray = self::monthArray();
				if ($UData->user_data_value != 0)
				{
					$UData->user_data_value = $monthArray[$UData->user_data_value];
				}
				else
				{
					$UData->user_data_value = "";
				}
			}

			$currentSFIData[$UData->user_data_field_id] = $UData->user_data_value;
		}

		return $currentSFIData;
	}

	static function setUserAsAAAReferred($User, $Data)
	{
		$UData = DAO_CFactory::create('user_data');
		$UData->user_id = $User->id;
		$UData->user_data_field_id = AAA_REFERRED_FIELD_ID;
		if (!$UData->find(true))
		{
			$UData->user_data_value = $Data;
			$UData->insert();
		}
	}

	static function isUserAAAReferred($User)
	{

		if ($User)
		{
			$UData = DAO_CFactory::create('user_data');
			$UData->user_id = $User->id;
			$UData->user_data_field_id = AAA_REFERRED_FIELD_ID;
			if ($UData->find(true))
			{
				return true;
			}
		}

		if (CBrowserSession::getValue('AAA_landing') == "1")
		{
			return true;
		}

		return false;
	}

	static function setReferralSourceNotes($User, $Data)
	{
		$UData = DAO_CFactory::create('user_data');
		$UData->user_id = $User->id;
		$UData->user_data_field_id = REFERRAL_SOURCE_NOTES;
		if (!$UData->find(true))
		{
			$UData->user_data_value = $Data;
			$UData->insert();
		}
		else
		{
			$UData->user_data_value = $Data;
			$UData->update();
		}
	}

	static function getReferralSourceNotes($User)
	{
		if ($User)
		{
			$UData = DAO_CFactory::create('user_data');
			$UData->user_id = $User->id;
			$UData->user_data_field_id = REFERRAL_SOURCE_NOTES;
			if ($UData->find(true))
			{
				return $UData->user_data_value;
			}
		}

		return false;
	}

	static function setTVOfferReferralSource($User, $Data)
	{
		$UData = DAO_CFactory::create('user_data');
		$UData->user_id = $User->id;
		$UData->user_data_field_id = TV_OFFER_REFERRAL_SOURCE;
		if (!$UData->find(true))
		{
			$UData->user_data_value = $Data;
			$UData->insert();
		}
		else
		{
			$UData->user_data_value = $Data;
			$UData->update();
		}
	}

	static function getTVOfferReferralSource($User)
	{
		if ($User)
		{
			$UData = DAO_CFactory::create('user_data');
			$UData->user_id = $User->id;
			$UData->user_data_field_id = TV_OFFER_REFERRAL_SOURCE;
			if ($UData->find(true))
			{
				return $UData->user_data_value;
			}
		}

		return false;
	}

	static function setAsExportedForRedBook($user_id, $Data)
	{
		$UData = DAO_CFactory::create('user_data');
		$UData->user_id = $user_id;
		$UData->user_data_field_id = EXPORTED_FOR_REDBOOK;
		if (!$UData->find(true))
		{
			$UData->user_data_value = $Data;
			$UData->insert();
		}
	}

	static function wasExportedForRedBook($user_id)
	{

		if ($user_id)
		{
			$UData = DAO_CFactory::create('user_data');
			$UData->user_id = $user_id;
			$UData->user_data_field_id = EXPORTED_FOR_REDBOOK;
			if ($UData->find(true))
			{
				return true;
			}
		}

		return false;
	}
}

?>