<?php
require_once("Config.inc");

require_once 'DAO/BusinessObject/COrders.php';
require_once 'DAO/BusinessObject/CBox.php';
require_once 'DAO/BusinessObject/CBoxInstance.php';
require_once 'DAO/BusinessObject/CBundle.php';

/**
 * Wrapper for the result of a method call. Contains
 * payload to be returned and acted upon (can be anything),
 * plus meta-data that describe any issue or information about the method
 * invocation.
 *
 * Allows for batching errors into a single report that can then be communicated
 * at the correct level.
 *
 */
class CResultToken
{
	const MSG_PREFIX = '   - ';
	private $successMessages =  [];
	private $failureMessages =  [];
	private $payload =  null;

	public function __construct()
	{
		$this->successMessages =  array();
		$this->failureMessages =  array();

	}

	/**
	 * @return array of string -success message
	 */
	public function getSuccessMessages(){
		return $this->successMessages;
	}

	/**
	 * @return array of strings - failure message
	 */
	public function getFailureMessages(){
		return $this->failureMessages;
	}

	/**
	 * This will set the value of isFailure() to true
	 * @param string $message
	 * @param  boolean default is false, send email notification on failure
	 */
	public function addFailureMessage($message, $notify = false){
		$this->failureMessages[] = array('message'=>$message);
		CLog::RecordNew(CLog::ERROR, $message, '', '', $notify);
	}

	/**
	 * @param string $message
	 */
	public function addSuccessMessage($message){
		$this->successMessages[] = array('message'=>$message);
	}

	/**
	 * @return bool true if there are any error messages set
	 */
	public function isFailure(){
		if( count($this->failureMessages) > 0){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @param mixed $obj data object to return to the caller
	 */
	public function setPayload($obj){
		$this->payload = $obj;
	}

	/**
	 *
	 * @return mixed any object that a called method wishes to return
	 */
	public function getPayload(){
		return $this->payload;
	}

	/**
	 * Instead of logging and notifing individually, batch up error messages and send in on
	 * log message.
	 *
	 * Will only work if there are failure messages
	 */
	public function batchLogAndNotifyFailureMessage($errorHandlerCallback = null, $errorHandleArgs = array()){
		if($this->isFailure()){
			$messages = '';
			foreach ($this->failureMessages as $message){
				$messages .= $message . ',';
			}
			CLog::RecordNew(CLog::ERROR, $messages, '', '', true);

			if(!is_null($errorHandlerCallback)){
				$errorHandlerCallback($errorHandleArgs, $this);
			}
		}
	}

	/**
	 * Combine another CResultToken into this token. This add messages (error and success)
	 * to this instances collections. It also changes the isFailure state to false if the passed
	 * token contians any errors.
	 *
	 * Does not currently collect multiple payloads but could be extended to do so. Only contains
	 * the payload set on the original token
	 */
	public function addResult($CResultToken){
		$newErrors = array_merge($this->failureMessages,$CResultToken->getFailureMessages());
		$newSuccess = array_merge($this->successMessages,$CResultToken->getSuccessMessages());
		$this->failureMessages = $newErrors;
		$this->successMessages = $newSuccess;

	}

	/**
	 * @return string All messages merged to one string
	 */
	public function allMessagesToString(){

		$result =   $this->successMessagesToString();
		$result .=   $this->failureMessagesToString();

		return $result;
	}

	/**
	 * Utility method to return all Success messages as string
	 */
	public function successMessagesToString(){
		$result = count($this->successMessages) . ' total success messages.' . PHP_EOL;
		foreach ( $this->successMessages as $msg)
		{
			$result .= self::MSG_PREFIX .  $msg['message'] . PHP_EOL;
		}
	}

	/**
	 * Utility method to return all Failure messages as string
	 */
	public function failureMessagesToString(){
		$result = count($this->failureMessages) . ' total error messages.' . PHP_EOL;
		foreach ( $this->failureMessages as $msg)
		{
			$result .=  self::MSG_PREFIX .  $msg['message'] . PHP_EOL;
		}

		return $result;
	}

	/**
	 *
	 * Utility method to echo out all messages to standard out
	 *
	 */
	public function echoAllMessages(){
		$this->echoSuccessMessages();
		$this->echoFailureMessages();
	}

	/**
	 * Utility method to echo out Success messages to standard out
	 */
	public function echoSuccessMessages(){
		echo count($this->successMessages) . ' total success messages.'.PHP_EOL;
		foreach ( $this->successMessages as $msg)
		{
			echo self::MSG_PREFIX . $msg['message'] . PHP_EOL;
		}
	}

	/**
	 * Utility method to echo out Failure messages to standard out
	 */
	public function echoFailureMessages(){
		echo count($this->failureMessages) . ' total error messages.'.PHP_EOL;
		foreach ( $this->failureMessages as $msg)
		{
			echo self::MSG_PREFIX . $msg['message'] . PHP_EOL;
		}
	}
}

?>