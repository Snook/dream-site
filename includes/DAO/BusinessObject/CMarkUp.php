<?php

	require_once 'DAO/Mark_up.php';

	/* ------------------------------------------------------------------------------------------------
	 *	Class: CMarkUp
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


	class CMarkUp extends DAO_Mark_up {

		const FLAT = 'FLAT';
		const PERCENTAGE = 'PERCENT';

		function __construct() {
			parent::__construct();
		}

		/**
		 * This function returns active rows for the store.
		 * @param menu_id, the menu of the current order. If false, it will only return a mark up that has no
		 * menu_id_start value
		 **/
		function findActive($menu_id = false) {

			if ( !$this->store_id)
				throw new Exception('need to set store id');

			if ( $menu_id )
				$where = " (menu_id_start <= $menu_id or menu_id_start IS NULL) ";
			else
				$where = " (menu_id_start IS NULL) ";

			$this->whereAdd( $where );
			$this->orderBy(' id DESC ');
			return $this->find();
		}
	}

?>