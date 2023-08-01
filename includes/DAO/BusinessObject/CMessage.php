<?php

	require_once 'DAO/Message.php';

	/* ------------------------------------------------------------------------------------------------
	 *	Class: CMessage
	 *
	 *	Data:
	 *
	 *	Methods:
	 *		Create()
	 *
	 *  	Properties:
	 *
	 *
	 *	Description:  Currently a simple class that allows attaching messaging to various objects for communication to stores and guests
	 *				  Initial requirement:  Post a warning dueing order processoing that is disaplyed at the order confirmatin page.
	 *
	 *
	 *	Requires:
	 *
	 * -------------------------------------------------------------------------------------------------- */


	class CMessage extends DAO_Message {

		// TYPES
		const UNKNOWN = 'UNKNOWN';
		const ORDER_WARNING = 'ORDER_WARNING';

		function __construct() {
			parent::__construct();
		}
		
		
		static function postMessage($type, $content, $store_id, $user_id = false, $order_id = false)
		{
			$newMessage = DAO_CFactory::create('message');
			$newMessage->content = $content;
			$newMessage->type = $type;
			$newMessage->store_id = $store_id;
			$newMessage->order_id = $order_id;
			
			$newMessage->insert();
		}
		
		
		static function getOrderWarnings($order_id)
		{
		    $retVal = false;
		    
			$message = DAO_CFactory::create('message');
			$message->query("select content from message where order_id = $order_id and type = 'ORDER_WARNING' and is_deleted = 0 and is_read = 0");
			
			while ($message->fetch())
			{
				$retVal .= $message->content . "<br />";
			}
			
			$message->query("update message set is_read = 1 where order_id = $order_id and type = 'ORDER_WARNING' and is_deleted = 0 and is_read = 0");
			
			return $retVal;
			
		}
		
		
		
	}

?>