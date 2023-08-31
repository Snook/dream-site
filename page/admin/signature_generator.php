<?php
require_once("CPageAdminOnly.inc");

class page_admin_signature_generator extends CPageAdminOnly {

	function runFranchiseManager()
	{
		$this->signatureGenerator();
	}
	function runHomeOfficeStaff()
	{
		$this->signatureGenerator();
	}
	function runHomeOfficeManager()
	{
		$this->signatureGenerator();
	}
	function runSiteAdmin()
	{
		$this->signatureGenerator();
	}
	function runFranchiseOwner()
	{
		$this->signatureGenerator();
	}
	function runFranchiseLead()
	{
		$this->signatureGenerator();
	}
	function runEventCoordinator()
	{
		$this->signatureGenerator();
	}
	function runOpsLead()
	{
		$this->signatureGenerator();
	}



	function signatureGenerator()
	{
		if (isset($_POST['signature']))
		{
			$filesize = strlen( $_POST['signature'] );

			header('Content-type: text/plain');
			header( 'Content-Length: '.$filesize );
			header( 'Content-Disposition: attachment; filename="dd_email_signature.htm"' );
			header( 'Cache-Control: max-age=10' );
			header('Pragma: public');

			echo trim(CGPC::do_clean($_POST['signature'],TYPE_NOCLEAN));
			exit;
		}

		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		$User = CUser::getCurrentUser();

		list($storeInfo, $ownerInfo) = CStore::getStoreAndOwnerInfo($User->home_store_id);

		$Form->DefaultValues['sig-first_last'] = $User->firstname . ' ' . $User->lastname;
		$Form->DefaultValues['sig-telephone'] = $storeInfo['telephone_1'];
		$Form->DefaultValues['sig-email'] = $User->primary_email;
		$Form->DefaultValues['sig-addressline1'] = $storeInfo['address_line1'];
		$Form->DefaultValues['sig-addressline2'] = $storeInfo['address_line2'];
		$Form->DefaultValues['sig-city'] = $storeInfo['city'];
		$Form->DefaultValues['sig-state'] = $storeInfo['state_id'];
		$Form->DefaultValues['sig-zipcode'] = $storeInfo['postal_code'];
		$Form->DefaultValues['sig-dd_link'] = $storeInfo['id'];

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-first_last",
				CForm::tooltip => true,
				CForm::placeholder => "First & Last Name",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-title",
				CForm::tooltip => true,
				CForm::placeholder => "Title",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-telephone",
				CForm::tooltip => true,
				CForm::placeholder => "Telephone",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium telephone"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-cellphone",
				CForm::tooltip => true,
				CForm::placeholder => "Cell Phone",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium telephone"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-faxnumber",
				CForm::tooltip => true,
				CForm::placeholder => "Fax Number",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium telephone"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-email",
				CForm::tooltip => true,
				CForm::placeholder => "Email Address",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-addressline1",
				CForm::tooltip => true,
				CForm::placeholder => "Address Line 1",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-addressline2",
				CForm::tooltip => true,
				CForm::placeholder => "Address Line 2",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-city",
				CForm::tooltip => true,
				CForm::placeholder => "City",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-state",
				CForm::tooltip => true,
				CForm::placeholder => "State",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-zipcode",
				CForm::tooltip => true,
				CForm::placeholder => "Zip Code",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
				CForm::name => "sig-dd_link",
				CForm::tooltip => true,
				CForm::placeholder => "Dream Dinners Store ID",
				CForm::maxlength => 80,
				CForm::size => 30,
				CForm::css_class => "medium"));

		$tpl->assign('form', $Form->render());
	}
}
?>