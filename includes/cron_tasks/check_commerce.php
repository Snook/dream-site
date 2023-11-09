<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("processor/admin/status.php");
require_once('CMail.inc');

require_once("CLog.inc");

//
try {

    require_once 'includes/payment/PayPalProcess.php';
    $process = new PayPalProcess();

	$result = $process->payFlowTest();

	$doAlert = false;

	switch($result)
	{
	    case 'success':
	        echo "All is Well\n";
            break;
	    case 'noMerchantAccountFound':
	        CLog::RecordNew(CLog::ERROR,'Merchant Account not found eCommerce Test: ',"","" ,true );
	        echo "No Merchant Account\n";
	        break;
	    case 'configurationError':
	        // error is emailed to admin in PAyPayProcess
	        echo "Configuration error\n";
	        break;
	    // The above 2 cases are problems with the test infrastructure - not eComm problems
	    case 'communicationError':
               // Here's the real problem
	        CLog::RecordNew(CLog::ERROR,'Failure of eCommerce Test - please investigate:',"","" ,true );
	        $doAlert = true;
            echo "Problem: \n";

	        break;
	    case 'transactionError':
	            // This could be a problem with the card but could also be a real issue
            echo "Potential Problem: \n";
            $resArr = $process->getResult();

            $OK_codes = array(13);
            // don't alert as theses codes have been seen to be returned when all systems are fine

            if (in_array($resArr['RESULT'], $OK_codes ))
            {
                CLog::RecordNew(CLog::ERROR,'Got an unexpected result from PayFlow during test but everything is probably fine. Here are the deets:' .  print_r($process->getResult(), true),"","" ,true );
            }
            else
            {
                 $doAlert = true;
            }
	        break;
	    case 'configurationErrorVoid':
	        // error is emailed to admin in PAyPayProcess
	        echo "Configuration error\n";
	        break;
	      // The above 2 cases are problems with the test infrastructure - not eComm problems
	    case 'communicationErrorVoid':
	     // Here's the real problem
            CLog::RecordNew(CLog::ERROR,'Failure of eCommerce Test - please investigate:',"","" ,true );
            $doAlert = true;
            echo "Problem: \n";

            break;
	    case 'transactionErrorVoid':
            // This could be a problem with the card but could also be a real issue
            echo "Potential Problem: \n";
            $resArr = $process->getResult();

            $OK_codes = array(13);
            // don't alert as theses codes have been seen to be returned when all systems are fine

            if (in_array($resArr['RESULT'], $OK_codes ))
            {
                CLog::RecordNew(CLog::ERROR,'Got an unexpected result from PayFlow during test but everything is probably fine. Here are the deets:' .  print_r($process->getResult(), true),"","" ,true );
            }
            else
            {
                 $doAlert = true;
            }
            break;

	    default:
	}



	if ($doAlert)
	{
	    $alertStr = "ECommerce Failure Alert\n\nProblem Type: " . $result . "\n";
	    $alertStr .= print_r($process->getResult(), true);

	    $Mail = new CMail();

        $Mail->send(null,
            null,
            "Dream Dinners Technical Staff",
            "josh.thayer@dreamdinners.com,ryan.snook@dreamdinners.com",
            "ECommerce Failure",
            null,
            $alertStr,
            '',
            '',
            0,
            '');


        $Mail->send(null,
            null,
            "Dream Dinners Technical Staff",
            "4257600812@vtext.com,4254205526@txt.att.net,4255123399@vtext.com",
            "ECommerce Failure",
            null,
            "ECommerce Failure. Please check your email for details.",
            '',
            '',
            0,
            '');
	}

} catch (exception $e) {
	CLog::RecordException($e);
}

?>