<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");


class page_admin_access_page_override extends CPageAdminOnly {

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();
		$form = new CForm();
		$form->Repost = TRUE;

		$obj = DAO_CFactory::create("access_control_page");
		$obj->find();
		$controlpages = array();

		while ($obj->fetch())
		{
			$controlpages[$obj->id] = $obj->page_name;
		}

		$form->AddElement(array(CForm::type=> CForm::DropDown,
							CForm::onChangeSubmit => false,
							CForm::allowAllOption => false,
							CForm::options => $controlpages,
							CForm::name => 'page_dropdown'));

		if (isset($_REQUEST['user_submit']))
		{
			$userentryid = CGPC::do_clean($_REQUEST['user_id_entry'],TYPE_INT);
			$query = "select user_type from user where id = $userentryid and user.is_deleted = 0";
			$objuser = DAO_CFactory::create("user");
			$objuser->query($query);
			$rslt = $objuser->fetch();

			if ($rslt > 0 && $objuser->user_type != CUser::CUSTOMER )
			{
				$pageid = CGPC::do_clean($_REQUEST['page_dropdown'],TYPE_INT);

				$sql = "SELECT `access_control_page_user`.`access_control_page_id`,`access_control_page_user`.`user_id` FROM `access_control_page_user` " .
				" where access_control_page_user.is_deleted = 0 and `access_control_page_user`.user_id = $userentryid and `access_control_page_user`.`access_control_page_id` = $pageid";

				$objuser = DAO_CFactory::create("user");
				$objuser->query($sql);
				$rslt = $objuser->fetch();
				if ($rslt > 0) {
					$tpl->setErrorMsg('This page access is already assigned');
				}
				else {
					$sql = "insert into access_control_page_user (user_id,access_control_page_id ) values ($userentryid, $pageid)";
					$objuser = DAO_CFactory::create("user");
					$objuser->query($sql);
					$tpl->setStatusMsg('Account has been added.  Please review list for details.');
				}
			}
		}

		$form->AddElement(array (CForm::type => CForm::Text,
		CForm::required => true,
		CForm::number => true,
		CForm::name => 'user_id_entry'));

		$userpages = $this->runControls ();

		if (isset($_REQUEST['remove_access']))
		{
			$updatelist = array();
			foreach($userpages as $key => $element)
			{
				$str = "ch_" . $key;
				if (isset($_REQUEST[$str]) && $_REQUEST[$str] == "on")
				{
					$updatelist[] = $key;
				}
			}

			if (count($updatelist) > 0)
			{
				$explodedlist = implode($updatelist, ",");

				$sql = "update access_control_page_user set access_control_page_user.is_deleted = 1 where access_control_page_user.id in ($explodedlist)";
				$obj = DAO_CFactory::create("access_control_page_user");
				$rslt = $obj->query($sql);

				if ($rslt > 0)
				{
					$userpages = $this->runControls();
				}
			}
		}

		$form->AddElement(array (CForm::type => CForm::Submit,
					CForm::css_class => 'btn btn-primary btn-sm',
					CForm::name => 'user_submit',
					CForm::value => 'Add'));


		$form->AddElement(array (CForm::type => CForm::Submit,
					CForm::css_class => 'btn btn-primary btn-sm',
					CForm::name => 'remove_access',
					CForm::value => 'Remove Access'));

		$tpl->assign('controlpages', $controlpages);
		$tpl->assign('userpages', $userpages);

		$FormArray = $form->render(true);
		$tpl->assign('form_create', $FormArray);
	}

	function runControls ()
	{
		$userpages = array();

		$obj = DAO_CFactory::create("access_control_page_user");
		$obj->query("SELECT
				`access_control_page_user`.`id` as access_id,
				`access_control_page`.`page_name`,
				`user`.`id`,
				`user`.`primary_email`,
				`user`.`firstname`,
				`user`.`lastname`,
				`access_control_page_user`.`is_deleted`
				FROM `access_control_page_user`
				Inner Join `access_control_page` ON `access_control_page_user`.`access_control_page_id` = `access_control_page`.`id`
				Inner Join `user` ON `access_control_page_user`.`user_id` = `user`.`id`
				where access_control_page_user.is_deleted = 0
				and access_control_page.is_deleted = 0
				order by  `user`.`lastname`, `page_name`");

		while ($obj->fetch())
		{
			$userpages[$obj->access_id] = $obj->toArray();
		}

		return $userpages;
	}
}
?>