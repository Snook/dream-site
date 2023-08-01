<?php
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');

class page_platepoints extends CPage {

	function runPublic() {

		$tpl = CApp::instance()->template();

		$tpl->assign('view_badge', false);

		$User = CUser::getCurrentUser();

		if (!empty($_REQUEST['badge']))
		{
			$badgeInfo = CPointsUserHistory::getLevelDetailsByLevel($_REQUEST['badge']);

			if ($badgeInfo)
			{
				$tpl->assign('view_badge', $badgeInfo);
			}
		}

		$tpl->assign('user', $User);
		$tpl->assign('user_is_preferred', $User->isUserPreferred());
	}
}
?>