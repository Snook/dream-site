<?php
/*
 * Created on Sep 29, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/CSession.php';
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Test_recipes.php';
require_once 'includes/DAO/BusinessObject/CTimezones.php';
require_once 'includes/DAO/BusinessObject/CPayment.php';

class page_admin_manage_survey extends CPageAdminOnly
{

	function runHomeOfficeManager() 
{
		$this->manageSurvey();
	}

	function runSiteAdmin()
{
		$this->manageSurvey();
	}

	function manageSurvey()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		if (isset($_POST['add_submit']))
		{
			$add_recipe_obj = DAO_CFactory::create('test_recipes');

			$add_recipe_obj->name = CGPC::do_clean($_POST['name'],TYPE_STR);
			$add_recipe_obj->link = CGPC::do_clean( $_POST['link'],TYPE_STR);

			$add_recipe_obj->insert();

			$tpl->setStatusMsg("Recipe was successfully added.");
		}

		if (isset($_POST['edit_submit']))
		{
			$add_recipe_obj = DAO_CFactory::create('test_recipes');

			$add_recipe_obj->id = CGPC::do_clean($_POST['edit_id'],TYPE_INT);

			$add_recipe_obj->find(true);
			$add_recipe_obj_org = clone($add_recipe_obj);

			$add_recipe_obj->name = CGPC::do_clean($_POST['name'],TYPE_STR);
			$add_recipe_obj->link = CGPC::do_clean($_POST['link'],TYPE_STR);
			$add_recipe_obj->update($add_recipe_obj_org);

			$tpl->setStatusMsg("Recipe was successfully updated.");
		}

		if (isset($_GET['action']) && $_GET['action'] == 'delete')
		{
			$delete_recipe_obj = DAO_CFactory::create('test_recipes');

			$delete_recipe_obj->id = CGPC::do_clean($_GET['recipe_id'],TYPE_INT);
			$delete_recipe_obj->find(true);
			$delete_recipe_obj->delete();

			$tpl->setStatusMsg("Recipe was successfully deleted.");

			CApp::bounce('main.php?page=admin_manage_survey');
		}

		$rows = array();
		$test_recipe_obj = DAO_CFactory::create('test_recipes');

		$test_recipe_obj->query("SELECT id, name, link FROM test_recipes WHERE is_deleted = 0");
		while ($test_recipe_obj->fetch())
		{
			$rows[$test_recipe_obj->id] = array(
				'name' => $test_recipe_obj->name,
				'link' => $test_recipe_obj->link
			);
		}

		$tpl->assign('rows', $rows);

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "name",
			CForm::size => 60,
			CForm::dd_required => true
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "link",
			CForm::size => 60,
			CForm::dd_required => true
		));
		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => "add_submit",
			CForm::css_class => 'button',
			CForm::value => 'Add'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => "edit_submit",
			CForm::css_class => 'button',
			CForm::value => 'Edit'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "edit_id"
		));

		$tpl->assign('form', $Form->Render());
	}
}

?>
