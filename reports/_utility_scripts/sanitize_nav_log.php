<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 // 1) Create new 3 servings intro items for the June Menu

//require_once("C:\\Users\\Carl.Samuelson\\Zend\workspaces\\DefaultWorkspace\\Responsive\\includes\\Config.inc");
require_once("/DreamSite/includes/Config.inc");


require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

function checkResult($result, $_db, $isFatal = true)
{
	if (!$result)
	{
		$errStr = mysqli_error($_db);

		if ($isFatal)
		{
			throw new Exception($errStr);
		}
	}
}

set_time_limit(100000);
ini_set('memory_limit', '1024M');

$exclusionArray	= array('ccNumber', 'ccSecurityCode', 'gift_card_number', 'gift_card_security_code', 'credit_card_number', 'credit_card_cvv',
	'credit_card_exp_month','credit_card_exp_year',
	'payment1_cc_security_code', 'payment2_cc_security_code', 'payment1_ccNumber', 'payment2_ccNumber', 'default_payment1_ccNumber',
	'default_payment2_ccNumber', 'default_payment1_cc_security_code', 'default_payment2_cc_security_code', 'card_number', 'login_password', 'password', 'password_confirm');


 try {
	 $utils = new DB_Common();

	 $_db = mysqli_connect(DB_LOG_SERVER, DB_LOG_SERVER_USERNAME, DB_LOG_SERVER_PASSWORD);
	 mysqli_select_db($_db, DB_LOG_DATABASE);

	 $Main_result = mysqli_query($_db, "SELECT id, request_content FROM `nav_log` where request_content REGEXP '[0-9]{15}' OR LOCATE('password', request_content) <> 0");

	while ($row = mysqli_fetch_assoc($Main_result))
	{
		$RAW = $row['request_content'];
		$didReplace = false;

		Foreach($exclusionArray as $thisDatum)
		{
			$offset = 0;
			$stillSearching = true;
			while ($stillSearching )
			{
				$offset = @strpos($RAW, $thisDatum, $offset);
				if ($offset === false)
				{
					$stillSearching = false;
					break;
				}

				$beginAt = 	$offset;
				$offset = strpos($RAW, "\n", $offset);

				$replaceMe = substr($RAW, $beginAt - 1, $offset - ($beginAt-1));

				if (strpos($replaceMe,"redacted") !== false)
				{
					echo "# " . $row['id'] . " ". $replaceMe . " already redacted\r\n";
					continue;
				}

				$replacement = "[" . $thisDatum . "] => redacted";

			//	echo "# " . $replaceMe . " with " . $replacement .  "\r\n";

				$RAW = substr_replace($RAW, $replacement, $beginAt - 1, $offset - ($beginAt-1) );
				$didReplace = true;
			}
		}

		if ($didReplace)
		{
			echo "update dreamlog.nav_log set request_content = " .  $utils->quoteSmart($RAW) . " where id = {$row['id']};\r\n";

		//	$result = mysqli_query($_db, "update dreamlog.nav_log set request_content = " .  $utils->quoteSmart($RAW) . " where id = {$row['id']}");
		//	checkResult($result, $_db);
		//	echo "\r\n\r\n" . $RAW;
		}


		//	$length = fputs($dest_fp, implode(",", $tArray) . "\r\n");
		}


	} catch (exception $e) {
		echo "new user behVIOR report failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}
?>
