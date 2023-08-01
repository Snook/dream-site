<?php
require_once("../includes/CApp.inc");
require_once ('CTemplate.inc');

CTemplate::noCache();

if (array_key_exists('processor', $_REQUEST) !== false)
{
	CApp::approveDirective($_REQUEST['processor']);
	$app = new CApp();
	$app->process($_REQUEST['processor']);
}
else
{
	header('Location: 404.php');
}
?>