<?php

/*
Header Defintion Valid attributes

align
-------
left
center
right
wrap

width
-------
a number of pixels
auto

type
-------
text
percent
percent_as_int
currency
currency_no_cents
datetime
y_axis_labels
URL
number
number_x
number_xxx
number_w_parens

decor
-------
subtotal
majortotal
fixed
left_border
left_border_heavy
yes_no_condform

OVERRIDE PARAMS
----------------
header_gradient_start
header_gradient_end
suppress_auto-filter
main_header_height
links_target_new_tab

FORMAT DIRECTIVES
------------------
Append "|=>" and a directive to the cell value to format a single cell

text
percent
percent_as_int
currency
currency_no_cents
datetime
y_axis_labels
URL
number
number_x
decimal_2
number_w_parens

MERGE DIRECTIVE
----------------
Append "|->" and a number to the cell value to merge several cells.
The number must be > 0 and < 25

*/


function makeTitle($reportName, $store, $addInfo = "")
{
    $TitleStr = $reportName;
    $timezoneID = 7;

    if (is_object($store) && isset($store->store_name))
    {
        $TitleStr .= "_" . $store->store_name;
        $timezoneID = !empty($store->timezone_id) ? $store->timezone_id : 7;
    }
    else if (isset($store) && is_numeric($store))
    {
        $storeObj = new DAO();
        $storeObj->query("select store_name, timezone_id from store where id = $store");
        $storeObj->fetch();
        $TitleStr .= "_" . $storeObj->store_name;
        $timezoneID = $storeObj->timezone_id;

    }
    else
    {
        $TitleStr .= "_" . $store;
    }

    if (!empty($addInfo))
    {
        $TitleStr .= "_" . $addInfo;
    }

    $dateTS = CTimeZones::getAdjustedServerTimeWithTimeZoneID($timezoneID);
    $printableDate = date("h-ia_m_d_Y", $dateTS);

    $TitleStr .= "_Ran_" .$printableDate;

    return $TitleStr;
}

function incrementColumn(&$thirdColLetter, &$secondColLetter, &$columnLetter)
{
	if ($columnLetter === "Z")
	{
		if ($secondColLetter === "")
		{
			$secondColLetter = "A";
		}
		else
		{
			if ($secondColLetter === "Z")
			{
				if ($thirdColLetter === "")
				{
					$thirdColLetter = "A";
				}
				else
				{
					$thirdColLetter = chr(ord($thirdColLetter) + 1);
				}

				$secondColLetter = "A";
			}
			else
			{
				$secondColLetter = chr(ord($secondColLetter) + 1);
			}
		}

		$columnLetter = "A";

	}
	else
	{
		$columnLetter = chr(ord($columnLetter) + 1);
	}



}

