<?php
	require_once("../../includes/CApp.inc");
	
	CApp::$isStoreView = true;
		
	if ( array_key_exists('processor',$_REQUEST) !== false ) {
		$app = new CApp();
		$app->process($_REQUEST['processor']);
	} else {
		header('Location: 404');
	}
?>
	