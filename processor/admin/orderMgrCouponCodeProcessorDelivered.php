<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CCouponCode.php");
require_once("includes/DAO/BusinessObject/CPointsUserHistory.php");
require_once("includes/DAO/BusinessObject/CDreamTasteEvent.php");
require_once("includes/DAO/BusinessObject/CBundle.php");
require_once("CTemplate.inc");

class processor_admin_orderMgrCouponCodeProcessorDelivered extends CPageProcessor
{
	private $currentStore = null;

	static $session = null;
	static $session_init = false;

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$nameValuePairs = explode("&", $_POST['params']);
		$values = array();
		foreach ($nameValuePairs as $val)
		{
			$parts = explode("=", $val);
			if (!empty($parts[0]))
			{
				if (isset($parts[1]))
				{
			    	 $values[$parts[0]] = $parts[1];
				}
				else
				{
					$values[$parts[0]] = null;
				}
			}
		}

		$daoStore = DAO_CFactory::create('store');
		$daoStore->id = $values['store_id'];
		$daoStore->find(true);

		$User = null;
		if (isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $_REQUEST['user_id'];
			if (!$User->find(true))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The user cannot be found in the Database.'
				));
				exit;
			}
		}

		list($Order, $SessionObj) = self::buildOrderFromArray($daoStore, $values, null);

        $menu_id = $SessionObj->menu_id;

		$Order->user_id = $User->id;
		$Order->id = $_REQUEST['order_id'];

		$orgOrderObj = null;
		if (isset($_REQUEST['order_state']) && $_REQUEST['order_state'] != 'ACTIVE')
		{
			$orgOrderObj = clone($Order);
		}

		if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'remove')
		{
		}
		else
		{
			$OrgOrderTime = $_REQUEST['org_ts'];
			$OrgOrderID = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : false;

			$couponCode = $_REQUEST['coupon_code'];

			if (empty($couponCode))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The coupon_code_not_supplied.'
				));
				exit;
			}
		}

		// TODO: check for valid store id
		if (!$Order)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The order could not be found.'
			));
			exit;
		}

		if (isset($Order->needToReviewCart) && $Order->needToReviewCart)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The cart must be reviewed.'
			));
			exit;
		}

		$Order->refresh($User);
		$Order->recalculate(true);

		if (isset($_REQUEST['op']) && $_REQUEST['op'] != 'remove')
		{
			$couponValidation = CCouponCode::isCodeValidForDelivered($couponCode, $Order, $menu_id, true, $OrgOrderTime, $OrgOrderID);

			if (gettype($couponValidation) !== "object" || get_class($couponValidation) !== 'CCouponCode')
			{
				$errors = array();
				foreach ($couponValidation as $thisError)
				{
					$errors[] = CCouponCode::getCouponErrorUserText($thisError);
				}

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'The coupon code could not be used.',
					'validation_errors' => $errors
				));
				exit;
			}
			else
			{
				$codeDAO = $couponValidation;
			}

			$Order->addCoupon($codeDAO);
		}
		else
		{
			$Order->coupon_code_id = 'null';
			$Order->coupon_code_discount_total = 0;
		}

		$Order->refresh($User, $menu_id);

		$Order->family_savings_discount = 0;
		$Order->menu_program_id = 1;


		$Order->recalculate(true);

		if (isset($_REQUEST['order_state']) && $_REQUEST['order_state'] != 'ACTIVE')
		{
		//	$Order->update($orgOrderObj);
		}

		$entreeServings = 0;
		$entreeTitle = "";

		// The Order Editor will calculate and update the display so we only need to return pertinent info

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The coupon code was attached.',
			'validation_errors' => false,
			'coupon_code_discount_total' => $Order->coupon_code_discount_total,
			'discount_method' => (isset($codeDAO) ? $codeDAO->discount_method : ""),
			'coupon' => (isset($codeDAO) ? $codeDAO : ""),
			'code_id' => (isset($codeDAO) ? $codeDAO->id : ""),
			'entree_title' => $entreeTitle,
			'entree_servings' => $entreeServings,
			'discount_var' => (isset($codeDAO) ? $codeDAO->discount_var : ""),
			'limit_to_finishing_touch' => (isset($codeDAO) && $codeDAO->limit_to_finishing_touch ? true : false),
			'valid_with_customer_referral_credit' => (isset($codeDAO) && $codeDAO->valid_with_customer_referral_credit ? true : false),
			'valid_with_plate_points_credits' => (isset($codeDAO) && $codeDAO->valid_with_plate_points_credits ? true : false),
			'coupon_obj' => (isset($codeDAO) ? DAO::getCompressedArrayFromDAO($codeDAO) : null)
		));
		exit;
	}

	/*
	 * Cache the session Obj
	 */
	static function buildSession($id, $storeId)
	{
		if (!$id)
		{
			throw new Exception('session not found');
		}

		if (self::$session and (self::$session->id != $id))
		{
			throw new Exception('invalid session cache' . self::$session->id . ':::' . $id);
		}

		if (!self::$session_init)
		{
			$Session = DAO_CFactory::create("session");
			$Session->id = $id;
			$Session->store_id = $storeId;
			$found = $Session->find(true);
			if ($found)
			{
				self::$session = $Session;
			}
			else
			{
				CLog::RecordIntense("Session not found", "ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com");
				$tpl = @CApp::instance()->template()->setErrorMsg("Could not find the session. Please start the order process again. Dream Dinners support has been notified that an issue has occurred.");
				CApp::bounce("?page=admin_list_users");
			}

			self::$session_init = true;
		}

		return self::$session;
	}

	const QUANTITY_PREFIX = 'qty_'; //entrees

	/**
	 * @return $Order
	 */
	public static function buildOrderFromArray($daoStore, $array, $User)
	{
		$Order = new COrdersDelivered();
		$Order->store_id = $daoStore->id;
		$Order->is_sampler = 0;
		$Order->family_savings_discount_version = 2;
		$Order->order_admin_notes = (!empty($array['order_admin_notes']) ? $array['order_admin_notes'] : null);
		$Order->order_user_notes = (!empty($array['order_user_notes']) ? $array['order_user_notes'] : null);

		$Order->setOrderType(COrders::DIRECT, false);

		if (isset($User) and isset($User->id))
		{
			$Order->user_id = $User->id;
		}

		$Session = self::buildSession($array['session'], $daoStore->id);
		$Order->addSession($Session);

		$getStoreMenu = CMenu::storeSpecificMenuExists($Session->menu_id, $Order->store_id);

		//look for coupon code
		if (isset($array['coupon_code_id']) and $array['coupon_code_id'])
		{
			$coupon = DAO_CFactory::create('coupon_code');
			$coupon->id = $array['coupon_code_id'];
			$foundIt = $coupon->find(true);
			if ($foundIt)
			{
				$Order->addCoupon($coupon);
			}
		}
		$items = array();

		foreach($array as $k => $v)
		{
			if (strpos($k, "qty_") === 0)
			{
				list ($pfx, $biid, $miid) = explode("_", $k);
				if (!isset($items[$biid]))
				{
					$items[$biid] = array();
				}

				$items[$biid][$miid] = $v;
			}
		}

		//clear existing menu items
		$Order->clearBoxesUnsafe();

		$totalItemQty = 0;
		//add menu items
		foreach ($items as $boxInstanceID => $boxData)
		{
			$boxInstanceObj = DAO_CFactory::create('box_instance');
			$boxInstanceObj->query("SELECT
					bi.*,
					b.title,
					b.description, 
					b.css_icon,
					b.box_type
					FROM box_instance AS bi
					JOIN box AS b ON b.id = bi.box_id
					WHERE bi.id = '" . $boxInstanceID . "'");
			$boxInstanceObj->fetch();

			foreach ($boxData as $id => $qty)
			{
				if (is_numeric($qty) && $qty > 0 && is_numeric($id))
				{
					$MenuItem = DAO_CFactory::create('menu_item');
					$MenuItem->query("SELECT
										mi.*
										FROM
										menu_item AS mi
										WHERE mi.id = $id
										AND mi.is_deleted = 0");

					if (!$MenuItem->fetch())
					{
						throw new Exception("Menu item not found: " . $id);
					}
					else
					{
						$totalItemQty += $qty;
						$Order->addItemToBox($boxInstanceObj, $MenuItem, $qty);
					}
				}
			}
		}

		//look for direct order discount
		if (isset($array['direct_order_discount']))
		{
			$Order->addDiscount($array['direct_order_discount']);
		}

		$Order->points_discount_total = 0;

        if (!empty($array['subtotal_delivery_fee']))
        {
            $Order->subtotal_service_fee = $array['subtotal_delivery_fee'];
        }


        return array(
			$Order,
			$Session
		);
	}
}

?>