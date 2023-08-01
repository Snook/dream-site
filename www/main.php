<?php
require_once("../includes/CApp.inc");

if (defined('SITE_DISABLED') && SITE_DISABLED)
{
	if (!isset($g_IP_ExclusionList) || (isset($g_IP_ExclusionList) && !in_array($_SERVER['REMOTE_ADDR'], $g_IP_ExclusionList)))
	{
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Retry-After: 3600');
		header('Location: maint.php');
		exit;
	}
}

if (!empty($_GET))
{
	foreach ($_GET as $k => $v)
	{
		if (strpos($k, '<') !== false)
		{
			header('location:404.php');
			exit();
		}
	}
}

if (array_key_exists('page', $_REQUEST) !== false)
{
	$req_page = CGPC::do_clean($_REQUEST['page'], TYPE_NOHTML, true);

	CApp::approveDirective($req_page);

	$app = new CApp();
	$app->run($req_page);
}
else if (array_key_exists('static', $_REQUEST) !== false)
{
	$req_static = CGPC::do_clean($_REQUEST['static'], TYPE_NOHTML, true);

	CApp::approveDirective($req_static);

	$app = new CApp();
	$app->runStatic($req_static);
}
else if (strpos(strtolower(urldecode($_SERVER['REQUEST_URI'])), '<script>') !== false)
{
	header("HTTP/1.1 403 Forbidden");
	echo "Internal Server Error";
	exit(0);
}
else
{
	CApp::approveDirective($domainConfig['default_page']);

	$app = new CApp();
	$app->run($domainConfig['default_page']);
}
?>