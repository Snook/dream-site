<?php

/* Created by Lynn Hook... subclass of the PDF_Label to handle the rasterization of the background
*  Dream Labels
*
*/

require_once('PDF_Label.php');

class dream_labels extends PDF_Label {
	var $_page_array = null;
	var $_cur_page = 0;
	var $_imageHeight = 0;
	var $_imageWidth = 0;

	var $_newHeader = 0;

	function __construct($format, $unit='mm', $posX=1, $posY=1, $orientation = 'P') {
		parent::__construct($format, $unit, $posX, $posY, $orientation);
	}

	function storeImageDetails ($imageArray, $width, $height)
	{
		$this->_page_array = $imageArray;
		$this->_imageHeight = $height;
		$this->_imageWidth = $width;
	}

	function Header()
	{
		$counter = 0;

		// orientation...
		// fill left row first with all data then work on right
		$curRow = 0;
		$curCol = 0;

		$YSPACE = $this->_Y_Space +3;
		$XSPACE = $this->_X_Space+.5;

		$MARGINTOP = $this->_Margin_Top-6;
		$MARGINLEFT = $this->_Margin_Left-2;

		if ($this->_page_array != NULL) {


			// when counter == 3 then shift to right hand column
		 	foreach ($this->_page_array[$this->_newHeader] as $entity) {
				if ($curRow == $this->_X_Number) {
					$curRow = 0;
					$curCol++;
				}
				$vertical = $MARGINTOP + ($curCol * ($this->_imageHeight + $YSPACE));
				$horizontal = $MARGINLEFT + ($curRow * ($this->_imageWidth + $XSPACE));
				$this->Image($entity,$horizontal,$vertical,$this->_imageWidth,$this->_imageHeight,'','');
				$counter++;
				$curRow++;
			}

		}
		$this->_newHeader++;
	}


}

?>