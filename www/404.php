<?php
require_once("../includes/Config.inc");

$full_path = explode('/', substr(strtolower(rtrim($_SERVER['REQUEST_URI'], "/")), 1));
$uri = end($full_path);
$uri = strtok($uri, '?');

// In case a store email has a . in it, it probably doesn't match file extensions.
$full_filename = explode('.', $uri);
$ext = end($full_filename);

$dont_store_search = array(
	'gif',
	'js',
	'css',
	'png',
	'jpg',
	'htm',
	'html',
	'map'
);

if (preg_match("/^[A-Za-z0-9_.-]+$/", $uri) && $uri != '404.php' && !in_array($ext, $dont_store_search))
{
	// Might be looking for a store, start store search
	require_once("DAO/CFactory.php");
	require_once("../includes/DAO/BusinessObject/CStatesAndProvinces.php");

	// check for store in url
	$store = DAO_CFactory::create('store');
	$store->active = 1;
	$store->show_on_customer_site = 1;
	$store->whereAdd("store.email_address LIKE '" . $uri . "%'");

	if ($store->find(true))
	{
		header('location: /' . MAIN_SCRIPT . '?page=store&id=' . $store->id . (!empty($_SERVER['REDIRECT_QUERY_STRING']) ? '&' . $_SERVER['REDIRECT_QUERY_STRING'] : ''));
		exit();
	}
	else
	{
		header("status: 404 Not Found");
		header('Location: /' . MAIN_SCRIPT . '?page=not_found');
		exit();
	}
}
else if (!in_array($ext, $dont_store_search))
{
	header("status: 404 Not Found");
	header('Location: /' . MAIN_SCRIPT . '?page=not_found');
	exit();
}
else
{
	header("status: 404 Not Found");
	exit();
}
?>