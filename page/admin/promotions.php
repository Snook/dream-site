<?php // page_admin_premium.php

require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStore.php");
require_once("includes/DAO/BusinessObject/CPromoCode.php");
require_once("includes/DAO/BusinessObject/CMenuItem.php");

class page_admin_promotions extends CPageAdminOnly {

	 function runFranchiseManager() {
		$this->runFranchiseOwner();
	}
	function runOpsLead() {
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff() {
		CApp::bounce('/?page=admin_access_error&topnavname=store&pagename=Promotions');
	}
	function runHomeOfficeManager() {
		$this->runSiteAdmin();
	}
	function runFranchiseStaff() {
		$this->runFranchiseOwner();
	}
	function runFranchiseLead() {
		$this->runFranchiseOwner();
	}

	 function runFranchiseOwner() {
	 	$active_menus_var = true;
		$tpl = CApp::instance()->template();
		$active_menus_var = isset($_REQUEST['show_active_menus']) ? false : true;
		$active_menus = array();
		$promoArr =  $this->_buildActiveTableArray($active_menus_var);
		$tpl->assign('promos', $promoArr);
		$tpl->assign('show_active_menus', $active_menus_var);
	 	$tpl->assign('displayOnly', true);

	 }

	 function runSiteAdmin() {
		$tpl = CApp::instance()->template();
		$active_menus_var = true;
		$Form = new CForm();
		$Form->Repost = false;

		if ( isset($_POST['new_submit']))
		{
			$newPromo = DAO_CFactory::create('promo_code');
			$newPromo->setFrom($_POST);
			$newPromo->promo_type = CPromoCode::FLAT;
			$newPromo->promo_code_active = 1;//isset($_POST['promo_code_active']) && $_POST['promo_code_active'] == 'on' ? 1 : 0;
			$newPromo->promo_code_start = DAO::now();

			$id = $newPromo->insert();

			if ( $id )
				$tpl->setStatusMsg('The promotion has been created');
			else
				$tpl->setErrorMsg('The promotion could not be created');
		}

		$active_menus_var = isset($_REQUEST['show_active_menus']) ? false : true;
		if ( isset($_REQUEST['delete'] ))
		{
			if (!$_REQUEST['delete'])
				$tpl->setErrorMsg("Attempt to delete invalid promotion id");

			$deletePromo = DAO_CFactory::create('Promo_code');
			$deletePromo->id = $_REQUEST['delete'];

			if (!$deletePromo->find(true))
				$tpl->setErrorMsg("Attempt to delete invalid promotion id");
			else {
				$deletePromo->delete();
				$tpl->setStatusMsg('The promotion has been deleted');
			}

			CApp::bounce('/?page=admin_promotions');
		}


		$Form->AddElement(array (CForm::type => CForm::TextArea,
		 CForm::rows => '3',
		 CForm::cols => '40',
		 CForm::name => 'promo_description'));

		$Form->AddElement(array (CForm::type => CForm::Text,
			CForm::required => true,
		 	CForm::name => 'promo_title'));

		$Form->AddElement(array (CForm::type => CForm::Text,
			CForm::required => true,
		 	CForm::name => 'promo_code'));

		/*
		$promoTypeList = array(CPromoCode::ITEM => CPromoCode::ITEM, CPromoCode::PERCENT => CPromoCode::PERCENT, CPromoCode::FLAT => CPromoCode::FLAT);

		$Form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::name => "promo_type",
								CForm::required => false,
								CForm::options => $promoTypeList));
		*/

		$MenuItems = DAO_CFactory::create('menu_item');

		/*
		$MenuItems->selectAdd();
		$MenuItems->selectAdd("id, menu_item_name, pricing_type");
		$MenuItems->orderBy('menu_item_name ASC');
		*/

		// New query for new pricing plan

		$lastmonth = mktime(0, 0, 0, date("m")-1, 15,  date("Y"));
		$prevMonth = date('Y-m-d', $lastmonth);

		$MenuItems->query("Select menu_item.id, menu.menu_name as menuName, menu_item.menu_item_name, menu_item.pricing_type " .
		" From menu_item " .
		" inner join menu_to_menu_item on menu_item.id = menu_to_menu_item.menu_item_id and menu_to_menu_item.is_deleted = 0 and menu_to_menu_item.store_id is null " .
		" inner join menu on menu_to_menu_item.menu_id = menu.id and menu.is_deleted = 0 " .
		" where menu.menu_start > '$prevMonth' and menu_item.is_deleted = 0 and menu_item.pricing_type != 'INTRO' and menu_item.is_optional = 0 and menu_item.menu_program_id = 1 " .
		" order by menu_item.menu_item_name ");

		$MenuItemArray = array();
		while($MenuItems->fetch())
		{
			$pricingType = '6 Servings';
			if ($MenuItems->pricing_type == CMenuItem::HALF)
				$pricingType = '3 Servings';

			$MenuItemArray[$MenuItems->id] = $MenuItems->menuName . " | " . $pricingType . " | " . $MenuItems->menu_item_name;
		}

	//	dbg();
	//	$Form->DefaultValues['promo_menu_item_id'] = $MenuItemArray[$CurrentPromo->promo_menu_item_id];

		$Form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::name => "promo_menu_item_id",
								CForm::required => false,
								CForm::onChangeSubmit => false,
								CForm::options => $MenuItemArray));

