<?php // admin_page_order_history.php
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/BusinessObject/CTimezones.php';
require_once 'includes/DAO/BusinessObject/CBooking.php';

class page_admin_order_history extends CPageAdminOnly
{

	public static $PAGE_SIZE = 10;
	function runSiteAdmin()
	{
		return $this->runFranchiseOwner();
	}

	function runManufacturerStaff()
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

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseOwner()
	{
		ini_set('memory_limit', '72M');

		$tpl = CApp::instance()->template();

		$id = false;
		
		if (isset($_REQUEST['id']) && $_REQUEST['id'])
		{
			$id = CGPC::do_clean($_REQUEST['id'], TYPE_INT);
		}

		if (!$id)
		{
			$tpl->setErrorMsg("The user id is invalid.");
			
			if (isset($_REQUEST['back']))
			{
				CApp::bounce($_REQUEST['back']);
			}

			CApp::bounce("main.php?page=admin_main");
		}

		if (isset($_REQUEST['back']))
		{
			$tpl->assign('back', $_REQUEST['back']);
		}
		else
		{
			$tpl->assign('back', 'main.php?page=admin_user_details&amp;id=' . $id);
		}

		$User = DAO_CFactory::create('user');
		$User->id = $id;
		if (!$User->find(true))
		{
			$tpl->setErrorMsg("The user could not be found.");
				
			if (isset($_REQUEST['back']))
			{
				CApp::bounce($_REQUEST['back']);
			}
				
			CApp::bounce("main.php?page=admin_main");
		}

		$tpl->assign('user', $User->toArray());
		$tpl->assign('user_id', $User->id);


		$Orders = self::fetchOrderHistory($User->id,self::$PAGE_SIZE);

		//paging control
		$totalFetchedOrder = count($Orders) ;
		$shouldPage = $totalFetchedOrder > (self::$PAGE_SIZE - 3);
		$tpl->assign('orders', $Orders);
		$tpl->assign('pagination', $shouldPage);
		$tpl->assign('pagination_prev', false);
		$tpl->assign('pagination_next', true);
		$tpl->assign('page_cur', 0);

		$tpl->assign('active_menus', CMenu::getActiveMenuArray());
		
		
		if (isset($_REQUEST['send_test_reminder_email']) && $_REQUEST['send_test_reminder_email'] = true)
		{
		    
		    if (isset($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
		    {
		        $order_id = $_REQUEST['order_id'];
		        
    		   $Booking =  DAO_CFactory::create('booking');
    		    
    		    $Booking->query("SELECT
    			s.id AS session_id,
    			s.session_start,
    			s.session_type,
    			s.duration_minutes,
    			s.menu_id,
    			m.menu_name,
    			u.firstname,
    			u.lastname,
    			u.primary_email,
    			st.store_name,
    			st.id AS store_id,
    			st.email_address AS store_email,
    			b.user_id,
    			b.order_id AS order_id,
    			b.booking_type,
    			o.menu_program_id,
    			o.bundle_id
    			FROM booking AS b
    			LEFT JOIN `session` AS s ON s.id = b.session_id AND s.is_deleted = '0'
    			LEFT JOIN `user` AS u ON u.id = b.user_id AND u.is_deleted = '0'
    			LEFT JOIN orders AS o ON b.order_id = o.id AND o.is_deleted = '0'
    			LEFT JOIN store AS st ON st.id = s.store_id AND st.is_deleted = '0'
    			LEFT JOIN menu AS m ON m.id = s.menu_id AND m.is_deleted = '0'
    			WHERE b.order_id = $order_id AND b.`status` = 'ACTIVE' AND b.is_deleted = '0'");
    		    }
    		    
    		    if ($Booking->fetch())
    		    {
    		        $Booking->send_reminder_email();
    		    }
		    
		}
		    
	}

	public static function fetchOrderHistory($user_id, $limit = 15){
		$Order = DAO_CFactory::create('orders');
		$query = "SELECT
				booking.id AS booking_id,
				booking.status,
				booking.order_id,
				booking.no_show,
                booking.reason_for_cancellation,
                booking.declined_MFY_option,
                booking.declined_to_reschedule,
				orders.id,
				orders.order_user_notes,
				orders.order_admin_notes,
				orders.order_confirmation,
				orders.grand_total,
				orders.type_of_order,
				orders.servings_total_count,
				orders.timestamp_created,
				orders.store_id,
				session.id AS session_id,
				session.session_start,
				session.menu_id AS idmenu,
       			session.session_type,
       			session.session_type_subtype,
				session.is_deleted AS session_is_deleted,
				store.store_name,
				store.timezone_id
				FROM orders
				INNER JOIN booking ON booking.order_id = orders.id AND booking.is_deleted = '0'
				INNER JOIN store ON store.id = orders.store_id AND store.is_deleted = '0'
				LEFT JOIN session ON booking.session_id = session.id
				WHERE orders.user_id = '" . $user_id . "'
				AND orders.is_deleted = 0
				AND booking.status != 'RESCHEDULED'
				ORDER BY session.session_start DESC limit ".$limit;
		$Order->query($query );

		$Orders = array();
		$totalFetched = 0;

		while ($Order->fetch())
		{
			$totalFetched++;
			// dont show saved orders for sessions which have been deleted
			if ($Order->status == CBooking::SAVED && !empty($Order->session_is_deleted))
			{
				continue;
			}

			$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($Order->timezone_id);

			$canView = true;
			$canEdit = true;
			$sessionTS = strtotime($Order->session_start);

			if ($Order->status != CBooking::ACTIVE && $Order->status != CBooking::SAVED)
			{
				$canEdit = false;
			}

			if ($Order->status == CBooking::SAVED)
			{
				$canView = false;
			}

			if ($canEdit)
			{

				$MenuObj = DAO_CFactory::create('menu');
				$MenuObj->id = $Order->idmenu;
				$MenuObj->find(true);
				$canEdit = $MenuObj->areSessionsOrdersEditable(false, $Order->timezone_id);

			}


			if ($Order->status != CBooking::RESCHEDULED)
			{

				$Orders[$Order->booking_id] = $Order->toArray();

				$statusText = "";
				if (!empty($Order->no_show))
				{

					if ($Order->status == CBooking::CANCELLED)
					{
						$statusText = "CANCELLED (No Show)";
					}
					else
					{
						$statusText = "NO SHOW";
					}

				}
				else if (strtotime($Order->session_start) < $now && $Order->status == CBooking::ACTIVE)
				{
					$statusText = "COMPLETED";
				}
				else
				{
					$statusText = $Order->status;
				}

				if ($Order->status == CBooking::CANCELLED)
				{
					if ($Order->declined_to_reschedule === 0 || $Order->declined_to_reschedule === "0")
					{
						$Orders[$Order->booking_id]['declined_to_reschedule'] = "NO";
					}
					else if (!empty($Order->declined_to_reschedule))
					{
						$Orders[$Order->booking_id]['declined_to_reschedule'] = "YES";
					}
					else
					{
						$Orders[$Order->booking_id]['declined_to_reschedule'] = null;
					}

					if ($Order->declined_MFY_option === 0 || $Order->declined_MFY_option === "0")
					{
						$Orders[$Order->booking_id]['declined_MFY_option'] = "NO";
					}
					else if (!empty($Order->declined_MFY_option))
					{
						$Orders[$Order->booking_id]['declined_MFY_option'] = "YES";
					}
					else
					{
						$Orders[$Order->booking_id]['declined_MFY_option'] = null;
					}

					if (!empty($Order->reason_for_cancellation))
					{
						$Orders[$Order->booking_id]['reason_for_cancellation'] = CBooking::getBookingCancellationReasonDisplayString($Order->reason_for_cancellation);
					}
					else
					{
						$Orders[$Order->booking_id]['reason_for_cancellation'] = "";
					}
				}

				$Orders[$Order->booking_id]['status_text'] = $statusText;
				$Orders[$Order->booking_id]['canEdit'] = $canEdit;
				$Orders[$Order->booking_id]['canView'] = $canView;
				$Orders[$Order->booking_id]['can_reschedule'] = $Order->can_reschedule();
				$Orders[$Order->booking_id]['is_future'] = (($Order->session_start && (strtotime($Order->session_start) > $now) && $Order->status == CBooking::ACTIVE) ? true : false);

				if (!empty($Orders[$Order->booking_id]['session_type_subtype']) && $Orders[$Order->booking_id]['session_type_subtype'] == CSession::DELIVERY)
				{
					$Orders[$Order->booking_id]['type_of_order'] .=  " (DELIVERY)";
				}

			}
		}

		return $Orders;
	}

		
}

?>