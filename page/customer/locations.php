<?php
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CMenu.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');

class page_locations extends CPage
{

	/**
	 * @throws exception
	 */
	function runPublic()
	{
		parent::runPublic();

		$CartObj = CCart2::instance();

		$req_post_zip = CGPC::do_clean((!empty($_POST['zip']) ? $_POST['zip'] : false), TYPE_POSTAL_CODE, true);
		$req_get_zip = CGPC::do_clean((!empty($_GET['zip']) ? $_GET['zip'] : false), TYPE_POSTAL_CODE, true);
		$req_get_state = CGPC::do_clean((!empty($_GET['state']) ? $_GET['state'] : false), TYPE_STR, true);
		$req_select_store = CGPC::do_clean((!empty($_REQUEST['select_store']) ? $_REQUEST['select_store'] : false), TYPE_INT);
		$req_select_menu = CGPC::do_clean((!empty($_REQUEST['select_menu']) ? $_REQUEST['select_menu'] : false), TYPE_INT);
		$req_select_dist_ctr = CGPC::do_clean((!empty($_REQUEST['select_dc']) ? $_REQUEST['select_dc'] : false), TYPE_INT);

		if ((isset($req_select_store) && is_numeric($req_select_store) && $req_select_store > 0) &&
			(isset($req_select_menu) && is_numeric($req_select_menu) && $req_select_menu > 0) )
		{
			$CartObj->storeChangeEvent($req_select_store);
			$CartObj->addMenuId($req_select_menu);
			$CartObj->addNavigationType(CSession::ALL_STANDARD);
			CApp::bounce('/session-menu');
		}

		if (isset($req_select_store) && is_numeric($req_select_store) && $req_select_store > 0)
		{
			/// ------------------------------------------------store was selected
			$CartObj->storeChangeEvent($req_select_store);

			$CartObj->addMenuId(CMenu::getCurrentMenuId());
			$CartObj->addNavigationType(CSession::ALL_STANDARD);

			if (CBrowserSession::getValue('dd_start_intro'))
			{
				CBrowserSession::setValue('dd_start_intro');
				CApp::bounce('/session-menu');
			}
			else
			{
				CApp::bounce('/session-menu');
			}
		}

		if (isset($req_select_dist_ctr) && is_numeric($req_select_dist_ctr))
		{
			/// ------------------------------------------------store was selected
			$CartObj->storeChangeEvent($req_select_dist_ctr);
			$CartObj->addNavigationType(CTemplate::DELIVERED);
			CApp::bounce('/box-select');
		}

		// if zip is in _POST redirect to _GET method
		if ($req_post_zip)
		{
			CApp::bounce('/locations?zip=' . $req_post_zip . '#zipsearch_zipcode');
		}

		$tpl = CApp::instance()->template();

		$Form = new CForm;
		$Form->Repost = false;

		$tpl->assign('zip_code', $req_get_zip);
		$Form->DefaultValues['zipsearch_zipcode_only'] = $req_get_zip;

		if (!empty($req_get_state) && CStatesAndProvinces::IsValid($req_get_state))
		{
			$tpl->assign('state', CStatesAndProvinces::GetName($req_get_state));
		}

		$tpl->setScript('foot', SCRIPT_PATH . '/customer/vendor/simplemaps/usmap.js');
		$tpl->setScriptVar('var simplemaps_usmap_mapdata = ' . json_encode(CStore::getSimpleMapsStoreArray()) . ';');

		$tpl->assign('cart_info', CUser::getCartIfExists());
		$tpl->assign('sticky_nav_bottom_disable', true);

		$Form->AddElement(array(
			CForm::type => CForm::Search,
			CForm::name => "zipsearch_zipcode_only",
			CForm::placeholder => "*Postal Code",
			CForm::maxlength => 5,
			CForm::gpc_type => TYPE_POSTAL_CODE,
			CForm::xss_filter => true,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Search,
			CForm::name => "zipsearch_zipcode",
			CForm::placeholder => "*Postal Code",
			CForm::maxlength => 5,
			CForm::gpc_type => TYPE_POSTAL_CODE,
			CForm::xss_filter => true,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Search,
			CForm::name => "zipsearch_address",
			CForm::required => false,
			CForm::tooltip => true,
			CForm::placeholder => "Street Address",
			CForm::required_msg => "Enter a street address for more accuracy",
			CForm::maxlength => 255,
			CForm::xss_filter => true,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Search,
			CForm::name => "zipsearch_city",
			CForm::required => true,
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city",
			CForm::maxlength => 255,
			CForm::xss_filter => true,
			CForm::css_class => "form-control"
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'zipsearch_state_id',
			CForm::required_msg => "Please select a state",
			CForm::required => true,
			CForm::css_class => "select-state form-control custom-select"
		));

		$tpl->assign('locations_form', $Form->Render());
	}
}