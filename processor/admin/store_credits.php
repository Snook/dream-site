<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once("page/admin/user_referral.php");

require_once("CTemplate.inc");

class processor_admin_store_credits extends CPageProcessor
{

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
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

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function getTotalStoreCreditAvailable($store_id, $user_id)
	{

		$Store_Credits_Total = 0;

		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->user_id = $user_id;
		$Store_Credit->store_id = $store_id;
		$Store_Credit->is_redeemed = 0;
		$Store_Credit->is_deleted = 0;
		$Store_Credit->is_expired = 0;

		$Store_Credit->find();

		while ($Store_Credit->fetch())
		{
			$Store_Credits_Total += $Store_Credit->amount;
		}

		return $Store_Credits_Total;
	}

	function runFranchiseOwner()
	{

		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : false;
		$credit_id = !empty($_POST['credit_id']) ? $_POST['credit_id'] : false;
		$action = !empty($_POST['action']) ? $_POST['action'] : false;
		$referred_user_id = !empty($_POST['referred_user_id']) ? $_POST['referred_user_id'] : false;
		$store_id = !empty($_POST['store_id']) ? $_POST['store_id'] : false;

		if ($user_id && $credit_id && $referred_user_id && $store_id && $action == 'process_now')
		{
			$user = DAO_CFactory::create('user');
			$user->id = $user_id;
			if (!$user->find(true))
			{
				throw new exception ('invalid user id in processor_admin_store_credits');
			}

			$referred_user = DAO_CFactory::create('user');
			$referred_user->id = $referred_user_id;
			if (!$referred_user->find(true))
			{
				throw new exception ('invalid referred user id in processor_admin_store_credits');
			}

			$refSourceObj = page_admin_user_referral::createUpdateReferralSource($referred_user, $user, $credit_id);

			$RefObj = page_admin_user_referral::bindAndRewardPendingReferral($referred_user, $user, $refSourceObj, $credit_id, $store_id);

			$StoreCreditObj = DAO_CFactory::create('store_credit');
			$StoreCreditObj->id = $RefObj->store_credit_id;
			if (!$StoreCreditObj->find(true))
			{
				throw new exception ('invalid store credit in processor_admin_store_credits');
			}

			$source = CCustomerReferral::$ShortOriginationDescription[$RefObj->origination_type_code];

			if (isset($_POST['client']) && $_POST['client'] == 'order_editor')
			{

				$totalStoreCredit = $this->getTotalStoreCreditAvailable($store_id, $user_id);

				echo json_encode(array(
					'processor_success' => true,
					'result_code' => 1,
					'newSCTotal' => $totalStoreCredit
				));
			}
			else
			{
				$html = '<tr class="bgcolor_lighter">';
				$html .= '<td><input type="checkbox" name="SC_' . $RefObj->store_credit_id . '" id="SC_' . $RefObj->store_credit_id . '" checked="checked" />Apply this credit</td>';
				$html .= '<td>$<span id="SCA_' . $RefObj->store_credit_id . '">' . $StoreCreditObj->amount . '</span></td>';
				$html .= '<td>' . $source . '</td><td></td></tr>';

				echo json_encode(array(
					'processor_success' => true,
					'result_code' => 1,
					'html' => $html
				));
			}

			exit;
		}
		else if ($user_id && $credit_id && $action == 'delete')
		{
			$StoreCreditObj = DAO_CFactory::create('store_credit');
			$StoreCreditObj->id = $credit_id;

			if ($StoreCreditObj->find(true))
			{
				if (CStore::userHasAccessToStore($StoreCreditObj->store_id))
				{
					$StoreCreditObj->delete();

					$totalStoreCredit = $this->getTotalStoreCreditAvailable($store_id, $user_id);

					echo json_encode(array(
						'processor_success' => true,
						'result_code' => 9999,
						'store_id' => $StoreCreditObj->store_id,
						'newSCTotal' => $totalStoreCredit,
						'processor_message' => 'Successfully deleted the store credit'
					));
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'result_code' => 9999,
						'processor_message' => 'You do not have permission to delete this store credit.'
					));
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'result_code' => 9999,
					'processor_message' => 'Store credit not found.'
				));
			}

			exit;
		}

		echo json_encode(array(
			'processor_success' => false,
			'result_code' => 9999,
			'processor_message' => 'Illegal parameter.'
		));
	}
}

?>