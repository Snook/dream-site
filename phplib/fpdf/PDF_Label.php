<?php

// ******************************************************************************************************************************
// this class will create pdf labels
// it inherits from MULTCELLTAG to allow for additional presentation features such as changing of font, size, style, etc.
// ******************************************************************************************************************************
require_once("fpdf/fpdf.php");
require_once("fpdf/class_multicelltag.php");
require_once('phplib/phpqrcode/qrlib.php');
require_once('includes/CAppUtil.inc');

class PDF_Label extends FPDF_MULTICELLTAG
{
	var $linespacing = .45;

	// Private properties
	var $_Avery_Name = '';                // Name of format
	var $_Margin_Left = 0;                // Left margin of labels
	var $_Margin_Top = 0;                // Top margin of labels
	var $_Margin_Right = 0;                // Top margin of labels
	var $_X_Space = 0;                // Horizontal space between 2 labels
	var $_Y_Space = 0;                // Vertical space between 2 labels
	var $_X_Number = 0;                // Number of labels horizontally
	var $_Y_Number = 0;                // Number of labels vertically
	var $_Width = 0;                // Width of label
	var $_Height = 0;                // Height of label
	var $_Char_Size = 10;                // Character size
	var $_Line_Height = 10;                // Default line height
	var $_Metric = 'mm';                // Type of metric for labels.. Will help to calculate good values
	var $_Metric_Doc = 'mm';                // Type of metric for the document
	var $_Font_Name = 'Arial';            // Name of the font
	var $angle = 0;            // Rotation angle

	var $_COUNTX = 0;
	var $_COUNTY = 0;

	var $coupon_array = array(

		'837' => 'MobileMD10',
		'516' => 'PhoenixMD10',
		'792' => 'NPhoenixMD10',
		'1000' => 'ScottsdaleMD10',
		'126' => 'TucsonMD10',
		'204' => 'BelmontMD10',
		'784' => 'BeniciaMD10',
		'497' => 'EncinitasMD10',
		'822G' => 'FountainValleyMD10',
		'248' => 'GranadaHillsMD10',
		'515' => 'LaCrescentaMD10',
		'870' => 'LaMesaMD10',
		'178' => 'LakeForestMD10',
		'747' => 'LosGatosMD10',
		'500' => 'ModestoMD10',
		'249' => 'PasadenaMD10',
		'215' => 'PowayRdMD10',
		'373' => 'ReddingMD10',
		'550' => 'RedlandsMD10',
		'216' => 'SacramentoMD10',
		'859' => 'TierrasantaMD10',
		'434_2' => 'CamdenParkMD10',
		'536' => 'SLOMD10',
		'351' => 'SanMarcosMD10',
		'347' => 'SolanaBeachMD10',
		'417' => 'TemeculaMD10',
		'529' => 'ThousandOaksMD10',
		'628' => 'TorranceMD10',
		'488' => 'TustinMD10',
		'632' => 'VacavilleMD10',
		'752' => 'VenturaMD10',
		'111' => 'WestLAMD10',
		'91' => 'CentennialMD10',
		'819' => 'COSpringsMD10',
		'63' => 'ParkerMD10',
		'136' => 'WheatRidgeMD10',
		'161' => 'BerlinMD10',
		'517' => 'ManchesterMD10',
		'310' => 'PinecrestMD10',
		'137' => 'SunriseMD10',
		'305' => 'CummingMD10',
		'835' => 'RoswellMD10',
		'882' => 'ChicagoMD10',
		'298' => 'IndianapolisMD10',
		'767' => 'LafayetteMD10',
		'293' => 'OverlandParkMD10',
		'260' => 'FraminghamMD10',
		'389' => 'PlainvilleMD10',
		'431' => 'WBoylstonMD10',
		'646' => 'FrederickMD10',
		'369' => 'SilverSpringMD10',
		'643' => 'AllenParkMD10',
		'258' => 'LivoniaMD10',
		'339' => 'RochesterHillsMD10',
		'447' => 'ClemmonsMD10',
		'527' => 'WaxhawMD10',
		'376' => 'BedfordMD10',
		'525' => 'FlemingtonMD10',
		'869' => 'CarsonCityMD10',
		'261' => 'BlueAshMD10',
		'722' => 'CentervilleMD10',
		'788' => 'CincinnatiWestMD10',
		'514' => 'AndersonMD10',
		'78' => 'PowellMD10',
		'280' => 'WestChesterOHMD10',
		'750' => 'WestervilleMD10',
		'309' => 'BeavertonMD10',
		'1' => 'ClackamasMD10',
		'314' => 'CorvallisMD10',
		'159' => 'TualatinMD10',
		'16' => 'ColmarMD10',
		'499' => 'LancasterMD10',
		'408' => 'WestChesterPASMD10',
		'549' => 'FortMillMD10',
		'874H' => 'NWAustinMD10',
		'691' => 'BellaireMD10',
		'677' => 'MissouriCityMD10',
		'402' => 'NRichlandHillsMD10',
		'492' => 'PearlandMD10',
		'823' => 'OremMD10',
		'358' => 'SaltLakeMD10',
		'292' => 'MidlothianMD10',
		'315' => 'KennewickMD10',
		'AAA' => 'MillCreekMD10',
		'17' => 'WSeattleMD10',
		'866H' => 'VancouverMD10'

	);

	// Listing of labels size
	var $_Avery_Labels = array(
		'8164' => array(
			'name' => '8164',
			'paper-size' => 'letter',
			'metric' => 'mm',
			'marginLeft' => 3.96,
			'marginTop' => 12.7,
			'NX' => 2,
			'NY' => 3,
			'SpaceX' => 10.5,
			'SpaceY' => 3,
			'width' => 100.5,
			'height' => 84.67,
			'font-size' => 8,
			'marginRight' => 3.96
		),
		'5160' => array(
			'name' => '5160',
			'paper-size' => 'letter',
			'metric' => 'mm',
			'marginLeft' => 1.762,
			'marginTop' => 10.7,
			'NX' => 3,
			'NY' => 10,
			'SpaceX' => 3.175,
			'SpaceY' => 0,
			'width' => 66.675,
			'height' => 25.4,
			'font-size' => 8
		),
		//4 rectangles
		'5168' => array(
			'name' => '5168',
			'paper-size' => 'letter',
			'metric' => 'mm',
			'marginLeft' => 8,
			'marginTop' => 5,
			'NX' => 2,
			'NY' => 2,
			'SpaceX' => 0,
			'SpaceY' => 0,
			'width' => 130,
			'height' => 65,
			'font-size' => 8,
			'marginRight' => 5
		),
		'5161' => array(
			'name' => '5161',
			'paper-size' => 'letter',
			'metric' => 'mm',
			'marginLeft' => 0.967,
			'marginTop' => 10.7,
			'NX' => 2,
			'NY' => 10,
			'SpaceX' => 3.967,
			'SpaceY' => 0,
			'width' => 101.6,
			'height' => 25.4,
			'font-size' => 8
		),
		'5162' => array(
			'name' => '5162',
			'paper-size' => 'letter',
			'metric' => 'mm',
			'marginLeft' => 0.97,
			'marginTop' => 20.224,
			'NX' => 2,
			'NY' => 7,
			'SpaceX' => 4.762,
			'SpaceY' => 0,
			'width' => 100.807,
			'height' => 35.72,
			'font-size' => 8
		),
		'5163' => array(
			'name' => '5163',
			'paper-size' => 'letter',
			'metric' => 'mm',
			'marginLeft' => 1.762,
			'marginTop' => 10.7,
			'NX' => 2,
			'NY' => 5,
			'SpaceX' => 3.175,
			'SpaceY' => 0,
			'width' => 101.6,
			'height' => 50.8,
			'font-size' => 8
		),
		'5164' => array(
			'name' => '5164',
			'paper-size' => 'letter',
			'metric' => 'in',
			'marginLeft' => 0.148,
			'marginTop' => 0.5,
			'NX' => 2,
			'NY' => 3,
			'SpaceX' => 0.2031,
			'SpaceY' => 0,
			'width' => 4.0,
			'height' => 3.33,
			'font-size' => 12
		),
		'8600' => array(
			'name' => '8600',
			'paper-size' => 'letter',
			'metric' => 'mm',
			'marginLeft' => 7.1,
			'marginTop' => 19,
			'NX' => 3,
			'NY' => 10,
			'SpaceX' => 9.5,
			'SpaceY' => 3.1,
			'width' => 66.6,
			'height' => 25.4,
			'font-size' => 8
		),
		'L7163' => array(
			'name' => 'L7163',
			'paper-size' => 'A4',
			'metric' => 'mm',
			'marginLeft' => 5,
			'marginTop' => 15,
			'NX' => 2,
			'NY' => 7,
			'SpaceX' => 25,
			'SpaceY' => 0,
			'width' => 99.1,
			'height' => 38.1,
			'font-size' => 9
		)
	);

	function _getColX()
	{
		return $this->_COUNTX;
	}

	function _getColY()
	{
		return $this->_COUNTY;
	}

	function _getWidth()
	{
		return $this->_Width;
	}

	function _getHeight()
	{
		return $this->_Height;
	}

	function _getX()
	{
		return $this->_X_Number;
	}

	function _getY()
	{
		return $this->_Y_Number;
	}

	// convert units (in to mm, mm to in)
	// $src and $dest must be 'in' or 'mm'
	function _Convert_Metric($value, $src, $dest)
	{
		if ($src != $dest)
		{
			$tab['in'] = 39.37008;
			$tab['mm'] = 1000;

			return $value * $tab[$dest] / $tab[$src];
		}
		else
		{
			return $value;
		}
	}

	// Give the height for a char size given.
	function _Get_Height_Chars($pt)
	{
		// Array matching character sizes and line heights
		$_Table_Hauteur_Chars = array(
			6 => 2,
			7 => 2.5,
			8 => 3,
			9 => 4,
			10 => 5,
			11 => 6,
			12 => 7,
			13 => 8,
			14 => 9,
			15 => 10
		);
		if (in_array($pt, array_keys($_Table_Hauteur_Chars)))
		{
			return $_Table_Hauteur_Chars[$pt];
		}
		else
		{
			return 100; // There is a prob..
		}
	}

	function _Set_Format($format)
	{
		$this->_Metric = $format['metric'];
		$this->_Avery_Name = $format['name'];
		$this->_Margin_Left = $this->_Convert_Metric($format['marginLeft'], $this->_Metric, $this->_Metric_Doc);
		$this->_Margin_Top = $this->_Convert_Metric($format['marginTop'], $this->_Metric, $this->_Metric_Doc);
		$this->_Margin_Right = $this->_Convert_Metric($format['marginRight'], $this->_Metric, $this->_Metric_Doc);

		$this->_X_Space = $this->_Convert_Metric($format['SpaceX'], $this->_Metric, $this->_Metric_Doc);
		$this->_Y_Space = $this->_Convert_Metric($format['SpaceY'], $this->_Metric, $this->_Metric_Doc);
		$this->_X_Number = $format['NX'];
		$this->_Y_Number = $format['NY'];
		$this->_Width = $this->_Convert_Metric($format['width'], $this->_Metric, $this->_Metric_Doc);
		$this->_Height = $this->_Convert_Metric($format['height'], $this->_Metric, $this->_Metric_Doc);
		$this->Set_Font_Size($format['font-size']);
	}

