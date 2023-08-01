<?php // page_admin_create_store.php

/**
 * @author Todd Wallar
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once('includes/DAO/BusinessObject/CGiftCard.php');
 require_once('includes/payment/PayPalProcess.php');
 require_once('includes/DAO/BusinessObject/COrders.php');
 require_once('includes/DAO/BusinessObject/CPayment.php');
 require_once('includes/class.inputfilter_clean.php');
 class page_admin_gift_card_management extends CPageAdminOnly {


 	function runSiteAdmin() {
	}
	function runHomeOfficeStaff() {
	}
	function runHomeOfficeManager() {
	}
	function runFranchiseManager() {
	}
	function runFranchiseOwner() {
	}
	function runFranchiseStaff() {
	}
	function runFranchiseLead() {
	}
	function runEventCoordinator(){
	}
	function runOpsLead(){
	}


}

?>