<?php
require_once 'DAO/Box_instance.php';
require_once 'DAO/BusinessObject/COrders.php';

class CBoxInstance extends DAO_Box_instance
{

	public $items = null;

	function __construct()
	{
		parent::__construct();
	}

	static function getBoxInstanceByID($box_instance_id)
	{

		$theObj = DAO_CFactory::create('box_instance');
		$theObj->query("select * from box_instance where id = $box_instance_id and is_deleted = 0");

		if ($theObj->fetch())
		{
			return $theObj;
		}

		return false;
	}

	static function getIncompleteBoxInstance($bundle_id, $box_id)
	{
		$CartObj = CCart2::instance();

		$boxObj = DAO_CFactory::create('box_instance');
		$boxObj->bundle_id = $bundle_id;
		$boxObj->box_id = $box_id;
		$boxObj->cart_contents_id = $CartObj->getCartDAO()->cart_contents_id;
		$boxObj->is_complete = 0;
		$boxObj->is_in_edit_mode = 1;

		if ($boxObj->find(true))
		{
			return $boxObj;
		}

		return false;
	}

	static function getNewEmptyBoxForBundle($bundle_id, $box_id, $savedOrder = false, $is_in_edit_mode = false, $is_complete = false, $ignoreCart = false)
	{


		$boxObj = DAO_CFactory::create('box_instance');
		if ($savedOrder)
		{
			$boxObj->order_id = $savedOrder;
		}

		if (!$ignoreCart)
		{
			$CartObj = CCart2::instance();
			$boxObj->cart_contents_id = $CartObj->getCartDAO()->cart_contents_id;
		}

		$boxObj->bundle_id = $bundle_id;
		$boxObj->box_id = $box_id;

		if ($is_in_edit_mode)
		{
			$boxObj->is_in_edit_mode = 1;
		}

		if ($is_complete)
		{
			$boxObj->is_complete = 1;
		}

		$boxObj->insert();

		return $boxObj->id;
	}

	static function updateBoxAsOrdered($box_instance_id, $order_id)
	{
		$boxObj = DAO_CFactory::create('box_instance');
		$boxObj->id = $box_instance_id;
		if ($boxObj->find(true))
		{
			if (!empty($boxObj->is_complete))
			{
				$orgObj = clone($boxObj);
				$boxObj->order_id = $order_id;
				$boxObj->cart_contents_id = 0;
				$boxObj->update($orgObj);
			}
			else
			{
				$boxObj->delete();
			}
		}
		else
		{
			throw new Exception("Box not found in updateBoxAsOrdered: " . $box_instance_id);
		}
	}

}

?>