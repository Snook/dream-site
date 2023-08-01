<?php
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_home extends CPage
{
	function runPublic()
	{
		$tpl = CApp::instance()->template();

		$this->runHomePage($tpl);
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$User = CUser::getCurrentUser();

		if ($User->isUserPreferred() && !$User->platePointsData['transition_has_expired'])
		{
			$User->platePointsData['conversion_data'] = CPointsUserHistory::getPreferredUserConversionData($User);
		}
		else if (($User->platePointsData['status'] == 'in_DR2' || $User->platePointsData['isDeactivatedDRUser']) && !$User->platePointsData['transition_has_expired'])
		{
			$User->platePointsData['conversion_data'] = CPointsUserHistory::getDR2ConversionData($User);
		}

		$tpl->assign('user', $User);
		$tpl->assign('DRState', CDreamRewardsHistory::getCurrentStateForUserShortForm($User));

		$this->runHomePage($tpl);
	}

	function detectAndHandleSharedLink()
	{
		if (isset($_GET['share']) && is_numeric($_GET['share']))
		{
			/// TODO: may need to throttle these requests as they represent a potential DOS attack

			$inviting_user_id = $_GET['share'];

			$newRefCode = CCustomerReferral::newSharedLinkedReferral($inviting_user_id, false);

			if ($newRefCode)
			{
				CBrowserSession::setValueAndDuration('RSV2_Origination_code', $newRefCode, 86400 * 7);
				CBrowserSession::setValueAndDuration('Inviting_user_id', $inviting_user_id, 86400 * 7);
				CBrowserSession::setValueAndDuration('RSV2_Share_source', 'user_referral', 86400 * 7);
			}

			CApp::bounce('main.php?static=share');
		}
	}

	function runHomePage($tpl)
	{
		$this->detectAndHandleSharedLink();
	}
}

?>