<?php

class page_submission_complete extends CPage
{
	function runPublic(): void
	{
		if (!CBrowserSession::getSessionVariable(CBrowserSession::SUBMISSION_MESSAGE))
		{
			CApp::bounce('/my-account');
		}

		$this->Template->assign('submission_message', CBrowserSession::getSessionVariableOnce(CBrowserSession::SUBMISSION_MESSAGE));
	}
}