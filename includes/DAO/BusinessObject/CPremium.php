<?php
	
	require_once 'DAO/Premium.php';
	
	/* ------------------------------------------------------------------------------------------------
	 *	Class: CPremium
	 *
	 *	Data:	
	 *
	 *	Methods:
	 *		Create()
	 *
	 *  	Properties:
	 *		
	 *
	 *	Description:
	 *		
	 *		
	 *	Requires:
	 *
	 * -------------------------------------------------------------------------------------------------- */
	 
	
	class CPremium extends DAO_Premium {
		
		const FLAT = 'FLAT';
		const PERCENTAGE = 'PERCENT';
		const DISTRIBUTE = 'DISTRIBUTE';
		
		function __construct() {
			parent::__construct();
		}
	}
	
?>