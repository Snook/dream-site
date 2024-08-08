<?php
require_once('includes/DAO/BusinessObject/CDreamTasteEvent.php');

class page_my_events extends CPage
{

	function runPublic()
	{
		CApp::forceLogin(returnUrl: CApp::instance()->template()->bounceBackUrl(currentUrl: true));
	}

	/**
	 * @throws Exception
	 */
	function runCustomer()
	{
		ini_set('memory_limit', '512M');

		$tpl = CApp::instance()->template();
		$User = CUser::getCurrentUser();

		$tpl->assign('isIE11', CTemplate::isIE11());
		$tpl->assign('isMobileSafari', CTemplate::isMobileSafari());

		$usersFuturePastEvents = CDreamTasteEvent::getUsersFuturePastEvents($User->id);

		$Store = DAO_CFactory::create('store');
		$Store->id = CUser::getCurrentUser()->home_store_id;
		$Store->find(true);

		if (!empty($usersFuturePastEvents['manageEvent']) && $usersFuturePastEvents['manageEvent']['is_past'])
		{
			$tpl->setErrorMsg('The session has passed and is no longer available.');
			CApp::bounce('/my-events');
		}

		$tpl->assign('store', $Store);
		$tpl->assign('usersFuturePastEvents', $usersFuturePastEvents);
	}
}