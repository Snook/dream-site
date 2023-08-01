<?php

require_once 'DAO/Orders_shipping.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: COrdersShipping
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

class COrdersShipping extends DAO_Orders_shipping
{
	const STATUS_NEW = 'NEW';
	const STATUS_SHIPPED = 'STATUS_SHIPPED';
	const STATUS_DELIVERED = 'DELIVERED';

}

?>