<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */


require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/User_retention_data_follow_up.php");

 class processor_admin_retentionProcessor extends CPageProcessor {

	 private $currentStore = null;

	function runPublic(){
		print "<node>Sorry, you are no longer logged into the system.  Please login to the franchise site again to continue saving data.";
	}

	function runFranchiseLead()
	{
	 	$this->runFranchiseOwner();
	}
	function runEventCoordinator()
	{
	 	$this->runFranchiseOwner();
	}
	function runHomeOfficeManager()
	{
	 	$this->runFranchiseOwner();
	}
	function runFranchiseManager() 
	{
		$this->runFranchiseOwner();
	}
	function runOpsLead() 
	{
		$this->runFranchiseOwner();
	}
	
	function runFranchiseOwner() {
		$rid = isset($_POST['rid']) ?  $_POST['rid'] : null;
		$action = isset($_POST['action']) ? $_POST['action'] : null;
		$uid = isset($_POST['uid']) ? $_POST['uid'] : null;
		$fid = isset($_POST['rfid']) ? $_POST['rfid'] : null;
		$reportid = isset($_POST['report']) ? $_POST['report'] : null;
		$follow_idx = isset($_POST['follow_idx']) ? $_POST['follow_idx'] : null;

		if ($action == 'update') {
			$rDate = isset($_POST['rDate']) ? $_POST['rDate'] : null;
			$rComm = isset($_POST['rComm']) ? $_POST['rComm'] : null;
			$fType = isset($_POST['fType']) ? $_POST['fType'] : null;
			$fDate = isset($_POST['fDate']) ? $_POST['fDate'] : null;

			if ($fid == null)
				print "<node>Sorry, an error occurred while saving the data. ERROR:120";
			else {
				$rslt = processor_admin_retentionProcessor::updateFollowup($fid,  $fType, $fDate);
				print "<node>$follow_idx<node>$rslt<node>$rid<node>1";
			}

		}
		else if ($action == 'insert' ) {
			$rDate = isset($_POST['rDate']) ? $_POST['rDate'] : null;
			$rComm = isset($_POST['rComm']) ? $_POST['rComm'] : null;
			$fType = isset($_POST['fType']) ? $_POST['fType'] : null;
			$fDate = isset($_POST['fDate']) ? $_POST['fDate'] : null;

			if ($fid == NULL) {

				$rslt = processor_admin_retentionProcessor::insertActionData($rid, $reportid, $fType, $fDate, $rDate, $rComm );
				if ($rslt > 0)
					print "<node>$follow_idx<node>$rslt<node>$reportid<node>$rid<node>$uid<node>-1";
				else
					print "<node>Sorry, an error occurred while saving the data. ERROR:120";

			}
			else {
				$rComm = htmlentities($rComm);
				$rslt = processor_admin_retentionProcessor::updateResults ($fid, $rDate, $rComm );
				print "<node>$follow_idx<node>$rslt<node>$reportid<node>$rid<node>$uid<node>$fid";

			}
		}
		else if ($action == 'deleteFollowUp') {
			$obj = DAO_CFactory::create('user_retention_data_follow_up');
			$obj->id = $fid;
			$rslt = $obj->delete();

			if ($rslt > 0)
				print "<node>$rid<node>$fid";
			else
				print "<node>Sorry, an error occurred while saving the data. ERROR:140";

		}
		else if ($action == 'deleteResult') {

			$obj = DAO_CFactory::create('user_retention_data_follow_up');
			$obj->id = $fid;
			$obj->results_comments = "NULL";
			$obj->results_date = "NULL";
			$rslt = $obj->update();
			if ($rslt > 0)
				print "<node>$rid<node>$fid";
			else
				print "<node>Sorry, an error occurred while saving the data. ERROR:130";
		}
	}

	/* Unused per CES 11/12/14
	
	static function getActionList ($user_id, $retention_data_id)
	{

		$sql = "Select user_retention_data_follow_up.id,user_retention_data_follow_up.follow_up_date,user_retention_data_follow_up.user_retention_data_id " .
		" From user_retention_data_follow_up Inner Join " .
		"user_retention_data ON user_retention_data_follow_up.user_retention_data_id = user_retention_data.id Where user_retention_data_follow_up.user_retention_data_id = $retention_data_id AND " .
		"user_retention_data.user_id = $user_id order by user_retention_data_follow_up.timestamp_created";

	//	$htmlData = '<SELECT ID="followupdates" NAME="followupdates">';
		$obj = DAO_CFactory::create('user_retention_data_follow_up');

		$obj->query($sql);

		$obj->fetch();



	//	while ( $obj->fetch() ) {
		//	$id = $obj->id;
		//	$follow_up_date = $obj->follow_up_date;
////			$user_retention_data_id = $obj->user_retention_data_id;

		//	$htmlData .= "<OPTION ID='$id' VALUE='$id'>" . $follow_up_date;
	//	}
		//$htmlData .= '</select>';

		return $htmlData;
	}
*/
	static function getLastResult ($rfid)
	{

		$sql = "Select user_retention_data_follow_up.results_date,user_retention_data_follow_up.results_comments " .
		"From user_retention_data_follow_up Inner Join user_retention_data ON user_retention_data_follow_up.user_retention_data_id = user_retention_data.id Where  " .
		"user_retention_data_follow_up.user_retention_data_id = $rfid order by user_retention_data_follow_up.timestamp_created asc limit 1";

		$obj = DAO_CFactory::create('user_retention_data_follow_up');

		$obj->query($sql);
		while ( $obj->fetch() ) {
			$xml .= "<node>" . $obj->results_date;
			$xml .= "<node>" . $obj->results_comments;
		}

		return $xml;
	}

	static function followupState ($followup_data_id)
	{

		$sql = "SELECT `user_retention_data_follow_up`.`results_comments`,`user_retention_data_follow_up`.`results_date`,`user_retention_data_follow_up`.`follow_up_date`," .
		" `user_retention_data_follow_up`.`user_retention_action_type_id`,`user_retention_data_follow_up`.`id` FROM " .
		" `user_retention_data_follow_up` where `user_retention_data_follow_up`.`id` = $followup_data_id ";

		$obj = DAO_CFactory::create('user_retention_data_follow_up');
		$obj->query($sql);

		$obj->fetch();

		if (empty($obj->id)) {
			$arr['id'] = -1;
		}
		else {
			$arr['id'] = $obj->id;

			$arr['follow_up_date'] = $obj->follow_up_date;
			$arr['action'] = $obj->user_retention_action_type_id;


			$arr['results_date'] = $obj->results_date;
			$arr['comments'] = $obj->results_comments;
		}


		return $arr;


	}

	static function getActionData ($followup_data_id)
	{

		$str = "";
		$sql = "Select
		user_retention_data_follow_up.follow_up_date,
		user_retention_action_type.Action_type,
		user_retention_data_follow_up.timestamp_created,
		user_retention_data_follow_up.results_date,
		user_retention_data_follow_up.results_comments

		From
		user_retention_data_follow_up
		Inner Join user_retention_action_type ON user_retention_data_follow_up.user_retention_action_type_id = user_retention_action_type.id
		Where
		user_retention_data_follow_up.id = $followup_data_id
		order by user_retention_data_follow_up.timestamp_created";

		$obj = DAO_CFactory::create('user_retention_data_follow_up');
		$obj->query($sql);

		while($obj->fetch()){

			$str = "<tr><td width=40%>Follow-up Created On:</td><td>". $obj->timestamp_created ."</td></tr>";
			$str .= "<tr><td width=40%>Follow-up Date:</td><td>" . $obj->follow_up_date . "</td></tr>";
			$str .="<tr><td width=40%>Follow-up Type</td><td>" . $obj->Action_type . "</td></tr>";
			if (!is_null($obj->results_date) && $obj->results_date != '0000-00-00') {
			   $str .="<tr><td width=40%>Results Recorded Date:</td><td>" . $obj->results_date . "</td></tr>";
			   $str .="<tr><td width=40%>Results Comment:</td><td>" . $obj->results_comments . "</td></tr>";
			   $str .="<tr><td width=40%><hr></td></tr>";
			}


		} // while

		return $str;


	}



	static function insertActionData($rid, $report_identifier, $followupType, $followUpDate, $rfid=null, $resultsDate=null, $resultsComments=null )
	{
		$id = -1;
		$obj = DAO_CFactory::create('user_retention_data_follow_up');

		if (!is_null($followUpDate)) {

			$formatDate = explode('/', $followUpDate);
			$newdate = $formatDate[2] . "-" . $formatDate[0] . "-" . $formatDate[1];

			$obj->follow_up_date = $newdate;
			$obj->user_retention_data_id = $rid;
			$obj->leading_report_identifier = $report_identifier;


			$rslt = $obj->find(true);
			if ($rslt > 0) return -100;

			$obj->results_date = "NULL" ;
			$obj->results_comments = "NULL" ;

			$obj->user_retention_action_type_id = is_null($followupType) ? "NULL" : $followupType ;
			$id = $obj->insert();
		}
		return $id;

	}

	static function updateResults ($rfid, $resultsDate, $resultsComments=null )
	{
		$id = -1;
		$obj = DAO_CFactory::create('user_retention_data_follow_up');
		if (!empty($rfid) && !empty($resultsDate)) {
			//$obj->id = $rfid;

			$formatDate = explode('/', $resultsDate);
			$newdate = $formatDate[2] . "-" . $formatDate[0] . "-" . $formatDate[1];

			//$obj->results_date = $newdate;

			if ($resultsComments == 'n/a' || count($resultsComments) == 0 || $resultsComments == '' || $resultsComments == ' ') $resultsComments = "null";

			//$obj->results_comments = $resultsComments ;


			$sql = "update user_retention_data_follow_up set results_date = '$newdate' , results_comments = '$resultsComments' WHERE (`id`='$rfid')";

			$id = $obj->query($sql);
			//$id = $obj->update();
		}
		return $id;

	}


	static function updateFollowup($rfid,  $followupType, $followUpDate)
	{
		$id = -1;


		$obj = DAO_CFactory::create('user_retention_data_follow_up');
		if (!empty($rfid)) {

			$finaldate = '';
			$finaldateday = '';

			$formatDate = explode('/', $followUpDate);
			$fmt = $formatDate[0];
			if ($fmt < 10) 	$finaldate .= '0' . $fmt;
			else $finaldate = $fmt;

			$fmtday = $formatDate[1];
			if ($fmt < 10) 	$finaldateday .= '0' . $fmtday;
			else $finaldateday = $fmt;


			$newdate = $formatDate[2] . "-" . $finaldate . "-" . $finaldateday;


			$type = is_null($followupType) ? "NULL" : $followupType ;

			$sql = "update user_retention_data_follow_up set follow_up_date = '$newdate' , user_retention_action_type_id = $type WHERE (`id`='$rfid')";


			$id = $obj->query($sql);

		}
		return $id;

	}

 };

?>