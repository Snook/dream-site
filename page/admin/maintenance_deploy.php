<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_maintenance_deploy extends CPageAdminOnly {

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		$field1 = '-u ryans ';
		$field2 = '-p gnatsum1! ';
		$field3 = '-e assets';
		$output = '';

		if (!empty($_POST['submit']))
		{
			//$output = exec(APP_BASE . "/deploy.sh " . escapeshellarg($field1 . $field2 . $field3));

			$output = exec('sudo -u webdev svn export -q --non-interactive --username ryans --password gnatsum1! --force -r head https://dreamdinners.sourcerepo.com/dreamdinners/main/trunk/src/DreamSite/assets /DreamSite/assets');

		}

		$tpl->assign('output', $output);

		$formArray = $Form->render();
		$tpl->assign('form', $formArray);
	}
}
?>