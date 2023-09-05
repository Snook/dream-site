<?php
require_once("../includes/Config.inc");

header("status: 404 Not Found");
header('Location: /' . MAIN_SCRIPT . '?page=not_found');
exit();
?>