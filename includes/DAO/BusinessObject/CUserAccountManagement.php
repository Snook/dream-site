<?php

require_once 'DAO/User_account_management.php';

class CUserAccountManagement extends DAO_User_account_management {

	const ACTION_DELETE_ACCOUNT = 'DELETE_ACCOUNT';
	const ACTION_SEND_ACCOUNT_INFORMATION = 'SEND_ACCOUNT_INFORMATION';


	const STATUS_REQUESTED = 'REQUESTED';
	const STATUS_PROCESSING = 'PROCESSING';
	const STATUS_COMPLETED = 'COMPLETED';
	const STATUS_FAILED = 'FAILED';

	static $VALID_TASK_TYPES = ['DELETE_ACCOUNT', 'SEND_ACCOUNT_INFORMATION'];
	static $VALID_STATUS = ['REQUESTED', 'PROCESSING','COMPLETED','FAILED' ];

	/**
	 * @param $user_id check to see if any account action have been created
	 *                       for this user
	 *
	 * @return bool true if any task exist for this user
	 * @throws Exception
	 */
	public static function hasTask($user_id)
	{
		if( is_numeric($user_id) ){
			$dao = DAO_CFactory::create('user_account_management');
			$dao->user_id = $user_id;
			if ($dao->find(true))
			{
				return $dao->N > 0;
			}
		}
		return false;
	}

	/**
	 * @param number $user_id user that requested this action
	 * @param string $taskType a valid CUserAccountManagement->VALID_TASK_TYPES
	 * @param string $taskStatus optional  a valid CUserAccountManagement->VALID_STATUS,
	 *                           will be REQUESTED if not specified
	 *
	 * @return mixed id of task record if record is created, else false
	 * @throws Exception
	 */
	public static function createTask( $user_id, $taskType, $taskStatus = CUserAccountManagement::STATUS_REQUESTED, $data = null)
	{
		if(  is_numeric($user_id)  && in_array($taskType, CUserAccountManagement::$VALID_TASK_TYPES, true) ){
			$dao = DAO_CFactory::create('user_account_management');
			$dao->user_id = $user_id;
			$dao->find(true);
			$dao->action = $taskType;
			$dao->status = $taskStatus;
			$dao->data = $data;

			if($dao->N == 0){
				return $dao->insert();
			}else{
				$dao->update();
				return $dao->id;
			}
		}
		return false;
	}

	/**
	 * @param $task_id
	 * @param $taskType
	 * @param $taskStatus
	 * @param $data optional
	 *
	 * @return bool true if valid numeric id was passed
	 * @throws Exception
	 */
	public static function updateTask( $task_id, $taskType, $taskStatus, $data = null )
	{
		if(  is_numeric($task_id) && in_array($taskType, CUserAccountManagement::$VALID_TASK_TYPES, true) ){
			$dao = DAO_CFactory::create('user_account_management');
			$dao->id = $task_id;
			$dao->find(true);
			$dao->action = $taskType;
			$dao->status = $taskStatus;
			$dao->data = $data;

			if($dao->N == 0){
				return true;
			}else{
				$dao->update();
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $user_id user that requested the actioin
	 * @param $taskType
	 *
	 * @return mixed|null returns the matching task or else return null
	 * @throws Exception
	 */
	public static function fetchTask( $user_id, $taskType )
	{
		if(  is_numeric($user_id) && in_array($taskType, CUserAccountManagement::$VALID_TASK_TYPES, true) ){
			$dao = DAO_CFactory::create('user_account_management');
			$dao->user_id = $user_id;
			$dao->action = $taskType;
			$dao->find(true);

			return $dao;

		}
		return null;
	}

}
