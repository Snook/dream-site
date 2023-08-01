<?php

	require_once 'DAO/Sales_tax.php';

	/* ------------------------------------------------------------------------------------------------
	 *	Class: CSalesTax
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


	class CSalesTax extends DAO_Sales_tax {

		function __construct() {
			parent::__construct();
		}

		/**
		 * This function returns active preferred customer rows for the store.
		 * Set user_id first if searching of a single customer
		 **/
		function findActive() {

			if ( !$this->store_id)
				throw new Exception('need to set store id');

			$where = ' (sales_tax_start <= now() or sales_tax_start IS NULL) AND' .
					'		(sales_tax_expiration > now() or sales_tax_expiration IS NULL)';

			$this->whereAdd( $where );

			return $this->find();
		}
	}

?>