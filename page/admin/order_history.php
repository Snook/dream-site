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

		$Orders = self::fetchOrderHistory($User->id, 0, self::$PAGE_SIZE);

		//paging control
		$totalFetchedOrder = count($Orders);
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

				$Booking = DAO_CFactory::create('booking');

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

	public static function fetchOrderHistory($user_id, $limit_start = 0, $limit = 15)
	{
		$DAO_orders = DAO_CFactory::create('orders', true);
		$DAO_orders->user_id = $user_id;
		$DAO_orders->selectAdd("booking.id AS booking_id,
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
				store.timezone_id");
		$DAO_booking = DAO_CFactory::create('booking', true);
		$DAO_booking->whereAdd("booking.status != '" . CBooking::RESCHEDULED . "'");
		$DAO_session = DAO_CFactory::create('session', true);
		$DAO_session->unsetProperty('is_deleted');
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('menu', true));
		$DAO_booking->joinAddWhereAsOn($DAO_session);
		$DAO_orders->joinAddWhereAsOn($DAO_booking);
		$DAO_orders->joinAddWhereAsOn(DAO_CFactory::create('store', true));
		$DAO_orders->limit($limit_start, $limit);
		$DAO_orders->orderBy("session.session_start DESC");

		$DAO_orders->find();

		$Orders = array();
		$totalFetched = 0;

		while ($DAO_orders->fetch())
		{
			$totalFetched++;
			// dont show saved orders for sessions which have been deleted
			if ($DAO_orders->DAO_booking->status == CBooking::SAVED && !empty($DAO_orders->session_is_deleted))
			{
				continue;
			}

			$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($DAO_orders->DAO_store->timezone_id);

			$canView = true;
			$canEdit = true;
			$sessionTS = strtotime($DAO_orders->DAO_session->session_start);

			if ($DAO_orders->DAO_booking->status != CBooking::ACTIVE && $DAO_orders->DAO_booking->status != CBooking::SAVED)
			{
				$canEdit = false;
			}

			if ($DAO_orders->DAO_booking->status == CBooking::SAVED)
			{
				$canView = false;
			}

			if ($canEdit)
			{
				$canEdit = $DAO_orders->DAO_menu->areSessionsOrdersEditable(false, $DAO_orders->DAO_store->timezone_id);
			}

			if ($DAO_orders->DAO_booking->status != CBooking::RESCHEDULED)
			{
				$Orders[$DAO_orders->DAO_booking->id] = $DAO_orders->toArray();
				$Orders[$DAO_orders->DAO_booking->id]['order_obj'] = $DAO_orders->cloneObj();

				$statusText = "";
				if (!empty($DAO_orders->DAO_booking->no_show))
				{

					if ($DAO_orders->DAO_booking->status == CBooking::CANCELLED)
					{
						$statusText = "CANCELLED (No Show)";
					}
					else
					{
						$statusText = "NO SHOW";
					}
				}
				else if (strtotime($DAO_orders->DAO_session->session_start) < $now && $DAO_orders->DAO_booking->status == CBooking::ACTIVE)
				{
					$statusText = "COMPLETED";
				}
				else
				{
					$statusText = $DAO_orders->DAO_booking->status;
				}

				if ($DAO_orders->DAO_booking->status == CBooking::CANCELLED)
				{
					if ($DAO_orders->declined_to_reschedule === 0 || $DAO_orders->declined_to_reschedule === "0")
					{
						$Orders[$DAO_orders->DAO_booking->id]['declined_to_reschedule'] = "NO";
					}
					else if (!empty($DAO_orders->DAO_booking->declined_to_reschedule))
					{
						$Orders[$DAO_orders->DAO_booking->id]['declined_to_reschedule'] = "YES";
					}
					else
					{
						$Orders[$DAO_orders->DAO_booking->id]['declined_to_reschedule'] = null;
					}

					if ($DAO_orders->DAO_booking->declined_MFY_option === 0 || $DAO_orders->DAO_booking->declined_MFY_option === "0")
					{
						$Orders[$DAO_orders->DAO_booking->id]['declined_MFY_option'] = "NO";
					}
					else if (!empty($DAO_orders->DAO_booking->declined_MFY_option))
					{
						$Orders[$DAO_orders->DAO_booking->id]['declined_MFY_option'] = "YES";
					}
					else
					{
						$Orders[$DAO_orders->DAO_booking->id]['declined_MFY_option'] = null;
					}

					if (!empty($DAO_orders->DAO_booking->reason_for_cancellation))
					{
						$Orders[$DAO_orders->DAO_booking->id]['reason_for_cancellation'] = CBooking::getBookingCancellationReasonDisplayString($DAO_orders->reason_for_cancellation);
					}
					else
					{
						$Orders[$DAO_orders->DAO_booking->id]['reason_for_cancellation'] = "";
					}
				}

				$Orders[$DAO_orders->DAO_booking->id]['status_text'] = $statusText;
				$Orders[$DAO_orders->DAO_booking->id]['canEdit'] = $canEdit;
				$Orders[$DAO_orders->DAO_booking->id]['canView'] = $canView;
				$Orders[$DAO_orders->DAO_booking->id]['can_reschedule'] = $DAO_orders->can_reschedule();
				$Orders[$DAO_orders->DAO_booking->id]['is_future'] = (($DAO_orders->DAO_session->session_start && (strtotime($DAO_orders->DAO_session->session_start) > $now) && $DAO_orders->DAO_booking->status == CBooking::ACTIVE) ? true : false);

				if (!empty($Orders[$DAO_orders->DAO_booking->id]['session_type_subtype']) && $Orders[$DAO_orders->DAO_booking->id]['session_type_subtype'] == CSession::DELIVERY)
				{
					$Orders[$DAO_orders->DAO_booking->id]['type_of_order'] .= " (DELIVERY)";
				}
			}
		}

		return $Orders;
	}

}

?>