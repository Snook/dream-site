<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once("CTemplate.inc");

class processor_admin_no_show_state extends CPageProcessor
{


	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runOpsSupport()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{

		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if ( isset($_REQUEST['bid']) && is_numeric($_REQUEST['bid']))
		{
			$booking_id = $_REQUEST['bid'];
			$Booking = DAO_CFactory::create('booking');
			$Booking->id = $booking_id;

			$is_no_show = (isset($_REQUEST['state']) && $_REQUEST['state'] == 'yes') ? 1 : 0;

			if ($Booking->find(true))
			{
				// TODO: Test for access rights

				$booking_org = clone($Booking);
				$Booking->no_show = $is_no_show;
				$Booking->update($booking_org);

				echo json_encode(array(

					'processor_success' => true,
					'processor_message' => 'Booking updated.',
					'dd_toasts' => array(
						array('message' => 'Booking updated.')
					)
				));
			}
			else
			{
				echo json_encode(array(

					'processor_success' => false,
					'processor_message' => 'Booking not found.',
					'dd_toasts' => array(
						array('message' => 'Booking not found.')
					)
				));
			}
		}
		else
		{
			echo json_encode(array(

				'processor_success' => false,
				'processor_message' => 'No booking ID.',
				'dd_toasts' => array(
					array('message' => 'No booking ID.')
				)
			));
		}

	 }

}
?>