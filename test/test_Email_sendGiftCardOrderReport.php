<?php
require_once("../includes/Config.inc");
require_once("DAO.inc");
require_once('includes/DAO/BusinessObject/CGiftCard.php');

/*
 *  Test order confirmation email
 */

CGiftCard::sendGiftCardOrderReport();

?>