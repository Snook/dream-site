<?php // page_user_details.php

require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStatesAndProvinces.php");
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CEnrollmentPackage.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CRecipe.php');
require_once("includes/CSessionToolsPrinting.inc");

class page_admin_session_tools_printing extends CPageAdminOnly
{

	private $current_store_id = null;

	private $show = array(
		'store_selector' => false
	);

	function runManufacturerStaff()
	{
		$this->runSessionToolsPrinting();
	}

	function runFranchiseStaff()
	{
		$this->runSessionToolsPrinting();
	}

	function runFranchiseLead()
	{
		$this->runSessionToolsPrinting();
	}

	function runEventCoordinator()
	{
		$this->runSessionToolsPrinting();
	}

	function runOpsLead()
	{
		$this->runSessionToolsPrinting();
	}

	function runOpsSupport()
	{
		$this->runSessionToolsPrinting();
	}

	function runFranchiseManager()
	{
		$this->runSessionToolsPrinting();
	}

	function runFranchiseOwner()
	{
		$this->runSessionToolsPrinting();
	}

	function runHomeOfficeStaff()
	{
		$this->show['store_selector'] = true;

		$this->runSessionToolsPrinting();
	}

	function runHomeOfficeManager()
	{
		$this->show['store_selector'] = true;

		$this->runSessionToolsPrinting();
	}

	function runSiteAdmin()
	{
		$this->show['store_selector'] = true;

		$this->runSessionToolsPrinting();
	}

