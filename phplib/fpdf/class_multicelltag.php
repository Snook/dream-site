<?php

require_once('fpdf.php');
require_once('class_string_tags.php');

class FPDF_MULTICELLTAG extends FPDF
{
	var $wt_Current_Tag;
	var $wt_FontInfo;//tags font info
	var $wt_DataInfo;//parsed string data info
	var $wt_DataExtraInfo;//data extra INFO

	function _wt_Reset_Datas(): void
	{
		$this->wt_Current_Tag = "";
		$this->wt_DataInfo = array();
		$this->wt_DataExtraInfo = array(
			"LAST_LINE_BR" => "",
			//CURRENT LINE BREAK TYPE
			"CURRENT_LINE_BR" => "",
			//LAST LINE BREAK TYPE
			"TAB_WIDTH" => 10
			//The tab WIDTH IS IN mm
		);

		//if another measure unit is used ... calculate your OWN
		$this->wt_DataExtraInfo["TAB_WIDTH"] *= (72 / 25.4) / $this->k;
		/*
			$this->wt_FontInfo - do not reset, once read ... is OK!!!
		*/
	}//function _wt_Reset_Datas

	/**
	 * Sets current tag to specified style
	 *
	 * @param        $tag - tag name
	 *                    $family - text font family
	 *                    $style - text style
	 *                    $size - text size
	 *                    $color - text color
	 */
	function SetStyle($tag, $family, $style, $size, $color): void
	{

		if ($tag == "ttags")
		{
			$this->Error(">> ttags << is reserved TAG Name.");
		}
		if ($tag == "")
		{
			$this->Error("Empty TAG Name.");
		}

		//use case insensitive tags
		$tag = trim(strtoupper($tag));
		$this->TagStyle[$tag]['family'] = trim($family);
		$this->TagStyle[$tag]['style'] = trim($style);
		$this->TagStyle[$tag]['size'] = trim($size);
		$this->TagStyle[$tag]['color'] = trim($color);
	}//function SetStyle

