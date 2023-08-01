<?php
require_once 'DAO/Product_orders.php';


class InvalidCouponException extends Exception
{
	var $errorArray;
}


class CProductOrders extends DAO_Product_orders
{
	var $product = null; // array of array($qty,$obj) or a single product
	var $storeObj = null;

	function getCouponObj($couponCodeStr)
	{
		$couponCode = DAO_CFactory::Create('coupon_code');
		$couponCode->coupon_code = $couponCodeStr;
		$found = $couponCode->find(true);
		if ($found)
		{
			return $couponCode;
		}

		return false;
	}

	function getStoreObj()
	{
		if ($this->storeObj != null)
		{
			return $this->storeObj;
		}

		if (!empty($this->store_id))
		{
			$this->storeObj = DAO_CFactory::Create('store');
			$this->storeObj->id = $this->store_id;
			$found = $this->storeObj->find(true);
			if ($found)
			{
				return $this->storeObj;
			}
		}

		return null;
	}

	function applySalesTax()
	{
		$this->sales_tax_id = 'null';
		$this->subtotal_all_taxes = 0.0;
		$this->subtotal_sales_taxes = 0.0;

		$taxObj = $this->storeObj->getCurrentSalesTaxObj();

		// only supports enrollments for now so apply to entire subtotal
		if ($taxObj)
		{
			$enrollmentTax = $taxObj->other2_tax;
			$this->sales_tax_id = $taxObj->id;
			$this->subtotal_sales_taxes = self::std_round($this->subtotal_all_items * $enrollmentTax / 100, 2);
			$this->subtotal_all_taxes = $this->subtotal_sales_taxes;
		}
	}


	function recalculate($couponCode = false)
	{

		if (is_array($this->product))
		{
			 //future
		}
		else if (is_object($this->product) && get_class($this->product) == 'DAO_Product_orders_items')
		{
			$this->subtotal_products = $this->product->item_cost;
		}

		$this->coupon_code_id = 'null';
		$this->coupon_code_discount_total = 0.0;

		if ($couponCode)
		{
			$couponObj = $this->getCouponObj($couponCode);
			if (!$couponObj)
			{
				// validated coupon not found. should be rare.
				throw new Exception("Coupon is no longer valid.");
			}
			else
			{
				// validate
				$couponValid = $couponObj->isValidForProduct($this);
				if (!empty($couponValid))
				{
					// if not true then return value is an array of errors
					$Exc = new InvalidCouponException("The coupon is not valid");
					$Exc->errorArray = $couponValid;
					throw $Exc;

				}

				// Calculate discount
				$this->coupon_code_discount_total = $couponObj->calculateForProduct($this);
				$this->coupon_code_id = $couponObj->id;
			}

		}

		$this->subtotal_all_items = $this->subtotal_products - $this->coupon_code_discount_total;


		$this->applySalesTax();

		$this->grand_total = $this->subtotal_all_items + $this->subtotal_all_taxes;

	}

	function processOrder($items, $couponCode = false)
	{
		CLog::RecordDebugTrace('CProductOrders::processOrder called for user: ' . $this->user_id, "TR_TRACING");

		$this->getStoreObj();

		$this->product = $items;

		try
		{
			$this->recalculate($couponCode);

			$this->insert();

			if (is_array($this->product))
			{


			}
			else if (is_object($this->product) && get_class($this->product) == 'DAO_Product_orders_items')
			{
				$this->product->product_orders_id = $this->id;
				$this->product->insert();
				return $this->product->id;
			}
		}
		catch (exception $e)
		{
			throw $e;
		}
	}
}
?>