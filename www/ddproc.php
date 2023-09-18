<?php
require_once("../includes/CApp.inc");
require_once ('CTemplate.inc');

CTemplate::noCache();

if (array_key_exists('processor', $_REQUEST) !== false)
{
	$processor = str_replace('-', '_', $_REQUEST['processor']);
	CApp::approveDirective($processor);
	$app = new CApp();
	$app->process($processor);
}
else
{
	header('Location: /not-found');
}
?>