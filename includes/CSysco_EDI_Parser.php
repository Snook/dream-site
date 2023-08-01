<?php
/*
 * Created on Jan 16, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("CLog.inc");
require_once("CAppUtil.inc");

function syscoToHumanDate($inDateStr)
{
	$year = "20" . substr($inDateStr, 0, 2); // good for 83 more years
	$month = substr($inDateStr, 2, 2);
	$day = substr($inDateStr, 4, 2);
	$betterDate = $month ."-" . $day ."-" . $year;
	return $betterDate;

}



class CSysco_EDI_Parser 
{

	const LINE_TYPE = 5;
	const DOC_TYPE = 45;
	const ACCOUNT_TYPE = 25;
	const INVOICE_NUMBER = 4;
	const STORE_ID = 3;
	const DATE = 48;
	const AMOUNT = 15;
	const TAX = 14;
	const ITEM_TAX = 17;
	const INVOICE_DATE = 10;
	
	
	static $placeHolder = array('doc_type' => 1, 'date' => false, 'invoice_number' => 0, 'vendor_id' => 0, 'account_number' => 0,
		'debit' => 0, 'total_amount' => 0, 'credit' => 0, 'dist_type' => 6);
	
	
	static $VendorIDMap = array(
		'324459' => array('syscoID' => '324459', 'storeID' => '000', 'vendorID' => 'SYSCO001'),
		'721399' => array('syscoID' => '721399', 'storeID' => '314', 'vendorID' => 'SYSCOPDX001'),
		'641696' => array('syscoID' => '641696', 'storeID' => '517', 'vendorID' => 'SYSCO002'),
		'997767' => array('syscoID' => '997767', 'storeID' => '376', 'vendorID' => 'SYSBOS001'),
		'759670' => array('syscoID' => '759670', 'storeID' => '339', 'vendorID' => 'SYSCODET001'),
		'817551' => array('syscoID' => '817551', 'storeID' => '882', 'vendorID' => 'SYSCHIC001'),
		'832725' => array('syscoID' => '832725', 'storeID' => '549', 'vendorID' => 'SYSCOCHA001'),
		'733485' => array('syscoID' => '733485', 'storeID' => '161', 'vendorID' => 'SYSCON001'),
		'00324459' => array('syscoID' => '324459', 'storeID' => '000', 'vendorID' => 'SYSCO001'),
		'00721399' => array('syscoID' => '721399', 'storeID' => '314', 'vendorID' => 'SYSCOPDX001'),
		'00641696' => array('syscoID' => '641696', 'storeID' => '517', 'vendorID' => 'SYSCO002'),
		'00997767' => array('syscoID' => '997767', 'storeID' => '376', 'vendorID' => 'SYSBOS001'),
		'00759670' => array('syscoID' => '759670', 'storeID' => '339', 'vendorID' => 'SYSCODET001'),
		'00817551' => array('syscoID' => '817551', 'storeID' => '882', 'vendorID' => 'SYSCHIC001'),
		'00832725' => array('syscoID' => '832725', 'storeID' => '549', 'vendorID' => 'SYSCOCHA001'),
		'00733485' => array('syscoID' => '733485', 'storeID' => '161', 'vendorID' => 'SYSCON001'),
		'1000094694' => array('syscoID' => '1000094694', 'storeID' => '358', 'vendorID' => 'SYSCOUT001'),
		'32748' => array('syscoID' => '032748', 'storeID' => '358', 'vendorID' => 'SYSCOUT001'),
		'032748' => array('syscoID' => '032748', 'storeID' => '358', 'vendorID' => 'SYSCOUT001'),		
		'00032748' => array('syscoID' => '032748', 'storeID' => '358', 'vendorID' => 'SYSCOUT001'),
	    '105636' => array('syscoID' => '105636', 'storeID' => '835', 'vendorID' => 'SYSCATL001'),
	    '00105636' => array('syscoID' => '105636', 'storeID' => '835', 'vendorID' => 'SYSCATL001'),
	    '831214' => array('syscoID' => '831214', 'storeID' => '447', 'vendorID' => 'SYSCHA001'),
	    '320489' => array('syscoID' => '320489', 'storeID' => '529', 'vendorID' => 'SYSCOVEN002'),
	    '320488' => array('syscoID' => '320488', 'storeID' => '248', 'vendorID' => 'SYSCOVEN001'),
	    '0831214' => array('syscoID' => '831214', 'storeID' => '447', 'vendorID' => 'SYSCHA001'),
	    '0320489' => array('syscoID' => '320489', 'storeID' => '529', 'vendorID' => 'SYSCOVEN002'),
	    '0320488' => array('syscoID' => '320488', 'storeID' => '248', 'vendorID' => 'SYSCOVEN001'),
	    '00831214' => array('syscoID' => '831214', 'storeID' => '447', 'vendorID' => 'SYSCHA001'),
	    '00320489' => array('syscoID' => '320489', 'storeID' => '529', 'vendorID' => 'SYSCOVEN002'),
	    '00320488' => array('syscoID' => '320488', 'storeID' => '248', 'vendorID' => 'SYSCOVEN001'),
        '321094' => array('syscoID' => '321094', 'storeID' => '752', 'vendorID' => 'SYSCOVEN003'),
        '0321094' => array('syscoID' => '321094', 'storeID' => '752', 'vendorID' => 'SYSCOVEN003'),
        '00321094' => array('syscoID' => '321094', 'storeID' => '752', 'vendorID' => 'SYSCOVEN003')
	);
		
	static $AccountsMap = array(
		'DAIRY PRODUCTS' => '00-5010-',
		'MEATS' => '00-5020-',
		'SEAFOOD' => '00-5030-',
		'POULTRY' => '00-5040-',
		'FROZEN' => '00-5050-',
		'CANNED AND DRY' => '00-5060-',
		'PRODUCE' => '00-5070-',
		'BEVERAGE' => '00-5080-',
		'PAPER & DISP' => '00-7130-',
		'CHEMICAL/JANTRL' => '00-7130-',
		'CHEMICAL/JNTRL' => '00-7130-',
		'HLTHCAR/HOSPLTY' => '00-7130-',
		'DISPENSER BEVRG' => '00-5080-',
		'DELIVERY_CHARGE' => '00-5210-',
		'SALES TAX' => '00-7130-',
		'ACCOUNTS PAYABLE' => '00-2000-');
	
	
	
	static function parseFile($inputFile, $outputFile, &$resultArr)
	{
	
	try {
	
		    
		    $fp = fopen($inputFile, 'r');
		    if ($fp === false)
		    {
		        $resultArr[] = "parseFile script: fopen failed";
		        return false;
		    }
		    
		    
		    if ($outputFile)
		    {
			    $fpd = fopen($outputFile, 'w');
			    if ($fpd === false)
			    {
			    	$resultArr[] = "parseFile script: fopen failed";
			        return false;
			    }
		    }
		    else
		    {
		    	$retVal = "";	
		    }
			    
		    $output = array();
			$invoiceSumArr = array();
			$invoiceDocTypeArr = array();
			$invoiceDateArr = array();
			
			
		    $currentInvoice = false;
		    $currentDocType = 1;
		    
		    while (!feof($fp))
		    {
		    	$buffer = fgets($fp, 4096);
		    
		    	if ($buffer === false)
		    	{
		    		break;
		    	}
		    
		       	
			    // make sure it isn't an empty line, sometimes show up at the end of the file.
			    $buffer = trim($buffer);
			    
			    if (!empty($buffer))
			    {
			    	$row = explode(",", $buffer);
			    	
			    	
			    	if ($row[self::LINE_TYPE] == 'HDR')
			    	{
			    		$currentInvoice = $row[self::INVOICE_NUMBER];
			    		$currentDocType = 0;
			    		$invoiceDateArr[$currentInvoice] = syscoToHumanDate($row[self::INVOICE_DATE]);
			    		 
			    	}
			    	else if ($row[self::LINE_TYPE] == 'DET')
			    	{
			    		CLog::Assert($currentInvoice == $row[self::INVOICE_NUMBER], "Invoice Number does not match last HDR line");
			    		
			    		if (!isset($output[$currentInvoice]))
			    		{
			    			$output[$currentInvoice] = array();
			    		}
			    		
			    		$rawStoreID = ltrim($row[self::STORE_ID], '0');
			    		$rawAccountType = trim($row[self::ACCOUNT_TYPE], "\" ");
			    		
			    		
			    		if (!empty($rawAccountType) && !isset(self::$AccountsMap[$rawAccountType]))
			    		{
			    			$resultArr[] =  "*** NOTICE !!!!! - Encountered unknown account type: $rawAccountType !!!! ****";
			    			continue;
			    		}
			    		
			    		if (empty($rawAccountType)) /// must be a delivery charge
			    		{
			    			
			    			$rawAccountType = 'DELIVERY_CHARGE';
			    		}
			    		
			    			    		
			    		if ($rawAccountType == 'PAPER & DISP')
			    		{
			    			if (isset($row[self::ITEM_TAX]) && is_numeric($row[self::ITEM_TAX]) && $row[self::ITEM_TAX] > 0)
			    			{
			    				$thisAcctType = self::$AccountsMap[$rawAccountType]. self::$VendorIDMap[$rawStoreID]['storeID'];
			    			}
			    			else 
			    			{
			    				$thisAcctType = "00-5100-" . self::$VendorIDMap[$rawStoreID]['storeID'];
			    			}
			    		}
			    		else 
			    		{
			    			$thisAcctType = self::$AccountsMap[$rawAccountType]. self::$VendorIDMap[$rawStoreID]['storeID'];
			    		}
			    		
			    		
			    		if (!isset($output[$currentInvoice][$thisAcctType]))
			    		{
			    			$output[$currentInvoice][$thisAcctType] = self::$placeHolder;
			    			$output[$currentInvoice][$thisAcctType]['debit'] =  $row[self::AMOUNT];
			    		}
			    		else 
			    		{
			    			$output[$currentInvoice][$thisAcctType]['debit']  += $row[self::AMOUNT];
			    		}
			    			    		
			    		$output[$currentInvoice][$thisAcctType]['doc_type'] = $currentDocType;
			    		$output[$currentInvoice][$thisAcctType]['invoice_number'] = $currentInvoice;
			    		$output[$currentInvoice][$thisAcctType]['vendor_id'] = self::$VendorIDMap[$row[self::STORE_ID]]['vendorID'];
			    		$output[$currentInvoice][$thisAcctType]['account_number'] = $thisAcctType;
			    		$output[$currentInvoice][$thisAcctType]['total_amount'] = "???";
			    		$output[$currentInvoice][$thisAcctType]['credit'] = 0;
			    		$output[$currentInvoice][$thisAcctType]['dist_type'] = 6;
			    		 
			    	}
			    	else if ($row[self::LINE_TYPE] == 'SUM')
			    	{
			    		$invoiceSumArr[$currentInvoice] = $row[self::AMOUNT] / 100;
			    					    		
			    		$invoiceDocTypeArr[$currentInvoice] = ($row[self::AMOUNT] > 0 ?  1 : 5);
			    		 
			    		if ($row[self::TAX] > 0)
			    		{
			    			$output[$currentInvoice]['TAX'] = self::$placeHolder;
			    			
			    			$output[$currentInvoice]['TAX']['doc_type'] = $currentDocType;
			    			$output[$currentInvoice]['TAX']['invoice_number'] = $currentInvoice;
			    			$output[$currentInvoice]['TAX']['vendor_id'] = self::$VendorIDMap[$row[self::STORE_ID]]['vendorID'];
			    			$output[$currentInvoice]['TAX']['account_number'] = self::$AccountsMap['SALES TAX'] . self::$VendorIDMap[$row[self::STORE_ID]]['storeID'];
			    			 
			    			$output[$currentInvoice]['TAX']['total_amount'] = 0;
			    			$output[$currentInvoice]['TAX']['debit']  = $row[self::TAX] ;
			    		
			    			$output[$currentInvoice]['TAX']['credit'] = 0;
			    			$output[$currentInvoice]['TAX']['dist_type'] = 6;
			    		}
			    		
			    		$output[$currentInvoice]['SUM'] = self::$placeHolder;
			    		 $output[$currentInvoice]['SUM']['doc_type'] = $currentDocType;
			    		$output[$currentInvoice]['SUM']['invoice_number'] = $currentInvoice;
			    		$output[$currentInvoice]['SUM']['vendor_id'] = self::$VendorIDMap[$row[self::STORE_ID]]['vendorID'];
			    		$output[$currentInvoice]['SUM']['account_number'] = self::$AccountsMap['ACCOUNTS PAYABLE'] . self::$VendorIDMap[$row[self::STORE_ID]]['storeID'];
			    		
			    		$output[$currentInvoice]['SUM']['total_amount'] = 0;
			    		
			    		if ($invoiceSumArr[$currentInvoice] > 0)
			    			$output[$currentInvoice]['SUM']['credit'] =  $invoiceSumArr[$currentInvoice];
			    		else 
			    			$output[$currentInvoice]['SUM']['debit'] =  $invoiceSumArr[$currentInvoice] * -1;
			    			
			    		$output[$currentInvoice]['SUM']['dist_type'] = 2;
			    		
			    		 
			    	}
			    	else 
			    	{
			    		throw new Exception("Unknown Line Type: " . $row[self::LINE_TYPE] );
			    	}
			    	
			    	
			    }
		    }
		    
		    
		    foreach($output as $invoice => $type)
		    {
		    	
		    	foreach ($type as $typeName => $data )
		    	{
		    		
		    		 if ($invoiceSumArr[$invoice] < 0)		    		
		    			 $data['total_amount'] = $invoiceSumArr[$invoice] * -1;
		    		 else 
		    		 	$data['total_amount'] = $invoiceSumArr[$invoice];
		    			 
		    		 	
		    		 	
		    		 $data['doc_type'] = $invoiceDocTypeArr[$invoice];
		    		 $data['date'] = $invoiceDateArr[$invoice];
		    		 
		    		 if ($data['debit'] < 0)
		    		 {
		    		 	if ($data['dist_type'] == 2)
		    		 	{
		    		 		$data['debit'] = $data['debit'] * -1;		    		 		 
		    		 	}
		    		 	else 
		    		 	{
		    		 		$data['credit'] = $data['debit'] * -1;
		    		 		$data['debit'] = 0;
		    		 	}
		    		 }
		    		 
		    		 
		       		 $thisLine = implode(",", $data);
		       		 
		       		 if ($outputFile)
		       		 {
		       		 	$length = fputs($fpd,  $thisLine . "\r\n");
		       		 }
		       		 else 
		       		 {
		       		 	$retVal .= $thisLine . "\r\n";
		       		 }
		    	}
		        
		    }
		    
		    fclose($fp);
		    
		    if ($outputFile)
		    {
		    	fclose($fpd);
		    	$resultArr[] =  "Successfully parsed " . $inputFile;
		    	return true;
		    }
		    else 
		    {
		    	$resultArr[] =  "Successfully parsed " . $inputFile;
		    	return $retVal;
		    }
		
		} catch (exception $e) {
		    $resultArr[] =  "Exception occurred in main loop: " . $e->getMessage();
		    return false;
		}
	}

}


?>