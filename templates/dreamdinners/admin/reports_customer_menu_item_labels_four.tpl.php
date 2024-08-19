<?php
// INTERFACE IS ZERO THEN DOING A STRAIGHT PRINT OF LABELS

if ($this->success == true && $this->interface == 0)
{
	require_once("fpdf/dream_labels.php");

	$NutritionLabelBGImage = "/ft_menu_label_four.jpg";

	$pdf = new dream_labels('5168', 'mm', 1, 1, 'L');
	$pdf->Open();
	// $pdf->SetFont('arial','',14);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTitle("Dream Dinners");

	$pdf->SetStyle("p", "helvetica", "", 8, "130,0,30");
	$pdf->SetStyle("pb", "helvetica", "B", 8, "130,0,30");
	$pdf->SetStyle("t1", "helvetica", "", 10, "0,0,0");
	$pdf->SetStyle("t8", "helvetica", "B", 8, "0,0,0");
	$pdf->SetStyle("t9", "helvetica", "BI", 8, "0,0,0");
	$pdf->SetStyle("t5", "helvetica", "", 5, "0,0,0");

	$pdf->SetStyle("tt", "helvetica", "", 5, "0,0,0");
	$pdf->SetStyle("ttb", "helvetica", "B", 5, "0,0,0");

	$pdf->SetStyle("ts", "helvetica", "B", 6, "0,0,0");

	$pdf->SetStyle("tft", "helvetica", "", 6, "0,0,0");
	$pdf->SetStyle("tftb", "helvetica", "B", 6, "0,0,0");

	$pdf->SetStyle("tx", "helvetica", "", 8, "0,0,0");
	$pdf->SetStyle("txb", "helvetica", "B", 7, "0,0,0");
	$pdf->SetStyle("t1b", "helvetica", "B", 9, "0,0,0");
	$pdf->SetStyle("t2b", "helvetica", "B", 9, "0,0,0");
	$pdf->SetStyle("t3b", "helvetica", "B", 11, "0,0,0");
	$pdf->SetStyle("t4b", "helvetica", "B", 13, "0,0,0");

	$pdf->SetStyle("h11", "helvetica", "", 11, "0,0,0");
	$pdf->SetStyle("h11b", "helvetica", "B", 11, "0,0,0");

	$pdf->SetStyle("t2", "helvetica", "", 11, "0,0,0");
	$pdf->SetStyle("t3", "helvetica", "B", 9, "0,0,0");
	//$pdf->SetStyle("t3c", "helvetica", "B", 9, "232, 119, 34");
	$pdf->SetStyle("t3c", "helvetica", "B", 12, "112, 116, 26");
	//$pdf->SetStyle("t3c", "helvetica", "B", 12, "149,154,33");
	$pdf->SetStyle("t4", "helvetica", "B", 7, "0,0,0");
	$pdf->SetStyle("t4p", "helvetica", "", 7, "0,0,0");
	$pdf->SetStyle("t5c", "helvetica", "B", 9, "214,90,2");
	$pdf->SetStyle("hh", "helvetica", "B", 11, "255,189,12");
	$pdf->SetStyle("font", "helvetica", "", 10, "0,0,255");
	$pdf->SetStyle("style", "helvetica", "BI", 10, "0,0,220");
	$pdf->SetStyle("size", "times", "BI", 13, "0,0,120");
	$pdf->SetStyle("color", "times", "B", 14, "92, 102, 114");
	//$pdf->SetStyle("bluecolor", "times", "B", 14, "92, 102, 114");

	$showBorders = false;
	$this->show_borders;

	$offsetX = 70;
	$offsetY = 74;
	$offsetXCard = 22;
	$offsetYCard = -15;

	$suppressFastlane = $this->suppressFastlane;

	$imageHeight = 16;

	$ServingTitle = "<t1>SERVING SUGGESTION</t1>";

	if (isset($this->store_name) && isset($this->store_phone))
	{
		$other = sprintf("<t4>%s (%s)</t4>", $this->store_name, $this->store_phone);
	}

	$bgImagePath = APP_BASE . 'www' . ADMIN_IMAGES_PATH;

	$backgroundPageArray = array();

	$ItemsPerPage = 4;

	$canBreak = isset($_REQUEST['break']) ? $_REQUEST['break'] : 0;
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

		$title = "<t3c>%s</t3c>"; // the menu item
		// $containertype = "<t1b>Container: %s</t1b>";
		$containertype = "";

		$servingType = "<t1b>%s%s</t1b>";

		$colx = $pdf->_getColX();
		$coly = $pdf->_getColY();

		$card = "x:" . $colx . "y:" . $coly;
		$height = $pdf->_getHeight();
		$width = $pdf->_getWidth();

		$customerdata = sprintf($customerdata, trim($entity['firstname']) . " " . trim($entity['lastname']));
		$sessiondata = sprintf($sessiondata, $entity['session_start']);
		// $servingSize = "6 Serving";  // temp

		$servingSize = (($entity['servings_per_item'] == 6) ? '<t5c>' : '');
		$servingSize .= CMenuItem::translateServingSizeToPricingTypeNoQuantity($entity['servings_per_item'], false);
		$servingSize .= (($entity['servings_per_item'] == 6) ? '</t5c> |' : ' |');

		$prepTime = $entity['prep_time'];

		if (!empty($prepTime))
		{
			$prepTime = "Prep Time " . $entity['prep_time'] . " | ";
		}

		$servingType = sprintf($servingType, $prepTime, $servingSize);

		$title = sprintf($title, $entity['menu_item']);

		$instructions = "";
		$test_instructions = sprintf("<t2>%s</t2>", $entity['instructions']);
		$overrideLineHeight = 5.5;

		// $pdf->_Line_Height = 3.45; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;
		$lines = $pdf->NbLines($pdf->_Width - 5, $test_instructions);

		switch ($lines)
		{
			case $lines <= 5:
				$test_instructions = sprintf("<t2>%s</t2>", $entity['instructions']);
				//    $pdf->_Line_Height = 5.0;// $pdf->_Get_Height_Chars(13)+$pdf->linespacing;
				$overrideLineHeight = 5.5;
				break;
			case $lines <= 10:
				$test_instructions = sprintf("<t2>%s</t2>", $entity['instructions']);
				//   $pdf->_Line_Height = 4.0; //$pdf->_Get_Height_Chars(9)+$pdf->linespacing;
				$overrideLineHeight = 4.0;
				break;
			case $lines <= 13:
				$test_instructions = sprintf("<t1>%s</t1>", $entity['instructions']);
				$overrideLineHeight = 4.0; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;
				break;
			case $lines <= 21:
				$test_instructions = sprintf("<tx>%s</tx>", $entity['instructions']);
				$overrideLineHeight = 3.25; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;
				break;
			case $lines >= 21:
				$test_instructions = sprintf("<tft>%s</tft>", $entity['instructions']);
				$overrideLineHeight = 2.5; //$pdf->_Get_Height_Chars(8)+$pdf->linespacing;
				break;
		}

		$instructions = $test_instructions;

		if (isset($entity['store_name']) && isset($entity['store_phone']) && $entity['store_name'] != "Not Available")
		{
			$other = sprintf("<t5>%s (%s)</t5>", $entity['store_name'], $entity['store_phone']);
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
					$txt1 = sprintf("%s\n\n%s\n%s", $title, $servingType, $instructions);
					$txt2 = sprintf("%s", $customerdata);
					$txt3 = sprintf("%s", $sessiondata);
					$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, '', $other);
				}
				else
				{
					$txt1 = sprintf("%s\n\n%s\n%s", $title, $servingType, $instructions);
					$txt2 = sprintf("%s", $customerdata);
					$txt3 = sprintf("%s", $sessiondata);
					$txt4 = sprintf("\n\n\n\n\n%s", $other);
				}
			}
			else
			{
				if ($isFinishingTouch)
				{
					$instructionTitle = "<t1>COOKING INSTRUCTIONS</t1>";
					$txt1 = sprintf("%s", $title);
					$txt2 = sprintf("%s", $instructionTitle);
					$txt3 = sprintf("%s", $instructions);
					$txt4 = '';
				}
				else if (!empty($entity['serving_suggestions']))
				{
					$txt1 = sprintf("%s\n\n%s\n%s", $title, $servingType, $instructions);
					$txt3 = sprintf("%s", $sessiondata);
					$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, '', $other);
				}
				else
				{
					$txt1 = sprintf("%s\n\n%s\n%s", $title, $servingType, $instructions);
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
				$txt1 = sprintf("%s\n\n%s\n%s", $title, $servingType, $instructions);
				$txt2 = sprintf("%s", $customerdata);
				$txt3 = sprintf("%s", $sessiondata);
				$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, '', $other);
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

				$txt1 = sprintf("%s\n\n%s\n%s", $title, $servingType, $instructions);
				$txt2 = sprintf("%s", $customerdata);
				$txt3 = sprintf("%s", $sessiondata);
				$txt4 = sprintf("%s\n%s\n\n\n%s", $ServingTitle, '', $other);

				$pdf->Four_Up_Add_PDF_Label($entity, $title, $instructions, $servingType, false, $showBorders, $overrideLineHeight);
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
			$pdf->Four_Up_Add_Finishing_Touch_PDF_Label($entity, $txt1, $txt2, $txt3, $txt4, $entity['prep_time'], $showBorders, $overrideLineHeight, 0, $entity['store_name'], $entity['store_phone']);
		}
		else
		{
			$pdf->Four_Up_Add_PDF_Label($entity, $title, $instructions, $servingType, false, $showBorders, $overrideLineHeight);
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
								<div style="float:right"><input type="submit" class="btn btn-primary btn-sm" value="Generate All" name="report_all" /> This will generate 1 page for every
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
					<tr style="display:none">
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
								<div style="float:right"><input type="submit" class="btn btn-primary btn-sm" value="Generate All" name="ft_report_all" /> This will generate 1 page for every
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