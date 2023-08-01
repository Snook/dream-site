<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/Address.php");

class page_admin_resend_gift_card_emails extends CPageAdminOnly
{

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
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
		$this->runSiteAdmin();
	}

	function runFranchiseOwner()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();
		//we actually want to always use a GET instead of a post for reports
		//not sure if we'll use much of the CForm functionality
		$Form = new CForm();
		$Form->Repost = false;

		$AdminUser = CUser::getCurrentUser();

		$tpl->assign('labels', null);
		$tpl->assign('rows', null);
		$tpl->assign('rowcount', null);

		$q = array_key_exists('q', $_POST) ? CGPC::do_clean($_POST['q'], TYPE_STR) : null;

		$search_type = CGPC::do_clean(isset($_REQUEST['search_type']) ? $_REQUEST['search_type'] : false, TYPE_STR);

		if ($search_type)
		{
			$Form->DefaultValues['search_type'] = $search_type;
		}
		else
		{
			$Form->DefaultValues['search_type'] = 'recipient_email';
		}

		$search_types = array(
			'recipient_email' => 'Recipient Email Address',
			'billing_email' => 'Billing Email Address',
			'billing_name' => 'Name on Credit Card'
		);
		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::onChange => 'searchTypeChange',
			CForm::allowAllOption => true,
			CForm::options => $search_types,
			CForm::name => 'search_type'
		));

		if ($q)
		{

			$GCObj = DAO_CFactory::create('gift_card_order');

			switch ($search_type)
			{
				case 'recipient_email':
					$whereClause = "( recipient_email_address LIKE '%" . $q . "%') ";
					break;
				case 'billing_email':
					$whereClause = "( email LIKE '%" . $q . "%') ";
					break;
				case 'billing_name':
					$whereClause = "( billing_name LIKE '%" . $q . "%')  ";
					break;
			}

			$GCObj->query("SELECT purchase_date, cc_ref_number, payment_card_number, billing_name, GROUP_CONCAT(recipient_email_address SEPARATOR '|') as email_dest, GROUP_CONCAT(shipping_address_1 SEPARATOR '|')as postal_dest FROM gift_card_order " . " where  cc_ref_number in ( " . " SELECT distinct cc_ref_number FROM gift_card_order " . " WHERE $whereClause AND paid = 1  AND design_type_id <> 1   AND  gift_card_order.is_deleted = 0  ) " . " group by cc_ref_number " . " ORDER BY  purchase_date desc");

			$labels = array();
			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $GCObj);
			$tpl->assign('rowcount', $GCObj->N);
		}

		$formArray = $Form->render();
		$tpl->assign('form_list_users', $formArray);
	}

}
?>