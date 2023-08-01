<?php
	require_once 'DAO/Enrollment_package.php';
	require_once 'DAO/BusinessObject/COrders.php';


	/* ------------------------------------------------------------------------------------------------
	 *	Class: CEnrollmentPackage
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


	class CEnrollmentPackage extends DAO_Enrollment_package {


		static function getCurrentEnrollmentPackages($user_id = false)
		{
			$packageDAO = DAO_CFactory::create('enrollment_package');

			$packageDAO->query("select ep.*, p.product_title, p.product_description, p.price from enrollment_package ep " .
			" join product p on ep.product_id = p.id " .
			" where now() >= ep.offering_start_date " .
			" and now() < ep.offering_end_date and ep.is_deleted = 0");
			$retArray = array();
			while($packageDAO->fetch())
			{
				$canAdd = true;
				if ($user_id && !$packageDAO->is_existing_user_eligible)
				{
					$DFLBooking = DAO_CFactory::create('booking');

					$DFLBooking->query("select b.id from booking b join orders o on o.id = b.order_id " .
					" where b.status = 'ACTIVE' and b.user_id = $user_id and b.is_deleted = 0 and o.menu_program_id = 2");

					if ($DFLBooking->N > 0)
						$canAdd = false;

				}

				if ($canAdd)
				{
					$retArray[$packageDAO->id] = array('id' => $packageDAO->id,
													'title' => $packageDAO->product_title,
													'desc' => $packageDAO->product_description,
													'price' => $packageDAO->price,
													'product_id' => $packageDAO->product_id);

				}
			}

			return $retArray;
		}



		static function getProductForPackageID($package_id)
		{
			$package = DAO_CFactory::create('enrollment_package');
			$package->query("select product_id from enrollment_package where id = $package_id");
			if ($package->fetch())
			{
				$product = DAO_CFactory::create('product');
				$product->id = $package->product_id;
				if ($product->find(true))
					return $product;
			}

			return false;
		}

	}

?>