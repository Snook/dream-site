<?php
require_once("includes/CPageAdminOnly.inc");
include_once("includes/CForm.inc");

class page_admin_list_stores extends CPageAdminOnly
{

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		//we actually want to always use a GET instead of a post for reports
		//not sure if we'll use much of the CForm functionality
		$Form = new CForm();
		$Form->Repost = FALSE;

		$Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::allowAllOption => false,
				CForm::setDefault => false,
				CForm::showInactiveStores => true) );

		//check out these query params:
		//
		//letter_select
		//store
		//q (string or id)
		//
		//send these guys to the form again
		$q = array_key_exists('q', $_GET)? CGPC::do_clean($_GET['q'],TYPE_STR) : null;
		$letter_select = array_key_exists('letter_select', $_GET)? CGPC::do_clean($_GET['letter_select'],TYPE_STR) : null;

		//no letter sorting if searching
		if ($q)
		{
			$letter_select = null;
		}

		$tpl->assign('q', $q );
		$tpl->assign('letter_select', $letter_select );

		$tpl->assign('labels', null);
		$tpl->assign('rows', null );
		$tpl->assign('rowcount', null);

		//build store drop down

		if ( $q || $letter_select )
		{
			$Store = DAO_CFactory::create('store');
			$Franchise = DAO_CFactory::create('franchise');
			$Store->joinAdd($Franchise);

			//filter by a letter
			if ( $letter_select && ($letter_select != 'all') )
			{
				if ( $letter_select == 'etc' )
				{
					$Store->whereAddFirstCharLike('store_name',"'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'", 'AND', true);
				}
				else
				{
					$Store->whereAddFirstCharLike('store_name',"'$letter_select'");
				}
			}

			//filter by a query string
			if ( $q )
			{
				$whereClause = "( store.store_name LIKE '%".$q."%' OR store.store_description LIKE '%".$q."%' ) ";
				$Store->whereAdd($whereClause, 'AND');
			}

			$Store->selectAdd();

			$fieldlist = 'store.id as "id", store.home_office_id, store.store_name, store.city, store.state_id, store.active, franchise.franchise_name, franchise.id as "franchise_id" ';

			$Store->selectAdd($fieldlist);
			$Store->orderBy('store.active DESC, store.state_id ASC, store.city ASC, store.store_name ASC');
			//we could get more than one address record
			$rowcount = $Store->find();

			$rows = array();

			while ($Store->fetch())
			{
				$rows [$Store->id]= $Store->toArray();
				$rows [$Store->id]['telephone_day'] = $tpl->telephoneFormat($rows [$Store->id]['telephone_day']);
			}

			$labels = $Store->getFieldLabels($fieldlist);

			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows );
			$tpl->assign('rowcount', count($rows));
		}

		$formArray = $Form->render();

		$tpl->assign('form_list_stores', $formArray);
	}
}
?>