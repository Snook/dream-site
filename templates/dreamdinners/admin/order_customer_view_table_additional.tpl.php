
<?php  if ( true /*isset($this->menuInfo)*/) {
	$ts = "";
	$header = "<table border=0>";



	if (!empty($this->ispreassembled) && $this->ispreassembled == 1)
		$subthheader = "<tr><td width=100><b>Quantity</b></td><td width=350><b>Menu Item</b></td><td><b>Serving Size</b></td></tr>";
	else
		$subthheader = "<tr><td width=100><b>Quantity</b></td><td width=300><b>Menu Item</b></td><td></td></tr>";



	$customerdetals = '';

	$footer = "</table>";

	//print_r($this->menuInfo);

	$counter = 0;

	if ($this->issidedish)
	{

		if (!empty($this->sidesArray))
		{
			$successfulreport = true;
			$customerdetals .=  "<tr>";
			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'><b>Customer:</b></td>";
			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'>" . $this->customer_name .   "</td>";

			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'><b>Session:</b></td>";
			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'>" .   CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE)  . "</td>";


			$customerdetals .=  "</tr>";

			$customerdetals .=  "<tr>";
			$customerdetals .=  "<td>&nbsp;</td>";
			$customerdetals .=  "<td>&nbsp;</td>";
			$customerdetals .=  "</tr>\n";

			foreach($this->sidesArray as  $element)
			{
				$ts .=  "<tr>";


				$ts .=  "<td>";

				$ts .= $element['qty'];
				$ts .=  "</td>";

				$ts .=  "<td>";
				$ts .= $element['display_title'];
				$ts .=  "</td>";

				if (!empty($this->ispreassembled) && $this->ispreassembled == 1)
				{
					if($this->showlongserving){
						$ts .=  "<td>" . CMenuItem::translatePricingTypeWithQuantity($element['pricing_type'], false) . "</td>";
					}else{
						$ts .=  "<td>" . CMenuItem::translatePricingType($element['pricing_type']) . "</td>";
					}

				}

				$ts .=  "</tr>";
			}

			echo $header   . $customerdetals . $subthheader . $ts .  $footer;
		}
		else
		{
			$session_start_time = CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE);
			echo $header . "<tr><td><font size=4>Sorry, no Side Dish items have been ordered for this session: <b>$session_start_time</b> </font></td></tr>" . $footer;

		}


	}
	else
	{

		if (!empty($this->preassembledArray))
		{

			$successfulreport = true;
			$customerdetals .=  "<tr>";
			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'><b>Customer:</b></td>";
			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'>" . $this->customer_name .   "</td>";

			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'><b>Session:</b></td>";
			$customerdetals .=  "<td style='border-bottom: #000000 1px dashed;border-top: #000000 1px dashed;'>" .   CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE)  . "</td>";


			$customerdetals .=  "</tr>";

			$customerdetals .=  "<tr>";
			$customerdetals .=  "<td>&nbsp;</td>";
			$customerdetals .=  "<td>&nbsp;</td>";
			$customerdetals .=  "</tr>";

			foreach($this->preassembledArray as  $element)
			{
				$ts .=  "<tr>";


				$ts .=  "<td>";

				$ts .= $element['qty'];
				$ts .=  "</td>";

				$ts .=  "<td>";
				if (!empty($element['is_bundle']))
				{
					$ts .= $element['display_title_pre_assembled'];
				}
				else
				{
					$ts .= $element['display_title'];
				}
				$ts .=  "</td>";

				if (!empty($this->ispreassembled) && $this->ispreassembled == 1)
				{
					if($this->showlongserving){
						$ts .=  "<td>" . CMenuItem::translatePricingTypeWithQuantity($element['pricing_type'], false) . "</td>";
					}else{
						$ts .=  "<td>" . CMenuItem::translatePricingType($element['pricing_type']) . "</td>";
					}
				}

				$ts .=  "</tr>";
			}

			echo $header   . $customerdetals . $subthheader . $ts .  $footer;

		}
		else
		{
			$session_start_time = CTemplate::sessionTypeDateTimeFormat($this->session['session_start'], $this->session['session_type_subtype'],VERBOSE);
			echo $header . "<tr><td><font size=4>No Pre-Assembled items have been ordered for this session: <b>$session_start_time</b> </font></td></tr>" . $footer;
		}

	}

}
?>