<?php

require_once ('PasswordLib.phar');


class CPasswordPolicy {

	const LOCKOUT_DURATION = 30; //minutes
	const FAILED_ATTEMPT_LIMIT = 6;
	const FAILED_ATTEMPT_TIME_SPAN = 15; //minutes

	static function getRemainingPasswordLife($userObj)
	{
		if (isset($userObj->last_password_update))
		{
			$lastupdateTS = strtotime($userObj->last_password_update);
		}
		else
		{
			//retrieve user_login
			$userLoginObj = DAO_CFactory::create('user_login');
			$userLoginObj->query("select last_password_update from user_login where user_id = {$userObj->id} and is_deleted = 0");
			if ($userLoginObj->fetch())
			{
				if (empty($userLoginObj->last_password_update))
				{
					return 0;
				}

				$lastupdateTS = strtotime($userLoginObj->last_password_update);
			}
			else
			{
				throw new Exception("user login not found in getRemainingPasswordLife()");
			}
		}

		$delta = floor((time() - $lastupdateTS) / 86400);

		return $delta;
		// the number of whole days that have elapsed since the last update

	}

	static function passwordPassesUniquenessRules($user_id, $password)
	{

		$lib = new PasswordLib\PasswordLib();

		$historyTester = DAO_CFactory::create('password_history');
		$historyTester->query("select * from password_history where user_id = $user_id order by id desc limit 4");

		while($historyTester->fetch())
		{
			if ($lib->verifyPasswordHash($password, $historyTester->password))
			{
				return false;
			}

		}

		return true;

	}

	static function recordPasswordUpdate($user_id, $hash)
	{
		$historySaver = DAO_CFactory::create('password_history');
		$historySaver->user_id = $user_id;
		$historySaver->password = $hash;
		$historySaver->insert();

		$dateTime = date("Y-m-d H:i:s");

		$userLoginObj = DAO_CFactory::create('user_login');
		$userLoginObj->query("update user_login set last_password_update = '$dateTime' where user_id = $user_id and is_deleted = 0");
	}


	static function handlePasswordFailure($loginName)
	{

		//$loginName = CGPC::do_clean($loginName, TYPE_STR);
		// Cleaning is done in CUser::Authenticate

		$ipAddress = $_SERVER['REMOTE_ADDR'];

		$NewpwFail = DAO_CFactory::create('user_login_failures');
		$NewpwFail->user_name = $loginName;
		$NewpwFail->time_of_failure = date("Y-m-d H:i:s");
		$NewpwFail->ip_address = $ipAddress;
		$NewpwFail->insert();

		$pwFailHistory = DAO_CFactory::create('user_login_failures');
		$pwFailHistory->query("select id from user_login_failures where '$loginName' = user_name and TIMESTAMPDIFF(MINUTE, time_of_failure, NOW()) < " . self::FAILED_ATTEMPT_TIME_SPAN);

		if ($pwFailHistory->N >= self::FAILED_ATTEMPT_LIMIT)
		{
			// must lock out

			$NewpwFail = DAO_CFactory::create('user_login_lockout');
			$NewpwFail->user_name = $loginName;
			$NewpwFail->time_of_lockout = date("Y-m-d H:i:s");
			$NewpwFail->ip_address = $ipAddress;
			$NewpwFail->insert();

		}

/*
		// do not lock out by IP instead a cron job runs every 15 minutes and looks for more than 12 attempts from the same IP. If this occurs an alert is sent to the Dev team
*/

	}

	static function clearLockOut($user_name)
	{
		//	$user_name = CGPC::do_clean($user_name, TYPE_STR);
		//  Cleaning is done CUser::Authenticate

		$ulLockTest = DAO_CFactory::create('user_login_lockout');
		$ulLockTest->query("delete from user_login_lockout where
				'$user_name' = user_name");

	}



	static function isLockedOut($user_name)
	{
 // Note: Removed test for IP since this locks out the entire store or office
 // instead, on password failure look for a large number of failures from the same IP and alert Dev staff. CES 9-14-2015

	//	$user_name = CGPC::do_clean($user_name, TYPE_STR);
		//  Cleaning is done CUser::Authenticate

		$ulLockTest = DAO_CFactory::create('user_login_lockout');
		$ulLockTest->query("select id from user_login_lockout where
				'$user_name' = user_name and TIMESTAMPDIFF(MINUTE, time_of_lockout, NOW()) < " . self::LOCKOUT_DURATION);

		if ($ulLockTest->N > 0)
			return true;


		return false;

	}

	static function verifyPassword($password, $hash)
	{
		$lib = new PasswordLib\PasswordLib();
		return $lib->verifyPasswordHash($password, $hash);
	}


	static function upgradePassword($password, $user_id)
	{

		$lib = new PasswordLib\PasswordLib();
		$hash = $lib->createPasswordHash($password);
		$loginObj = DAO_CFactory::create('user_login');
		$loginObj->query("update user_login set ul_password2 = '$hash', uses_bcrypt = 1, ul_password = null where user_id = $user_id and is_deleted = 0");

		CPasswordPolicy::recordPasswordUpdate($user_id, $hash);

	}

	/*
	 * Used when inserting a new guest
	 */
	static function getHash($password, $user_id, $recordUpdate = false)
	{

		$lib = new PasswordLib\PasswordLib();
		$hash = $lib->createPasswordHash($password);

		if ($recordUpdate)
			self::recordPasswordUpdate($user_id, $hash);

		return $hash;
	}


}




?>