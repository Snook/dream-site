<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
header("status: 404 Not Found");
header('Location: /not-found');
exit();
?>