<?php // menus.php
header("status: 500 Internal Server Error");

class page_cart_watcher extends CPage {

	function runSiteAdmin()
	{
		$this->runCartWatcher();
	}

	function runPublic()
	{
		//if you're not logged in and not on the live server you can also run the cartWatcher.
		//This is so you can watch your cart while not logged in

		if (!defined('DD_SERVER_NAME') || DD_SERVER_NAME != 'LIVE')
		{
			$this->runCartWatcher();
		}
	}

	/**
	 * @throws exception
	 */
	function runCartWatcher() {

	    $tpl = CApp::instance()->template();

	    $Form = new CForm();
	    $Form->Repost = true;

	    $Form->DefaultValues['attach_method'] = 'MINE';


	    $Form->AddElement(array(
	        CForm::type => CForm::RadioButton,
	        CForm::name => "attach_method",
	        CForm::css_class => 'custom-control-input',
	        CForm::value => 'MINE',
	        CForm::label => 'Mine',
	        CForm::label_css_class => 'custom-control-label'
	    ));

	    $Form->AddElement(array(
	        CForm::type => CForm::RadioButton,
	        CForm::name => "attach_method",
	        CForm::css_class => 'custom-control-input',
	        CForm::value => 'CART_ID',
	        CForm::label => 'Cart ID',
	        CForm::label_css_class => 'custom-control-label'
	    ));

	    $Form->AddElement(array(
	        CForm::type => CForm::RadioButton,
	        CForm::name => "attach_method",
	        CForm::css_class => 'custom-control-input',
	        CForm::value => 'USER_ID',
	        CForm::label => 'User ID',
	        CForm::label_css_class => 'custom-control-label'
	    ));

	    $Form->AddElement(array(
	        CForm::type => CForm::Text,
	        CForm::name => "user_id",
	        CForm::required => false,
	        CForm::placeholder => "*USER ID",
	        CForm::required_msg => "Please enter a user id.",
	        CForm::maxlength => 10,
	        CForm::size => 10,
	        CForm::xss_filter => true,
	        CForm::css_class => "form-control"
	    ));

	    $Form->AddElement(array(
	        CForm::type => CForm::Text,
	        CForm::name => "cart_id",
	        CForm::required => false,
	        CForm::placeholder => "*CART ID",
	        CForm::required_msg => "Please enter a cart id.",
	        CForm::maxlength => 40,
	        CForm::size => 40,
	        CForm::xss_filter => true,
	        CForm::css_class => "form-control"
	    ));


	    $tpl->assign('form', $Form->Render());
	}
}