function writeExcelFile($fileName, $header, $rows, $showHeader=true, $titleRows = false, $columnDescriptions = false, 
    $headersAreEmbedded = false, $callbacks = false, $superHeader = false, $returnHTML = false, $suppressLabelsDisplay = false,
	$overrideValues = false, $use_Lib_1_8 = false, $saveFilePath = false)
{

	/** Include PHPExcel */

	if ($use_Lib_1_8)
	{
		require_once("phplib/PHPExcel_1.8/PHPExcel.php");
	}
	else
	{
		require_once("phplib/PHPExcel/PHPExcel.php");
	}

	try {

		set_time_limit(3600);

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		$AdminUser = CUser::getCurrentUser();

		if (!empty($AdminUser->firstname))
		{
			// Set document properties
			$objPHPExcel->getProperties()->setCreator($AdminUser->firstname . " " . $AdminUser->lastname)
			->setLastModifiedBy($AdminUser->firstname . " " . $AdminUser->lastname)
			->setTitle($fileName)
			->setKeywords("office 2007 openxml php")
			->setCategory("Dream Dinners report file");
		}
		else
		{
			// Set document properties
			$objPHPExcel->getProperties()->setTitle("test")
			->setKeywords("office 2007 openxml php")
			->setCategory("Dream Dinners report file");
		}

		$objPHPExcel->setActiveSheetIndex(0);


		$labelsRow = 1;
		$numTitlesRows = (!empty($titleRows) ? count($titleRows) : 0);

		// Title rows are displayed above the column headers
		if ($numTitlesRows) {

            $labelsRow += $numTitlesRows;
            $row = 1;

			if ($titleRows[0] == "small placeholder")
			{
				foreach ($titleRows as $thisTitleRow)
				{
					$objPHPExcel->getActiveSheet()->mergeCells("A$row:I$row");

					$row++;
				}
			}
			else if ($titleRows[0] == "placeholder")
            {
                foreach ($titleRows as $thisTitleRow)
                {
                    $objPHPExcel->getActiveSheet()->mergeCells("A$row:D$row");
                    $objPHPExcel->getActiveSheet()->mergeCells("E$row:K$row");

                    $row++;
                }
            }
            else
            {
                foreach ($titleRows as $thisTitleRow)
                {
                	$finalCol = "Z";
                	if (!empty($overrideValues['titleRowFinalColumn']))
					{
						$finalCol = $overrideValues['titleRowFinalColumn'];
					}

                    $objPHPExcel->getActiveSheet()->mergeCells("A$row:$finalCol$row");
                    $str = implode(" ", $thisTitleRow);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $str);

                    $styleArray = array('font' => array('bold' => true, 'size' => 12),
                        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'wrap' => false),
                        'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_NONE)),
                        'fill' => array('type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array('argb' => 'FFFDFBE5',), 'endcolor' => array('argb' => 'FFF1DB3C')));

                    $objPHPExcel->getActiveSheet()->getStyle("A$row:J$row")->applyFromArray($styleArray);

                    $row++;
                }
            }
		}
		else
		{
			$row = 1;
		}

		if ($headersAreEmbedded)
		{
			$showHeader = true;

			if (isset($rows[0]))
				$header = array_keys($rows[0]);
			else
			{
				$header = array_keys(current($rows));
				reset($rows);

			}
		}


		if ($superHeader)
		{

			$col = "A";
			$colSecondChar = "";
			$thirdColLetter = "";
			
			if (isset($overrideValues['header_gradient_start']))
			{
			    $gradient_start = $overrideValues['header_gradient_start'];
			}
			else
			{
			    $gradient_start = 'FFE3F072';
			}
			
			if (isset($overrideValues['header_gradient_end']))
			{
			    $gradient_end = $overrideValues['header_gradient_end'];
			}
			else
			{
			    $gradient_end = 'FFA8B355';
			}
			


			  foreach($superHeader as $name => $columns)
			  {
			  		$firstColumn = $thirdColLetter . $colSecondChar . $col;

				  	for ($x = 0; $x < $columns - 1; $x++)
				  	{
				  		incrementColumn($thirdColLetter, $colSecondChar, $col);
				  	}

				  	$lastColumn = $thirdColLetter . $colSecondChar . $col;

				  	$objPHPExcel->getActiveSheet()->mergeCells("$firstColumn$row:$lastColumn$row");
				  	$objPHPExcel->getActiveSheet()->setCellValue($firstColumn. $row, $name);

				  	$styleArray = array('borders' => array('right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
				  	$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter .$colSecondChar . $col . $row)->applyFromArray($styleArray);

				  	incrementColumn($thirdColLetter, $colSecondChar, $col);
			  }



			  $styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
			  			'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
			      'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => $gradient_start, ), 'endcolor' => array( 'argb' => $gradient_end)));

			  $objPHPExcel->getActiveSheet()->getStyle("A$labelsRow:$lastColumn" . $row)->applyFromArray($styleArray);

			  $labelsRow++;
		}

		$headerColCount = 0;
		if ($showHeader)
		{
	 		// create and format the header cells (labels)
			$row = $labelsRow;
			$col = "A";
			$colSecondChar = "";
			$thirdColLetter = "";

			$headerExcpetions = array();
			foreach($header as $thisColumn)
			{

				$headerColCount++;
				$lastColumn = $thirdColLetter . $colSecondChar. $col;

				if (!$suppressLabelsDisplay)
				{
    				$objPHPExcel->getActiveSheet()->setCellValue($thirdColLetter . $colSecondChar . $col . $row, $thisColumn);
    
    				$styleArray = array('borders' => array('right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
    				$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter .$colSecondChar . $col . $row)->applyFromArray($styleArray);
    
    
    				if (isset($columnDescriptions[$lastColumn]['decor']) && $columnDescriptions[$lastColumn]['decor'] == 'majortotal')
    					$headerExcpetions[$lastColumn] = 'majortotal';
    
				}
				
				incrementColumn($thirdColLetter, $colSecondChar, $col);
			}
			
			if (!$suppressLabelsDisplay)
			{
			    
			    if (isset($overrideValues['main_header_height']))
			    {
			        $objPHPExcel->getActiveSheet()->getRowDimension($labelsRow)->setRowHeight($overrideValues['main_header_height']);
			    }
			    else 
			    {
			        $objPHPExcel->getActiveSheet()->getRowDimension($labelsRow)->setRowHeight(44);
			    }
			    
			    
			    if (isset($overrideValues['header_gradient_start']))
			    {
			        $gradient_start = $overrideValues['header_gradient_start'];
			    }
			    else 
			    {
			        $gradient_start = 'FFE3F072';
			    }
    
			    if (isset($overrideValues['header_gradient_end']))
			    {
			        $gradient_end = $overrideValues['header_gradient_end'];
			    }
			    else
			    {
			        $gradient_end = 'FFA8B355';
			    }
			    
			    
			    
    			$styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
    					'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
    					'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )),
    			    'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => $gradient_start, ), 'endcolor' => array( 'argb' => $gradient_end)));
    
    			$objPHPExcel->getActiveSheet()->getStyle("A$labelsRow:$lastColumn" . $labelsRow)->applyFromArray($styleArray);
    
    			if (count($headerExcpetions))
    			{
    				$styleArray2 = array( 'font' => array( 'bold' => true, 'size' => 10 ),
    						'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
    						'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )),
    						'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => 'FF95B3D7' ), 'endcolor' => array( 'argb' => 'FF90AED2')));
    
    				foreach ($headerExcpetions as $col => $type)
    				{
    					// the only type is majortotal right now
    
    					$objPHPExcel->getActiveSheet()->getStyle($col . $labelsRow)->applyFromArray($styleArray2);
    				}
    			}
			}
		}
		else
		{
			$labelsRow = 0;
		}

		$freezeLeftmostColumn = false;

		$col = 'A';
		$colSecondChar = "";
		$thirdColLetter = "";
		
		
		for ($x = 0; $x < $headerColCount; $x++)
		{
			// apply formatting
			$colRange = $thirdColLetter . $colSecondChar. $col . ($labelsRow + 1) . ":" . $thirdColLetter . $colSecondChar.  $col . (count($rows) + $labelsRow);

			$fullCol = $thirdColLetter . $colSecondChar . $col;

			if ($columnDescriptions && isset($columnDescriptions[$fullCol]))
			{

				if (isset($columnDescriptions[$fullCol]['width']))
				{
					if ($columnDescriptions[$fullCol]['width'] == 'auto')
						$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setAutoSize(true);
					else
						$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setWidth($columnDescriptions[$fullCol]['width']);
				}
				else
				{
					$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setWidth(12);
				}

				if (isset($columnDescriptions[$fullCol]['type']))
				{
					if ($columnDescriptions[$fullCol]['type'] == 'text')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					}
					else if ($columnDescriptions[$fullCol]['type'] == 'percent')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
					}
					else if ($columnDescriptions[$fullCol]['type'] == 'percent_as_int')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
					}						
					else if ($columnDescriptions[$fullCol]['type'] == 'currency')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
					}
				    else if ($columnDescriptions[$fullCol]['type'] == 'currency_no_cents')
				    {
				        $objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
				    }
				    else if ($columnDescriptions[$fullCol]['type'] == 'datetime')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME_DREAM_DINNERS);
					}
					else if ($columnDescriptions[$fullCol]['type'] == 'y_axis_labels')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					}
					else if ($columnDescriptions[$fullCol]['type'] == 'URL')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setUnderline(true);
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLUE ) );
					}
					else if ($columnDescriptions[$fullCol]['type'] == 'number')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
					}
					else if ($columnDescriptions[$fullCol]['type'] == 'number_x')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_0);
					}
                    else if ($columnDescriptions[$fullCol]['type'] == 'number_xxx')
                    {
                        $objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_000);
                    }
                    else if ($columnDescriptions[$fullCol]['type'] == 'number_w_parens')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);
					}
				}

				if (isset($columnDescriptions[$fullCol]['align']))
				{
					if ($columnDescriptions[$fullCol]['align'] == 'left')
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					else if ($columnDescriptions[$fullCol]['align'] == 'center')
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					else if ($columnDescriptions[$fullCol]['align'] == 'right')
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				}
				
				if (isset($columnDescriptions[$fullCol]['wrap']) && $columnDescriptions[$fullCol]['wrap'])
				{
					$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setWrapText(true);
				}
				
				if (isset($columnDescriptions[$fullCol]['decor']))
				{
					if ($columnDescriptions[$fullCol]['decor'] == 'subtotal')
					{
						$styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
								'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
								'borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )),
								'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('argb' => 'FFA8B355')));

						$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
					}
					else if ($columnDescriptions[$fullCol]['decor'] == 'majortotal')
					{
						$styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
								'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
								'borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )),
								'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('argb' => 'FF95B3D7')));

						$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
					}
					else if ($columnDescriptions[$fullCol]['decor'] == 'fixed')
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setBold(true);
						$tempThird = $thirdColLetter;
						$tempSecond = $colSecondChar;
						$tempCol = $col;

						incrementColumn($tempThird, $tempSecond, $tempCol);

						$freezeLeftmostColumn = $tempThird . $tempSecond . $tempCol;
					}
					else if ($columnDescriptions[$fullCol]['decor'] == 'left_border')
					{
						$styleArray = array('borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
					}
					else if ($columnDescriptions[$fullCol]['decor'] == 'left_border_heavy')
					{
						$styleArray = array('borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )));
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
					}

					else if ($columnDescriptions[$fullCol]['decor'] == 'yes_no_condform')
					{
						$objConditional1 = new PHPExcel_Style_Conditional();
						$objConditional1->setConditionType(PHPExcel_Style_Conditional::CONDITION_CONTAINSTEXT)->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_CONTAINSTEXT)->setText("no");
						$objConditional1->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getEndColor()->setARGB('FFFFAAAA');
						
						$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($colRange)->getConditionalStyles();
						array_push($conditionalStyles, $objConditional1);
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->setConditionalStyles($conditionalStyles);
					}
				}
			}
			else
			{
				$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setWidth(12);
			}

			incrementColumn($thirdColLetter, $colSecondChar, $col);
		}

 // TODO: handle empty data set

		// set the data cells
		$row = $labelsRow + 1;
		foreach($rows as $data)
		{
			$col = "A";
			$colSecondChar = "";
			$thirdColLetter = "";
			if ($headerColCount == 0)
			{
				$headerColCount = count($data);
			}

			//first figure out how many cells are merged
			$numMerges = 0;
			foreach($data as $colName => $datum)
			{
				if (strpos($datum, "|->") !== false)
				{
					$mergeDirective = substr($datum, strpos($datum, "|->"));
					$mergeDirective = str_replace("|->", "", $mergeDirective);
					$numMerges += ($mergeDirective - 1);
				}
			}

			$padSize = $headerColCount - $numMerges;
			if ($padSize > 0)
			{
				$data = array_pad($data, $padSize, "");
			}


			foreach($data as $colName => $datum)
			{
			    if (strpos($datum, "|->") !== false)
			    {
			        $mergeDirective = substr($datum, strpos($datum, "|->"));
			        $datum = substr($datum, 0, strpos($datum, "|->"));
			        
			        $mergeDirective = str_replace("|->", "", $mergeDirective);
			        CLog::Assert(is_numeric($mergeDirective) && $mergeDirective > 0 && $mergeDirective < 25, "Invalid Merge Directive - while parsing data for Excel File: $mergeDirective");
			        
			        $firstColumn = $thirdColLetter . $colSecondChar. $col;
			        
			        for($i = 0; $i < $mergeDirective - 1; $i++)
			        {
			            incrementColumn($thirdColLetter, $colSecondChar, $col);
			        }
			        
			        $lastColumn = $thirdColLetter . $colSecondChar . $col;
			        
			        $objPHPExcel->getActiveSheet()->mergeCells("$firstColumn$row:$lastColumn$row");
			        $objPHPExcel->getActiveSheet()->setCellValue($firstColumn. $row, $datum);
			        $objPHPExcel->getActiveSheet()->getStyle($firstColumn. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			        
			    }
			    else if (strpos($datum, "|=>") !== false)
			    {
			        $formatDirective = substr($datum, strpos($datum, "|=>"));
			        $datum = substr($datum, 0, strpos($datum, "|=>"));
			        
			        $formatDirective = str_replace("|=>", "", $formatDirective);
			        
			            if ($formatDirective == 'text')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			            }
			            else if ($formatDirective == 'percent')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			            }
			            else if ($formatDirective == 'percent_as_int')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			            }
			            else if ($formatDirective == 'currency')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			            }
			            else if ($formatDirective == 'currency_no_cents')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
			            }
			            else if ($formatDirective == 'datetime')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME_DREAM_DINNERS);
			            }
			            else if ($formatDirective == 'y_axis_labels')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getFont()->setBold(true);
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			            }
			            else if ($formatDirective == 'URL')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getFont()->setUnderline(true);
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLUE ) );
			            }
			            else if ($formatDirective == 'number')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
			            }
			            else if ($formatDirective == 'number_x')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_0);
			            }
			            else if ($formatDirective == 'number_w_parens')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);
			            }
			            else if ($formatDirective == 'decimal_2')
			            {
			                $objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			            }
			            
			            
			            $objPHPExcel->getActiveSheet()->setCellValue($thirdColLetter . $colSecondChar. $col . $row, $datum);
			            
			    }
			    else 
			    {
			    
				    $objPHPExcel->getActiveSheet()->setCellValue($thirdColLetter . $colSecondChar. $col . $row, $datum);
			    }

				$bottomRightExtent = $thirdColLetter . $colSecondChar . $col . $row;

				if ($callbacks && isset($callbacks['cell_callback']))
				{
					$callbacks['cell_callback']($objPHPExcel->getActiveSheet(),$colName, $datum, $thirdColLetter . $colSecondChar . $col, $row);
				}

				incrementColumn($thirdColLetter, $colSecondChar, $col);

			}

			if ($callbacks && isset($callbacks['row_callback']))
			{
				$callbacks['row_callback']($objPHPExcel->getActiveSheet(), $data, $row, $bottomRightExtent);
			}

			$row++;
		}


		if ($callbacks && isset($callbacks['final_render']))
		{
		    $callbacks['final_render']($objPHPExcel->getActiveSheet(), $rows);
		}
		
		
		
		$rowCount = count($rows);

		$fileName = str_replace(array( "*", ":", "/", "\\", "?", '[', ']'), array( "-", "-", "-", "-", "-", '-', '-'), $fileName);
		$sheetName = substr($fileName, 0, 31);

		if ($labelsRow && !$suppressLabelsDisplay && !isset($overrideValues['suppress_auto_filter']))
		{
			$objPHPExcel->getActiveSheet()->setAutoFilter("A" . $labelsRow . ":" .$bottomRightExtent);
		}

		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle($sheetName);



		if (isset($overrideValues['pane_freeze_cell']))
        {
            $objPHPExcel->getActiveSheet()->freezePane($overrideValues['pane_freeze_cell']);
        }
        else if ($freezeLeftmostColumn)
		{
			$objPHPExcel->getActiveSheet()->freezePane($freezeLeftmostColumn . ($labelsRow + 1));
		}
		else
		{
			$objPHPExcel->getActiveSheet()->freezePane('A' . ($labelsRow + 1));
		}



		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

	} catch(Exception $e) {

		echo $e->getMessage();
		throw $e;
	}

	
	if ($returnHTML)
	{
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
	    $styles = $objWriter->generateStyles(true);
	    
	    $linksTargetNewTab = false;
	    
	    if (isset($overrideValues['links_target_new_tab']))
	    {
	        $linksTargetNewTab = true;
	    }
	        
	    return array( $styles, $objWriter->generateSheetData($linksTargetNewTab));
	}
	else if ($saveFilePath)
	{
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($saveFilePath);
	}
	else 
	{
    	// Redirect output to a client's web browser (Excel2007)
    	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    	header("Content-Disposition: attachment;filename=\"$fileName.xlsx\"");
    	header('Cache-Control: max-age=0');
    
    	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    	$objWriter->save('php://output');
	   exit;
	}
}




