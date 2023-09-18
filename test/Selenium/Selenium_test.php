<?php

namespace Facebook\WebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
require_once("../../includes/CApp.inc");
require_once('vendor/autoload.php');

// This would be the url of the host running the server-standalone.jar
$host = 'http://localhost:4444/wd/hub'; // this is the default
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

$driver->get("http://dreamdinners.test/");

$cookies = $driver->manage()->getCookies();
print_r($cookies);

// print the title of the current page
echo "The title is '" . $driver->getTitle() . "'\n";
// print the URI of the current page
echo "The current URI is '" . $driver->getCurrentURL() . "'\n";