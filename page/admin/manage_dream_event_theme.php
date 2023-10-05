<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('page/admin/manage_bundle.php');

class page_admin_manage_dream_event_theme extends CPageAdminOnly
{
	function runHomeOfficeManager()
	{
		$this->manageDreamEventTheme();
	}

	function runSiteAdmin()
	{
		$this->manageDreamEventTheme();
	}

	function manageDreamEventTheme()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		$tpl->assign('editBundleTheme', false);
		$tpl->assign('createBundleTheme', false);

		$bundleTheme = DAO_CFactory::create('dream_taste_event_theme');
		$bundleTheme->query("SELECT * FROM dream_taste_event_theme ORDER BY id DESC");

		$bundleThemeArray = array();
		while($bundleTheme->fetch())
		{
			$bundleThemeArray[$bundleTheme->id] = clone($bundleTheme);
		}

		if (isset($_GET['create']))
		{
			if (!empty($_POST['submit']) && $_POST['submit'] == 'create' && !empty($_POST['theme_string']))
			{
				$p_title = CGPC::do_clean((!empty($_POST['title']) ? $_POST['title'] : false), TYPE_NOHTML, true);
				$p_title_public = CGPC::do_clean((!empty($_POST['title_public']) ? $_POST['title_public'] : false), TYPE_NOHTML, true);
				$p_fadmin_acronym = CGPC::do_clean((!empty($_POST['fadmin_acronym']) ? $_POST['fadmin_acronym'] : false), TYPE_NOHTML, true);
				$p_sub_theme = CGPC::do_clean((!empty($_POST['sub_theme']) ? $_POST['sub_theme'] : false), TYPE_NOHTML, true);
				$p_sub_sub_theme = CGPC::do_clean((!empty($_POST['sub_sub_theme']) ? $_POST['sub_sub_theme'] : false), TYPE_NOHTML, true);
				$p_theme_string = CGPC::do_clean((!empty($_POST['theme_string']) ? $_POST['theme_string'] : false), TYPE_NOHTML, true);
				$p_session_type = CGPC::do_clean((!empty($_POST['session_type']) ? $_POST['session_type'] : false), TYPE_NOHTML, true);

				$findDuplicate = DAO_CFactory::create('dream_taste_event_theme');
				$findDuplicate->theme_string = $p_theme_string;

				if ($findDuplicate->find(true))
				{
					$tpl->setErrorMsg('Theme path duplicate, must be unique.');
				}
				else
				{
					$createBundle = DAO_CFactory::create('dream_taste_event_theme');
					$createBundle->title = $p_title;
					$createBundle->title_public = $p_title_public;
					$createBundle->fadmin_acronym = $p_fadmin_acronym;
					$createBundle->session_type = $p_session_type;
					$createBundle->sub_theme = $p_sub_theme;
					$createBundle->sub_sub_theme = $p_sub_sub_theme;
					$createBundle->theme_string = $p_theme_string;
					$createBundle->insert();

					$tpl->setStatusMsg('Theme created.');
					CApp::bounce('/backoffice/manage_dream_event_theme');
				}
			}

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'submit',
				CForm::css_class => 'button',
				CForm::value => 'create',
				CForm::text => 'Create Theme'
			));

			$tpl->assign('createBundleTheme', true);
		}

		if (!empty($_GET['edit']) && is_numeric($_GET['edit']))
		{
			$editTheme = DAO_CFactory::create('dream_taste_event_theme');
			$editTheme->id = $_GET['edit'];
			if (!$editTheme->find(true))
			{
				$tpl->setStatusMsg('Theme not found.');
				CApp::bounce('/backoffice/manage_dream_event_theme');
			}

			if (!empty($_POST['submit']) && $_POST['submit'] == 'update')
			{
				$p_title = CGPC::do_clean((!empty($_POST['title']) ? $_POST['title'] : false), TYPE_NOHTML, true);
				$p_title_public = CGPC::do_clean((!empty($_POST['title_public']) ? $_POST['title_public'] : false), TYPE_NOHTML, true);
				$p_fadmin_acronym = CGPC::do_clean((!empty($_POST['fadmin_acronym']) ? $_POST['fadmin_acronym'] : false), TYPE_NOHTML, true);
				$p_sub_theme = CGPC::do_clean((!empty($_POST['sub_theme']) ? $_POST['sub_theme'] : false), TYPE_NOHTML, true);
				$p_sub_sub_theme = CGPC::do_clean((!empty($_POST['sub_sub_theme']) ? $_POST['sub_sub_theme'] : false), TYPE_NOHTML, true);
				$p_theme_string = CGPC::do_clean((!empty($_POST['theme_string']) ? $_POST['theme_string'] : false), TYPE_NOHTML, true);
				$p_session_type = CGPC::do_clean((!empty($_POST['session_type']) ? $_POST['session_type'] : false), TYPE_NOHTML, true);

				$editTheme->title = $p_title;
				$editTheme->title_public = $p_title_public;
				$editTheme->fadmin_acronym = $p_fadmin_acronym;
				$editTheme->sub_theme = $p_sub_theme;
				$editTheme->sub_sub_theme = $p_sub_sub_theme;
				$editTheme->theme_string = $p_theme_string;
				$editTheme->session_type = $p_session_type;
				$editTheme->update();

				$tpl->setStatusMsg('Theme updated');
			}

			$tpl->assign('editBundleTheme', $editTheme);

			$Form->DefaultValues['title'] = $editTheme->title;
			$Form->DefaultValues['title_public'] = $editTheme->title_public;
			$Form->DefaultValues['fadmin_acronym'] = $editTheme->fadmin_acronym;
			$Form->DefaultValues['sub_theme'] = $editTheme->sub_theme;
			$Form->DefaultValues['sub_sub_theme'] = $editTheme->sub_sub_theme;
			$Form->DefaultValues['theme_string'] = $editTheme->theme_string;
			$Form->DefaultValues['session_type'] = $editTheme->session_type;

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'submit',
				CForm::css_class => 'button',
				CForm::value => 'update',
				CForm::text => 'Update Bundle'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'delete',
				CForm::css_class => 'button',
				CForm::value => 'delete',
				CForm::text => 'Delete Bundle'
			));
		}

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'title',
			CForm::dd_required => true,
			CForm::required => true
		));

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'title_public',
			CForm::dd_required => true,
			CForm::required => true
		));

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'fadmin_acronym',
			CForm::dd_required => true,
			CForm::required => true
		));

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'theme_string',
			CForm::style => 'width: 600px;',
			CForm::dd_required => true,
			CForm::required => true
		));

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'sub_theme',
			CForm::dd_required => true,
			CForm::required => true
		));

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'sub_sub_theme',
			CForm::dd_required => true,
			CForm::required => true
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'session_type',
			CForm::dd_required => true,
			CForm::options => array(
				'' => 'Select Type',
				CSession::DREAM_TASTE => CSession::DREAM_TASTE,
				CSession::FUNDRAISER => CSession::FUNDRAISER
			)
		));

		$tpl->assign('bundleThemeArray', $bundleThemeArray);
		$tpl->assign('form', $Form->render());
	}
}
?>