<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO.inc");
require_once('includes/DAO/BusinessObject/CGiftCard.php');

/*
 *  Test order confirmation email
 */

CGiftCard::sendGiftCardOrderReport();

?>