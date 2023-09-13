<?php
	require_once("../../includes/CApp.inc");
	CApp::$isStoreView = true;

	global $isDinnersForLife;

	if (strpos($_SERVER['SERVER_NAME'], "ddhub") !== false ||
		strpos($_SERVER['SERVER_NAME'], "dinnersforlife") !== false ||
		strpos($_SERVER['SERVER_NAME'], "dfl") !== false)
	{
		$isDinnersForLife = true;
	}
	else
	{
		$isDinnersForLife = false;
	}

	if (defined('SITE_DISABLED') && SITE_DISABLED)
	{
		if (!isset($g_IP_ExclusionList) || (isset($g_IP_ExclusionList) && !in_array($_SERVER['REMOTE_ADDR'], $g_IP_ExclusionList)))
		{
			header('Location: maint.php');
			exit;
		}
	}

	if ( array_key_exists('page',$_REQUEST) !== false ) {
		if ( ($_REQUEST['page'] != 'storelogin') && (!CBrowserSession::getCurrentStoreView()) ) {
			header('Location: ' . WEB_BASE .  'storelogin');
		}
		$app = new CApp();
		$app->run($_REQUEST['page']);
	} else if ( array_key_exists('static',$_REQUEST) !== false ) {
		if ( ($_REQUEST['static'] != 'storelogin') && (!CBrowserSession::getCurrentStoreView()) ) {
			header('Location: ' . WEB_BASE .  'storelogin');
		}
		$app = new CApp();
		$app->runStatic($_REQUEST['static']);
	} else {
		echo "404";
	}
?>