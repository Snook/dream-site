<?php

/**
 *IF ENV ==
 * DEV - WILL TEST CONNECTION, WILL NOT TRANSFER, WILL NOT SEND EMAIL
 * TEST - WILL TEST CONNECTION, WILL NOT TRANSFER, WILL SEND EMAIL
 * LIVE - WILL TRANSFER, WILL SEND EMAIL
 */
class VoxieDataRouter
{
	private static $instance = null;
	private $env = null;

	private $temp_file_path = null;

	private $ftp_server = null;
	private $ftp_user_name = null;
	private $ftp_user_pass = null;
	private $ftp_private_key = null;


	private function __construct($env)
	{
		$this->env = $env;

		if ($env == 'DEV')
		{
			require_once(defined(DEV_PATH_TO_CONFIG_FILE)?DEV_PATH_TO_CONFIG_FILE:"C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
			$this->temp_file_path = defined(DEV_OUPUT_DIRECTORY)?DEV_OUPUT_DIRECTORY:"C:\\Development\\output\\voxie\\";
		}
		elseif ($env == 'TEST')
		{
			require_once("/DreamReports/includes/Config.inc");
			$this->temp_file_path = "/DreamReports/voxie/tmp/";
		}
		else
		{
			error_reporting(E_ERROR );
			require_once("/DreamReports/includes/Config.inc");
			$this->temp_file_path = "/DreamReports/voxie/tmp/";
		}

		require_once("DAO/BusinessObject/CUser.php");
		require_once("DAO/CFactory.php");
		require_once("CLog.inc");

		$this->ftp_server = VOXIE_FTP_SERVER;
		$this->ftp_user_name = VOXIE_USER;
		$this->ftp_user_pass = VOXIE_PASSWORD;
		$this->ftp_private_key = VOXIE_PRIVATE_KEY;
	}

	public static function getInstance($env = 'DEV')
	{
		if (self::$instance == null)
		{
			self::$instance = new VoxieDataRouter($env);
		}

		return self::$instance;
	}

	/**
	 * @param string $timeBack e.g -10 days, -1 month, -5 months. Default is -1 months
	 * @param boolean $echoProgress shows progress in console, default is false
	 * @param string $method how to send to Voxie [client, command, none]: 1) via library SFTP == 'client', 2) via command line sftp == 'command'. The method 'client' is the default
	 *               value. If set as 'none', then will not try to connect to Voxie
	 *
	 * @throws Exception
	 */
	public function sendUserSessionDataToVoxie($timeBack = '-1 months',$echoProgress = false, $method = 'client')
	{
		$resultToken = new ResultToken($echoProgress);
		$resultToken->addProcessingMessages('Configured for the '.$this->env.' environment with data going back '.$timeBack.'.');

		$file_name = 'voxie_report_'.date("Y_m_d-H_i_s").'.csv';
		$full_file_path = '<not set>';
		$this->buildUserSessionReport($timeBack, $file_name,$resultToken);
		if(!$resultToken->isFailure())
		{
			$full_file_path = $resultToken->getPayload();
			
			$this->sendToVoxieVia($method,$full_file_path,$resultToken);

			if(!$resultToken->isFailure())
			{
				$this->cleanupVoxieReport($full_file_path,$resultToken);
			}
		}

		$this->emailRunResults($full_file_path,$resultToken);

		return $resultToken;
	}

	/**
	 * will retry 4 times to connect and transfer if method is client, command does not retry
	 *
	 * @param $method [client,command,none]
	 * @param $resultToken with payload containing full path to file
	 */
	public function resendOnlyToVoxie($method, &$resultToken)
	{
		$this->sendToVoxieVia($method, $resultToken->getPayload(), $resultToken);
	}

	private function sendToVoxieVia($method, $temp_file_path, $resultToken)
	{
		if($method == 'none')
		{
			$resultToken->addProcessingMessages('Not attempting to connect to Voxie.');
		}
		elseif($method == 'client')
		{
			$this->sendToVoxieSFTP_withPK($temp_file_path,$resultToken);
			$attemptCount = 0;
			while ($attemptCount++ < 5 && $resultToken->shouldRetry())
			{
				$resultToken->addProcessingMessages('Resend attempt '.$attemptCount.'.');
				$this->sendToVoxieSFTP_withPK($temp_file_path,$resultToken);
				sleep(60);
			}
			$resultToken->setShouldRetry(false);
		}
		else
		{
			$this->sendToVoxieSFTP_withPK_commandLine($temp_file_path,$resultToken);
		}
	}

	//-------HELPERS--------------------------------
	/**
	 * @throws Exception
	 */
	private function buildUserSessionReport($timeBack, $file_name, &$resultToken)
	{
		$resultToken->addProcessingMessages('Building User-Session report file ('.$file_name.').');

		$full_file_path = $this->temp_file_path . $file_name;

		$dest_fp = fopen($full_file_path, 'w');


		if($dest_fp != false)
		{
			$labels = array("Store ID", "User ID", "First Name", "Last Name", "Telephone", "Last Session Date",
							"Last Session Time", "Last Session Type", "Next Session Date","Next Session Time",
							"Next Session Type","Next Session Status","User Share URL");

			fputcsv($dest_fp, $labels, ',');

			$howFarBack = date('Y-m-d', strtotime($timeBack)) .' 00:00:00';

			$userObj = DAO_CFactory::create('user');
			$userObj->query("select distinct u.home_store_id, u.id, u.firstname, u.lastname, 
                				IF(u.telephone_1_type = 'MOBILE', u.telephone_1, u.telephone_2) as mobile,
                				CONCAT('https://www.dreamdinners.com/share/',u.id) as share
							from user u
							inner join store s on s.id = u.home_store_id and s.active = 1 and s.is_deleted = 0
							inner join orders o on o.user_id = u.id and o.is_deleted = 0 and o.timestamp_created > '$howFarBack'
							where u.is_deleted = 0 and (u.telephone_1_type = 'MOBILE' or u.telephone_2_type = 'MOBILE')
							order by u.id");
			$lineCount = 0;
			$resultToken->addProcessingMessages($userObj->N.' potential users found.');
			while ($userObj->fetch())
			{
				//echo memory_get_usage().PHP_EOL;;

				$orderObj = DAO_CFactory::create('orders');
				$orderObj->query("select s.id as session_id, s.store_id, s.menu_id as session_menu_id, s.session_start, s.session_type, 
       								s.session_type_subtype, s.session_password, b.status
								from orders o
								left join booking b on b.order_id = o.id and b.is_deleted = 0 and b.status IN ('ACTIVE','CANCELLED') 
								left join session s on s.id = b.session_id and s.is_deleted = 0 and s.session_publish_state in ('PUBLISHED','CLOSED')
								where o.user_id = {$userObj->id} and o.timestamp_created > '$howFarBack' and o.is_deleted = 0
								order by s.session_start desc");

				$pastSessions = array();
				$futureSessions = array();
				while ($orderObj->fetch())//sort the sessions for this user into future and past
				{
					$sessionStart = new DateTime($orderObj->session_start);
					if( $sessionStart->diff(new DateTime)->format('%R') == '+')
					{
						$pastSessions[] = $orderObj->toArray();
					}
					else
					{
						$futureSessions[] = $orderObj->toArray();
					}
				}

				$hasSessionData = false;

				$previousSession = null;
				if(count($pastSessions) > 0)
				{
					$previousSession = $pastSessions[0];
					$hasSessionData = $this->createSessionData($previousSession);
				}

				$nextSession = null;
				if(count($futureSessions) > 0)
				{
					$nextSession = $futureSessions[count($futureSessions)-1];
					if(count($futureSessions) > 1 && $nextSession['status'] == 'CANCELLED')
					{
						$nextSession = $futureSessions[count($futureSessions)-2];
					}
					$hasSessionData = $this->createSessionData($nextSession);
				}

				if($hasSessionData)
				{
					$cleanPhone = $this->cleanTelephone($userObj->mobile);
					if( $cleanPhone != false )
					{
						$lineArr = array($this->csvClean($userObj->home_store_id),
										 $this->csvClean($userObj->id),
										 $this->csvClean($userObj->firstname),
										 $this->csvClean($userObj->lastname),
										 $this->csvClean($cleanPhone),
										 $this->csvClean($previousSession['startDate']),
										 $this->csvClean($previousSession['startTime']),
										 $this->csvClean($this->mapSessionType($previousSession['sessionInfo'])),
										 $this->csvClean($nextSession['startDate']),
										 $this->csvClean($nextSession['startTime']),
										 $this->csvClean($this->mapSessionType($nextSession['sessionInfo'])),
										 $this->csvClean($nextSession['status']),
										 $this->csvClean($userObj->share)) ;

						fputcsv($dest_fp, $lineArr, ',');
						$resultToken->showProgress(++$lineCount, $userObj->N);
					}
				}
			}
			$resultToken->addProcessingMessages('Sending '.$lineCount.' users with recent sessions.');
			fclose($dest_fp);
			chmod($full_file_path,0644);
		}
		else
		{
			$resultToken->addFailureMessages("Temporary output file could not be opened: " . $full_file_path);

		}
		$resultToken->addProcessingMessages('Built User-Session report file.');

		$resultToken->setPayload($full_file_path);
	}

	private function sendToVoxieSFTP_withPK($filePointer, &$resultToken)
	{
		$fileName = basename($filePointer);
		$resultToken->addProcessingMessages( 'Connecting to Voxie via SFTP library.');

		set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib1.0.20');

		include('Net/SFTP.php');
		include('Crypt/RSA.php');

		define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

		//$sftp = new SFTP($this->ftp_server);
		$sftp = new Net_SFTP($this->ftp_server,22,700);

		// create new RSA key
		$privateKey = new Crypt_RSA();

		// in case that key has a password
		//$privateKey->setPassword('private key password');
		//echo $this->ftp_private_key;

		// load the private key
		$privateKey->loadKey($this->ftp_private_key);

		// login via sftp
		if (!$sftp->login($this->ftp_user_name, $privateKey)) {
			$resultToken->addFailureMessages('Could not connect to the Voxie sftp server with private key: are the values set in the config file? '
				. '[Server ' . (empty($this->ftp_server)? 'is empty':': '.$this->ftp_server)
				. ', User ' . (empty($this->ftp_user_name)? 'is empty':': '.$this->ftp_user_name) .']');
			$resultToken->setShouldRetry(true);
		}
		else
		{
			$sftp->chdir('samples');
			$resultToken->addProcessingMessages('Directory on the server is: '.$sftp->pwd());

			if($this->env == 'LIVE')
			{
				$resultToken->addProcessingMessages( 'Sending file ('.$filePointer.') to Voxie via SFTP library.');
				$sftp->put($fileName , $filePointer, NET_SFTP_LOCAL_FILE);
				$resultToken->addProcessingMessages( 'File sent via sftp library.');
			}
			else
			{
				$resultToken->addProcessingMessages('Did connect, but not the LIVE server so did not transfer the file.');
			}

			$resultToken->setShouldRetry(false);
			$sftp->disconnect();
		}
	}

	private function sendToVoxieSFTP_withPK_commandLine($filePointer, &$resultToken)
	{
		$command = 'scp -i '.VOXIE_PRIVATE_KEY_PATH.' -P 22 '.$filePointer.' dreamdinners@sftp01.voxie.com:/samples/'.$resultToken->getPayload();

		if($this->env == 'LIVE')
		{
			$resultToken->addProcessingMessages( 'Sending file ('.$filePointer.') to Voxie via SFTP command line('.$command.').');
			exec($command);
			$resultToken->addProcessingMessages( 'File sent via command line.');
		}
		else
		{
			$resultToken->addProcessingMessages($command);
		}

	}

	private function cleanupVoxieReport($temp_file, &$resultToken)
	{
		//$resultToken->addProcessingMessages( 'Cleaning Voxie temp file ('.$temp_file.').');
		//unlink($temp_file);
		//$resultToken->addProcessingMessages('Cleaned temp file.');

		$resultToken->addProcessingMessages('Did not remove temp file ('.$temp_file.'). Uncomment code if this should happen.');
	}

	private function emailRunResults($fullFilePath,&$resultToken)
	{
		$to = "evan.lee@dreamdinners.com";
		$fileName = basename($fullFilePath);
		if ($this->env == 'LIVE')
		{
			$to = "evan.lee@dreamdinners.com";
		}

		if ($this->env != 'DEV')
		{
			if($resultToken->isFailure())
			{
				$this->mail( $to,
					"Nightly Voxie Data Send Report - FAILURE",
					$resultToken->getFailureMessagesString('<br>'), $fileName, $fullFilePath );
			}
			else
			{
				$this->mail( $to,
					"Nightly Voxie Data Send Report - OK",
					$resultToken->getProcessingMessagesHTML(), $fileName, $fullFilePath );
			}

		}
		else
		{
			$resultToken->addProcessingMessages('Not sending email from the DEV environment.');
		}

	}

	public function testVoxieSFTP_Connection_withPK(&$resultToken)
	{
		$resultToken->addProcessingMessages('Testing using the SFTP library to send to Voxie.');
		set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib1.0.20');

		include('Net/SFTP.php');
		include('Crypt/RSA.php');

		define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

		//$sftp = new SFTP($this->ftp_server);
		$sftp = new Net_SFTP($this->ftp_server);

		// create new RSA key
		$privateKey = new Crypt_RSA();

		// in case that key has a password
		//$privateKey->setPassword('private key password');
		//echo $this->ftp_private_key;

		// load the private key
		$privateKey->loadKey($this->ftp_private_key);

		// login via sftp
		if (!$sftp->login($this->ftp_user_name, $privateKey)) {
			$resultToken->addFailureMessages('Could not connect to the Voxie sftp server with private key: are the values set in the config file? '
				. '[Server ' . (empty($this->ftp_server)? 'is empty':': '.$this->ftp_server)
				. ', User ' . (empty($this->ftp_user_name)? 'is empty':': '.$this->ftp_user_name) .']');
		}
		else
		{
			$sftp->chdir('samples');
			$resultToken->addProcessingMessages('Directory on the server is: '.$sftp->pwd());
			$resultToken->addProcessingMessages('Connection test is good!');
			$sftp->disconnect();
		}
	}

	private function mail($to, $subject, $msg, $fileName = null, $fullFilePath = null)
	{
		$Mail = new CMail();
		$Mail->from_name = 'Voxie Data Sync';
		$Mail->to_id = false;
		$Mail->to_email = $to;



		$Mail->subject = $subject;
		$Mail->body_html = "<html><head></head><body>".$msg."</body></html>";
		$Mail->body_text = $msg;
		$Mail->template_name = 'none';

		if(!is_null($fileName) && !is_null($fullFilePath) && defined(VOXIE_SEND_ATTACHMENT) && VOXIE_SEND_ATTACHMENT)
		{
			$Mail->attachment = array('name' => $fileName, 'type' => 'text/csv', 'tmp_name' => $fullFilePath, error => 0, 'size' => filesize($fullFilePath));
		}

		$Mail->sendEmail();
	}

	private function mapSessionType($sessionInfo)
	{
		if(is_null($sessionInfo)){
			return '';
		}
		switch ($sessionInfo['session_type_fadmin_acronym']) {
			case 'MFY':
				return "Pick Up";
			case 'A':
				return "Assembly";
			case 'TD':
			case 'TODD':
				return "Taste of Dream Dinners";
			case 'MPW':
				return "Meal Prep Workshop";
			case 'MPWC':
				return "Meal Prep Workshop Pick Up";
			case 'F':
				return "Fundraiser";
			case 'FC':
				return "Fundraiser Pick Up";
			case 'FNO':
				return "Friends Night Out";
			case 'C':
			case 'CP':
			case 'CPP':
				return "Community Pick Up";
			case 'WI':
				return "Walk-In";
			case 'HDP':
			case 'HD':
				return "Home Delivery";
			case 'P':
				return "Private Party";
			case 'OH':
				return "Open House";
			case 'OHC':
				return "Open House Pickup";
			case 'OHRA':
				return "Open House RSVP Only";
			case 'OHRN':
				return "Open House RSVP Only - New Guests";
			default:
				return $sessionInfo['session_type_fadmin_acronym'] . '='.$sessionInfo['session_type_string'];

		}
	}

	//-------SIMPLE UTILITIES------------------
	private function createSessionData(&$sessionData)
	{
		if(!empty($sessionData['session_id']))
		{
			$sessionInfoArray = CSession::getSessionDetailArray($sessionData['session_id'], false,  false);
			$sessionData['sessionInfo'] = array_values($sessionInfoArray)[0];

			//$nextSession['localizedSessionStartTime'] = CTimezones::localizeAndFormatTimeStamp($nextSession['session_start'], $nextSession['store_id'], MYSQL);
			$sessionStart = $this->splitDateTime($sessionData['session_start']);

			$sessionData['startDate'] = $sessionStart[0];
			$sessionData['startTime'] = $sessionStart[1] . ' ' . $sessionStart[2];
			return true;
		}
		else
		{
			return false;
		}
	}
	private function cleanTelephone($number)
	{
		$number = trim($number);
		$val = str_replace('-','',$number);

		if(strlen($val) != 10 ||
			$val == '0000000000' ||
			$val == '1234567890' ||
			$val == '0123456789' ||
			$val == '1111111111' ||
			$val == '2222222222' ||
			$val == '3333333333' ||
			$val == '4444444444' ||
			$val == '5555555555' ||
			$val == '6666666666' ||
			$val == '7777777777' ||
			$val == '8888888888' ||
			$val == '9999999999' )
		{
			return false;
		}

		if(strpos($number, "5555555") !== false){
			return false;
		}

		if(preg_match('/^[0-9]{10}+$/', $val))
		{
			return $val;
		}
		else
		{
			return false;
		}
	}

	private function csvClean($value)
	{
		if(is_null($value))
		{
			return "";
		}
		return trim($value);
	}

	private function splitDateTime($datetime)
	{
		if(!empty($datetime)){
			$d = DateTime::createFromFormat("Y-m-d H:i:s", $datetime);
			$short =  $d->format("m/d/y h:i A");
			return  explode(" ", $short);
		}
		else
		{
			return '';
		}
	}
}


class ResultToken
{
	private $doEcho = false;

	private $payload = null;
	private $failureMessages = array();
	private $processingMessages = array();
	private $isFailure = false;
	private $shouldRetry = false;

	public function __construct($echo = false)
	{
		$this->doEcho = $echo;
	}

	public function setPayload($payload)
	{
		$this->payload = $payload;
	}

	public function getPayload()
	{
		return $this->payload;
	}

	public function shouldRetry()
	{
		return $this->shouldRetry;
	}

	public function setShouldRetry($val)
	{
		$this->shouldRetry = $val;
	}

	public function isFailure()
	{
		return $this->isFailure;
	}

	public function getFailureMessages()
	{
		return $this->failureMessages;
	}

	public function getFailureMessagesString($delim)
	{
		return implode($delim,$this->failureMessages);
	}

	public function getFailureMessageHTML()
	{
		return '<ul><li>'.implode('</li><li>',$this->processingMessages).'</li></ul>';
	}

	public function addFailureMessages($msg)
	{
		if($this->doEcho)
		{
			echo 'FAILURE :: '.$msg.PHP_EOL;
		}
		$this->isFailure = true;
		$this->failureMessages[] = $msg;
	}

	public function getProcessingMessages()
	{
		return $this->processingMessages;
	}

	public function getProcessingMessagesString($delim)
	{
		return implode($delim,$this->processingMessages);
	}

	public function getProcessingMessagesHTML()
	{
		return '<ul><li>'.implode('</li><li>',$this->processingMessages).'</li></ul>';
	}

	public function addProcessingMessages($msg)
	{
		if($this->doEcho)
		{
			echo $msg.PHP_EOL;
		}
		$this->processingMessages[] = $msg;
	}

	public function showProgress($lineCount,$total)
	{
		if($this->doEcho)
		{
			echo '.';
			if($lineCount % 50 == 0)
			{
				echo PHP_EOL;
			}
			if($lineCount % 100 == 0)
			{
				echo '  About '.round(( $lineCount / $total ) * 100,0) . '% complete...';
				echo PHP_EOL;
			}
		}
	}
}
?>