function writeExcelFileMultiSheet ($fileName, $header, $sheets, $showHeader=true, $titleRows = false, $columnDescriptions = false,
						$headersAreEmbedded = false, $callbacks = false, $superHeader = false, $returnHTML = false, $suppressLabelsDisplay = false,
						$overrideValues = false, $use_Lib_1_8 = false, $saveFilePath = false)
{

	/** Include PHPExcel */

	if ($use_Lib_1_8)
	{
		require_once("phplib/PHPExcel_1.8/PHPExcel.php");
	}
	else
	{
		require_once("phplib/PHPExcel/PHPExcel.php");
	}

	try {

		set_time_limit(3600);

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		$AdminUser = CUser::getCurrentUser();

		if (!empty($AdminUser->firstname))
		{
			// Set document properties
			$objPHPExcel->getProperties()->setCreator($AdminUser->firstname . " " . $AdminUser->lastname)
				->setLastModifiedBy($AdminUser->firstname . " " . $AdminUser->lastname)
				->setTitle($fileName)
				->setKeywords("office 2007 openxml php")
				->setCategory("Dream Dinners report file");
		}
		else
		{
			// Set document properties
			$objPHPExcel->getProperties()->setTitle("test")
				->setKeywords("office 2007 openxml php")
				->setCategory("Dream Dinners report file");
		}

		$sheetCount = 0;
		foreach($sheets as $sheet_name => $sheet){

			if($sheetCount > 0){
				$sheetClone = $objPHPExcel->getActiveSheet()->copy();
				//$sheetClone->disconnectCells();
				$sheetClone->setTitle($sheet_name);
				$objPHPExcel->addSheet($sheetClone,$sheetCount);
			}

			$objPHPExcel->setActiveSheetIndex($sheetCount);
			$rows = $sheet;


			$labelsRow = 1;
			$numTitlesRows = (!empty($titleRows) ? count($titleRows) : 0);

			// Title rows are displayed above the column headers
			if ($numTitlesRows) {

				$labelsRow += $numTitlesRows;
				$row = 1;

				if ($titleRows[0] == "small placeholder")
				{
					foreach ($titleRows as $thisTitleRow)
					{
						$objPHPExcel->getActiveSheet()->mergeCells("A$row:I$row");

						$row++;
					}
				}
				else if ($titleRows[0] == "placeholder")
				{
					foreach ($titleRows as $thisTitleRow)
					{
						$objPHPExcel->getActiveSheet()->mergeCells("A$row:D$row");
						$objPHPExcel->getActiveSheet()->mergeCells("E$row:K$row");

						$row++;
					}
				}
				else
				{
					foreach ($titleRows as $thisTitleRow)
					{
						$finalCol = "Z";
						if (!empty($overrideValues['titleRowFinalColumn']))
						{
							$finalCol = $overrideValues['titleRowFinalColumn'];
						}

						$objPHPExcel->getActiveSheet()->mergeCells("A$row:$finalCol$row");
						$str = implode(" ", $thisTitleRow);
						$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $str);

						$styleArray = array('font' => array('bold' => true, 'size' => 12),
											'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'wrap' => false),
											'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_NONE)),
											'fill' => array('type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array('argb' => 'FFFDFBE5',), 'endcolor' => array('argb' => 'FFF1DB3C')));

						$objPHPExcel->getActiveSheet()->getStyle("A$row:J$row")->applyFromArray($styleArray);

						$row++;
					}
				}
			}
			else
			{
				$row = 1;
			}

			if ($headersAreEmbedded)
			{
				$showHeader = true;

				if (isset($rows[0]))
					$header = array_keys($rows[0]);
				else
				{
					$header = array_keys(current($rows));
					reset($rows);

				}
			}


			if ($superHeader)
			{

				$col = "A";
				$colSecondChar = "";
				$thirdColLetter = "";

				if (isset($overrideValues['header_gradient_start']))
				{
					$gradient_start = $overrideValues['header_gradient_start'];
				}
				else
				{
					$gradient_start = 'FFE3F072';
				}

				if (isset($overrideValues['header_gradient_end']))
				{
					$gradient_end = $overrideValues['header_gradient_end'];
				}
				else
				{
					$gradient_end = 'FFA8B355';
				}



				foreach($superHeader as $name => $columns)
				{
					$firstColumn = $thirdColLetter . $colSecondChar . $col;

					for ($x = 0; $x < $columns - 1; $x++)
					{
						incrementColumn($thirdColLetter, $colSecondChar, $col);
					}

					$lastColumn = $thirdColLetter . $colSecondChar . $col;

					$objPHPExcel->getActiveSheet()->mergeCells("$firstColumn$row:$lastColumn$row");
					$objPHPExcel->getActiveSheet()->setCellValue($firstColumn. $row, $name);

					$styleArray = array('borders' => array('right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
					$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter .$colSecondChar . $col . $row)->applyFromArray($styleArray);

					incrementColumn($thirdColLetter, $colSecondChar, $col);
				}



				$styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
									 'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
									 'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => $gradient_start, ), 'endcolor' => array( 'argb' => $gradient_end)));

				$objPHPExcel->getActiveSheet()->getStyle("A$labelsRow:$lastColumn" . $row)->applyFromArray($styleArray);

				$labelsRow++;
			}

			$headerColCount = 0;
			if ($showHeader)
			{
				// create and format the header cells (labels)
				$row = $labelsRow;
				$col = "A";
				$colSecondChar = "";
				$thirdColLetter = "";

				$headerExcpetions = array();
				foreach($header as $thisColumn)
				{

					$headerColCount++;
					$lastColumn = $thirdColLetter . $colSecondChar. $col;

					if (!$suppressLabelsDisplay)
					{
						$objPHPExcel->getActiveSheet()->setCellValue($thirdColLetter . $colSecondChar . $col . $row, $thisColumn);

						$styleArray = array('borders' => array('right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
						$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter .$colSecondChar . $col . $row)->applyFromArray($styleArray);


						if (isset($columnDescriptions[$lastColumn]['decor']) && $columnDescriptions[$lastColumn]['decor'] == 'majortotal')
							$headerExcpetions[$lastColumn] = 'majortotal';

					}

					incrementColumn($thirdColLetter, $colSecondChar, $col);
				}

				if (!$suppressLabelsDisplay)
				{

					if (isset($overrideValues['main_header_height']))
					{
						$objPHPExcel->getActiveSheet()->getRowDimension($labelsRow)->setRowHeight($overrideValues['main_header_height']);
					}
					else
					{
						$objPHPExcel->getActiveSheet()->getRowDimension($labelsRow)->setRowHeight(44);
					}


					if (isset($overrideValues['header_gradient_start']))
					{
						$gradient_start = $overrideValues['header_gradient_start'];
					}
					else
					{
						$gradient_start = 'FFE3F072';
					}

					if (isset($overrideValues['header_gradient_end']))
					{
						$gradient_end = $overrideValues['header_gradient_end'];
					}
					else
					{
						$gradient_end = 'FFA8B355';
					}



					$styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
										 'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
										 'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )),
										 'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => $gradient_start, ), 'endcolor' => array( 'argb' => $gradient_end)));

					$objPHPExcel->getActiveSheet()->getStyle("A$labelsRow:$lastColumn" . $labelsRow)->applyFromArray($styleArray);

					if (count($headerExcpetions))
					{
						$styleArray2 = array( 'font' => array( 'bold' => true, 'size' => 10 ),
											  'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
											  'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )),
											  'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => 'FF95B3D7' ), 'endcolor' => array( 'argb' => 'FF90AED2')));

						foreach ($headerExcpetions as $col => $type)
						{
							// the only type is majortotal right now

							$objPHPExcel->getActiveSheet()->getStyle($col . $labelsRow)->applyFromArray($styleArray2);
						}
					}
				}
			}
			else
			{
				$labelsRow = 0;
			}

			$freezeLeftmostColumn = false;

			$col = 'A';
			$colSecondChar = "";
			$thirdColLetter = "";


			for ($x = 0; $x < $headerColCount; $x++)
			{
				// apply formatting
				$colRange = $thirdColLetter . $colSecondChar. $col . ($labelsRow + 1) . ":" . $thirdColLetter . $colSecondChar.  $col . (count($rows) + $labelsRow);

				$fullCol = $thirdColLetter . $colSecondChar . $col;

				if ($columnDescriptions && isset($columnDescriptions[$fullCol]))
				{

					if (isset($columnDescriptions[$fullCol]['width']))
					{
						if ($columnDescriptions[$fullCol]['width'] == 'auto')
							$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setAutoSize(true);
						else
							$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setWidth($columnDescriptions[$fullCol]['width']);
					}
					else
					{
						$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setWidth(12);
					}

					if (isset($columnDescriptions[$fullCol]['type']))
					{
						if ($columnDescriptions[$fullCol]['type'] == 'text')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'percent')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'percent_as_int')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'currency')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'currency_no_cents')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'datetime')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME_DREAM_DINNERS);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'y_axis_labels')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'URL')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setUnderline(true);
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLUE ) );
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'number')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'number_x')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_0);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'number_xxx')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_000);
						}
						else if ($columnDescriptions[$fullCol]['type'] == 'number_w_parens')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);
						}
					}

					if (isset($columnDescriptions[$fullCol]['align']))
					{
						if ($columnDescriptions[$fullCol]['align'] == 'left')
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						else if ($columnDescriptions[$fullCol]['align'] == 'center')
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						else if ($columnDescriptions[$fullCol]['align'] == 'right')
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					}

					if (isset($columnDescriptions[$fullCol]['wrap']) && $columnDescriptions[$fullCol]['wrap'])
					{
						$objPHPExcel->getActiveSheet()->getStyle($colRange)->getAlignment()->setWrapText(true);
					}

					if (isset($columnDescriptions[$fullCol]['decor']))
					{
						if ($columnDescriptions[$fullCol]['decor'] == 'subtotal')
						{
							$styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
												 'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
												 'borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )),
												 'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('argb' => 'FFA8B355')));

							$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
						}
						else if ($columnDescriptions[$fullCol]['decor'] == 'majortotal')
						{
							$styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
												 'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ),
												 'borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )),
												 'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('argb' => 'FF95B3D7')));

							$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
						}
						else if ($columnDescriptions[$fullCol]['decor'] == 'fixed')
						{
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->getFont()->setBold(true);
							$tempThird = $thirdColLetter;
							$tempSecond = $colSecondChar;
							$tempCol = $col;

							incrementColumn($tempThird, $tempSecond, $tempCol);

							$freezeLeftmostColumn = $tempThird . $tempSecond . $tempCol;
						}
						else if ($columnDescriptions[$fullCol]['decor'] == 'left_border')
						{
							$styleArray = array('borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
						}
						else if ($columnDescriptions[$fullCol]['decor'] == 'left_border_heavy')
						{
							$styleArray = array('borders' => array('left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )));
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->applyFromArray($styleArray);
						}

						else if ($columnDescriptions[$fullCol]['decor'] == 'yes_no_condform')
						{
							$objConditional1 = new PHPExcel_Style_Conditional();
							$objConditional1->setConditionType(PHPExcel_Style_Conditional::CONDITION_CONTAINSTEXT)->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_CONTAINSTEXT)->setText("no");
							$objConditional1->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getEndColor()->setARGB('FFFFAAAA');

							$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($colRange)->getConditionalStyles();
							array_push($conditionalStyles, $objConditional1);
							$objPHPExcel->getActiveSheet()->getStyle($colRange)->setConditionalStyles($conditionalStyles);
						}
					}
				}
				else
				{
					$objPHPExcel->getActiveSheet()->getColumnDimension($fullCol)->setWidth(12);
				}

				incrementColumn($thirdColLetter, $colSecondChar, $col);
			}

			// TODO: handle empty data set

			// set the data cells
			$row = $labelsRow + 1;
			foreach($rows as $data)
			{
				$col = "A";
				$colSecondChar = "";
				$thirdColLetter = "";
				if ($headerColCount == 0)
				{
					$headerColCount = count($data);
				}

				//first figure out how many cells are merged
				$numMerges = 0;
				foreach($data as $colName => $datum)
				{
					if (strpos($datum, "|->") !== false)
					{
						$mergeDirective = substr($datum, strpos($datum, "|->"));
						$mergeDirective = str_replace("|->", "", $mergeDirective);
						$numMerges += ($mergeDirective - 1);
					}
				}

				$padSize = $headerColCount - $numMerges;
				if ($padSize > 0)
				{
					$data = array_pad($data, $padSize, "");
				}


				foreach($data as $colName => $datum)
				{
					if (strpos($datum, "|->") !== false)
					{
						$mergeDirective = substr($datum, strpos($datum, "|->"));
						$datum = substr($datum, 0, strpos($datum, "|->"));

						$mergeDirective = str_replace("|->", "", $mergeDirective);
						CLog::Assert(is_numeric($mergeDirective) && $mergeDirective > 0 && $mergeDirective < 25, "Invalid Merge Directive - while parsing data for Excel File: $mergeDirective");

						$firstColumn = $thirdColLetter . $colSecondChar. $col;

						for($i = 0; $i < $mergeDirective - 1; $i++)
						{
							incrementColumn($thirdColLetter, $colSecondChar, $col);
						}

						$lastColumn = $thirdColLetter . $colSecondChar . $col;

						$objPHPExcel->getActiveSheet()->mergeCells("$firstColumn$row:$lastColumn$row");
						$objPHPExcel->getActiveSheet()->setCellValue($firstColumn. $row, $datum);
						$objPHPExcel->getActiveSheet()->getStyle($firstColumn. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

					}
					else if (strpos($datum, "|=>") !== false)
					{
						$formatDirective = substr($datum, strpos($datum, "|=>"));
						$datum = substr($datum, 0, strpos($datum, "|=>"));

						$formatDirective = str_replace("|=>", "", $formatDirective);

						if ($formatDirective == 'text')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
						}
						else if ($formatDirective == 'percent')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
						}
						else if ($formatDirective == 'percent_as_int')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
						}
						else if ($formatDirective == 'currency')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
						}
						else if ($formatDirective == 'currency_no_cents')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
						}
						else if ($formatDirective == 'datetime')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME_DREAM_DINNERS);
						}
						else if ($formatDirective == 'y_axis_labels')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
						}
						else if ($formatDirective == 'URL')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getFont()->setUnderline(true);
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLUE ) );
						}
						else if ($formatDirective == 'number')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
						}
						else if ($formatDirective == 'number_x')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_0);
						}
						else if ($formatDirective == 'number_w_parens')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);
						}
						else if ($formatDirective == 'decimal_2')
						{
							$objPHPExcel->getActiveSheet()->getStyle($thirdColLetter . $colSecondChar. $col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
						}


						$objPHPExcel->getActiveSheet()->setCellValue($thirdColLetter . $colSecondChar. $col . $row, $datum);

					}
					else
					{

						$objPHPExcel->getActiveSheet()->setCellValue($thirdColLetter . $colSecondChar. $col . $row, $datum);
					}

					$bottomRightExtent = $thirdColLetter . $colSecondChar . $col . $row;

					if ($callbacks && isset($callbacks['cell_callback']))
					{
						$callbacks['cell_callback']($objPHPExcel->getActiveSheet(),$colName, $datum, $thirdColLetter . $colSecondChar . $col, $row);
					}

					incrementColumn($thirdColLetter, $colSecondChar, $col);

				}

				if ($callbacks && isset($callbacks['row_callback']))
				{
					$callbacks['row_callback']($objPHPExcel->getActiveSheet(), $data, $row, $bottomRightExtent);
				}

				$row++;
			}


			if ($callbacks && isset($callbacks['final_render']))
			{
				$callbacks['final_render']($objPHPExcel->getActiveSheet(), $rows);
			}



			$rowCount = count($rows);

			$fileName = str_replace(array( "*", ":", "/", "\\", "?", '[', ']'), array( "-", "-", "-", "-", "-", '-', '-'), $fileName);

			if ($labelsRow && !$suppressLabelsDisplay && !isset($overrideValues['suppress_auto_filter']))
			{
				$objPHPExcel->getActiveSheet()->setAutoFilter("A" . $labelsRow . ":" .$bottomRightExtent);
			}

			// Rename worksheet
			if($sheetCount == 0){
				$objPHPExcel->getActiveSheet()->setTitle($sheet_name);
			}



			if (isset($overrideValues['pane_freeze_cell']))
			{
				$objPHPExcel->getActiveSheet()->freezePane($overrideValues['pane_freeze_cell']);
			}
			else if ($freezeLeftmostColumn)
			{
				$objPHPExcel->getActiveSheet()->freezePane($freezeLeftmostColumn . ($labelsRow + 1));
			}
			else
			{
				$objPHPExcel->getActiveSheet()->freezePane('A' . ($labelsRow + 1));
			}



			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$sheetCount++;

	}


	} catch(Exception $e) {

		echo $e->getMessage();
		throw $e;
	}

	if ($returnHTML)
	{
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
		$styles = $objWriter->generateStyles(true);

		$linksTargetNewTab = false;

		if (isset($overrideValues['links_target_new_tab']))
		{
			$linksTargetNewTab = true;
		}

		return array( $styles, $objWriter->generateSheetData($linksTargetNewTab));
	}
	else if ($saveFilePath)
	{
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($saveFilePath);
	}
	else
	{
		// Redirect output to a client's web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"$fileName.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
}

?>