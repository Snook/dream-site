<?php
require_once 'Build_Utils.php';

define('DEV', true);

if (DEV)
{
	require_once("C:\\Users\\Carl.Samuelson\\Zend\workspaces\\DefaultWorkspace\\DreamDigest\\includes\\Config.inc");
	define ('BASE_CSV_PATH' ,"C:/Users/Carl.Samuelson/Zend/workspaces/DefaultWorkspace/DreamDigest/CSV/" );
	define('TARGET', 'PLAYGROUND');
}
else
{
	require_once("/DreamDigest/includes/Config.inc");
	define ('BASE_CSV_PATH' ,"/DreamDigest/CSV/" );
	define('TARGET', 'LIVE');
}

require_once("../includes/DAO/CFactory.php");
require_once("../includes/DAO2/CDigestFactory.php");
require_once("../includes/CLog.inc");

global $cvs_file_name;
global $sf_api_name;
global $internal_name;
global $external_id_column;
global $token;
global $instance_url;
global $isDeleteFunction;
global $digestTableName;

$isDeleteFunction = false;

global $currentDAO;
global $fullReport;
$fullReport = "";

global $dailyFolder;
$dailyFolder = date("Y-m-d");

define("NORMAL_DAO", "NORMAL_DAO");
define("DIGEST_DAO", "DIGEST_DAO");


ini_set('memory_limit','20000M');
set_time_limit(3600 * 24);

$currentDAO = DIGEST_DAO;

class SFMC_API
{

	function niceEcho($label, $str)
	{
		$nice_str = "\r\n" . $label . " : " . $str . "\r\n\r\n";
		logstr($nice_str);
	}

	function doSomething($sessionID)
	{
		global $instance_url;

		//  $content = json_encode(array("object" => $sf_api_name, "contentType" => "CSV", "operation" => $op,
		//      "lineEnding" => "CRLF", "externalIdFieldName" => $external_id_column, "concurrencyMode" => "Serial"));

		//  $url = $instance_url . "salesforce.com/services/data/v20.0/sobjects/Account/describe";
		$url = "https://wa98290--maindev.cs14.my.salesforce.com/services/apexrest/GuestChild/all";

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Content-type: application/json; charset=UTF-8",
			"Accept: application/json;",
			"Authorization: Bearer $sessionID"
		));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		//curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

		$json_response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($status != 200 && $status != 201)
		{
			throw new Exception("getJobIDCSV Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $json_response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl));
		}

		curl_close($curl);

		$response = json_decode($json_response, true);

		logstr("Job Response\r\n" . print_r($response, true));
	}

	function attemptLogin()
	{
		global $instance_url;

		$url = "https://mc01mx89tgtvsgs7trq9ghzl-v9q.auth.marketingcloudapis.com/v2/token";
		if (DEV)
		{
			$content = array(
				"grant_type" => "client_credentials",
				"client_id" => "2obygg7em50gi86ua82ew0x4",
				"client_secret" => "2iK7xaODJwwWdNJcTPvjqt4E"
			);
			$content = json_encode($content);
		}
		else
		{

		}

		$curl = curl_init($url);

		$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				"Content-type: application/json",
				"Accept: application/json"
			));

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

		$json_response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($status != 200)
		{
			throw new Exception("attemptLogin Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl));
		}

		$result = json_decode($json_response, true);

		curl_close($curl);

		logstr("Login Successful");

		return $result['access_token'];
	}

}

try
{

	$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
	$options = array('database' => 'mysqli://'.DB_SERVER_USERNAME.':'.DB_SERVER_PASSWORD.'@'.DB_SERVER.'/dreamdigest', 'schema_location' => APP_BASE.'includes/DAO2', 'class_location' => APP_BASE.'includes/DAO2', 'class_prefix' => 'DAO2_', 'extends' => 'DAO', 'extends_location' => 'DAO.inc', 'debug' => 0);

	logStr("-------------  getting Access Token\r\n\r\n");
	$token = attemptLogin();
	logStr("Token: " . $token, true, true);

	doSomething($token);

	echo 'success';
	return;

}

catch
(exception $e)
{
	LogImport("UPDATE", "ERROR", $internal_name, "FAILED", $jobID, 0, 0, 0, null, null, $e->getMessage(), $fullReport);
	echo 'failure';

	return;
}

?>