	function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
	{
		$k = $this->k;
		$hp = $this->h;
		if ($style == 'F')
		{
			$op = 'f';
		}
		else if ($style == 'FD' || $style == 'DF')
		{
			$op = 'B';
		}
		else
		{
			$op = 'S';
		}
		$MyArc = 4 / 3 * (sqrt(2) - 1);
		$this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));

		$xc = $x + $w - $r;
		$yc = $y + $r;
		$this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
		if (strpos($corners, '2') === false)
		{
			$this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $y) * $k));
		}
		else
		{
			$this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
		}

		$xc = $x + $w - $r;
		$yc = $y + $h - $r;
		$this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
		if (strpos($corners, '3') === false)
		{
			$this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - ($y + $h)) * $k));
		}
		else
		{
			$this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
		}

		$xc = $x + $r;
		$yc = $y + $h - $r;
		$this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
		if (strpos($corners, '4') === false)
		{
			$this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - ($y + $h)) * $k));
		}
		else
		{
			$this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
		}

		$xc = $x + $r;
		$yc = $y + $r;
		$this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
		if (strpos($corners, '1') === false)
		{
			$this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $y) * $k));
			$this->_out(sprintf('%.2F %.2F l', ($x + $r) * $k, ($hp - $y) * $k));
		}
		else
		{
			$this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
		}
		$this->_out($op);
	}

	function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
	{
		$h = $this->h;
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1 * $this->k, ($h - $y1) * $this->k, $x2 * $this->k, ($h - $y2) * $this->k, $x3 * $this->k, ($h - $y3) * $this->k));
	}

	/**
	 * Sets current tag style as the current settings
	 * - if the tag name is not in the tag list then de "DEFAULT" tag is saved.
	 * This includes a fist call of the function SaveCurrentStyle()
	 *
	 * @param        $tag - tag name
	 */
	function ApplyStyle($tag): void
	{

		//use case insensitive tags
		$tag = trim(strtoupper($tag));

		if ($this->wt_Current_Tag == $tag)
		{
			return;
		}

		if (($tag == "") || (!isset($this->TagStyle[$tag])))
		{
			$tag = "DEFAULT";
		}

		$this->wt_Current_Tag = $tag;

		$style = &$this->TagStyle[$tag];

		if (isset($style))
		{
			$this->SetFont($style['family'], $style['style'], $style['size']);
			//this is textcolor in FPDF format
			if (isset($style['textcolor_fpdf']))
			{
				$this->TextColor = $style['textcolor_fpdf'];
				$this->ColorFlag = ($this->FillColor != $this->TextColor);
			}
			else
			{
				if ($style['color'] <> "")
				{//if we have a specified color
					$temp = explode(",", $style['color']);
					$this->SetTextColor($temp[0], $temp[1], $temp[2]);
				}//fi
			}
			/**/
		}//isset
	}//function ApplyStyle

	/**
	 * Save the current settings as a tag default style under the DEFAUTLT tag name
	 */
	function SaveCurrentStyle(): void
	{
		//*
		$this->TagStyle['DEFAULT']['family'] = $this->FontFamily;;
		$this->TagStyle['DEFAULT']['style'] = $this->FontStyle;
		$this->TagStyle['DEFAULT']['size'] = $this->FontSizePt;
		$this->TagStyle['DEFAULT']['textcolor_fpdf'] = $this->TextColor;
		$this->TagStyle['DEFAULT']['color'] = "";
		/**/
	}//function SaveCurrentStyle

	/**
	 * Divides $this->wt_DataInfo and returnes a line from this variable
	 *
	 * @param        $w - Width of the text
	 *
	 * @return     $aLine = array() -> contains informations to draw a line
	 */
	function MakeLine($w)
	{

		$aDataInfo = &$this->wt_DataInfo;
		$aExtraInfo = &$this->wt_DataExtraInfo;

		//last line break >> current line break
		$aExtraInfo['LAST_LINE_BR'] = $aExtraInfo['CURRENT_LINE_BR'];
		$aExtraInfo['CURRENT_LINE_BR'] = "";

		if ($w == 0)
		{
			$w = $this->w - $this->rMargin - $this->x;
		}

		$wmax = ($w - 2 * $this->cMargin) * 1000;//max width

		$aLine = array();//this will contain the result
		$return_result = false;//if break and return result
		$reset_spaces = false;

		$line_width = 0;//line string width
		$total_chars = 0;//total characters included in the result string
		$space_count = 0;//numer of spaces in the result string
		$fw = &$this->wt_FontInfo;//font info array

		$last_sepch = ""; //last separator character

		foreach ($aDataInfo as $key => $val)
		{

			$s = $val['text'];

			$tag = &$val['tag'];

			$s_lenght = strlen($s);

			#if($s_lenght>0 and $s[$s_lenght-1]=="\n") $s_lenght--;

			$i = 0;//from where is the string remain
			$j = 0;//untill where is the string good to copy -- leave this == 1->> copy at least one character!!!
			$str = "";
			$s_width = 0;    //string width
			$last_sep = -1; //last separator position
			$last_sepwidth = 0;
			$last_sepch_width = 0;
			$ante_last_sep = -1; //ante last separator position
			$spaces = 0;

			//parse the whole string
			while ($i < $s_lenght)
			{
				$c = $s[$i];

				if ($c == "\n")
				{//Explicit line break
					$i++; //ignore/skip this caracter
					$aExtraInfo['CURRENT_LINE_BR'] = "BREAK";
					$return_result = true;
					$reset_spaces = true;
					break;
				}

				//space
				if ($c == " ")
				{
					$space_count++;//increase the number of spaces
					$spaces++;
				}

				//	Font Width / Size Array
				if (!isset($fw[$tag]) || ($tag == ""))
				{
					//if this font was not used untill now,
					$this->ApplyStyle($tag);
					$fw[$tag]['w'] = $this->CurrentFont['cw'];//width
					$fw[$tag]['s'] = $this->FontSize;//size
				}

				$char_width = $fw[$tag]['w'][$c] * $fw[$tag]['s'];

				//separators
				if (is_int(strpos(" ,.:;", $c)))
				{

					$ante_last_sep = $last_sep;
					$ante_last_sepch = $last_sepch;
					$ante_last_sepwidth = $last_sepwidth;
					$ante_last_sepch_width = $last_sepch_width;

					$last_sep = $i;//last separator position
					$last_sepch = $c;//last separator char
					$last_sepch_width = $char_width;//last separator char
					$last_sepwidth = $s_width;
				}

				if ($c == "\t")
				{
					$c = "";
					//$s[$i] = "";
					$char_width = $aExtraInfo['TAB_WIDTH'] * 1000;
				}

				$line_width += $char_width;

				if ($line_width > $wmax)
				{//Automatic line break

					$aExtraInfo['CURRENT_LINE_BR'] = "AUTO";

					if ($total_chars == 0)
					{
						/* This MEANS that the $w (width) is lower than a char width...
							Put $i and $j to 1 ... otherwise infinite while*/
						$i = 1;
						$j = 1;
					}//fi

					if ($last_sep <> -1)
					{
						//we have a separator in this tag!!!
						//untill now there one separator
						if (($last_sepch == $c) && ($last_sepch != " "))
						{
							/*	this is the last character and it is a separator, if it is a space the leave it...
                                Have to jump back to the las separator... even a space
							*/
							$last_sep = $ante_last_sep;
							$last_sepch = $ante_last_sepch;
							$last_sepwidth = $ante_last_sepwidth;
						}

						if ($last_sepch == " ")
						{
							$j = $last_sep;//just ignore the last space (it is at end of line)
							$i = $last_sep + 1;
							if ($spaces > 0)
							{
								$spaces--;
							}
							$s_width = $last_sepwidth;
						}
						else
						{
							$j = $last_sep + 1;
							$i = $last_sep + 1;
							#$s_width = $last_sepwidth + $fw[$tag]['w'][$last_sepch] * $fw[$tag]['s'];
							$s_width = $last_sepwidth + $last_sepch_width;
						}
					}
					else if (count($aLine) > 0)
					{
						//we have elements in the last tag!!!!
						if ($last_sepch == " ")
						{//the last tag ends with a space, have to remove it

							$temp = &$aLine[count($aLine) - 1];

							if ($temp['text'][strlen($temp['text']) - 1] == " ")
							{

								$temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
								$temp['width'] -= $fw[$temp['tag']]['w'][" "] * $fw[$temp['tag']]['s'];
								$temp['spaces']--;

								//imediat return from this function
								break 2;
							}
							else
							{
								die("should not be!!!");
							}//fi
						}//fi
					}//fi else

					$return_result = true;
					break;
				}//fi - Auto line break

				//increase the string width ONLY when it is added!!!!
				$s_width += $char_width;

				$i++;
				$j = $i;
				$total_chars++;
			}//while

			$str = substr($s, 0, $j);

			$sTmpStr = &$aDataInfo[$key]['text'];
			$sTmpStr = substr($sTmpStr, $i, strlen($sTmpStr));

			if (($sTmpStr == "") || ($sTmpStr === false))//empty
			{
				array_shift($aDataInfo);
			}

			//we have a partial result
			$aLine[] = array(
				'text' => $str,
				'tag' => $val['tag'],
				'href' => array_key_exists('href', $val) ? $val['href'] : null,
				'width' => $s_width,
				'spaces' => $spaces
			);

			if ($return_result)
			{
				break;
			}//break this for

		}//foreach

		// Check the first and last tag -> if first and last caracters are " " space remove them!!!"

		if ((count($aLine) > 0) && ($aExtraInfo['LAST_LINE_BR'] == "AUTO"))
		{
			//first tag
			$temp = &$aLine[0];
			if ((strlen($temp['text']) > 0) && ($temp['text'][0] == " "))
			{
				$temp['text'] = substr($temp['text'], 1, strlen($temp['text']));
				$temp['width'] -= $fw[$temp['tag']]['w'][" "] * $fw[$temp['tag']]['s'];
				$temp['spaces']--;
			}

			//last tag
			$temp = &$aLine[count($aLine) - 1];
			if ((strlen($temp['text']) > 0) && ($temp['text'][strlen($temp['text']) - 1] == " "))
			{
				$temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
				$temp['width'] -= $fw[$temp['tag']]['w'][" "] * $fw[$temp['tag']]['s'];
				$temp['spaces']--;
			}
		}

		if ($reset_spaces)
		{//this is used in case of a "Explicit Line Break"
			//put all spaces to 0 so in case of "J" align there is no space extension
			for ($k = 0; $k < count($aLine); $k++)
			{
				$aLine[$k]['spaces'] = 0;
			}
		}//fi

		return $aLine;
	}//function MakeLine

	/**
	 * Draws a MultiCell with TAG recognition parameters
	 *
	 * @param        $w - with of the cell
	 *                  $h - height of the cell
	 *                  $pStr - string to be printed
	 *                  $border - border
	 *                  $align    - align
	 *                  $fill - fill
	 *
	 * These paramaters are the same and have the same behavior as at Multicell function
	 */
	function MultiCellTag($w, $h, $pStr, $border = 0, $align = 'J', $fill = 0): void
	{
		//save the current style settings, this will be the default in case of no style is specified
		$this->SaveCurrentStyle();
		$this->_wt_Reset_Datas();

		$pStr = str_replace("\t", "<ttags>\t</ttags>", $pStr);
		$pStr = str_replace("\r", "", $pStr);

		//initialize the String_TAGS class
		$sWork = new String_TAGS(5);

		//get the string divisions by tags
		$this->wt_DataInfo = $sWork->get_tags($pStr);

		$b = $b1 = $b2 = $b3 = '';//borders

		//save the current X position, we will have to jump back!!!!
		$startX = $this->GetX();

		if ($border)
		{
			if ($border == 1)
			{
				$border = 'LTRB';
				$b1 = 'LRT';//without the bottom
				$b2 = 'LR';//without the top and bottom
				$b3 = 'LRB';//without the top
			}
			else
			{
				$b2 = '';
				if (is_int(strpos($border, 'L')))
				{
					$b2 .= 'L';
				}
				if (is_int(strpos($border, 'R')))
				{
					$b2 .= 'R';
				}
				$b1 = is_int(strpos($border, 'T')) ? $b2 . 'T' : $b2;
				$b3 = is_int(strpos($border, 'B')) ? $b2 . 'B' : $b2;
			}

			//used if there is only one line
			$b = '';
			$b .= is_int(strpos($border, 'L')) ? 'L' : "";
			$b .= is_int(strpos($border, 'R')) ? 'R' : "";
			$b .= is_int(strpos($border, 'T')) ? 'T' : "";
			$b .= is_int(strpos($border, 'B')) ? 'B' : "";
		}

		$first_line = true;
		$last_line = !(count($this->wt_DataInfo) > 0);

		while (!$last_line)
		{
			if ($fill == 1)
			{
				//fill in the cell at this point and write after the text without filling
				$this->Cell($w, $h, "", 0, 0, "", 1);
				$this->SetX($startX);//restore the X position
			}

			//make a line
			$str_data = $this->MakeLine($w);

			//check for last line
			$last_line = !(count($this->wt_DataInfo) > 0);

			if ($last_line && ($align == "J"))
			{//do not Justify the Last Line
				$align = "L";
			}

			//outputs a line
			$this->PrintLine($w, $h, $str_data, $align);

			//see what border we draw:
			if ($first_line && $last_line)
			{
				//we have only 1 line
				$real_brd = $b;
			}
			else if ($first_line)
			{
				$real_brd = $b1;
			}
			else if ($last_line)
			{
				$real_brd = $b3;
			}
			else
			{
				$real_brd = $b2;
			}

			if ($first_line)
			{
				$first_line = false;
			}

			//draw the border and jump to the next line
			$this->SetX($startX);//restore the X
			$this->Cell($w, $h, "", $real_brd, 2);
		}//while(! $last_line){

		//APPLY THE DEFAULT STYLE
		$this->ApplyStyle("DEFAULT");

		$this->x = $this->lMargin;
	}//function MultiCellExt

	/**
	 * This method returns the number of lines that will a text ocupy on the specified width
	 *
	 * @param        $w - with of the cell
	 *                  $pStr - string to be printed
	 *
	 * @return     $nb_lines - number of lines
	 */
	function NbLines($w, $pStr)
	{

		//save the current style settings, this will be the default in case of no style is specified
		$this->SaveCurrentStyle();
		$this->_wt_Reset_Datas();

		$pStr = str_replace("\t", "<ttags>\t</ttags>", $pStr);
		$pStr = str_replace("\r", "", $pStr);

		//initialize the String_TAGS class
		$sWork = new String_TAGS(5);

		//get the string divisions by tags
		$this->wt_DataInfo = $sWork->get_tags($pStr);

		$first_line = true;
		$last_line = !(count($this->wt_DataInfo) > 0);
		$nb_lines = 0;

		while (!$last_line)
		{

			//make a line
			$str_data = $this->MakeLine($w);

			//check for last line
			$last_line = !(count($this->wt_DataInfo) > 0);

			if ($first_line)
			{
				$first_line = false;
			}

			$nb_lines++;
		}//while(! $last_line){

		//APPLY THE DEFAULT STYLE
		$this->ApplyStyle("DEFAULT");

		return $nb_lines;
	}//function MultiCellExt

	/**
	 * Draws a line returned from MakeLine function
	 *
	 * @param        $w - with of the cell
	 *                  $h - height of the cell
	 *                  $aTxt - array from MakeLine
	 *                  $align - text align
	 */
	function PrintLine($w, $h, $aTxt, $align = 'J'): void
	{

		if ($w == 0)
		{
			$w = $this->w - $this->rMargin - $this->x;
		}

		$wmax = $w; //Maximum width

		$total_width = 0;    //the total width of all strings
		$total_spaces = 0;    //the total number of spaces

		$nr = count($aTxt);//number of elements

		for ($i = 0; $i < $nr; $i++)
		{
			$total_width += ($aTxt[$i]['width'] / 1000);
			$total_spaces += $aTxt[$i]['spaces'];
		}

		//default
		$w_first = $this->cMargin;

		switch ($align)
		{
			case 'J':
				if ($total_spaces > 0)
				{
					$extra_space = ($wmax - 2 * $this->cMargin - $total_width) / $total_spaces;
				}
				else
				{
					$extra_space = 0;
				}
				break;
			case 'L':
				break;
			case 'C':
				$w_first = ($wmax - $total_width) / 2;
				break;
			case 'R':
				$w_first = $wmax - $total_width - $this->cMargin;;
				break;
		}

		// Output the first Cell
		if ($w_first != 0)
		{
			$this->Cell($w_first, $h, "", 0, 0, "L", 0);
		}

		$last_width = $wmax - $w_first;

		foreach ($aTxt as $key => $val)
		{

			//apply current tag style
			$this->ApplyStyle($val['tag']);

			//If > 0 then we will move the current X Position
			$extra_X = 0;

			//string width
			$width = $this->GetStringWidth($val['text']);
			$width = $val['width'] / 1000;

			if ($width == 0)
			{
				continue;
			}// No width jump over!!!

			if ($align == 'J')
			{
				if ($val['spaces'] < 1)
				{
					$temp_X = 0;
				}
				else
				{
					$temp_X = $extra_space;
				}

				$this->ws = $temp_X;

				$this->_out(sprintf('%.3f Tw', $temp_X * $this->k));

				$extra_X = $extra_space * $val['spaces'];//increase the extra_X Space

			}
			else
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}//fi

			//Output the Text/Links
			$this->Cell($width, $h, $val['text'], 0, 0, "C", 0, $val['href']);

			$last_width -= $width;//last column width

			if ($extra_X != 0)
			{
				$this->SetX($this->GetX() + $extra_X);
				$last_width -= $extra_X;
			}//fi

		}

		// Output the Last Cell
		if ($last_width != 0)
		{
			$this->Cell($last_width, $h, "", 0, 0, "", 0);
		}//fi
	}//function PrintLine
}//class

?>