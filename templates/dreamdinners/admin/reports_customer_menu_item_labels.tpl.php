<?php
// INTERFACE IS ZERO THEN DOING A STRAIGHT PRINT OF LABELS

if ($this->success == true && $this->interface == 0)
{
	require_once("fpdf/dream_labels.php");

	//$NutritionLabelBGImage = "/ft_menu_label.jpg";
	$NutritionLabelBGImage = "/ft_menu_label_no_title.jpg";

	$pdf = new dream_labels('8164', 'mm', 1, 1);
	$pdf->Open();
	// $pdf->SetFont('arial','',14);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTitle("Dream Dinners");

	$pdf->SetStyle("p", "helvetica", "", 8, "130,0,30");
	$pdf->SetStyle("pb", "helvetica", "B", 8, "130,0,30");
	$pdf->SetStyle("t1", "helvetica", "", 8, "0,0,0");
	$pdf->SetStyle("t7h", "helvetica", "B", 7, "0,0,0");
	$pdf->SetStyle("t8", "helvetica", "B", 8, "0,0,0");
	$pdf->SetStyle("t8h", "helvetica", "B", 8, "0,0,0");
	$pdf->SetStyle("t9", "helvetica", "BI", 8, "0,0,0");
	$pdf->SetStyle("t5", "helvetica", "", 5, "0,0,0");

	$pdf->SetStyle("tt", "helvetica", "", 5, "0,0,0");
	$pdf->SetStyle("ttb", "helvetica", "B", 5, "0,0,0");

	$pdf->SetStyle("ts", "times", "B", 6, "0,0,0");

	$pdf->SetStyle("tft", "helvetica", "", 6, "0,0,0");
	$pdf->SetStyle("tfth", "helvetica", "", 6, "0,0,0");
	$pdf->SetStyle("tftb", "helvetica", "B", 6, "0,0,0");

	$pdf->SetStyle("txs", "helvetica", "", 6, "0,0,0");
	$pdf->SetStyle("tx", "helvetica", "", 7, "0,0,0");
	$pdf->SetStyle("t1b", "helvetica", "B", 8, "0,0,0");
	$pdf->SetStyle("t1", "helvetica", "", 8, "0,0,0");
	$pdf->SetStyle("t2b", "helvetica", "B", 9, "0,0,0");
	$pdf->SetStyle("t3b", "helvetica", "B", 9, "0,0,0");
	$pdf->SetStyle("t4b", "helvetica", "B", 13, "0,0,0");

	$pdf->SetStyle("h11", "helvetica", "", 11, "0,0,0");
	$pdf->SetStyle("h11b", "helvetica", "B", 11, "0,0,0");

	$pdf->SetStyle("t2", "helvetica", "", 9, "0,0,0");
	$pdf->SetStyle("t3", "helvetica", "", 9, "0,0,0");
	$pdf->SetStyle("t3c", "helvetica", "B", 9, "149,154,33");
	$pdf->SetStyle("t4", "helvetica", "", 7, "0,0,0");
	$pdf->SetStyle("t5c", "helvetica", "B", 9, "214,90,2");
	$pdf->SetStyle("hh", "helvetica", "B", 11, "255,189,12");
	$pdf->SetStyle("font", "helvetica", "", 10, "0,0,255");
	$pdf->SetStyle("style", "helvetica", "BI", 10, "0,0,220");
	$pdf->SetStyle("size", "times", "BI", 13, "0,0,120");
	$pdf->SetStyle("color", "times", "BI", 13, "0,0,255");
	$pdf->SetStyle("tc", "times", "B", 14, "92, 102, 114");

	$showBorders = $this->show_borders;

	$offsetX = 80;
	$offsetY = 74;
	$offsetXCard = 22;
	$offsetYCard = -15;

	$suppressFastlane = $this->suppressFastlane;

	$imageHeight = 12;

	$instructionTitle = "<t1>COOKING INSTRUCTIONS</t1>";
	$ServingTitle = "<t1>SERVING SUGGESTIONS</t1>";

	if (isset($this->store_name) && isset($this->store_phone))
	{
		$other = sprintf("<t4>%s (%s)</t4>", $this->store_name, $this->store_phone);
	}

	$bgImagePath = APP_BASE . 'www' . ADMIN_IMAGES_PATH;

	$backgroundPageArray = array();
	// create our background page arrays
	$ItemsPerPage = 6;
	$page = 0;
	$counter = 0;

	$canBreak = isset($_REQUEST['break']) ? $_REQUEST['break'] : 0;

	$lastorderid = 0;
	$curOrderID = 0;
	$iterPageBreaks = false;

	if ($canBreak == true)
	{
		foreach ($this->order_details as $entity)
		{

			if (isset($entity['is_finishing_touch']) && $entity['is_finishing_touch'])
			{
				$curOrderID = $entity['order_id'];
				if ($lastorderid == 0 || $iterPageBreaks == true)
				{
					$lastorderid = $entity['order_id'];
					$iterPageBreaks = false;
				}

				if ($curOrderID == $lastorderid)
				{
					$path = $bgImagePath . $NutritionLabelBGImage;
				}
				else
				{
					$iterPageBreaks = true;
					if ($counter > 0)
					{
						$path = $bgImagePath . '/pdf_space.jpg';
						for ($i = $counter; $i < $ItemsPerPage; $i++)
						{
							$backgroundPageArray[$page][$i] = $path;
						}
						$page++;
					}

					$counter = 0;
					$path = $bgImagePath . $NutritionLabelBGImage;

					$backgroundPageArray[$page][$counter] = $path;
					$counter++;
					continue;
				}

				$backgroundPageArray[$page][$counter] = $path;
				if ($counter >= $ItemsPerPage - 1)
				{
					$counter = 0;
					$page++;
				}
				else
				{
					$counter++;
				}
				// if ($iterPageBreaks == false) $lastorderid = $curOrderID;
				$lastorderid = $curOrderID;
			}
			else
			{
				$pdf->storeImageDetails($backgroundPageArray, 101, 84);
				$curOrderID = $entity['order_id'];
				if ($lastorderid == 0 || $iterPageBreaks == true)
				{
					$lastorderid = $entity['order_id'];
					$iterPageBreaks = false;
				}

				if ($curOrderID == $lastorderid)
				{

					$path = $bgImagePath . '/menu_label_no_size.jpg';
				}
				else
				{
					$iterPageBreaks = true;
					if ($counter > 0)
					{
						$path = $bgImagePath . '/pdf_space.jpg';
						for ($i = $counter; $i < $ItemsPerPage; $i++)
						{
							$backgroundPageArray[$page][$i] = $path;
						}
						$page++;
					}

					$counter = 0;
					// go back and grab the item that was in the last entity

					$path = $bgImagePath . '/menu_label_no_size.jpg';

					$backgroundPageArray[$page][$counter] = $path;
					$counter++;
					continue;
				}

				$backgroundPageArray[$page][$counter] = $path;
				if ($counter >= $ItemsPerPage - 1)
				{
					$counter = 0;
					$page++;
				}
				else
				{
					$counter++;
				}
				// if ($iterPageBreaks == false) $lastorderid = $curOrderID;
				$lastorderid = $curOrderID;
			}
		}
	}
	else
	{
		foreach ($this->order_details as $entity)
		{
			if (isset($entity['is_finishing_touch']) && $entity['is_finishing_touch'])
			{

				$path = $bgImagePath . $NutritionLabelBGImage;

				$backgroundPageArray[$page][$counter] = $path;
				if ($counter >= $ItemsPerPage - 1)
				{
					$counter = 0;
					$page++;
				}
				else
				{
					$counter++;
				}
			}
			else
			{
				$pdf->storeImageDetails($backgroundPageArray, 101, 84);

				$path = $bgImagePath . '/menu_label_no_size.jpg';

				$backgroundPageArray[$page][$counter] = $path;
				if ($counter >= $ItemsPerPage - 1)
				{
					$counter = 0;
					$page++;
				}
				else
				{
					$counter++;
				}
			}
		}
	}

	reset($this->order_details);

	// ------------------------------------------------
	$lastorderid = 0;
	$curOrderID = 0;
	$iterPageBreaks = false;
	$page = 0;
	$counter = 0;
	$maxchars = 525; /// change layout based on character string size

	foreach ($this->order_details as $id => $entity)
	{
		$isFinishingTouch = (isset($entity['is_finishing_touch']) && $entity['is_finishing_touch']);

		if (isset($entity['order_id']))
		{
			$curOrderID = $entity['order_id'];
		}

		if ($lastorderid == 0 || $iterPageBreaks == true)
		{
			if (isset($entity['order_id']))
			{
				$lastorderid = $entity['order_id'];
			}
			$iterPageBreaks = false;
		}

		$customerdata = "<t8>%s</t8>";
		$sessiondata = "<t9>%s</t9>";

		$title = "<t3b>%s</t3b>"; // the menu item
		// $containertype = "<t1b>Container: %s</t1b>";
		$containertype = "";

		$servingType = "<t1b>%s</t1b>";

		$colx = $pdf->_getColX();
		$coly = $pdf->_getColY();

		$card = "x:" . $colx . "y:" . $coly;
		$height = $pdf->_getHeight();
		$width = $pdf->_getWidth();

		$customerdata = sprintf($customerdata, trim($entity['firstname']) . " " . trim($entity['lastname']));
		$sessiondata = sprintf($sessiondata, $entity['session_start']);
		// $servingSize = "6 Serving";  // temp
		$servingSize = CMenuItem::translateServingSizeToPricingTypeNoQuantity($entity['servings_per_item'], false);

		$servingSize = (($entity['servings_per_item'] == 6) ? '<t5c>' : '');
		$servingSize .= CMenuItem::translateServingSizeToPricingTypeNoQuantity($entity['servings_per_item'], false);
		$servingSize .= (($entity['servings_per_item'] == 6) ? '</t5c>' : '');
		// temp
		// if ($entity['plan_type'] == "HALF")  $servingSize = "3 Serving";
		$servingType = sprintf($servingType, $servingSize);
		// $containertype = sprintf($containertype, $entity['container_type']);

		if (isset($entity['is_finishing_touch']) && $entity['is_finishing_touch'])
		{
			if (strlen($entity['menu_item']) > 100)
			{
				$sub_title = substr($entity['menu_item'], 0, 94) . '...';
			}
			else
			{
				$sub_title = $entity['menu_item'];
			}
		}
		else
		{
			if (strlen($entity['menu_item']) > 70)
			{
				$sub_title = substr($entity['menu_item'], 0, 64) . '...';
			}
			else
			{
				$sub_title = $entity['menu_item'];
			}
		}

		$title = sprintf($title, $sub_title);

		$instructions = "";
		//	$test_instructions = "<t1b>%s</t1b>";
		$test_instructions = sprintf("<t1b>%s</t1b>", $entity['instructions']);

		$overrideLineHeight = 3.45;

		// $pdf->_Line_Height = 3.45; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;
		$lines = $pdf->NbLines($pdf->_Width, $test_instructions);

		$do_not_override = true;
		if ($entity['menu_item'] == "Down Home Apple Pie")
		{
			$do_not_override = false;
		}

		if ($lines < 14 && $do_not_override)
		{
			if ($lines < 5)
			{
				$test_instructions = sprintf("<t4b>%s</t4b>", $entity['instructions']);
				//    $pdf->_Line_Height = 5.0;// $pdf->_Get_Height_Chars(13)+$pdf->linespacing;
				$overrideLineHeight = 5.0;
			}
			else if ($lines < 7)
			{
				$test_instructions = sprintf("<t3b>%s</t3b>", $entity['instructions']);
				//   $pdf->_Line_Height = 4.5; // $pdf->_Get_Height_Chars(11)+$pdf->linespacing;
				$overrideLineHeight = 4.5;
			}
			else if ($lines < 10)
			{
				$test_instructions = sprintf("<t2b>%s</t2b>", $entity['instructions']);
				//   $pdf->_Line_Height = 4.0; //$pdf->_Get_Height_Chars(9)+$pdf->linespacing;
				$overrideLineHeight = 4.0;
			}
			else
			{
				$test_instructions = sprintf("<t1b>%s</t1b>", $entity['instructions']);
				$overrideLineHeight = 3.45; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;

			}
		}
		else if ($lines < 17 && $do_not_override)
		{
			$test_instructions = sprintf("<tx>%s</tx>", $entity['instructions']);
			$overrideLineHeight = 3.2; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;
		}
		else
		{
			$test_instructions = sprintf("<txs>%s</txs>", $entity['instructions']);
			$overrideLineHeight = 3.2; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;
		}

		$instructions = $test_instructions;

		if ($isFinishingTouch)
		{
			$serving = "%s";
		}
		else
		{
			$serving = "<t1b>%s</t1b>";
		}

		$test_serving = sprintf($serving, $entity['serving_suggestions']);
		$lines = $pdf->NbLines(($pdf->_Width * .75), $test_serving);

		if ($lines > 2)
		{
			$test_serving = sprintf("<ts>%s</ts>", $entity['serving_suggestions']);
		}
		else if ($lines == 1)
		{
			$test_serving .= "\n ";
		}

		$serving = $test_serving;

		if (isset($entity['store_name']) && isset($entity['store_phone']) && $entity['store_name'] != "Not Available")
		{
			$other = sprintf("<t5>%s (%s)</t5>", $entity['store_name'], $entity['store_phone']);
		}

		if (!empty($entity['prep_time']))
		{
			$txt5 = "<t1>TIME TO TABLE\n</t1><t8>" . $entity['prep_time'] . "     </t8>";
		}
		else
		{
			$txt5 = false;
		}

		$txt2 = false;
		$txt3 = false;
		$txt4 = false;

		$showInterface = isset ($_REQUEST["interface"]) ? $_REQUEST["interface"] : 0;
		if ($canBreak == false)
		{
			if ($showInterface == 0)
			{
				if (!empty($entity['serving_suggestions']))
				{
					$txt1 = sprintf("%s\n%s\n%s\n%s", $title, $servingType, $instructionTitle, $instructions);
					$txt2 = sprintf("%s", $customerdata);
					$txt3 = sprintf("%s", $sessiondata);
					$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, $serving, $other);
				}
				else
				{
					$txt1 = sprintf("%s\n%s\n%s\n%s", $title, $servingType, $instructionTitle, $instructions);
					$txt2 = sprintf("%s", $customerdata);
					$txt3 = sprintf("%s", $sessiondata);
					$txt4 = sprintf("\n\n\n\n\n%s", $other);
				}
			}
			else
			{
				if ($isFinishingTouch)
				{
					$txt1 = sprintf("%s", $title);
					$txt2 = sprintf("%s", $instructionTitle);
					$txt3 = sprintf("%s", $instructions);
					$txt4 = sprintf("%s", $serving);
				}
				else if (!empty($entity['serving_suggestions']))
				{
					$txt1 = sprintf("%s\n%s\n%s\n%s", $title, $servingType, $instructionTitle, $instructions);
					$txt3 = sprintf("%s", $sessiondata);
					$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, $serving, $other);
				}
				else
				{
					$txt1 = sprintf("%s\n%s\n\n%s\n%s", $title, $servingType, $instructionTitle, $instructions);
					$txt3 = sprintf("%s", $sessiondata);
					$txt4 = sprintf("\n\n\n\n\n%s", $other);
				}
			}
		}
		else
		{
			if ($curOrderID == $lastorderid)
			{
				$iterPageBreaks = false;
				$txt1 = sprintf("%s\n%s\n%s\n%s", $title, $servingType, $instructionTitle, $instructions);
				$txt2 = sprintf("%s", $customerdata);
				$txt3 = sprintf("%s", $sessiondata);
				$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, $serving, $other);
			}
			else
			{
				$iterPageBreaks = true;

				if ($counter > 0)
				{
					/*
					 *  Disable marketing label May 16, 2017
					 *
					$pdf->Add_Marketing_Label($this->storeObj, $showBorders);
					$counter++;
					*/

					for ($i = $counter; $i < $ItemsPerPage; $i++)
					{
						$pdf->Add_PDF_Label("", false, $showBorders);

						$yoff = $pdf->y - $imageHeight;
						$xoff = ($height * $colx) + $offsetX;
						if ($colx == 1)
						{
							$xoff += $offsetXCard;
						}
					}

					$page++;
				}

				$counter = 0;

				$txt1 = sprintf("%s\n%s\n%s\n%s", $title, $servingType, $instructionTitle, $instructions);
				$txt2 = sprintf("%s", $customerdata);
				$txt3 = sprintf("%s", $sessiondata);
				$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, $serving, $other);

				$pdf->Carls_Add_PDF_Label($entity, $txt1, false, $showBorders, $overrideLineHeight);
				$counter++;

				$yoff = $pdf->y - $imageHeight;
				$xoff = ($height * $colx) + $offsetX;
				if ($colx == 1)
				{
					$xoff += $offsetXCard;
				}

				continue;
			}
		}

		$entity['show_long_date'] = $this->showLongDate;

		if ($isFinishingTouch)
		{
			$pdf->Add_Finishing_Touch_PDF_Label($txt1, $txt2, $txt3, $txt4, $txt5, $showBorders, $overrideLineHeight, 0, $entity['store_name'], $entity['store_phone'], $entity);
		}
		else
		{
			$pdf->Carls_Add_PDF_Label($entity, $txt1, false, $showBorders, $overrideLineHeight);
		}

		if ($counter >= $ItemsPerPage - 1)
		{
			$counter = 0;
			$page++;
		}
		else
		{
			$counter++;
		}

		$yoff = $pdf->y - $imageHeight;
		$xoff = ($height * $colx) + $offsetX;
		if ($colx == 1)
		{
			$xoff += $offsetXCard;
		}

		$lastorderid = $curOrderID;
	}

	/*
	 * Disable marketing label May 16, 2017
	 *
	if (!empty($canBreak) || !empty($_REQUEST['booking_id']))
	{
		$pdf->Add_Marketing_Label($this->storeObj, $showBorders);
	}
	*/

	$pdf->Output();
}
else
{
	?>
	<?php $this->assign('page_title', 'Generic Labels'); ?>
	<?php $this->assign('topnav', 'reports'); ?>
	<?php $this->setOnLoad("init_label_reporting();"); ?>
	<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports_customer_menu_item_labels.min.js'); ?>
	<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<form method='post' onsubmit="submitIt(this); return true;">

		<div>
			<h3>Entr&eacute;e Labels</h3>
			<table>

				<?php if (isset($this->form_list['menus_html'])) { ?>
					<tr>
						<td>
							Pick a menu
						</td>

						<td>
							<?= $this->form_list['menus_html'] ?>

							<?php if (isset($this->form_list['menu_items_html'])) { ?>
								<div style="float:right"><!--input type="submit" class="btn btn-primary btn-sm" value="Generate All (6-Up)" name="report_all"/-->
									<input type="submit" class="btn btn-primary btn-sm" value="Generate All" name="report_all_four" />This will generate 2 pages for every
									<br /> menu item<i> (Note: This could take a while.)</i></div>
							<?php } ?>

						</td>
					</tr>
				<?php } ?>

				<?php if (isset($this->form_list['menu_items_html'])) { ?>
					<tr>
						<td>
							Pick a menu item
						</td>
						<td style='width:700px'>
							<input type="checkbox" id="zero_inv_filter" name="zero_inv_filter" /> Hide items in list with zero inventory<br />
							<?= $this->form_list['menu_items_html'] ?>
						</td>
					</tr>
				<?php } ?>

				<?php if (isset($this->form_list['labels_to_print_html'])) { ?>
					<tr>
						<td>
							Enter the number of <br />pages to print (up to 4)
						</td>
						<td>
							<?= $this->form_list['labels_to_print_html'] ?>
						</td>
					</tr>
				<?php } ?>

				<?php if (isset($this->form_list['labels_per_sheet_html'])) { ?>
					<tr style="display:none;">
						<td>
							Select the number<br />of labels per sheet
						</td>
						<td style='width:700px'>
							<?= $this->form_list['labels_per_sheet_html'] ?>
						</td>
					</tr>
				<?php } ?>
				<?php if (isset($this->form_list['date_format_html'])) { ?>
					<tr>
						<td>

						</td>
						<td style='width:700px'>
							<?= $this->form_list['date_format_html'] ?>
						</td>
					</tr>
				<?php } ?>
				<?php if (isset($this->form_list['report_submit_html'])) { ?>
					<tr>
						<td>&nbsp;</td>
						<td>
							<?= $this->form_list['report_submit_html'] ?><br />
							The labels will appear in a new window. To print labels for several menu items close the new window each time, exposing the selection form, and repeat the process.
						</td>
					</tr>
				<?php } ?>

			</table>
		</div>

		<div>
			<br />
			<hr />
			<br />

			<h3>Sides &amp; Sweets Labels</h3>

			<table>

				<?php if (isset($this->form_list['ft_menus_html'])) { ?>
					<tr>
						<td>
							Pick a menu
						</td>

						<td>
							<?= $this->form_list['ft_menus_html'] ?>

							<?php if (isset($this->form_list['ft_menu_items_html'])) { ?>
								<div style="float:right"><input type="submit" class="btn btn-primary btn-sm" value="Generate All" name="ft_report_all" /> This will generate 2 pages for every
									<br /> Sides &amp; Sweets item<i> (Note: This could take a while.)</i></div>
							<?php } ?>

						</td>
					</tr>
				<?php } ?>

				<?php if (isset($this->form_list['ft_menu_items_html'])) { ?>
					<tr>
						<td>
							Pick a Sides &amp; Sweets item
						</td>
						<td style='width:700px'>
							<input type="checkbox" id="ft_zero_inv_filter" name="ft_zero_inv_filter" /> Hide items in list with zero inventory<br />
							<?= $this->form_list['ft_menu_items_html'] ?>
						</td>
					</tr>
				<?php } ?>

				<?php if (isset($this->form_list['ft_labels_to_print_html'])) { ?>
					<tr>
						<td>
							Enter the number of <br />pages to print (up to 4)
						</td>
						<td>
							<?= $this->form_list['ft_labels_to_print_html'] ?>
						</td>
					</tr>
				<?php } ?>

				<?php if (isset($this->form_list['ft_labels_per_sheet_html'])) { ?>
					<tr>
						<td>
							Select the number<br />of labels per sheet
						</td>
						<td style='width:700px'>
							<?= $this->form_list['ft_labels_per_sheet_html'] ?>
						</td>
					</tr>
				<?php } ?>
				<?php if (isset($this->form_list['ft_date_format_html'])) { ?>
					<tr>
						<td>

						</td>
						<td style='width:700px'>
							<?= $this->form_list['ft_date_format_html'] ?>
						</td>
					</tr>
				<?php } ?>
				<?php if (isset($this->form_list['ft_report_submit_html'])) { ?>
					<tr>
						<td>&nbsp;</td>
						<td>
							<?= $this->form_list['ft_report_submit_html'] ?><br />
							The labels will appear in a new window. To print labels for several menu items close the new window each time, exposing the selection form, and repeat the process.
						</td>
					</tr>
				<?php } ?>

			</table>
		</div>

	</form>

	<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>

<?php } // end if $this->interface == 0 ?>