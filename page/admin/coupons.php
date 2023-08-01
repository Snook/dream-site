<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStore.php");
require_once("includes/DAO/BusinessObject/CMenuItem.php");
require_once("includes/DAO/Coupon_code_program.php");
require_once("includes/DAO/BusinessObject/CCouponCode.php");

class page_admin_coupons extends CPageAdminOnly
{
	private $needStoreSelector = false;
	private $read_only = false;

	function runHomeOfficeManager()
	{
		$this->needStoreSelector = true;
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->read_only = true;
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
		$this->needStoreSelector = true;
		$this->runFranchiseOwner();
	}

	function runSiteAdmin()
	{
		$this->needStoreSelector = true;
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{

		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		// ------------------------------ figure out active store and create store widget if necessary
		$store_id = false;
		if ($this->needStoreSelector)
		{

			$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => false,
				CForm::onChange => 'storeChange',
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));

			$store_id = $Form->value('store');
		}
		else
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}

		if (!$store_id)
		{
			throw new Exception('no store id found');
		}

		$DAO_store = DAO_CFactory::create('store');
		$DAO_store->id = $store_id;
		if (!$DAO_store->find(true))
		{
			throw new Exception('Store not found in Menu Editor');
		}

		$filterOptions = array(
			'current' => "Current Coupons",
			'expired' => "Expired Coupons",
			'other_stores_current' => "Other store's current coupons",
			'other_stores_expired' => "Other store's expired coupons"
		);

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'filter',
			CForm::onChangeSubmit => true,
			CForm::options => $filterOptions,
			CForm::onChange => 'filterChange'
		));

		if (isset($_POST['action']) && $_POST['action'] == 'finalize')
		{

			if (!empty($_POST['optouts']))
			{
				$optouts = explode("|", $_POST['optouts']);
				array_pop($optouts);

				foreach ($optouts as $thisOpt)
				{

					if (substr($thisOpt, 0, 3) === "pid")
					{
						$pid = substr($thisOpt, 4);
						$DAO_store_coupon_program_exclusion = DAO_CFactory::create('store_coupon_program_exclusion');
						$DAO_store_coupon_program_exclusion->coupon_program_id = $pid;
						$DAO_store_coupon_program_exclusion->store_id = $store_id;
						if (!$DAO_store_coupon_program_exclusion->find())
						{
							$DAO_store_coupon_program_exclusion->insert();
						}
						else
						{
							/// shouldn't happen
							$DAO_store_coupon_program_exclusion->update();
						}
					}
					else if (substr($thisOpt, 0, 3) === "cid")
					{
						$cid = substr($thisOpt, 4);
						$DAO_store_coupon_code_exclusion = DAO_CFactory::create('store_coupon_code_exclusion');
						$DAO_store_coupon_code_exclusion->coupon_code_id = $cid;
						$DAO_store_coupon_code_exclusion->store_id = $store_id;
						if (!$DAO_store_coupon_code_exclusion->find())
						{
							$DAO_store_coupon_code_exclusion->insert();
						}
						else
						{
							/// shouldn't happen
							$DAO_store_coupon_code_exclusion->update();
						}
					}
					else if (substr($thisOpt, 0, 3) === "mid")
					{
						$DAO_store_coupon_program_exclusion = DAO_CFactory::create('store_coupon_program_exclusion');
						$DAO_store_coupon_program_exclusion->coupon_program_id = 'null';
						$DAO_store_coupon_program_exclusion->store_id = $store_id;
						if (!$DAO_store_coupon_program_exclusion->find())
						{
							$DAO_store_coupon_program_exclusion->insert();
						}
						else
						{
							/// shouldn't happen
							$DAO_store_coupon_program_exclusion->update();
						}
					}
				}
			}

			if (!empty($_POST['optins']))
			{
				$optins = explode("|", CGPC::do_clean($_POST['optins'], TYPE_STR));
				array_pop($optins);
				foreach ($optins as $thisOpt)
				{
					if (substr($thisOpt, 0, 3) === "pid")
					{
						$pid = substr($thisOpt, 4);
						$DAO_store_coupon_program_exclusion = DAO_CFactory::create('store_coupon_program_exclusion');
						$DAO_store_coupon_program_exclusion->coupon_program_id = $pid;
						$DAO_store_coupon_program_exclusion->store_id = $store_id;
						if ($DAO_store_coupon_program_exclusion->find(true))
						{
							$DAO_store_coupon_program_exclusion->delete();
						}
						else
						{
							/// shouldn't happen
						}
					}
					else if (substr($thisOpt, 0, 3) === "cid")
					{
						$cid = substr($thisOpt, 4);
						$DAO_store_coupon_code_exclusion = DAO_CFactory::create('store_coupon_code_exclusion');
						$DAO_store_coupon_code_exclusion->coupon_code_id = $cid;
						$DAO_store_coupon_code_exclusion->store_id = $store_id;
						if ($DAO_store_coupon_code_exclusion->find(true))
						{
							$DAO_store_coupon_code_exclusion->delete();
						}
						else
						{
							/// shouldn't happen
						}
					}
					else if (substr($thisOpt, 0, 3) === "mid")
					{
						$DAO_store_coupon_program_exclusion = DAO_CFactory::create('store_coupon_program_exclusion');
						$DAO_store_coupon_program_exclusion->coupon_program_id = 'null';
						$DAO_store_coupon_program_exclusion->store_id = $store_id;
						if ($DAO_store_coupon_program_exclusion->find(true))
						{
							$DAO_store_coupon_program_exclusion->delete();
						}
						else
						{
							/// shouldn't happen
						}
					}
				}
			}
		}

		$excludeAll = false;

		$DAO_store_coupon_program_exclusion = DAO_CFactory::create('store_coupon_program_exclusion');
		$DAO_store_coupon_program_exclusion->store_id = $store_id;
		$DAO_store_coupon_program_exclusion->coupon_program_id = 'NULL';
		$DAO_store_coupon_program_exclusion->find();

		if ($DAO_store_coupon_program_exclusion->fetch())
		{
			$excludeAll = true;
		}

		$couponArray = $this->buildCouponArray($DAO_store, isset($_POST['filter']) ? CGPC::do_clean($_POST['filter'], TYPE_STR) : 'current');

		if (isset($_POST['filter']) && (CGPC::do_clean($_POST['filter'], TYPE_STR) == 'other_stores_current' || CGPC::do_clean($_POST['filter'], TYPE_STR) == 'other_stores_expired'))
		{
			$this->read_only = true;
		}

		$tpl->assign('read_only', $this->read_only);
		$tpl->assign('form', $Form->render());
		$tpl->assign('master_exclusion', $excludeAll);
		$tpl->assign('coupon_array', $couponArray);
	}

	function buildCouponArray($DAO_store, $filter = 'current')
	{
		$couponArray = array();

		$DAO_coupon_code = DAO_CFactory::create('coupon_code');
		$DAO_coupon_code->selectAdd();
		$DAO_coupon_code->selectAdd("coupon_code.*");

		$DAO_coupon_code_program = DAO_CFactory::create('coupon_code_program');
		$DAO_store_coupon_program_exclusion = DAO_CFactory::create('store_coupon_program_exclusion');
		$DAO_store_coupon_program_exclusion->store_id = $DAO_store->id;

		$DAO_coupon_code_program->joinAddWhereAsOn($DAO_store_coupon_program_exclusion, 'LEFT');

		$DAO_coupon_code->joinAddWhereAsOn($DAO_coupon_code_program, 'LEFT');
		$DAO_coupon_code->joinAddWhereAsOn(DAO_CFactory::create('menu_item'), 'LEFT');

		$DAO_coupon_code_to_store = DAO_CFactory::create('coupon_to_store');
		$DAO_coupon_code_to_store->store_id = $DAO_store->id;

		$DAO_coupon_code->joinAddWhereAsOn($DAO_coupon_code_to_store, 'LEFT');

		$DAO_store_coupon_code_exclusion = DAO_CFactory::create('store_coupon_code_exclusion');
		$DAO_store_coupon_code_exclusion->store_id = $DAO_store->id;

		$DAO_coupon_code->joinAddWhereAsOn($DAO_store_coupon_code_exclusion, 'LEFT');

		switch ($filter)
		{
			case 'other_stores_expired':
			case 'expired':
				$DAO_coupon_code->whereAdd("coupon_code.valid_timespan_end <= NOW()");
				break;
			case 'other_stores_current':
			case 'current':
			default:
				$DAO_coupon_code->whereAdd("coupon_code.valid_timespan_end > NOW()");
				break;
		}

		switch ($filter)
		{
			case 'other_stores_expired':
			case 'other_stores_current':
				$display_only = true;
				$DAO_coupon_code->having("coupon_code.is_store_specific = 1 AND coupon_to_store.id IS NULL");
				break;
			case 'expired':
			case 'current':
			default:
				$display_only = false;
				$DAO_coupon_code->having("coupon_code.is_store_specific = 0 OR coupon_to_store.id IS NOT NULL");
				break;
		}

		$DAO_coupon_code->find();

		while ($DAO_coupon_code->fetch())
		{
			if (empty($couponArray[$DAO_coupon_code->program_id]))
			{
				$couponArray[$DAO_coupon_code->program_id] = array(
					'excluded' => (!empty($DAO_coupon_code->DAO_store_coupon_program_exclusion->id) ? true : false),
					'name' => $DAO_coupon_code->DAO_coupon_code_program->program_name
				);
			}

			$couponArray[$DAO_coupon_code->program_id][$DAO_coupon_code->id] = array(
				'coupon_title' => $DAO_coupon_code->coupon_code_title,
				'coupon_code' => $DAO_coupon_code->coupon_code,
				'comments' => $DAO_coupon_code->comments,
				'coupon_code_description' => $DAO_coupon_code->coupon_code_description,
				'coupon_type' => $DAO_coupon_code->discount_method,
				'coupon_menu_item_id' => $DAO_coupon_code->menu_item_id,
				'coupon_menu_item_name' => $DAO_coupon_code->DAO_menu_item->menu_item_namee,
				'valid_timespan_start' => $DAO_coupon_code->valid_timespan_start,
				'valid_timespan_end' => $DAO_coupon_code->valid_timespan_end,
				'excluded' => (!empty($DAO_coupon_code->DAO_store_coupon_code_exclusion->id) ? true : false)
			);

			if ($DAO_coupon_code->discount_method == CCouponCode::FREE_MEAL && !empty($DAO_coupon_code->menu_item_id))
			{
				$menuItem = CMenuItem::getStoreSpecificItem($DAO_store, $DAO_coupon_code->menu_item_id);
				$item_cost = '$' . CTemplate::moneyFormat($menuItem->store_price);

				$couponArray[$DAO_coupon_code->program_id][$DAO_coupon_code->id]['coupon_value'] = $item_cost;
			}
			else if ($DAO_coupon_code->discount_method == CCouponCode::FLAT)
			{
				$couponArray[$DAO_coupon_code->program_id][$DAO_coupon_code->id]['coupon_value'] = '$' . CTemplate::moneyFormat($DAO_coupon_code->discount_var);
			}
			else if ($DAO_coupon_code->discount_method == CCouponCode::PERCENT)
			{
				$couponArray[$DAO_coupon_code->program_id][$DAO_coupon_code->id]['coupon_value'] = CTemplate::moneyFormat($DAO_coupon_code->discount_var) . "%";
			}
		}

		return $couponArray;
	}
}

?>