		$Form->AddElement(array (CForm::type => CForm::Label,
		 	CForm::name => 'promo_type', CForm::value => 'FLAT'));


//		$Form->AddElement(array (CForm::type => CForm::Text,
//			CForm::required => true,
//		 	CForm::name => 'promo_var'));
//
//		$Form->AddElement(array (CForm::type => CForm::CheckBox,
//			CForm::required => true,
//		 	CForm::name => 'promo_code_active'));
//
		 $Form->AddElement(array (CForm::type => CForm::TextArea,
		 CForm::rows => '5',
		 CForm::cols => '40',
		 CForm::name => 'note'));

		$Form->AddElement(array (CForm::type => CForm::Submit,
	 		CForm::name => 'new_submit', CForm::value => 'Create Promotion'));

//		$Form->AddElement(array (CForm::type => CForm::Submit,
//	 		CForm::name => 'delete', CForm::value => 'Delete'));
		$active_menus = array();
		$promoArray =  $this->_buildActiveTableArray($active_menus_var);
		$tpl->assign('show_active_menus', $active_menus_var);
		$tpl->assign('promos',$promoArray);
		$tpl->assign('active_menus',$active_menus);
		$tpl->assign('form_promos', $Form->render());
	 }


	private function _buildActiveTableArray($show_active_menu, $num_menus_to_capture = 2) {

	 	$Promos = DAO_CFactory::create('promo_code');
	 	$sql = 'select promo_code.*, menu_item.menu_item_name, menu_to_menu_item.menu_id, menu_item.pricing_type from promo_code LEFT JOIN menu_item ON promo_code.promo_menu_item_id =
		menu_item.id INNER JOIN menu_to_menu_item ON promo_code.promo_menu_item_id = menu_to_menu_item.menu_item_id and menu_to_menu_item.store_id is null WHERE promo_code.promo_code_active = 1 and
		menu_to_menu_item.is_deleted = 0 and
		menu_item.is_deleted = 0 and promo_code.is_deleted = 0 and (promo_code.promo_code_start <= now() or promo_code.promo_code_start IS NULL) AND
		(promo_code.promo_code_expiration > now() or promo_code.promo_code_expiration IS NULL) order by menu_id DESC, menu_item_name, menu_item.pricing_type';
		$Promos->query($sql);
		$PromosArray = array();
		$iter = 0;
	 	while($Promos->fetch())
	 	{
			$PromosArray[$Promos->menu_id][$Promos->id] = $Promos->toArray();
		}

		$finalArray = array();
		$len = count($PromosArray);
		if ($num_menus_to_capture > $len) $num_menus_to_capture = $len;
		if ($show_active_menu == false)
		  $PromosArray = array_splice($PromosArray, $num_menus_to_capture);
		else {
		   $PromosArray = array_splice($PromosArray, 0,$num_menus_to_capture);
			krsort($PromosArray);
		}

		return $PromosArray;
	}
}

?>