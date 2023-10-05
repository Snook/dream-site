<?php

/*
* Created Lynn Hook
* This class takes care of presenting the customer info into a label form.
*/
// INTERFACE IS ZERO THEN DOING A STRAIGHT PRINT OF LABELS


if ($this->success == true && $this->interface == 0)
{
	require_once("fpdf/dream_labels.php");

	$useNewLabelFormat = true;

	$pdf = new dream_labels('5168', 'mm', 1, 1, 'L');
	$pdf->Open();
	$pdf->SetFont('arial','',14);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTitle("Dream Dinners");

	$pdf->SetStyle("p", "times", "", 8, "130,0,30");
	$pdf->SetStyle("pb", "times", "B", 8, "130,0,30");
	$pdf->SetStyle("t1", "times", "", 8, "0,0,0");
	$pdf->SetStyle("t8", "times", "B", 8, "0,0,0");
	$pdf->SetStyle("t9", "times", "BI", 8, "0,0,0");
	$pdf->SetStyle("t5", "times", "", 5, "0,0,0");

	$pdf->SetStyle("tt", "times", "", 5, "0,0,0");
	$pdf->SetStyle("ttb", "times", "B", 5, "0,0,0");

	$pdf->SetStyle("ts", "times", "B", 6, "0,0,0");

	$pdf->SetStyle("tft", "times", "", 6, "0,0,0");
	$pdf->SetStyle("tftb", "times", "B", 6, "0,0,0");


	$pdf->SetStyle("t1b", "times", "B", 8, "0,0,0");
	$pdf->SetStyle("t2b", "times", "B", 9, "0,0,0");
	$pdf->SetStyle("t3b", "times", "B", 11, "0,0,0");
	$pdf->SetStyle("t4b", "times", "B", 13, "0,0,0");

	$pdf->SetStyle("t2", "helvetica", "", 9, "0,0,0");
	$pdf->SetStyle("t3", "helvetica", "B", 9, "0,0,0");
	$pdf->SetStyle("t4", "times", "B", 7, "0,0,0");
	$pdf->SetStyle("hh", "times", "B", 11, "255,189,12");
	$pdf->SetStyle("font", "helvetica", "", 10, "0,0,255");
	$pdf->SetStyle("style", "helvetica", "BI", 10, "0,0,220");
	$pdf->SetStyle("size", "times", "BI", 13, "0,0,120");
	$pdf->SetStyle("color", "times", "BI", 13, "0,0,255");

	$showBorders = $this->show_borders;

	$offsetX = 80;
	$offsetY = 74;
	$offsetXCard = 22;
	$offsetYCard = -15;

	$imageHeight = 12;

	$ItemsPerPage = 4;
	$canBreak = isset($_REQUEST['break']) ? $_REQUEST['break'] : 0;
	$lastorderid = 0;
	$curOrderID = 0;
	$iterPageBreaks = false;
	$page = 0;
	$counter = 0;
	$maxchars = 525; /// change layout based on character string size

	foreach ($this->order_details as $index => $entity)
	{
		if (isset($entity['order_id']))
		{
			$curOrderID = $entity['order_id'];
		}

		if ($lastorderid == 0 || $iterPageBreaks == true)
		{
			if (isset($entity['order_id'])) $lastorderid = $entity['order_id'];
			$iterPageBreaks = false;
		}

		$title = "<t3>%s</t3>"; // the menu item
		$item = "<t1b>(Item: %s of %s)</t1b>";

		$colx = $pdf->_getColX();
		$coly = $pdf->_getColY();

		$card = "x:" . $colx . "y:" . $coly;
		$height = $pdf->_getHeight();
		$width = $pdf->_getWidth();

		$isSide = false;

		if ($this->label_action != 'report_std')
		{
			$isSide = true;
		}


		if (!empty($entity['item_number']) && !empty($entity['total_items']))
		{
			$item = sprintf($item, $entity['item_number'], $entity['total_items']);
		}

		if ($canBreak)
		{
			if ($curOrderID == $lastorderid)
			{
				$iterPageBreaks = false;
			}
			else
			{
				$iterPageBreaks = true;

				if ($counter > 0) {
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


				$pdf->Add_Nutrition_Label_four_up( $entity, $showBorders, 3.45,null,$isSide );

				$counter++;

				$yoff = $pdf->y - $imageHeight;
				$xoff = ($height * $colx) + $offsetX;
				if ($colx == 1)$xoff += $offsetXCard;

				continue;
			}
		}

		$data = print_r($entity, true);
		$pdf->Add_Nutrition_Label_four_up($entity, $showBorders, 0, $this->store,$isSide );

		if ($counter >= $ItemsPerPage-1)
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

	$pdf->Output();
}
else
{
	$this->assign('page_title', 'Nutrition Labels');

	$this->assign('topnav', 'reports');

	include $this->loadTemplate('admin/page_header.tpl.php');
	?>

	<script type="text/javascript">


		function print_labels(str)
		{
			$('#label_action').val(str);

			$('#labelForm').submit();

		}

		function changeMenu()
		{
			var targ = $('#labelForm')[0].target;
			$('#labelForm')[0].target = "";
			$('#label_action').val('');

			$('#labelForm').submit();

			$('#labelForm')[0].target = targ;

		}

	</script>

	<?php if (isset($this->form['store_html'])) { ?>
	<form method="POST">
		<b>Store:</b>&nbsp;<?=$this->form['store_html']; ?><br /><br />
	</form>
<?php } ?>

	<form id="labelForm" method='post' target="_out">
		<?=$this->form_list['hidden_html']?>

		<div>

			<h3>Entr&eacute;e Nutrition Labels</h3>
			<table>

				<?php	if (isset($this->form_list['menus_html'])) { ?>
					<tr>
						<td>
							Pick a menu
						</td>

						<td>
							<?= $this->form_list['menus_html'] ?>

							<?php	if (isset($this->form_list['menu_items_html'])) { ?>
								<div style="float:right">	<input type="submit" value="Generate All" class="btn btn-primary btn-sm" name="report_all" /> This will generate 1 page for every <br /> menu item<i> (Note: This could take a while.)</i></div>
							<?php	} ?>

						</td>
					</tr>
				<?php } ?>


				<?php	if (isset($this->form_list['menu_items_html'])) { ?>
					<tr>
						<td >
							Pick a menu item
						</td>
						<td style='width:700px'>
							<?=$this->form_list['menu_items_html'] ?>
						</td>
					</tr>
				<?php	} ?>

				<?php if (isset($this->form_list['labels_to_print_html'])) { ?>
					<tr>
						<td>
							Enter the number of <br />pages to print (up to 4)
						</td>
						<td>
							<?=$this->form_list['labels_to_print_html']?>
						</td>
					</tr>
				<?php	} ?>

				<?php if (isset($this->form_list['labels_per_sheet_html'])) { ?>
					<tr>
						<td>
							Select the number <br>of labels per sheet
						</td>
						<td>
							<?=$this->form_list['labels_per_sheet_html']?>
						</td>
					</tr>
				<?php	} ?>

				<?php	if (isset($this->form_list['hidden_html'])) { ?>
					<tr>
						<td>&nbsp;</td>
						<td >
							<button onclick="print_labels('report_std'); return false;" class="btn btn-primary btn-sm">Print Core Item Nutrition Labels</button>
							<br/>
							The labels will appear in a new window. To print	labels for several menu items close the new window each time, exposing the selection form, and repeat the process.
						</td>
					</tr>
				<?php	} ?>

			</table>

		</div>

		<hr />

		<div>

			<h3>Sides &amp; Sweets Nutrition Labels</h3>

			<table>

				<?php	if (isset($this->form_list['ft_menus_html'])) { ?>
					<tr>
						<td >
							Pick a menu
						</td>

						<td >
							<?= $this->form_list['ft_menus_html'] ?>

							<?php	if (isset($this->form_list['ft_menu_items_html'])) { ?>
								<div style="float:right"><input type="submit" value="Generate All" class="btn btn-primary btn-sm" name="ft_report_all"/> This will generate 1 page for every <br/> Sides &amp; Sweets item<i> (Note: This
																																																			  could take a while.)</i></div>
							<?php	} ?>

						</td>
					</tr>
				<?php } ?>


				<?php	if (isset($this->form_list['ft_menu_items_html'])) { ?>
					<tr>
						<td >
							Pick a Sides &amp; Sweets item
						</td>
						<td style='width:700px'>
							<?=$this->form_list['ft_menu_items_html'] ?>
						</td>
					</tr>
				<?php	} ?>

				<?php if (isset($this->form_list['ft_labels_to_print_html'])) { ?>
					<tr>
						<td>
							Enter the number of <br />pages to print (up to 4)
						</td>
						<td>
							<?=$this->form_list['ft_labels_to_print_html']?>
						</td>
					</tr>
				<?php	} ?>

				<?php if (isset($this->form_list['ft_labels_per_sheet_html'])) { ?>
					<tr>
						<td>
							Select the number <br>of labels per sheet
						</td>
						<td>
							<?=$this->form_list['ft_labels_per_sheet_html']?>
						</td>
					</tr>
				<?php	} ?>

				<?php	if (isset($this->form_list['hidden_html'])) { ?>
					<tr>
						<td>&nbsp;</td>
						<td >
							<button onclick="print_labels('report_ft'); return false;" class="btn btn-primary btn-sm">Print Sides &amp; Sweets Item Nutrition Labels</button>
							<br/>
							The labels will appear in a new window. To print	labels for several menu items close the new window each time, exposing the selection form, and repeat the process.
						</td>
					</tr>
				<?php	} ?>

			</table>

		</div>

	</form>
	<?php include $this->loadTemplate('admin/page_footer.tpl.php');
}
?>