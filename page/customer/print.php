<?php
require_once("page/customer/order_details_gift_card.php");
require_once('includes/DAO/BusinessObject/CRecipe.php');
require_once('includes/DAO/BusinessObject/CFundraiser.php');
require_once('includes/DAO/BusinessObject/CDreamTasteEvent.php');
require_once('includes/DAO/BusinessObject/COffsitelocation.php');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once("includes/CSessionToolsPrinting.inc");

class page_print extends CPage
{
	/**
	 * @throws Exception
	 */
	function runPublic(): void
	{
		$req_menu = CGPC::do_clean((!empty($_REQUEST['menu']) ? $_REQUEST['menu'] : false), TYPE_INT);
		$req_store = CGPC::do_clean((!empty($_REQUEST['store']) ? $_REQUEST['store'] : false), TYPE_INT);

		if (!empty($req_menu) && is_numeric($req_menu) && !empty($req_store) && is_numeric($req_store))
		{
			$active_menus = CMenu::getActiveMenuArray();
			if (!array_key_exists($req_menu, $active_menus))
			{
				$this->Template->setErrorMsg('Requested menu not found, please contact support.');
				CApp::bounce();
			}

			$storeInfo = CStore::getStoreAndOwnerInfo($req_store);
			if (!$storeInfo)
			{
				$this->Template->setErrorMsg('Requested store not found, please contact support.');
				CApp::bounce();
			}

			$docSetPrinter = new CSessionToolsPDFDrawer();
			$docSetData = new CSessionToolsData($req_menu, $req_store);
			$docSetData->loadStaticData();
			$docSetPrinter->setData($docSetData);
			if (!empty($_GET['intro']) && $_GET['intro'] == 'true')
			{
				$docSetData->loadBundleMenuData();
				$docSetPrinter->printGenericIntroMenuPDF();
			}
			else if (!empty($_GET['nutrition']) && $_GET['nutrition'] == 'true')
			{
				$docSetData->loadMenuData();

				$docSetPrinter->printGenericNutritionPDF();
			}
			else
			{
				$docSetData->loadMenuData();
				$docSetPrinter->printGenericCoreMenuPDF();
			}
			$docSetPrinter->output();
			exit;
		}
		else
		{
			CApp::bounce();
		}

		CApp::forceLogin();
	}

