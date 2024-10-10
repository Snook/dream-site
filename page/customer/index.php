<?php
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_index extends CPage
{
	function runPublic(): void
	{
		if (!defined('ENABLE_CUSTOMER_SITE') || ENABLE_CUSTOMER_SITE)
		{
			CApp::bounce();
		}
	}

	function runCustomer(): void
	{
		if (!defined('ENABLE_CUSTOMER_SITE') || ENABLE_CUSTOMER_SITE)
		{
			// Allow home office to see this page to work
			if (!$this->CurrentUser->isUserGroupHomeOffice())
			{
				CApp::bounce();
			}
		}
	}
}