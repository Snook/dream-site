<?php
require_once("includes/CPageAdminOnly.inc");
include_once("includes/CForm.inc");

class page_admin_list_franchise extends CPageAdminOnly
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

		//check out these query params:
		//
		//letter_select
		//store
		//q (string or id)
		//
		//send these guys to the form again
		$q = array_key_exists('q',$_GET)? CGPC::do_clean($_GET['q'],TYPE_STR) : null;
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
		$whereClause = '' ;

		if ( $q || $letter_select )
		{
			$Franchise = DAO_CFactory::create('franchise');
			$User = DAO_CFactory::create('user');
			$Franchise->joinAdd($User);

			//filter by a letter
			if ( $letter_select && ($letter_select != 'all') )
			{
				if ( $letter_select == 'etc' )
				{
					$Franchise->whereAddFirstCharLike('franchise_name',"'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'", 'AND', true);
				}
				else
				{
					$Franchise->whereAddFirstCharLike('franchise_name',"'$letter_select'");
				}
			}

			//filter by a query string
			if ( $q )
			{
				$whereClause .= " ( franchise.franchise_name LIKE '%".$q."%' ) ";
				$Franchise->whereAdd($whereClause, 'AND');
			}

			$fieldlist = 'franchise.id as "franchise_id", franchise.franchise_name, franchise.active, timestamp_created, timestamp_updated';

			$Franchise->selectAdd($fieldlist);
			$Franchise->orderBy('franchise.active DESC, franchise.franchise_name ASC');
			//we could get more than one address record
			$rowcount = $Franchise->find();

			$rows = array();

			while ($Franchise->fetch())
			{
				$Info = $Franchise->toArray();
				array_splice($Info, 3, 1);
				array_splice($Info, 5, 9);
				$rows []= $Info;
			}

			$labels = $Franchise->getFieldLabels($fieldlist);

			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows );
			$tpl->assign('rowcount', count($rows));
		}

		$formArray = $Form->render();

		$tpl->assign('page_title','List Franchise');
		$tpl->assign('form_list_franchise', $formArray);
	}
}
?>