	function runSessionToolsPrinting()
	{
		ini_set('memory_limit', '128M');

		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		$current_menu_id = CMenu::getCurrentMenuId();

		$menu_array = CMenu::menuInfoArray(false, false, 'DESC', 4, true);

		$menuOptions = array();

		foreach ($menu_array as $menu_id => $menuInfo)
		{
			$menuOptions[$menu_id] = $menuInfo['menu_name'];
		}

		$Form->DefaultValues['menus'] = $current_menu_id;

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::options => $menuOptions,
			CForm::name => 'menus'
		));

		if ($this->show['store_selector'])
		{
			if (!empty($_GET['store_id']))
			{
				$Form->DefaultValues['store'] = CGPC::do_clean($_GET['store_id'], TYPE_INT);
			}
			else
			{
				$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();
			}

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));

			$this->current_store_id = $Form->value('store');
		}
		else
		{
			$this->current_store_id = CBrowserSession::getCurrentFadminStore();
		}

		$session_id = false;
		$session_info_array = false;

		if (!empty($_GET['session']) && is_numeric($_GET['session']))
		{
			$session_id = $_GET['session'];

			if (CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER)
			{
				$session_info_array = CSession::getDeliveredSessionDetailArray($session_id);
			}
			else
			{
				$session_info_array = CSession::getSessionDetailArray($session_id);
			}

			if (empty($session_info_array))
			{
				$tpl->setErrorMsg('Session not found.');

				CApp::bounce('main.php?page=admin_session_tools_printing');
			}
		}

		if (!empty($_GET['date']))
		{
			$date = CGPC::do_clean($_GET['date'], TYPE_STR);

			list ($date_info_array, $session_info_array) = CSession::getSessionDetailArrayByDate($this->current_store_id, $date);

			if (empty($session_info_array))
			{
				$tpl->setErrorMsg('No Sessions found for the requested date.');

				CApp::bounce('main.php?page=admin_session_tools_printing');
			}
		}

		if (!empty($_GET['menu']))
		{
			$menu_id = CGPC::do_clean($_GET['menu'], TYPE_INT);
		}

		if (!empty($_GET['do']) && $_GET['do'] == 'print')
		{
			$docSetPrinter = new CSessionToolsPDFDrawer();

			// do generic menu
			if (!empty($_GET['menu']))
			{
				$docSetData = new CSessionToolsData($menu_id, $this->current_store_id);
				$docSetData->loadStaticData();
				$docSetPrinter->setData($docSetData);

				if ((!empty($_GET['core']) && $_GET['core'] == 'true') || (!empty($_GET['freezer']) && $_GET['freezer'] == 'true') || (!empty($_GET['nutrition']) && $_GET['nutrition'] == 'true') || (!empty($_GET['recipe_expert']) && $_GET['recipe_expert'] == 'true'))
				{
					$docSetData->loadMenuData();
				}

				if (!empty($_GET['core']) && $_GET['core'] == 'true')
				{
					$docSetPrinter->printGenericCoreMenuPDF();
				}

				if (!empty($_GET['freezer']) && $_GET['freezer'] == 'true')
				{
					$docSetPrinter->printGenericFreezerListPDF();
				}

				if (!empty($_GET['nutrition']) && $_GET['nutrition'] == 'true')
				{
					$docSetPrinter->printGenericNutritionPDF();
				}

				if (!empty($_GET['intro']) && $_GET['intro'] == 'true')
				{
					$docSetData->loadBundleMenuData('TV_OFFER');

					$docSetPrinter->printGenericIntroMenuPDF();
				}

				if (!empty($_GET['dream_taste']) && $_GET['dream_taste'] == 'true')
				{
					$docSetData->loadBundleMenuData('DREAM_TASTE');

					$docSetPrinter->printGenericDreamTasteMenuPDF();
				}

				if (!empty($_GET['recipe_expert']) && $_GET['recipe_expert'] == 'true')
				{
					$docSetPrinter->printGenericRecipeExpertPDF();
				}

				$docSetPrinter->output();
				exit;
			}
			// do customer menus
			else
			{
				foreach ($session_info_array as $session_id => $sessionArray)
				{
					$menu_id = $sessionArray['menu_id'];

					$docSetData = new CSessionToolsData($menu_id, $this->current_store_id);
					$docSetData->loadStaticData();
					$docSetPrinter->setData($docSetData);

					$bookings = array();
					if ($sessionArray['session_type'] === CSession::DELIVERED)
					{
						$bookings = array_merge($sessionArray['bookings'], $sessionArray['shipping_bookings']);
					}
					else
					{
						$bookings = $sessionArray['bookings'];
					}

					if (!empty($bookings))
					{
						// get array of orders
						$session_orders = array();
						foreach ($bookings as $bid => $booking)
						{
							$session_orders[] = $booking['order_id'];
						}

						if ((!empty($_GET['freezer']) && $_GET['freezer'] == 'true') || (!empty($_GET['nutrition']) && $_GET['nutrition'] == 'true'))
						{
							if ($docSetData->storeInfo->store_type == CStore::DISTRIBUTION_CENTER)
							{
								$docSetData->loadOrderedItemDataDelivered($session_orders);
							}
							else
							{
								$docSetData->loadOrderedItemData($session_orders);
							}
						}

						foreach ($bookings as $bid => $booking)
						{
							// get a single user
							if (!empty($_GET['user_id']) && $_GET['user_id'] != $booking['user_id'])
							{
								continue;
							}

							if ($booking['status'] == CBooking::ACTIVE)
							{
								$docSetData->loadUserData($booking);

								if (!empty($_GET['core']) && $_GET['core'] == 'true')
								{
									// print if no next month order
									$has_next_month_order = false;

									foreach ($booking['user']->nextSession as $nextSession)
									{
										if ($nextSession->menu_id != $booking['menu_id'] && ($nextSession->session_type == CSession::STANDARD || $nextSession->session_type == CSession::SPECIAL_EVENT))
										{
											$has_next_month_order = true;
											break;
										}
									}

									if (!empty($_GET['cur']) && $_GET['cur'] == 'true')
									{
										$docSetData->loadMenuData($booking, false);

										$docSetPrinter->printThisMonthsMenuPDF();
									}
									else
									{
										if (!$has_next_month_order)
										{
											if (!empty($booking['user']->preferences[CUser::SESSION_PRINT_NEXT_MENU]['value']) || !empty($_GET['force']))
											{
												$docSetData->loadMenuData($booking, true);

												$docSetPrinter->printNextMonthsMenuPDF();
											}
										}
									}
								}

								if (!empty($_GET['freezer']) && $_GET['freezer'] == 'true')
								{
									if (!empty($booking['user']->preferences[CUser::SESSION_PRINT_FREEZER_SHEET]['value']) || !empty($_GET['force']))
									{
										$docSetPrinter->printFreezerListPDF();
									}
								}

								if (!empty($_GET['nutrition']) && $_GET['nutrition'] == 'true')
								{
									if (!empty($booking['user']->preferences[CUser::SESSION_PRINT_NUTRITIONALS]['value']) || !empty($_GET['force']))
									{
										$docSetPrinter->printNutritionalPDF();
									}
								}
							}
						}
					}
				}

				$docSetPrinter->output();
				exit;
			}
		}

		$tpl->assign('show', $this->show);
		$tpl->assign('store_id', $this->current_store_id);
		$tpl->assign('form', $Form->render());
	}
}

?>