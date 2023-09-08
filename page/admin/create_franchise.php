<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_create_franchise extends CPageAdminOnly
{

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		//
		// Create form elements
		//

		$Form = new CForm();
		$Form->Repost = TRUE;

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "franchise_name",
			CForm::required => true));

		$Form->AddElement(array(CForm::type=> CForm::TextArea,
			CForm::name => "franchise_description",
			CForm::required => true,
			CForm::height => 100,
			CForm::default_value => "",
			CForm::width => 400));

		$Form->AddElement(array(CForm::type=> CForm::Submit,
			CForm::name => "Submit",
			CForm::css_class => 'button',
			CForm::value => "Save"));

		$Form->AddElement(array(CForm::type=> CForm::CheckBox,
			CForm::default_value => true,
			CForm::name => "CheckBox_active"));

		//
		// Check for POST
		//

		if ($Form->Post)
		{
			if (strlen($Form->value('franchise_name')))
			{
				$Franchise = DAO_CFactory::create('franchise');
				$Franchise->franchise_name = $Form->value('franchise_name');

				if ($Franchise->exists())
				{
					$tpl->setErrorMsg('A franchise with that name already exists.');
				}
				else
				{
					$Franchise->active = $Form->value('CheckBox_active');
					$Franchise->franchise_description = $Form->value('franchise_description');
					$Franchise->insert();
					$tpl->setStatusMsg('Franchise Created');
					CApp::bounce('?page=admin_franchise_details&id='.$Franchise->id);
				}
			}
		}

		$tpl->assign('form_create_franchise',$Form->Render());

	}
}
?>