	/**
	 * @throws Exception
	 */
	function runCustomer(): void
	{
		// Generated PDF requested
		$req_fundraiser_event_pdf = CGPC::do_clean((!empty($_REQUEST['fundraiser_event_pdf']) ? $_REQUEST['fundraiser_event_pdf'] : false), TYPE_INT);
		$req_dream_taste_event_pdf = CGPC::do_clean((!empty($_REQUEST['dream_taste_event_pdf']) ? $_REQUEST['dream_taste_event_pdf'] : false), TYPE_INT);
		$req_pick_up_event_pdf = CGPC::do_clean((!empty($_REQUEST['remote_pickup_private_event_pdf']) ? $_REQUEST['remote_pickup_private_event_pdf'] : false), TYPE_INT);
		$req_menu = CGPC::do_clean((!empty($_REQUEST['menu']) ? $_REQUEST['menu'] : false), TYPE_INT);
		$req_store = CGPC::do_clean((!empty($_REQUEST['store']) ? $_REQUEST['store'] : false), TYPE_INT);
		$req_order = CGPC::do_clean((!empty($_REQUEST['order']) ? $_REQUEST['order'] : false), TYPE_INT);

		$active_menus = CMenu::getActiveMenuArray();
		if (!empty($req_menu) && !array_key_exists($req_menu, $active_menus) && empty($req_order) && empty($req_pick_up_event_pdf) && empty($req_fundraiser_event_pdf) && empty($req_dream_taste_event_pdf))
		{
			$this->Template->setErrorMsg('Requested menu not found, please contact support.');
			CApp::bounce();
		}

		$storeInfo = CStore::getStoreAndOwnerInfo($req_store);
		if (!$storeInfo && empty($req_order) && empty($req_pick_up_event_pdf) && empty($req_fundraiser_event_pdf) && empty($req_dream_taste_event_pdf))
		{
			$this->Template->setErrorMsg('Requested store not found, please contact support.');
			CApp::bounce();
		}

		if (!empty($req_dream_taste_event_pdf))
		{
			CDreamTasteEvent::generatePDF($req_dream_taste_event_pdf);
			exit;
		}
		else if (!empty($req_fundraiser_event_pdf))
		{
			CFundraiser::generatePDF($req_fundraiser_event_pdf);
			exit;
		}
		else if (!empty($req_pick_up_event_pdf))
		{
			COffsitelocation::generatePDF($req_pick_up_event_pdf);
			exit;
		}

		// PDF menu tool
		if (!empty($req_menu) && is_numeric($req_menu) && !empty($req_store) && is_numeric($req_store))
		{
			$docSetPrinter = new CSessionToolsPDFDrawer();
			$docSetData = new CSessionToolsData($req_menu, $req_store);
			$docSetData->loadStaticData();
			$docSetPrinter->setData($docSetData);
			if (!empty($_GET['intro']) && $_GET['intro'] == 'true')
			{
				$docSetData->loadBundleMenuData();
				$docSetPrinter->printGenericIntroMenuPDF();
			}
			else if (!empty($_GET['nutrition']) && $_GET['nutrition'] == 'true')
			{
				$docSetData->loadMenuData();

				$docSetPrinter->printGenericNutritionPDF();
			}
			else
			{
				$userID = CUser::getCurrentUser()->id;
				$docSetData->loadPartialUserData($userID);
				$docSetData->loadMenuData(false, false, $userID);
				$docSetPrinter->printGenericCoreMenuPDF();
			}
			$docSetPrinter->output();
			exit;
		}
		else if (!empty($req_order) && is_numeric($req_order))
		{
			$booking = DAO_CFactory::create('booking');
			$booking->order_id = $req_order;
			if (CUser::getCurrentUser()->user_type == CUser::CUSTOMER)
			{
				$booking->user_id = CUser::getCurrentUser()->id;
			}
			$booking->joinAdd(DAO_CFactory::create('orders'));
			$booking->joinAdd(DAO_CFactory::create('session'));
			$booking->selectAdd("booking.id AS booking_id");
			$booking->whereAdd("booking.status = '" . CBooking::ACTIVE . "'");

			if ($booking->find(true))
			{
				// NOTE: if the order is a delivered order we need to ignore the menu_id of the session and used the
				// menu_id of the bundle instead
				if ($booking->session_type == CSession::DELIVERED)
				{
					$booking->menu_id = COrdersDelivered::getMenuIDBasedOnBundle($req_order);
				}

				$docSetPrinter = new CSessionToolsPDFDrawer();

				$session_info_array = CSession::getSessionDetailArray($booking->session_id);

				foreach ($session_info_array as $sessionArray)
				{
					$docSetData = new CSessionToolsData($booking->menu_id, $booking->store_id);
					$docSetData->loadStaticData();
					$docSetPrinter->setData($docSetData);

					$session_orders[] = $booking->order_id;

					$docSetData->loadOrderedItemData($session_orders);

					$booking = $sessionArray['bookings'][$booking->booking_id];

					if ($booking['status'] == CBooking::ACTIVE)
					{
						$docSetData->loadUserData($booking);

						if (!empty($_GET['core']) && $_GET['core'] == 'true')
						{
							if (!empty($_GET['cur']) && $_GET['cur'] == 'true')
							{
								$docSetData->loadMenuData($booking);
								$docSetPrinter->printThisMonthsMenuPDF();
							}
							else
							{
								$docSetData->loadMenuData($booking, true);
								$docSetPrinter->printNextMonthsMenuPDF();
							}
						}

						if (!empty($_GET['freezer']) && $_GET['freezer'] == 'true')
						{
							$docSetPrinter->printFreezerListPDF();
						}

						if (!empty($_GET['nutrition']) && $_GET['nutrition'] == 'true')
						{
							$docSetPrinter->printNutritionalPDF();
						}
					}
				}

				$docSetPrinter->output();
				exit;
			}
			else
			{
				$this->Template->setErrorMsg('Requested menu not found, please contact support.');
				CApp::bounce();
			}
		}

		CApp::bounce();
	}
}