	function Rotate($angle, $x = -1, $y = -1)
	{
		if ($x == -1)
		{
			$x = $this->x;
		}

		if ($y == -1)
		{
			$y = $this->y;
		}

		if ($this->angle != 0)
		{
			$this->_out('Q');
		}

		$this->angle = $angle;

		if ($angle != 0)
		{
			$angle *= M_PI / 180;
			$c = cos($angle);
			$s = sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}

	function _endpage()
	{
		if ($this->angle != 0)
		{
			$this->angle = 0;
			$this->_out('Q');
		}

		parent::_endpage();
	}

	function RotatedText($txt, $angle)
	{
		$txt = str_replace("\t", "<ttags>\t</ttags>", $txt);
		$txt = str_replace("\r", "", $txt);

		$sWork = new String_TAGS(5);

		//get the string divisions by tags
		$this->wt_DataInfo = $sWork->get_tags($txt);

		$this->ApplyStyle($this->wt_DataInfo[0]['tag']);

		//Text rotated around its origin
		$this->Rotate($angle, $this->x, $this->y);
		$this->Text($this->x, $this->y, $this->wt_DataInfo[0]['text']);
		$this->Rotate(0);
	}

	// Constructor
	function __construct($format, $unit = 'mm', $posX = 1, $posY = 1, $orientation = 'P')
	{
		if (is_array($format))
		{
			// Custom format
			$Tformat = $format;
		}
		else
		{
			// Avery format
			$Tformat = $this->_Avery_Labels[$format];
		}

		parent::__construct($orientation, $Tformat['metric'], $Tformat['paper-size']);
		$this->_Set_Format($Tformat);
		$this->Set_Font_Name('Arial');
		$this->SetMargins(0, 0);
		$this->SetAutoPageBreak(false);

		$this->_Metric_Doc = $unit;
		// Start at the given label position
		if ($posX > 1)
		{
			$posX--;
		}
		else
		{
			$posX = 0;
		}
		if ($posY > 1)
		{
			$posY--;
		}
		else
		{
			$posY = 0;
		}
		if ($posX >= $this->_X_Number)
		{
			$posX = $this->_X_Number - 1;
		}
		if ($posY >= $this->_Y_Number)
		{
			$posY = $this->_Y_Number - 1;
		}
		$this->_COUNTX = $posX;
		$this->_COUNTY = $posY;
	}

	// Sets the character size
	// This changes the line height too
	function Set_Font_Size($pt)
	{
		if ($pt > 3)
		{
			$this->_Char_Size = $pt;
			$this->_Line_Height = $this->_Get_Height_Chars($pt) + $this->linespacing;
			$this->SetFontSize($this->_Char_Size);
		}
	}

	// Method to change font name
	function Set_Font_Name($fontname)
	{
		if ($fontname != '')
		{
			$this->_Font_Name = $fontname;
			$this->SetFont($this->_Font_Name);
		}
	}

	// Print a label
	function Add_PDF_Label($texte, $useSimpleFormatting = false, $showBorders = 0)
	{
		// We are in a new page, then we must add a page

		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$_PosX = $this->_Margin_Left + ($this->_COUNTX * ($this->_Width + $this->_X_Space));

		$_PosX -= $this->_Margin_Right;

		$_PosY = $this->_Margin_Top + ($this->_COUNTY * ($this->_Height + $this->_Y_Space));
		$this->SetXY($_PosX + 3, $_PosY + 3);

		if ($useSimpleFormatting == true)
		{
			$this->MultiCell($this->_Width, $this->_Line_Height, $texte, 1);
		}
		else
		{
			$this->MultiCellTag($this->_Width, $this->_Line_Height, $texte, $showBorders, "L", 0);
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	/*
	 * This function executes the MultiCellTag call unless there is a less than sign in the value.
	 * Since there is apparently no way to escape a less than sign (I think the class_strings_tags would need to be modified.) this
	 * function has a special case and uses text() rather than MultiCellTag if the "<" is present.
	 *
	 * value style is hard-coded to db2
	*/

	function print_Nutrition_Element($overrideLineHeight, $XOffset, $labelStyle, $element, $label, $showBorders, $alignment, $fill, $suffix = '')
	{
		//	$this->print_Nutrition_Element($this->_Width, $overrideLineHeight, "<db2>", "Trans Fat_", $entity['component'][1], $showBorders, "L", 0);
		$openStyleTag = '<' . $labelStyle . '>';
		$closeStyleTag = '</' . $labelStyle . '>';

		if ($element['prefix'] == '<')
		{
			$this->MultiCellTag($this->_Width, $overrideLineHeight, $openStyleTag . $label . $closeStyleTag, $showBorders, $alignment, $fill);
			$this->ApplyStyle('db2');
			$this->Text($this->x + $XOffset, $this->y + 0.7, $element['prefix'] . CTemplate::formatDecimal($element['value']) . $element['measure_label'] . $suffix);
		}
		else
		{
			$tStr = $openStyleTag . $label . $closeStyleTag . ' <db2>' . $element['prefix'] . CTemplate::formatDecimal($element['value']) . $element['measure_label'] . $suffix . '</db2>';
			$this->MultiCellTag($this->_Width, $overrideLineHeight, $tStr, $showBorders, $alignment, $fill);
		}
	}

	const FDA_BOX_HEIGHT = 82;

	function Add_Nutrition_Label($entity, $showBorders = 0, $overrideLineHeight = 3.45, $Store = null)
	{
		$showBorders = 0;

		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$this->SetStyle("t3", "helvetica", "B", 8, "0,0,0");
		$this->SetStyle("db", "helvetica", "B", 14, "0,0,0");
		$this->SetStyle("db2", "helvetica", "", 6, "0,0,0");
		$this->SetStyle("db2b", "helvetica", "B", 6, "0,0,0");
		$this->SetStyle("db3", "helvetica", "B", 5.2, "0,0,0");
		$this->SetStyle("db4", "helvetica", "B", 6, "0,0,0");
		$this->SetStyle("db6", "helvetica", "", 4.5, "0,0,0");
		$this->SetStyle("db7", "helvetica", "B", 7, "0,0,0");
		$this->SetStyle("db8", "helvetica", "", 4, "0,0,0");
		$this->SetStyle("rs", "helvetica", "", 3.5, "0,0,0");
		$this->SetStyle("rss", "helvetica", "", 3, "0,0,0");
		$this->SetStyle("db9", "helvetica", "", 5.5, "0,0,0");

		//	$overrideLineHeight = 3.45;
		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 112);
		$_PosX -= $this->_Margin_Right;
		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (90));

		// FDA Box
		$this->Rect($_PosX + 3, $_PosY - 5, 38, 82);

		// Box Label
		$this->SetXY($_PosX + 3, $_PosY - 2.0);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db>Nutrition Facts</db>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + .25, $_PosX + 39.5, $_PosY + .25);

		//		// Serving per Container
		//		if (!empty($entity['info']['servings_per_container']))
		//		{
		//			$this->SetXY($_PosX + 3, $_PosY + 1.75);
		//
		//			$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db9>" . $entity['info']['servings_per_container'] . " 6 servings per container (Large)</db9>", $showBorders, "L", 0);
		//		}

		$this->SetXY($_PosX + 3, $_PosY + 1.75);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db9> 6 servings per container (Large)</db9>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 3, $_PosY + 3.6);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db9> 3 servings per container (Medium) </db9>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 5);
		$this->MultiCellTag(38, 2.0, "<db2b>Serving size</db2b>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 3, $_PosY + 5);
		$this->MultiCellTag(38, 2.0, "<db2>                      " . $entity['info']['serving'] . "</db2>", $showBorders, "L", 0);
		// Note: the 22 spaces in the line above are intentional and provide a way to draw over the previous rectangle and avoid the text collision.
		// This could also be accomplished by concatenating the data and drawing in 1 rectangle.

		if (false) // the old way
		{
			// Serving
			$lines = $this->NbLines(34, "<db2b>" . $entity['info']['serving'] . "</db2b>");
			if ($lines <= 2)
			{
				$this->SetXY($_PosX + 3, $_PosY + 3.2);
				$this->MultiCellTag(38, 2.0, "<db2b>Serving size</db2b>", $showBorders, "L", 0);
				$this->SetXY($_PosX + 18, $_PosY + 3.2);
				$this->MultiCellTag(22, 2.0, "<db2b>" . $entity['info']['serving'] . "</db2b>", $showBorders, "R", 0);
			}
			else
			{
				$this->SetXY($_PosX + 3, $_PosY + 1);
				$this->MultiCellTag(38, 2.0, "<db2b>Serving size</db2b>", $showBorders, "R", 0);
				$this->SetXY($_PosX + 3, $_PosY + 3.2);
				$this->MultiCellTag(38, 2.0, "<db2b>" . $entity['info']['serving'] . "</db2b>", $showBorders, "R", 0);
			}
		}

		//Header Line
		$this->SetLineWidth(1.5);
		$this->Line($_PosX + 4.5, $_PosY + 8.5, $_PosX + 39.5, $_PosY + 8.5);

		// Amount Per serving header
		$this->SetXY($_PosX + 3, $_PosY + 10.8);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db3>Amount per serving</db3>", $showBorders, "L", 0);

		// Calories
		$this->SetXY($_PosX + 3, $_PosY + 10.6 + 3);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<t3>Calories</t3>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 10 + 3);
		$this->MultiCellTag(38, $overrideLineHeight, "<db>" . CTemplate::formatDecimal($entity['component'][1]['Calories']['value']) . " " . $entity['component'][1]['Calories']['measure_label'] . "</db>", $showBorders, "R", 0);

		//Header Line
		$this->SetLineWidth(0.8);
		$this->Line($_PosX + 4.5, $_PosY + 15.4, $_PosX + 39.5, $_PosY + 15.4);

		$this->SetXY($_PosX + 3, $_PosY + 17.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db3>% Daily Value*</db3>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 19, $_PosX + 39.5, $_PosY + 19);

		// Total Fat
		$this->SetXY($_PosX + 3, $_PosY + 15.9 + 5);
		$this->print_Nutrition_Element($overrideLineHeight, 13.5, "db4", $entity['component'][1]['Fat'], 'Total Fat ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Total Fat</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Fat']['value']) . $entity['component'][1]['Fat']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 15.9 + 5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Fat']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 17.2 + 5.2, $_PosX + 39.5, $_PosY + 17.2 + 5.2);

		// Saturated Fat
		$this->SetXY($_PosX + 5, $_PosY + 18.5 + 5.5);
		$this->print_Nutrition_Element($overrideLineHeight, 19.6, "db2", $entity['component'][1]['Sat Fat'], 'Saturated Fat ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Saturated Fat " . CTemplate::formatDecimal($entity['component'][1]['Sat Fat']['value']) . $entity['component'][1]['Sat Fat']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 18.5 + 5.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Sat Fat']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 19.8 + 5.5, $_PosX + 39.5, $_PosY + 19.8 + 5.5);

		// Trans fat
		$this->SetXY($_PosX + 5, $_PosY + 21.1 + 6);
		$this->print_Nutrition_Element($overrideLineHeight, 16, "db2", $entity['component'][1]['Trans Fats'], 'Trans Fat ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Trans Fat " . CTemplate::formatDecimal($entity['component'][1]['Trans Fats']['value']) . $entity['component'][1]['Trans Fats']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 22.4 + 6, $_PosX + 39.5, $_PosY + 22.4 + 6);

		// Cholesterol
		$this->SetXY($_PosX + 3, $_PosY + 23.7 + 6.5);
		$this->print_Nutrition_Element($overrideLineHeight, 17, "db4", $entity['component'][1]['Cholesterol'], 'Cholesterol ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Cholesterol</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Cholesterol']['value']) . $entity['component'][1]['Cholesterol']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 23.7 + 6.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Cholesterol']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 25 + 6.5, $_PosX + 39.5, $_PosY + 25 + 6.5);

		// Sodium
		$this->SetXY($_PosX + 3, $_PosY + 26.3 + 7);
		$this->print_Nutrition_Element($overrideLineHeight, 12.5, "db4", $entity['component'][1]['Sodium'], 'Sodium ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Sodium</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Sodium']['value']) . $entity['component'][1]['Sodium']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 26.3 + 7);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Sodium']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 27.6 + 7, $_PosX + 39.5, $_PosY + 27.6 + 7);

		// Total Carbs
		$this->SetXY($_PosX + 3, $_PosY + 28.9 + 7.5);
		$this->print_Nutrition_Element($overrideLineHeight, 24, "db4", $entity['component'][1]['Carbs'], 'Total Carbohydrate ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Total Carbohydrate</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Carbs']['value']) . $entity['component'][1]['Carbs']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 28.9 + 7.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Carbs']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 30.2 + 7.5, $_PosX + 39.5, $_PosY + 30.2 + 7.5);

		// Dietary Fiber
		$this->SetXY($_PosX + 5, $_PosY + 31.5 + 8);
		$this->print_Nutrition_Element($overrideLineHeight, 19, "db2", $entity['component'][1]['Fiber'], 'Dietary Fiber ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Dietary Fiber " . CTemplate::formatDecimal($entity['component'][1]['Fiber']['value']) . $entity['component'][1]['Fiber']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 31.5 + 8);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Fiber']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 32.8 + 8, $_PosX + 39.5, $_PosY + 32.8 + 8);

		// Sugar
		$this->SetXY($_PosX + 5, $_PosY + 34.1 + 8.5);
		$this->print_Nutrition_Element($overrideLineHeight, 19, "db2", $entity['component'][1]['Sugars'], 'Total Sugars ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Total Sugars " . CTemplate::formatDecimal($entity['component'][1]['Sugars']['value']) . $entity['component'][1]['Sugars']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 8, $_PosY + 35.4 + 8.5, $_PosX + 39.5, $_PosY + 35.4 + 8.5);

		//added sugar
		$this->SetXY($_PosX + 7, $_PosY + 45.7);
		$this->print_Nutrition_Element($overrideLineHeight, 16.6, "db2", $entity['component'][1]['Added Sugar'], 'Includes  ', $showBorders, "L", 0, " Added Sugars");
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Includes " . CTemplate::formatDecimal($entity['component'][1]['Added Sugar']['value']) . $entity['component'][1]['Added Sugar']['measure_label'] . " Added Sugars</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 39 + 8.5, $_PosX + 39.5, $_PosY + 39 + 8.5);

		// Protein
		$this->SetXY($_PosX + 3, $_PosY + 40.3 + 9);
		$this->print_Nutrition_Element($overrideLineHeight, 12.5, "db4", $entity['component'][1]['Protein'], 'Protein ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db4>Protein</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Protein']['value']) . $entity['component'][1]['Protein']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(1.5);
		$this->Line($_PosX + 4.5, $_PosY + 51.6, $_PosX + 39.5, $_PosY + 51.6);

		// Vitamin D
		$this->SetXY($_PosX + 3, $_PosY + 54.4);
		$entity['component'][1]['Vit D']['measure_label'] = utf8_decode($entity['component'][1]['Vit D']['measure_label']);
		$this->print_Nutrition_Element($overrideLineHeight, 14, "db2", $entity['component'][1]['Vit D'], 'Vitamin D ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Vitamin D " . CTemplate::formatDecimal($entity['component'][1]['Vit D']['value']) . $entity['component'][1]['Vit D']['measure_label'] . "</db2>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 3, $_PosY + 54.4);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Vit D']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 56, $_PosX + 39.5, $_PosY + 56);

		// Calcium
		$this->SetXY($_PosX + 3, $_PosY + 58);
		$this->print_Nutrition_Element($overrideLineHeight, 12.5, "db2", $entity['component'][1]['Calcium'], 'Calcium ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Calcium " . CTemplate::formatDecimal($entity['component'][1]['Calcium']['value']) . $entity['component'][1]['Calcium']['measure_label'] . "</db2>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 3, $_PosY + 58);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Calcium']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 59.6, $_PosX + 39.5, $_PosY + 59.6);

		// iron
		$this->SetXY($_PosX + 3, $_PosY + 61.6);
		$this->print_Nutrition_Element($overrideLineHeight, 8.6, "db2", $entity['component'][1]['Iron'], 'Iron ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Iron " . CTemplate::formatDecimal($entity['component'][1]['Iron']['value']) . $entity['component'][1]['Iron']['measure_label'] . "</db2>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 3, $_PosY + 61.6);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Iron']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 63.2, $_PosX + 39.5, $_PosY + 63.2);

		// Potassium
		$this->SetXY($_PosX + 3, $_PosY + 65.2);
		$this->print_Nutrition_Element($overrideLineHeight, 15, "db2", $entity['component'][1]['Potassium (K)'], 'Potassium ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Potassium " . CTemplate::formatDecimal($entity['component'][1]['Potassium (K)']['value']) . $entity['component'][1]['Potassium (K)']['measure_label'] . "</db2>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 3, $_PosY + 65.2);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Potassium (K)']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		// Notes
		$this->SetLineWidth(1);
		$this->Line($_PosX + 4.5, $_PosY + 67.5, $_PosX + 39.5, $_PosY + 67.5);

		$this->SetXY($_PosX + 3, $_PosY + 68.75);
		$this->MultiCellTag(38, 2.0, "<db6>*Percent Daily Value (DV) tells you how much a nutrient in a serving of food contributes to daily diet. 2,000 calories a day is used for general nutrition advice.</db6>", $showBorders, "L", 0);

		// end FDA label
		//$title = "<t3>" . $entity['info']['menu_item_name'] . "Gobble Gobble". "</t3>";
		$this->SetLineWidth(.1);

		$title = "<t3>" . CAppUtil::toPDFCharConversions($entity['info']['menu_item_name']) . "</t3>";

		//Item Title
		$lines = $this->NbLines(60, $title);

		$this->SetXY($_PosX + 43, $_PosY);
		$this->MultiCellTag(60, 3.45, $title, $showBorders, "L", 0);

		// ingredients
		$offset = $_PosY + ($lines * 4.4) + 1;
		$this->Rect($_PosX + 43, $offset, 59, 56.5 - (($lines - 1) * 4.4));

		$this->SetXY($_PosX + 43, $_PosY - .5 + ($lines * 4.4));
		$this->MultiCellTag(60, null, "<db7>Ingredients</db7>", $showBorders, "L", 0);

		if (strlen($entity['info']['ingredients']) > 2950)
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(60, 1.6, "<rss>" . $entity['info']['ingredients'] . "</rss>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 2500)
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(60, 1.6, "<rs>" . $entity['info']['ingredients'] . "</rs>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 1720)
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(60, 1.6, "<db8>" . $entity['info']['ingredients'] . "</db8>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 1000)
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(60, 2.0, "<db6>" . $entity['info']['ingredients'] . "</db6>", $showBorders, "L", 0);
		}
		else
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(60, 2.5, "<db2>" . $entity['info']['ingredients'] . "</db2>", $showBorders, "L", 0);
		}

		//Allergens
		$this->SetXY($_PosX + 43, $_PosY + 63.5);
		$this->MultiCellTag(60, null, "<db7>Contains</db7>", $showBorders, "L", 0);

		$this->Rect($_PosX + 43, $_PosY + 65, 59, 4);

		$this->SetXY($_PosX + 43, $_PosY + 65.5);
		$this->MultiCellTag(60, 1.5, "<db8>" . $entity['info']['allergens'] . "</db8>", $showBorders, "L", 0);

		// Disclaimer
		$this->SetXY($_PosX + 43, $_PosY + 69.5);
		$this->MultiCellTag(60, 1.5, "<db8>May Contain: Milk, Eggs, Fish, Shellfish, Tree Nuts, Peanuts, Wheat, Soybeans, Sesame.</db8>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 43, $_PosY + 71);
		$this->MultiCellTag(60, 1.5, "<db8>Variations in ingredients and preparation, as well as substitutions, will increase or decrease any stated nutritional values. Items may vary by store, may not be available at all locations, and are subject to change.</db8>", $showBorders, "L", 0);

		if (!empty($Store))
		{
			// Manufactured by
			$this->SetXY($_PosX + 43, $_PosY + 75.5);
			$this->MultiCellTag(60, 1.5, "<db8>Manufactured by: " . $Store->store_name . ", " . $Store->address_line1 . ((!empty($Store->address_line2)) ? ', ' . $Store->address_line2 : '') . ", " . $Store->city . ", " . $Store->state_id . " " . $Store->postal_code . ".</db8>", $showBorders, "L", 0);
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	function Add_Nutrition_Label_four_up($entity, $showBorders = 0, $overrideLineHeight = 3.45, $Store = null, $isSide = false)
	{
		$showBorders = false;

		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$this->SetStyle("t3", "helvetica", "B", 8, "0,0,0");
		$this->SetStyle("db", "helvetica", "B", 14, "0,0,0");
		$this->SetStyle("db2", "helvetica", "", 6, "0,0,0");
		$this->SetStyle("db2b", "helvetica", "B", 6, "0,0,0");
		$this->SetStyle("db3", "helvetica", "B", 5.2, "0,0,0");
		$this->SetStyle("db4", "helvetica", "B", 6, "0,0,0");
		$this->SetStyle("db5", "helvetica", "", 5, "0,0,0");
		$this->SetStyle("db6s", "helvetica", "", 4, "0,0,0");
		$this->SetStyle("db6", "helvetica", "", 4.5, "0,0,0");
		$this->SetStyle("db6l", "helvetica", "", 5.5, "0,0,0");
		$this->SetStyle("db7", "helvetica", "B", 7, "0,0,0");
		$this->SetStyle("db8", "helvetica", "", 4, "0,0,0");
		$this->SetStyle("rs", "helvetica", "", 3, "0,0,0");
		$this->SetStyle("rss", "helvetica", "", 2.5, "0,0,0");
		$this->SetStyle("db9", "helvetica", "", 5.5, "0,0,0");
		$this->SetStyle("color", "times", "B", 13, "92, 102, 114");
		$this->SetStyle("title", "times", "B", 11, "92, 102, 114");
		$this->SetStyle("t7", "helvetica", "B", 6, "0,0,0");

		//	$overrideLineHeight = 3.45;
		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 136);
		$_PosX -= $this->_Margin_Right;
		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (108));

		if ($isSide)
		{
			$this->SetXY($_PosX + 100, $_PosY);
			$this->MultiCellTag($this->_Width, $this->_Line_Height, "<title>DREAM DINNERS</title>", $showBorders, "L", 0);

			$this->SetXY($_PosX + 100, $_PosY + 2.65);
			$this->MultiCellTag($this->_Width, $this->_Line_Height, "<t7>Sides & Sweets</t7>", $showBorders, "L", 0);
		}
		else
		{
			$this->SetXY($_PosX + 95, $_PosY);
			$this->MultiCellTag($this->_Width, $this->_Line_Height, "<color>DREAM DINNERS</color>", $showBorders, "L", 0);
		}

		$yRelative = 2;
		// FDA Box
		$this->SetLineWidth(.1);

		$this->Rect($_PosX + 3, $_PosY, 38, 96);

		// Box Label
		$this->SetXY($_PosX + 3, $_PosY + $yRelative);
		$this->MultiCellTag($this->_Width, 5, "<db>Nutrition Facts</db>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 6.25 + $yRelative, $_PosX + 39.5, $_PosY + 6.25 + $yRelative);

		$yRelative = $yRelative + 7;
		// Serving per Container
		if (!empty($entity['info']['servings_per_container']) || $entity["info"]["menu_item_category_id"] < 9)
		{
			$this->SetXY($_PosX + 3, $_PosY + 1.75 + $yRelative);

			if ($entity["info"]["menu_item_category_id"] == 9)
			{
				$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db9>" . $entity['info']['servings_per_container'] . " servings per container</db9>", $showBorders, "L", 0);
			}
			else
			{
				$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db9>Lrg (6) & Med (3) servings per container</db9>", $showBorders, "L", 0);
			}
		}

		$this->SetXY($_PosX + 3, $_PosY + 3.2 + $yRelative);
		$this->MultiCellTag(38, 2.0, "<db2b>Serving size</db2b>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 3, $_PosY + 3.2 + $yRelative);
		$this->MultiCellTag(38, 2.0, "<db2>                      " . $entity['info']['serving'] . "</db2>", $showBorders, "L", 0);

		//Header Line
		$this->SetLineWidth(1.5);
		$this->Line($_PosX + 4.5, $_PosY + 8.5 + $yRelative, $_PosX + 39.5, $_PosY + 8.5 + $yRelative);

		// Amount Per serving header
		$this->SetXY($_PosX + 3, $_PosY + 10.8 + $yRelative);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db3>Amount per serving</db3>", $showBorders, "L", 0);

		// Calories
		$this->SetXY($_PosX + 3, $_PosY + 13.6 + $yRelative);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<t3>Calories</t3>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 13 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db>" . CTemplate::formatDecimal($entity['component'][1]['Calories']['value']) . " " . $entity['component'][1]['Calories']['measure_label'] . "</db>", $showBorders, "R", 0);

		//Header Line
		$this->SetLineWidth(0.8);
		$this->Line($_PosX + 4.5, $_PosY + 15.4 + $yRelative, $_PosX + 39.5, $_PosY + 15.4 + $yRelative);

		$this->SetXY($_PosX + 3, $_PosY + 17.5 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db3>% Daily Value*</db3>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 19 + $yRelative, $_PosX + 39.5, $_PosY + 19 + $yRelative);

		// Total Fat
		$this->SetXY($_PosX + 3, $_PosY + 20.9 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 16.5, "db4", $entity['component'][1]['Fat'], 'Total Fat ', $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 20.9 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Fat']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 22.4 + $yRelative, $_PosX + 39.5, $_PosY + 22.4 + $yRelative);

		// Saturated Fat
		$this->SetXY($_PosX + 5, $_PosY + 24 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 22.6, "db2", $entity['component'][1]['Sat Fat'], 'Saturated Fat ', $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 24 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Sat Fat']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 25.3 + $yRelative, $_PosX + 39.5, $_PosY + 25.3 + $yRelative);

		// Trans fat
		$this->SetXY($_PosX + 5, $_PosY + 27.1 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 19, "db2", $entity['component'][1]['Trans Fats'], 'Trans Fat ', $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 28.4 + $yRelative, $_PosX + 39.5, $_PosY + 28.4 + $yRelative);

		// Cholesterol
		$this->SetXY($_PosX + 3, $_PosY + 30.2 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 20, "db4", $entity['component'][1]['Cholesterol'], 'Cholesterol ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Cholesterol</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Cholesterol']['value']) . $entity['component'][1]['Cholesterol']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 30.2 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Cholesterol']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 31.5 + $yRelative, $_PosX + 39.5, $_PosY + 31.5 + $yRelative);

		// Sodium
		$this->SetXY($_PosX + 3, $_PosY + 33.3 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 15.5, "db4", $entity['component'][1]['Sodium'], 'Sodium ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Sodium</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Sodium']['value']) . $entity['component'][1]['Sodium']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 33.3 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Sodium']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 34.6 + $yRelative, $_PosX + 39.5, $_PosY + 34.6 + $yRelative);

		// Total Carbs
		$this->SetXY($_PosX + 3, $_PosY + 36.4 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 27, "db4", $entity['component'][1]['Carbs'], 'Total Carbohydrate ', $showBorders, "L", 0);
		//$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Total Carbohydrate</db4> <db2>" . CTemplate::formatDecimal($entity['component'][1]['Carbs']['value']) . $entity['component'][1]['Carbs']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 36.4 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Carbs']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 37.7 + $yRelative, $_PosX + 39.5, $_PosY + 37.7 + $yRelative);

		// Dietary Fiber
		$this->SetXY($_PosX + 5, $_PosY + 39.5 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 22, "db2", $entity['component'][1]['Fiber'], 'Dietary Fiber ', $showBorders, "L", 0);
		//$this->MultiCellTag(38, $overrideLineHeight, "<db2>Dietary Fiber " . CTemplate::formatDecimal($entity['component'][1]['Fiber']['value']) . $entity['component'][1]['Fiber']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 39.5 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['component'][1]['Fiber']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 40.8 + $yRelative, $_PosX + 39.5, $_PosY + 40.8 + $yRelative);

		// Sugar
		$this->SetXY($_PosX + 5, $_PosY + 42.6 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 22, "db2", $entity['component'][1]['Sugars'], 'Total Sugars ', $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 8, $_PosY + 43.9 + $yRelative, $_PosX + 39.5, $_PosY + 43.9 + $yRelative);

		//added sugar
		$this->SetXY($_PosX + 7, $_PosY + 45.7 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 19.6, "db2", $entity['component'][1]['Added Sugar'], 'Includes  ', $showBorders, "L", 0, " Added Sugars");

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 47.7 + $yRelative, $_PosX + 39.5, $_PosY + 47.7 + $yRelative);

		// Protein
		$this->SetXY($_PosX + 3, $_PosY + 49.3 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 15.5, "db4", $entity['component'][1]['Protein'], 'Protein ', $showBorders, "L", 0);

		$this->SetLineWidth(1.5);
		$this->Line($_PosX + 4.5, $_PosY + 51.6 + $yRelative, $_PosX + 39.5, $_PosY + 51.6 + $yRelative);

		// Vitamin D
		$this->SetXY($_PosX + 3, $_PosY + 54.4 + $yRelative);
		$entity['component'][1]['Vit D']['measure_label'] = utf8_decode($entity['component'][1]['Vit D']['measure_label']);
		$this->print_Nutrition_Element($overrideLineHeight, 17, "db2", $entity['component'][1]['Vit D'], 'Vitamin D ', $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 54.4 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Vit D']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 56 + $yRelative, $_PosX + 39.5, $_PosY + 56 + $yRelative);

		// Calcium
		$this->SetXY($_PosX + 3, $_PosY + 58 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 15.5, "db2", $entity['component'][1]['Calcium'], 'Calcium ', $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 58 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Calcium']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 59.6 + $yRelative, $_PosX + 39.5, $_PosY + 59.6 + $yRelative);

		// iron
		$this->SetXY($_PosX + 3, $_PosY + 61.6 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 11.6, "db2", $entity['component'][1]['Iron'], 'Iron ', $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 61.6 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Iron']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 63.2 + $yRelative, $_PosX + 39.5, $_PosY + 63.2 + $yRelative);

		// Potassium
		$this->SetXY($_PosX + 3, $_PosY + 65.2 + $yRelative);
		$this->print_Nutrition_Element($overrideLineHeight, 18, "db2", $entity['component'][1]['Potassium (K)'], 'Potassium ', $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 65.2 + $yRelative);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>" . $entity['component'][1]['Potassium (K)']['percent_daily_value'] . "%</db2>", $showBorders, "R", 0);

		// Notes
		$this->SetLineWidth(1);
		$this->Line($_PosX + 4.5, $_PosY + 67.5 + $yRelative, $_PosX + 39.5, $_PosY + 67.5 + $yRelative);

		$this->SetXY($_PosX + 3, $_PosY + 68.75 + $yRelative);
		$this->MultiCellTag(38, 2.0, "<db6l>*Percent Daily Value (DV) tells you how much a nutrient in a serving of food contributes to daily diet. 2,000 calories a day is used for general nutrition advice.</db6l>", $showBorders, "L", 0);

		// end FDA label
		$this->SetLineWidth(.1);

		$title = "<t3>" . CAppUtil::toPDFCharConversions($entity['info']['menu_item_name']) . "</t3>";

		//Item Title
		$lines = $this->NbLines(52, $title);

		$this->SetXY($_PosX + 43, $_PosY);
		$this->MultiCellTag(54, 3.45, $title, $showBorders, "L", 0);

		// ingredients
		$offset = $_PosY + ($lines * 4.4) + 1;
		$this->Rect($_PosX + 43, $offset, 91, 77 - (($lines - 1) * 4.4));

		//for testing random instruction sizes
		$length = '';
		//		$txt = $this->generateRandomString( rand(1500, 9010));
		//		$entity['info']['ingredients'] = $txt;
		//		$length = '-'.strlen($txt);

		$this->SetXY($_PosX + 43, $_PosY - .2 + ($lines * 4.4));
		$this->MultiCellTag(60, null, "<t7>Ingredients" . $length . "</t7>", $showBorders, "L", 0);

		//About ~9000 characters, including spaces, max
		if (strlen($entity['info']['ingredients']) > 6700)//2.5
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(90, 1.6, "<rss>" . $entity['info']['ingredients'] . "</rss>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 5100)//3
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(90, 1.6, "<rs>" . $entity['info']['ingredients'] . "</rs>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 4100)//4
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(90, 1.6, "<db6s>" . $entity['info']['ingredients'] . "</db6s>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 3000)//5
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(90, 1.6, "<db5>" . $entity['info']['ingredients'] . "</db5>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 2200)//5.5
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(90, 2.0, "<db6l>" . $entity['info']['ingredients'] . "</db6l>", $showBorders, "L", 0);
		}
		else//6font
		{
			$this->SetXY($_PosX + 43, $_PosY + 1.5 + ($lines * 4.4));
			$this->MultiCellTag(90, 2.5, "<db2>" . $entity['info']['ingredients'] . "</db2>", $showBorders, "L", 0);
		}

		if (!empty($entity['info']['recipe_id']))
		{
			$this->SetXY($_PosX + 122, $_PosY + 82.60);
			$this->Image($this->getNutritionQrCodePath($entity['info']['recipe_id'], false), null, null, 12);
		}

		//Allergens
		$this->SetXY($_PosX + 43, $_PosY + 84.5);
		$this->MultiCellTag(60, null, "<db7>Contains</db7>", $showBorders, "L", 0);

		$this->Rect($_PosX + 43, $_PosY + 86, 78, 4);

		$this->SetXY($_PosX + 43, $_PosY + 86.5);
		$this->MultiCellTag(77, 1.5, "<db8>" . $entity['info']['allergens'] . "</db8>", $showBorders, "L", 0);

		// Disclaimer
		$this->SetXY($_PosX + 43, $_PosY + 90.5);
		$this->MultiCellTag(77, 1.5, "<db8>May Contain: Milk, Eggs, Fish, Shellfish, Tree Nuts, Peanuts, Wheat, Soybeans, Sesame.</db8>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 43, $_PosY + 92);
		$this->MultiCellTag(75, 1.5, "<db8>Variations in ingredients and preparation, as well as substitutions, will increase or decrease any stated nutritional values. Items may vary by store, may not be available at all locations, and are subject to change.</db8>", $showBorders, "L", 0);

		if (!empty($Store))
		{
			// Manufactured by
			$this->SetXY($_PosX + 43, $_PosY + 95);
			$this->MultiCellTag(60, 1.5, "<db8>Manufactured by: " . $Store->store_name . ", " . $Store->address_line1 . ((!empty($Store->address_line2)) ? ', ' . $Store->address_line2 : '') . ", " . $Store->city . ", " . $Store->state_id . " " . $Store->postal_code . ".</db8>", $showBorders, "L", 0);
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()+=-|';
		$charactersLength = strlen($characters);
		$randomString = '';
		$charSinseLastSpace = 0;
		$charLimit = rand(9, 11);
		for ($i = 0; $i < $length; $i++)
		{
			$rand = rand(4, 11);

			if ($i % $rand == 0 || $charSinseLastSpace > $charLimit)
			{
				$randomString .= ' ';
				$charSinseLastSpace = 0;
			}
			else
			{
				$charSinseLastSpace++;
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
		}

		return $randomString;
	}

	function Add_Manufacturer_Nutrition_Label($entity, $showBorders = 0, $overrideLineHeight = 3.45, $Store = null)
	{
		$pushDown = 0;
		//$showBorders = 1;

		if ($Store->id == 257)
		{
			if ($entity['info']['label_type'] == 'avery_8164')
			{
				if ($this->_COUNTY == 0) // move top row down
				{
					$pushDown = 3;
				}
			}
			else if ($entity['info']['label_type'] == 'perforated_labels')
			{
				if ($this->_COUNTY == 2) // move bottom row down
				{
					$pushDown = 4;
				}
			}
		}

		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$this->SetFont('helvetica', '', 14);
		$this->SetTextColor(0, 0, 0);
		$this->SetFillColor(0, 0, 0);
		$this->SetTitle("Dream Dinners");

		$this->SetStyle("t3", "helvetica", "B", 9, "0,0,0");

		$this->SetStyle("db", "helvetica", "B", 14, "0,0,0");
		$this->SetStyle("db2", "helvetica", "", 6, "0,0,0");
		$this->SetStyle("db3", "helvetica", "B", 5.2, "0,0,0");
		$this->SetStyle("db4", "helvetica", "B", 6, "0,0,0");
		$this->SetStyle("db6", "helvetica", "", 4.5, "0,0,0");
		$this->SetStyle("db7", "helvetica", "B", 7, "0,0,0");
		$this->SetStyle("db8", "helvetica", "", 4, "0,0,0");
		$this->SetStyle("db9", "helvetica", "", 5.5, "0,0,0");

		$this->SetStyle("ing", "helvetica", "", 6, "0,0,0");
		$this->SetStyle("ing2", "helvetica", "", 5, "0,0,0");
		$this->SetStyle("ing3", "helvetica", "", 4.5, "0,0,0");
		$this->SetStyle("ing4", "helvetica", "", 4, "0,0,0");

		$this->SetStyle("asbl", "helvetica", "", 6.5, "0,0,0"); // 1/16th inch per FDA req for manufactured by
		$this->SetStyle("ntwt", "helvetica", "B", 12, "0,0,0"); // net weight must be 1/8th inch or 3/16th inch depending on principal display
		$this->SetStyle("ntwt2", "helvetica", "", 14, "0,0,0"); // net weight must be 1/8th inch or 3/16th inch depending on principal display

		//	$overrideLineHeight = 3.45;
		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 112);
		$_PosX -= $this->_Margin_Right;
		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (90)) + $pushDown;

		// menu class logo
		if ($entity['info']['menu_item_category_id'] == 9)
		{
			$class_logo = ASSETS_PATH . '/pdf_label/nutrition_label_manufacturer_finishing_touch_bg.png';
		}
		else
		{
			$class_logo = ASSETS_PATH . '/pdf_label/nutrition_label_manufacturer_entree_bg.png';
		}
		$this->SetXY($_PosX + 41.75, $_PosY - 6);
		$this->Image($class_logo, $this->GetX(), $this->GetY(), 51);

		// FDA Box
		//$this->Rect($_PosX + 3, $_PosY - 5, 38, 82);
		$this->Rect($_PosX + 3, $_PosY - 5, 38, 67);

		// Box Label
		$this->SetXY($_PosX + 3, $_PosY - 2.25);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db>Nutrition Facts</db>", $showBorders, "L", 0);

		// Serving per Container
		$this->SetXY($_PosX + 3, $_PosY + .5);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db9>Serving Per Container " . $entity['info']['servings_per_container'] . "</db9>", $showBorders, "L", 0);

		// Serving
		$this->SetXY($_PosX + 3, $_PosY + 1.5);
		$this->MultiCellTag(38, 2.0, "<db9>Serving Size " . $entity['info']['serving_size_combined'] . "</db9>", $showBorders, "L", 0);

		//Header Line
		$this->SetLineWidth(1.5);
		$this->Line($_PosX + 4.5, $_PosY + 8.5, $_PosX + 39.5, $_PosY + 8.5);

		// Amount Per serving header
		$this->SetXY($_PosX + 3, $_PosY + 10.8);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db3>Amount Per Serving</db3>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 9.2 + 3, $_PosX + 39.5, $_PosY + 9.2 + 3);

		// Calories
		$this->SetXY($_PosX + 3, $_PosY + 10.6 + 3);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Calories</db4> <db2>" . CTemplate::formatDecimal($entity['element']['Calories']['value']) . " " . $entity['element']['Calories']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 10.6 + 3);
		$this->MultiCellTag(38, $overrideLineHeight, "<db2>Calories from Fat " . CTemplate::formatDecimal(($entity['element']['Fat']['value'] * 9)) . "</db2>", $showBorders, "R", 0);

		//Header Line
		$this->SetLineWidth(0.8);
		$this->Line($_PosX + 4.5, $_PosY + 15.4, $_PosX + 39.5, $_PosY + 15.4);

		$this->SetXY($_PosX + 3, $_PosY + 17.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db3>% Daily Value*</db3>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 19, $_PosX + 39.5, $_PosY + 19);

		// Total Fat
		$this->SetXY($_PosX + 3, $_PosY + 15.9 + 5);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Total Fat</db4> <db2>" . CTemplate::formatDecimal($entity['element']['Fat']['value']) . " " . $entity['element']['Fat']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 15.9 + 5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['element']['Fat']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 6, $_PosY + 17.2 + 5.2, $_PosX + 39.5, $_PosY + 17.2 + 5.2);

		// Saturated Fat
		$this->SetXY($_PosX + 5, $_PosY + 18.5 + 5.5);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Saturated Fat " . CTemplate::formatDecimal($entity['element']['Sat Fat']['value']) . " " . $entity['element']['Sat Fat']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 18.5 + 5.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['element']['Sat Fat']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 6, $_PosY + 19.8 + 5.5, $_PosX + 39.5, $_PosY + 19.8 + 5.5);

		// Trans fat
		$this->SetXY($_PosX + 5, $_PosY + 21.1 + 6);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Trans Fat " . CTemplate::formatDecimal($entity['element']['Trans Fats']['value']) . " " . $entity['element']['Trans Fats']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 22.4 + 6, $_PosX + 39.5, $_PosY + 22.4 + 6);

		// Cholesterol
		$this->SetXY($_PosX + 3, $_PosY + 23.7 + 6.5);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Cholesterol</db4> <db2>" . CTemplate::formatDecimal($entity['element']['Cholesterol']['value']) . " " . $entity['element']['Cholesterol']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 23.7 + 6.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['element']['Cholesterol']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 25 + 6.5, $_PosX + 39.5, $_PosY + 25 + 6.5);

		// Sodium
		$this->SetXY($_PosX + 3, $_PosY + 26.3 + 7);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Sodium</db4> <db2>" . CTemplate::formatDecimal($entity['element']['Sodium']['value']) . " " . $entity['element']['Sodium']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 26.3 + 7);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['element']['Sodium']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 27.6 + 7, $_PosX + 39.5, $_PosY + 27.6 + 7);

		// Total Carbs
		$this->SetXY($_PosX + 3, $_PosY + 28.9 + 7.5);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Total Carbohydrate</db4> <db2>" . CTemplate::formatDecimal($entity['element']['Carbs']['value']) . " " . $entity['element']['Carbs']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 28.9 + 7.5);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['element']['Carbs']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 6, $_PosY + 30.2 + 7.5, $_PosX + 39.5, $_PosY + 30.2 + 7.5);

		// Dietary Fiber
		$this->SetXY($_PosX + 5, $_PosY + 31.5 + 8);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Dietary Fiber " . CTemplate::formatDecimal($entity['element']['Fiber']['value']) . " " . $entity['element']['Fiber']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 31.5 + 8);
		$this->MultiCellTag(38, $overrideLineHeight, "<db4>" . $entity['element']['Fiber']['percent_daily_value'] . "</db4><db2>%</db2>", $showBorders, "R", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 6, $_PosY + 32.8 + 8, $_PosX + 39.5, $_PosY + 32.8 + 8);

		// Sugar
		$this->SetXY($_PosX + 5, $_PosY + 34.1 + 8.5);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Sugars " . CTemplate::formatDecimal($entity['element']['Sugars']['value']) . " " . $entity['element']['Sugars']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 35.4 + 8.5, $_PosX + 39.5, $_PosY + 35.4 + 8.5);

		// Protein
		$this->SetXY($_PosX + 3, $_PosY + 36.7 + 9);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db4>Protein</db4> <db2>" . CTemplate::formatDecimal($entity['element']['Protein']['value']) . " " . $entity['element']['Protein']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(1.5);
		$this->Line($_PosX + 4.5, $_PosY + 48, $_PosX + 39.5, $_PosY + 48);

		$bulletString = "<db2>\x95</db2>";

		// Vitamins
		$this->SetXY($_PosX + 3, $_PosY + 50.8);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Vitamin A " . CTemplate::formatDecimal($entity['element']['% Vit A']['value']) . " " . $entity['element']['% Vit A']['measure_label'] . "</db2>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 19, $_PosY + 50.8);
		$this->MultiCellTag(43, $overrideLineHeight, $bulletString, $showBorders, "C", 0);
		$this->SetXY($_PosX + 25, $_PosY + 50.8);
		$this->MultiCellTag(40, $overrideLineHeight, "<db2>Vitamin C " . CTemplate::formatDecimal($entity['element']['% Vit c']['value']) . " " . $entity['element']['% Vit c']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 52.4, $_PosX + 39.5, $_PosY + 52.4);

		// Nutrients
		$this->SetXY($_PosX + 3, $_PosY + 54.4);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, "<db2>Calcium " . CTemplate::formatDecimal($entity['element']['% Calcium']['value']) . " " . $entity['element']['% Calcium']['measure_label'] . "</db2>", $showBorders, "L", 0);
		$this->SetXY($_PosX + 19, $_PosY + 54.4);
		$this->MultiCellTag(43, $overrideLineHeight, $bulletString, $showBorders, "C", 0);
		$this->SetXY($_PosX + 25, $_PosY + 54.4);
		$this->MultiCellTag(40, $overrideLineHeight, "<db2>Iron " . CTemplate::formatDecimal($entity['element']['% Iron']['value']) . " " . $entity['element']['% Iron']['measure_label'] . "</db2>", $showBorders, "L", 0);

		$this->SetLineWidth(.1);
		$this->Line($_PosX + 4.5, $_PosY + 56, $_PosX + 39.5, $_PosY + 56);

		$this->SetXY($_PosX + 3, $_PosY + 56.5);
		$this->MultiCellTag(38, 1.75, "<db6>*Percent Daily Values are based on a 2000 calorie diet. Your daily values may be higher or lower depending on your calorie needs.</db6>", $showBorders, "L", 0);

		/*
				$this->SetXY($_PosX + 15, $_PosY+ 63);
				$this->MultiCellTag(10, 1.8, "<db6>Calories</db6>", $showBorders, "L", 0);

				$this->SetXY($_PosX + 25, $_PosY+ 63);
				$this->MultiCellTag(10, 1.8, "<db6>2000</db6>", $showBorders, "L", 0);

				$this->SetXY($_PosX + 33, $_PosY+ 63);
				$this->MultiCellTag(10, 1.8, "<db6>2500</db6>", $showBorders, "L", 0);

				$this->SetLineWidth(.1);
				$this->Line($_PosX + 4.5, $_PosY+ 65, $_PosX + 39.5, $_PosY+ 65);

				$this->SetXY($_PosX + 3, $_PosY+ 65.5);
				$this->MultiCellTag(20, 1.8, "<db6>Total Fat\n Sat Fat\nCholesterol\nSodium\nTotal Carbohydrate\n Dietary Fiber</db6>", $showBorders, "L", 0);

				$this->SetXY($_PosX + 15, $_PosY+ 65.5);
				$this->MultiCellTag(20, 1.8, "<db6>Less than\nLess than\nLess than\nLess than</db6>", $showBorders, "L", 0);

				$this->SetXY($_PosX + 25, $_PosY+ 65.5);
				$this->MultiCellTag(20, 1.8, "<db6>65g\n20g\n30mg\n2,400mg\n300g\n25g</db6>", $showBorders, "L", 0);

				$this->SetXY($_PosX + 33, $_PosY+ 65.5);
				$this->MultiCellTag(20, 1.8, "<db6>80g\n25g\n300mg\n2,400mg\n375g\n30g\n</db6>", $showBorders, "L", 0);
				// end FDA label
		*/
		// title
		$title = "<t3>" . $entity['info']['recipe_name'] . "</t3>";

		//Item Title
		$lines = $this->NbLines(59, $title);

		$this->SetXY($_PosX + 42, $_PosY + 5);
		$this->MultiCellTag(59, 3.45, $title, $showBorders, "L", 0);

		// ingredients
		$offset = $_PosY + ($lines * 4.4) + 7.5;
		$this->Rect($_PosX + 43, $offset, 59, 54 - (($lines - 1) * 4.4));

		$this->SetXY($_PosX + 42.5, $_PosY + 5.3 + ($lines * 4.4));
		$this->MultiCellTag(60, null, "<db7>Ingredients</db7>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 42.5, $_PosY + +8 + ($lines * 4.4));

		if (strlen($entity['info']['ingredients']) > 2400)
		{
			$this->MultiCellTag(60, 1.5, "<ing4>" . $entity['info']['ingredients'] . "</ing4>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 1700)
		{
			$this->MultiCellTag(60, 1.5, "<ing3>" . $entity['info']['ingredients'] . "</ing3>", $showBorders, "L", 0);
		}
		else if (strlen($entity['info']['ingredients']) > 1400)
		{
			$this->MultiCellTag(60, 2, "<ing2>" . $entity['info']['ingredients'] . "</ing2>", $showBorders, "L", 0);
		}
		else
		{
			$this->MultiCellTag(60, 2, "<ing>" . $entity['info']['ingredients'] . "</ing>", $showBorders, "L", 0);
		}

		// Contains allergens
		$this->SetXY($_PosX + 42.5, $_PosY + 67.5);
		$this->MultiCellTag(60, null, "<db7>Contains</db7>", $showBorders, "L", 0);

		$this->Rect($_PosX + 43, $_PosY + 69, 59, 3.25);

		$this->SetXY($_PosX + 42.5, $_PosY + 69.5);
		$this->MultiCellTag(60, 2.5, "<db2>" . $entity['info']['allergens'] . "</db2>", $showBorders, "L", 0);

		// Disclaimer
		$this->SetXY($_PosX + 42, $_PosY + 72.75);
		$this->MultiCellTag(60, 1.5, "<db8>May Contain: Milk, Eggs, Fish, Shellfish, Tree Nuts, Peanuts, Wheat, Soybeans, Sesame.</db8>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 42, $_PosY + 74.25);
		$this->MultiCellTag(60, 1.5, "<db8>Variations in ingredients and preparation, as well as substitutions, will increase or decrease any stated nutritional values. Items may vary by store, may not be available at all locations, and are subject to change.</db8>", $showBorders, "L", 0);

		// Assembled by
		$this->SetXY($_PosX + 2, $_PosY + 62.25);
		$this->MultiCellTag(38, 2.25, "<asbl>ASSEMBLED BY: " . strtoupper($Store->store_name . ", " . $Store->address_line1 . ((!empty($Store->address_line2)) ? ', ' . $Store->address_line2 : '') . ", " . $Store->city . ", " . $Store->state_id . " " . $Store->postal_code) . "</asbl>", $showBorders, "C", 0);

		// Net Weight
		//$this->SetXY($_PosX + 87, $_PosY + 68.5);
		//$this->MultiCellTag(16, 2.75, "<ntwt>NET WT.</ntwt>", $showBorders, "C", 0);
		$this->SetXY($_PosX + 2, $_PosY + 72);
		$this->MultiCellTag(42, 4, "<ntwt2>" . $entity['info']['weight'] . "oz (" . round($entity['info']['weight'] * 28.3495231) . "g)</ntwt2>", $showBorders, "C", 0);

		// UPC
		//$this->SetXY($_PosX + 42.5, $_PosY + 62);
		//$this->RotatedText("<db8>UPC " . $entity['info']['upc'] . "</db8>", 90);

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	function Add_Manufacturer_Cooking_Instruction_Label($entity, $showBorders, $Store)
	{
		$pushDown = 0;

		if ($Store->id == 257)
		{
			if ($entity['info']['label_type'] == 'avery_8164')
			{
				if ($this->_COUNTY == 0) // move top row down
				{
					$pushDown = 3;
				}
			}
			else if ($entity['info']['label_type'] == 'perforated_labels')
			{
				if ($this->_COUNTY == 0) // move top row up
				{
					$pushDown = -2;
				}
				else if ($this->_COUNTY == 2) // move bottom row down
				{
					$pushDown = 3;
				}
			}
		}

		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$this->SetFont('helvetica', '', 14);
		$this->SetTextColor(0, 0, 0);
		$this->SetFillColor(0, 0, 0);
		$this->SetTitle("Dream Dinners");

		$this->SetStyle("t3", "helvetica", "B", 8, "0,0,0");
		$this->SetStyle("t4", "helvetica", "", 8, "0,0,0");
		$this->SetStyle("ttt", "helvetica", "B", 7, "0,0,0");
		$this->SetStyle("bb", "helvetica", "", 7, "0,0,0");
		$this->SetStyle("tft", "helvetica", "", 6, "0,0,0");
		$this->SetStyle("tftb", "helvetica", "B", 6, "0,0,0");

		$this->SetStyle("asbl", "helvetica", "", 6, "0,0,0"); // 1/16th inch per FDA req for manufactured by

		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 112);
		$_PosX -= $this->_Margin_Right;
		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (90)) + $pushDown;

		// menu class logo
		if ($entity['info']['menu_item_category_id'] == 9)
		{
			$class_logo = ASSETS_PATH . '/pdf_label/nutrition_label_manufacturer_finishing_touch_bg.png';
		}
		else
		{
			$class_logo = ASSETS_PATH . '/pdf_label/nutrition_label_manufacturer_entree_bg.png';
		}
		$this->SetXY($_PosX + 3, $_PosY - 6);
		$this->Image($class_logo, $this->GetX(), $this->GetY(), 51);

		// Time to table
		$this->SetXY($_PosX + 80, $_PosY - 3);
		$this->MultiCellTag(20, 2.5, "<ttt>Size: " . ucfirst(strtolower($entity['info']['recipe_size'])) . "\n Time to Table " . $entity['info']['cooking_time'] . "</ttt>", $showBorders, "C", 0);

		// recipe title
		$this->SetXY($_PosX + 3, $_PosY + 7);
		$this->MultiCellTag($this->_Width, 3.45, "<t3>" . $entity['info']['recipe_name'] . "</t3>", $showBorders, "L", 0);

		// instructions
		$this->SetXY($_PosX + 3, $_PosY + 14);
		$this->MultiCellTag($this->_Width, 3.45, "<t4>" . $entity['info']['cooking_instructions'] . "</t4>", $showBorders, "L", 0);

		// Best by
		$this->SetXY($_PosX + 3, $_PosY + 66);
		$this->MultiCellTag(63, 2.5, "<bb>Best By: " . $entity['info']['use_by_date'] . "</bb>", $showBorders, "L", 0);

		// food safety
		$this->SetXY($_PosX + 3, $_PosY + 69.5);
		$this->MultiCellTag(63, 2, "<tftb>FOOD SAFETY ALERT - CAUTION:</tftb><tft>This meal may contain raw ingredients that cannot be eaten prior to thorough cooking. Store in freezer until ready to thaw or prepare.</tft>", $showBorders, "L", 0);

		// Assembled by
		$this->SetXY($_PosX + 3, $_PosY + 74.5);
		$this->MultiCellTag(63, 2.25, "<asbl>" . strtoupper($Store->store_name . ", " . $Store->address_line1 . ((!empty($Store->address_line2)) ? ', ' . $Store->address_line2 : '') . ", " . $Store->city . ", " . $Store->state_id . " " . $Store->postal_code) . "</asbl>", $showBorders, "L", 0);

		// barcode generate
		/* DISABLED 1/24/2017 for transition to Corporate Crate */
		if (false && !empty($entity['info']['upc']))
		{
			$barcodePath = ASSETS_PATH . '/pdf_label/barcode/' . $entity['info']['upc'] . '.png';

			if (!file_exists($barcodePath)) // only generate if not generated before
			{
				require_once('Barcode2.php');
				$image = Image_Barcode2::draw($entity['info']['upc'], Image_Barcode2::BARCODE_UPCA, Image_Barcode2::IMAGE_PNG, false, 30, 1);
				$upc = imagepng($image, $barcodePath);
			}

			// barcode embed
			$this->SetXY($_PosX + 66.5, $_PosY + 66);
			$this->Image($barcodePath, $this->GetX(), $this->GetY(), 34);
			/*
	 		// test fpdf generated barcode?

			require_once("fpdf/PDF_EAN13.php");

			$barcode = new PDF_EAN13();

			$barcode->UPC_A($_PosX + 69, $_PosY + 56, $entity['info']['upc']);
			*/
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	function Add_Finishing_Touch_PDF_Label($title, $inst_title, $instructions = false, $serving_suggestion = false, $prep_time = false, $showBorders = 0, $overrideLineHeight = 3.45, $pushDown = 0, $storeName = false, $storePhone = false, $entity)
	{
		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$prep_time = $entity['prep_time'];

		$title = $entity['menu_item'];

		if (strlen($title) > 62)
		{
			$title = substr($title, 0, 62) . '...';
		}

		//$showBorders = true;
		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 110);
		$_PosX -= $this->_Margin_Right;
		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (90));

		$rPadding = 0;
		if ($this->_COUNTX == 1)
		{
			$rPadding = 0;
		}

		$this->SetXY($_PosX + 3, $_PosY + 5);
		$this->MultiCellTag($this->_Width * .5, $this->_Line_Height, "<tc>Dream Dinners</tc>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 9);
		$this->MultiCellTag($this->_Width * .5, $this->_Line_Height, "<t8h>Sides & Sweets</t8h>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 14 + $pushDown);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, '<t3c>' . $title . '</t3c>', $showBorders, "L", 0);

		//		$this->SetXY($_PosX + 3, $_PosY + 22 + $pushDown);
		//		$this->MultiCellTag($this->_Width, $overrideLineHeight, $inst_title, $showBorders, "L", 0);

		if ($prep_time)
		{
			$this->SetXY($_PosX + 3, $_PosY + 19);
			$this->MultiCellTag($this->_Width, $this->_Line_Height, '<t1b>Prep Time ' . $prep_time . '</t1b>', $showBorders, "L", 0);
		}

		$instructions = trim(preg_replace('/\t+/', '', $instructions));
		$this->SetXY($_PosX + 3, $_PosY + 23 + $pushDown);
		$this->MultiCellTag($this->_Width - $rPadding, $overrideLineHeight, $instructions, $showBorders, "L", 0);


		$dateFormatted = CTemplate::dateTimeFormat(date("Y-m-d H:i:s"),VERBOSE_MONTH_YEAR);
		if($entity['show_long_date']){
			$dateFormatted =  CTemplate::dateTimeFormat(date("Y-m-d H:i:s"),MONTH_DAY_YEAR);
		}
		$this->SetXY($_PosX + 53, $_PosY + 5.5);
		$this->MultiCellTag($this->_Width * .5 - $rPadding, $this->_Line_Height, "<t7h>Assembled " . $dateFormatted. "</t7h>", $showBorders, "R", 0);

		if (!empty($entity['best_prepared_by']))
		{
			$this->SetXY($_PosX + 53, $_PosY + 9);
			$this->MultiCellTag($this->_Width * .5 - $rPadding, $this->_Line_Height, "<tftb>" . $entity['best_prepared_by'] . "</tftb>", $showBorders, "R", 0);
		}

		if (!empty($storeName))
		{
			$this->SetXY($_PosX + 3, $_PosY + 70);
			$this->MultiCellTag($this->_Width * .8, 2, "<tfth>$storeName - $storePhone</tfth>", $showBorders, "L", 0);
		}

		$FoodSafety = "<tftb>FOOD SAFETY ALERT - CAUTION:</tftb> <tfth>This meal may contain raw ingredients that cannot be eaten prior to thorough cooking. Store in freezer until ready to thaw or prepare.</tfth>";
		$this->SetXY($_PosX + 3, $_PosY + 72.5);
		$this->MultiCellTag($this->_Width - 17, 2, $FoodSafety, $showBorders, "L", 0);

		if (!empty($entity['recipe_id']))
		{
			$this->SetXY($_PosX + 86.4, $_PosY + 66);
			$this->Image($this->getRecipeQrCodePath($entity['recipe_id'], false), null, null, 12);
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	function Add_Finishing_Touch_PDF_Label_v1($title, $inst_title, $instructions = false, $serving_suggestion = false, $prep_time = false, $showBorders = 0, $overrideLineHeight = 3.45, $pushDown = 0, $storeName = false, $storePhone = false)
	{
		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 112);
		$_PosX -= $this->_Margin_Right;
		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (90));

		$this->SetXY($_PosX + 3, $_PosY + 5 + $pushDown);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, $title, $showBorders, "C", 0);

		$this->SetXY($_PosX + 3, $_PosY + 12 + $pushDown);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, $inst_title, $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 16 + $pushDown);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, $instructions, $showBorders, "L", 0);

		$Assembled_on = "<tft>Assembled: " . date('F jS, Y', time()) . (!empty($serving_suggestion) ? ' - Pair with ' . $serving_suggestion : '') . "</tft>";

		$this->SetXY($_PosX + 3, $_PosY + 65);
		$this->MultiCellTag($this->_Width * .8, 2.5, $Assembled_on, $showBorders, "L", 0);

		$FoodSafety = "<tftb>FOOD SAFETY ALERT - CAUTION:</tftb> <tft>This meal may contain raw ingredients that cannot be eaten prior to thorough cooking. Store in freezer until ready to thaw or prepare.</tft> <tftb>Best if prepared within 2-3 months</tftb>";
		$this->SetXY($_PosX + 3, $_PosY + 68);
		$this->MultiCellTag($this->_Width * .8, 2, $FoodSafety, $showBorders, "L", 0);

		if (!empty($storeName))
		{
			$this->SetXY($_PosX + 3, $_PosY + 72.5);
			$this->MultiCellTag($this->_Width * .8, 2, "<tft>$storeName ($storePhone)</tft>", $showBorders, "L", 0);
		}

		$this->SetXY($_PosX + 3, $_PosY + 72.5);
		$this->MultiCellTag($this->_Width - 22, 2.5, "<tftb>All The Ingredients for a Great Meal</tftb><ttb>" . iconv('UTF-8', 'windows-1252', "™") . "</ttb>", $showBorders, "R", 0);

		if ($prep_time)
		{
			$this->SetXY($_PosX + 3, $_PosY + 58);
			$this->MultiCellTag($this->_Width, $this->_Line_Height, $prep_time, $showBorders, "R", 0);
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	// Print a label
	function Four_Up_Add_Finishing_Touch_PDF_Label($title, $inst_title, $instructions = false, $serving_suggestion = false, $prep_time = false, $showBorders = 0, $overrideLineHeight = 3.45, $pushDown = 0, $storeName = false, $storePhone = false, $entity)
	{
		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}
		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 140);

		$_PosX -= $this->_Margin_Right;

		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (100));

		$this->SetXY($_PosX + 3, $_PosY + 10);
		$this->MultiCellTag($this->_Width, $this->_Line_Height, "<color>Dream Dinners</color>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 14);
		$this->MultiCellTag($this->_Width, $this->_Line_Height, "<t8>Sides & Sweets</t8>", $showBorders, "L", 0);

		$this->SetXY($_PosX + 3, $_PosY + 20);
		//		$this->MultiCellTag($this->_Width, $this->_Line_Height, $title, $showBorders, "L", 0);
		$this->MultiCellTag($this->_Width * .83, $this->_Line_Height, $title, $showBorders, "L", 0);

		if ($prep_time)
		{
			$this->SetXY($_PosX + 3, $_PosY + 27);
			$this->MultiCellTag($this->_Width, $this->_Line_Height, '<t1b>Prep Time ' . $prep_time . '</t1b>', $showBorders, "L", 0);
		}

		$dateFormatted = CTemplate::dateTimeFormat(date("Y-m-d H:i:s"),VERBOSE_MONTH_YEAR);
		if($entity['show_long_date']){
			$dateFormatted =  CTemplate::dateTimeFormat(date("Y-m-d H:i:s"),MONTH_DAY_YEAR);
		}

		$this->SetXY($_PosX + 61, $_PosY + 10);
		$this->MultiCellTag($this->_Width * .4, $this->_Line_Height, "<t8>Assembled " . $dateFormatted . "</t8>", $showBorders, "R", 0);

		if (!empty($entity['best_prepared_by']))
		{
			$this->SetXY($_PosX + 61, $_PosY + 14);
			$this->MultiCellTag($this->_Width * .4, $this->_Line_Height, "<tftb>" . $entity['best_prepared_by'] . "</tftb>", $showBorders, "R", 0);
		}

		$instructions = trim(preg_replace('/\t+/', '', $instructions));
		$pushDown = 0;
		if ($this->NbLines($this->_Width, $instructions) < 4)
		{
			$pushDown += 5;
		}
		$this->SetXY($_PosX + 3, $_PosY + 31 + $pushDown);
		$this->MultiCellTag($this->_Width, $overrideLineHeight, $instructions, $showBorders, "L", 0);

		if (!empty($entity['recipe_id']))
		{
			$this->SetXY($_PosX + 114, $_PosY + 9);
			$this->Image($this->getRecipeQrCodePath($entity['recipe_id'], false), null, null, 18);
		}

		if (!empty($entity['store_name']) && !empty($entity['store_phone']) && $entity['store_name'] != "Not Available")
		{
			$addr = $entity['address_line1'] . ', ' . $entity['address_line2'] . ', ' . $entity['city'] . ', ' . $entity['state_id'] . ' ' . $entity['postal_code'];
			$this->SetXY($_PosX + 3, $_PosY + 89);
			$this->MultiCellTag($this->_Width * .9, 1, "<tt>" . $entity['store_name'] . " - " . $entity['store_phone'] . " - " . $addr . "</tt>", $showBorders, "L", 0);
		}

		$this->SetXY($_PosX + 3, $_PosY + 91);
		$this->MultiCellTag($this->_Width, 1.5, "<tt>FOOD SAFETY ALERT - CAUTION: This meal may contain raw ingredients that cannot be eaten prior to thorough cooking. Store in freezer until ready to thaw or prepare.</tt>", $showBorders, "L", 0);

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	// Print a label
	function Four_Up_Add_PDF_Label($menuItemArray, $title, $instructions, $servingType, $useSimpleFormatting = false, $showBorders = 0, $overrideLineHeight = 3.45)
	{
		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 140);

		$_PosX -= $this->_Margin_Right;

		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (100));

		$this->SetXY($_PosX + 3, $_PosY + 20);
		$this->MultiCellTag($this->_Width * .83, $this->_Line_Height, $title, $showBorders, "L", 0);

		$numTitleLines = $this->NbLines($this->_Width * .83, $title);

		$pushDown = 0;

		if ($numTitleLines > 1)
		{
			$pushDown += 4;
		}

		$instructions = trim(preg_replace('/\t+/', '', $instructions));
		if ($this->NbLines($this->_Width, $instructions) < 8)
		{
			$pushDown += 5;
		}

		$this->SetXY($_PosX + 3, $_PosY + 31.5 + $pushDown);
		if ($useSimpleFormatting == true)
		{
			$this->MultiCell($this->_Width, $this->_Line_Height, $instructions, 1);
		}
		else
		{
			$this->MultiCellTag($this->_Width, $overrideLineHeight, $instructions, $showBorders, "L", 0);
		}

		if (!empty($menuItemArray['session_start']))    // session data
		{

			$assemble_verbiage = 'Assembled ';
			$shortDate = $menuItemArray['session_start'];
			if (array_key_exists('order_id', $menuItemArray))
			{
				$shortDate = CTemplate::dateTimeFormat($menuItemArray['session_start_database'],CONCISE_NO_SECONDS);
				$assemble_verbiage = 'Ordered for ';
			}
			else
			{
				$shortDate = CTemplate::dateTimeFormat(date("Y-m-d H:i:s"),VERBOSE_MONTH_YEAR);
				if($menuItemArray['show_long_date']){
					$shortDate =  CTemplate::dateTimeFormat(date("Y-m-d H:i:s"),MONTH_DAY_YEAR);
				}
			}

			$this->SetXY($_PosX + 45, $_PosY + 11);
			$this->MultiCellTag($this->_Width * .5, $this->_Line_Height, "<t7>" . $assemble_verbiage . $shortDate . "</t7>", $showBorders, "R", 0);
		}

		if (!empty($menuItemArray['best_prepared_by']))
		{
			$this->SetXY($_PosX + 58, $_PosY + 14);
			$this->MultiCellTag($this->_Width * .4, $this->_Line_Height, "<tftb>" . $menuItemArray['best_prepared_by'] . "</tftb>", $showBorders, "R", 0);
		}

		if (!empty($menuItemArray['meal_customization']))
		{
			//cyan
			//69, 207, 209
			$this->SetFillColor(224, 255, 255);

			$section = explode('<br>', $menuItemArray['meal_customization']);
			$backgroundCharsAdded = strlen("{$section[0]}");
			$backgroundCharsSpec = 0;
			if (!empty($section[1]))
			{
				$backgroundCharsSpec = strlen("{$section[1]}");
			}

			$backgroundChars = $backgroundCharsAdded > $backgroundCharsSpec ? $backgroundCharsAdded : $backgroundCharsSpec;

			// header line
			if (!empty($section[0]))
			{
				$this->Rect($_PosX + 4, $_PosY + 4.2, $backgroundChars, 2.6, 'F');
				$this->SetXY($_PosX + 3, $_PosY + 4.3);
				$this->MultiCellTag($this->_Width, $this->_Line_Height - 1, "<t5>{$section[0]}</t5>", $showBorders, "L", 0);
			}
			if (!empty($section[1]))
			{
				// Special Request header line

				$this->Rect($_PosX + 4, $_PosY + 6.8, $backgroundChars, 2.3, 'F');
				$this->SetXY($_PosX + 3, $_PosY + 6.0);
				$this->MultiCellTag($this->_Width, $this->_Line_Height, "<t5>{$section[1]}</t5>", $showBorders, "L", 0);
			}
		}

		$this->SetXY($_PosX + 3, $_PosY + 10);
		$this->MultiCellTag($this->_Width, $this->_Line_Height, "<color>DREAM DINNERS</color>", $showBorders, "L", 0);

		if (!empty($menuItemArray['firstname']))    // customer data
		{
			$this->SetXY($_PosX + 3, $_PosY + 14);
			$this->MultiCellTag($this->_Width * .6, $this->_Line_Height, "<t8>" . $menuItemArray['firstname'] . " " . $menuItemArray['lastname'] . "</t8>", $showBorders, "L", 0);
		}

		$lines = $this->NbLines($this->_Width * .83, $title);

		$yOff = 24;
		if ($lines > 1)
		{
			$yOff = 28;
		}

		$sTypeWidth = $this->GetStringWidth($servingType);
		if ($menuItemArray['servings_per_item'] == 6)
		{
			$iconListX = $sTypeWidth - 18;
		}
		else
		{
			$iconListX = $sTypeWidth - 3;
		}

		$iconListY = $yOff + .5;
		$iconListIconWidth = 3;
		$hasIcon = false;

		foreach ($menuItemArray['icons'] as $icon)
		{
			if ($icon['print_meal_detail_enabled'] && $icon['show'])
			{
				$this->SetXY($_PosX + $iconListX, $_PosY + $iconListY);
				$this->Image(ASSETS_PATH . '/pdf_label/' . $icon['png_icon'], null, null, $iconListIconWidth);
				$iconListX += $iconListIconWidth;
				$hasIcon = true;
			}
		}

		if (!$hasIcon)
		{
			//remove last |
			$pos = strrpos($servingType, '|');

			if ($pos !== false)
			{
				$servingType = substr_replace($servingType, '', $pos, 1);
			}
		}

		$this->SetXY($_PosX + 3, $_PosY + $yOff);
		$this->MultiCellTag($this->_Width * .6, $this->_Line_Height, $servingType, $showBorders, "L", 0);

		if (!empty($menuItemArray['instructions_air_fryer']) || !empty($menuItemArray['instructions_crock_pot']) || !empty($menuItemArray['instructions_grill']))
		{
			$alternate_instruction_type = 'alternate';
			if(!empty($menuItemArray['instructions_air_fryer']))
			{
				$alternate_instruction_type = 'air fryer';
			}
			else if (!empty($menuItemArray['instructions_crock_pot']))
			{
				$alternate_instruction_type = 'crock-pot or instant pot';
			}
			else if (!empty($menuItemArray['instructions_grill']))
			{
				$alternate_instruction_type = 'grill';
			}

			$this->SetXY($_PosX + 3, $_PosY + 4 + $yOff);
			$this->MultiCellTag($this->_Width, $this->_Line_Height, "<t1>*Scan the QR code to get " . $alternate_instruction_type . " instructions*</t1>", $showBorders, "L", 0);
		}

		if (!empty($menuItemArray['recipe_id']))
		{
			$this->SetXY($_PosX + 111, $_PosY + 9);
			$this->Image($this->getRecipeQrCodePath($menuItemArray['recipe_id'], false), null, null, 18);
		}

		if (!empty($menuItemArray['store_name']) && !empty($menuItemArray['store_phone']) && $menuItemArray['store_name'] != "Not Available")
		{
			$addr = $menuItemArray['address_line1'] . ', ' . $menuItemArray['address_line2'] . ', ' . $menuItemArray['city'] . ', ' . $menuItemArray['state_id'] . ' ' . $menuItemArray['postal_code'];
			$this->SetXY($_PosX + 3, $_PosY + 89);
			$this->MultiCellTag($this->_Width * .85, 1, "<tt>" . $menuItemArray['store_name'] . " - " . $menuItemArray['store_phone'] . " - " . $addr . "</tt>", $showBorders, "L", 0);
		}

		$this->SetXY($_PosX + 3, $_PosY + 91);
		$this->MultiCellTag($this->_Width * .85, 1.4, "<tt>FOOD SAFETY ALERT - CAUTION: This meal may contain raw ingredients that cannot be eaten prior to thorough cooking. Store in freezer until ready to thaw or prepare.</tt>", $showBorders, "L", 0);

		if (!empty($menuItemArray['item_number']))
		{

			$this->SetXY($_PosX, $_PosY + 91);
			$this->MultiCellTag($this->_Width, 1, '<tft>(Item ' . $menuItemArray['item_number'] . ' of ' . $menuItemArray['total_items'] . ')</tft>', $showBorders, "R", 0);
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	function getRecipeQrCodePath($recipeId, $forceQrRegen = false)
	{
		$path = ASSETS_PATH . '/pdf_label/qrcode/recipe/qr-recipe-' . $recipeId . '.png';

		if (!file_exists($path) || $forceQrRegen)
		{

			/*$ecc: This parameter specifies the error correction capability of QR. It has 4 levels L, M, Q and H.
			$pixel_Size: This specifies the pixel size of QR.
			$frame_Size: This specifies the size of Qr. It is from level 1-10.*/ //			QRcode::png(HTTPS_SERVER . '/item?recipe='.$recipeId,$path,QR_ECLEVEL_L,3,4,false,0xFFFFFF,
			//				0x959a21);
			QRcode::png(HTTPS_SERVER . '/item?recipe=' . $recipeId . '&utm_source=qr-code&utm_medium=cooking-instructions&utm_campaign=' . $recipeId, $path, QR_ECLEVEL_L, 3, 4, false);
		}

		return $path;
	}

	function getNutritionQrCodePath($recipeId, $forceQrRegen = false)
	{
		$path = ASSETS_PATH . '/pdf_label/qrcode/recipe/qr-recipe-' . $recipeId . '.png';

		if (!file_exists($path) || $forceQrRegen)
		{

			/*$ecc: This parameter specifies the error correction capability of QR. It has 4 levels L, M, Q and H.
			$pixel_Size: This specifies the pixel size of QR.
			$frame_Size: This specifies the size of Qr. It is from level 1-10.*/ //			QRcode::png(HTTPS_SERVER . '/item?recipe='.$recipeId,$path,QR_ECLEVEL_L,3,4,false,0xFFFFFF,
			//				0x959a21);
			QRcode::png(HTTPS_SERVER . '/item?recipe=' . $recipeId . '&tab=nutrition&utm_source=qr-code&utm_medium=nutritionals&utm_campaign=' . $recipeId, $path, QR_ECLEVEL_L, 3, 4, false);
		}

		return $path;
	}

	// Print a label
	function Carls_Add_PDF_Label($entity, $texte, $useSimpleFormatting = false, $showBorders = 0, $overrideLineHeight = 3.45)
	{
		// We are in a new page, then we must add a page
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			$this->AddPage();
		}

		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 110);

		$_PosX -= $this->_Margin_Right;

		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (90));

		$this->SetXY($_PosX + 3, $_PosY + 3);
		$texte = trim(preg_replace('/\t+/', '', $texte));

		if ($useSimpleFormatting == true)
		{
			$this->MultiCell($this->_Width, $this->_Line_Height, $texte, 1);
		}
		else
		{
			$this->MultiCellTag($this->_Width, $overrideLineHeight, $texte, $showBorders, "L", 0);
		}

		if (!empty($entity['firstname']))    // customer data
		{
			$this->SetXY($_PosX + $this->_Width - ($this->_Width * .6), $_PosY + 10);
			$this->MultiCellTag($this->_Width * .6, $this->_Line_Height, "<t8>" . $entity['firstname'] . " " . $entity['lastname'] . "</t8>", $showBorders, "R", 0);
		}

		if (!empty($entity['session_start']))    // session data
		{
			$this->SetXY($_PosX + $this->_Width - ($this->_Width * .6), $_PosY + 7);
			$this->MultiCellTag($this->_Width * .6, $this->_Line_Height, "<t9>" . $entity['session_start'] . "</t9>", $showBorders, "R", 0);
		}

		if (!empty($entity['serving_suggestions']))
		{
			$this->SetXY($_PosX + 3, $_PosY + 58);
			$this->MultiCellTag($this->_Width * .8, $this->_Line_Height, "<t1>SERVING SUGGESTIONS</t1>", $showBorders, "L", 0);

			$this->SetXY($_PosX + 3, $_PosY + 60.5);
			$this->MultiCellTag(78, 3, "<t1b>" . $entity['serving_suggestions'] . "</t1b>", $showBorders, "L", 0);
		}

		if (!empty($entity['best_prepared_by']))
		{
			$this->SetXY($_PosX + 3, $_PosY + 66.5);
			$this->MultiCellTag($this->_Width * .8, $this->_Line_Height, "<t1b>" . $entity['best_prepared_by'] . "</t1b>", $showBorders, "L", 0);
		}

		//		if (!empty($entity['prep_time']))
		//		{
		//			$this->SetXY($_PosX + 50, $_PosY + 58);
		//			$this->MultiCellTag(54, $this->_Line_Height, "<t1>TIME TO TABLE</t1>: <t8>" . $entity['prep_time'] . "</t8>", $showBorders, "C", 0);
		//		}

		if (!empty($entity['prep_time']))
		{
			$this->SetXY($_PosX + 79, $_PosY + 58);
			$this->MultiCellTag(24, $this->_Line_Height, "<t1>TIME TO TABLE</t1>\n<t8>" . $entity['prep_time'] . "</t8>", $showBorders, "C", 0);
		}

		if (!empty($entity['cooking_instruction_youtube_id']))
		{
			$this->SetXY($_PosX + 17, $_PosY + 6.5);
			$this->Image(ASSETS_PATH . '/pdf_label/menu-icon05.png', null, null, 4);

			$this->SetXY($_PosX + 20.5, $_PosY + 7);
			$this->MultiCellTag(50, 2.5, '<tftb>View preparation video at dreamdinners.com</tftb>', $showBorders, "L", 0);
		}

		$this->SetXY($_PosX + 3, $_PosY + 70.5);
		$this->MultiCellTag($this->_Width * .7, 1.5, "<tt>FOOD SAFETY ALERT - CAUTION: This meal may contain raw ingredients that cannot be eaten prior to thorough cooking. Store in freezer until ready to thaw or prepare.</tt>", $showBorders, "L", 0);

		if (!empty($entity['store_name']) && !empty($entity['store_phone']) && $entity['store_name'] != "Not Available")
		{
			$this->SetXY($_PosX + 3, $_PosY + 74);
			$this->MultiCellTag($this->_Width * .7, 2.5, "<t5>" . $entity['store_name'] . " (" . $entity['store_phone'] . ")</t5>", $showBorders, "L", 0);
		}

		if (!empty($entity['item_number']))
		{

			$this->SetXY($_PosX + 3, $_PosY + 76.5);
			$this->MultiCellTag($this->_Width, 2.5, '<t1>(Item ' . $entity['item_number'] . ' of ' . $entity['total_items'] . ')</t1>', $showBorders, "R", 0);
		}

		//		if (!empty($entity['recipe_id']))
		//		{
		//			$this->SetXY($_PosX + 86.4, $_PosY + 63);
		//			$this->Image($this->getRecipeQrCodePath($entity['recipe_id'], false), null, null, 12);
		//		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}

	function Add_Marketing_Label($storeObj, $showBorders = 0)
	{
		// We are in a new page don't add marketing message
		if (($this->_COUNTX == 0) && ($this->_COUNTY == 0))
		{
			return;
		}

		$_PosX = $this->_Margin_Left + ($this->_COUNTX * 112);

		$_PosX -= $this->_Margin_Right;

		$_PosY = $this->_Margin_Top + ($this->_COUNTY * (90));

		$this->SetXY($_PosX, $_PosY - 8);
		$this->Image(ASSETS_PATH . '/pdf_label/label_marketing/SC_Mothersday_CI_f.png', null, null, 100);

		// ensure there is a coupon code
		if (!empty($this->coupon_array[$storeObj->home_office_id]))
		{
			$this->SetXY($_PosX + 10, $_PosY + 64);
			$this->MultiCellTag($this->_Width - 16, 6, "<h11>Use promo code " . $this->coupon_array[$storeObj->home_office_id] . " & save 10%. Visit lovingwithfood.com.</h11>", $showBorders, "C", 0);
		}
		else
		{
			$this->SetXY($_PosX + 10, $_PosY + 64);
			$this->MultiCellTag($this->_Width - 16, 6, "<h11>Visit lovingwithfood.com.</h11>", $showBorders, "C", 0);
		}

		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
		{
			// Page full, we start a new one
			$this->_COUNTY++;
			$this->_COUNTX = 0;
		}

		if ($this->_COUNTY == $this->_Y_Number)
		{
			$this->_COUNTY = 0;
			$this->_COUNTX = 0;
		}
	}
}

?>