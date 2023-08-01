<?php
require_once("includes/CPageAdminOnly.inc");
include_once("includes/CForm.inc");

class page_admin_list_franchisees extends CPageAdminOnly
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

		//TODO: ACCESS_CHANGE
		//build store drop down

		if ( $q || $letter_select )
		{
			$fieldlist = 'user.id as "user_id", user.lastname, user.firstname, user.primary_email, user.telephone_1, franchise.franchise_name, franchise.active, franchise.id as "franchise_id" ';

			$Owner = DAO_CFactory::create('user_to_store');
			$select = 'select ' .$fieldlist . 'FROM user_to_store ';
			$joins = "INNER JOIN user ON user.id=user_to_store.user_id INNER JOIN store ON store.id = user_to_store.store_id INNER JOIN franchise ON franchise.id=store.franchise_id ";
			$whereClause = "where user.is_deleted = 0 AND franchise.is_deleted = 0 AND user_to_store.is_deleted = 0 ";
			$groupBy = "GROUP BY user_id ";
			$orderBy = "ORDER BY user.lastname ";
			//filter by a letter

			if ( $letter_select && ($letter_select != 'all') )
			{
				if ( $letter_select == 'etc' )
				{
					$whereClause .= "AND (LEFT(lastname, 1)) NOT BETWEEN 'A' AND 'Z' AND (LEFT(lastname, 1)) NOT BETWEEN 'a' AND 'z' ";
				}
				else
				{
					$whereClause .= "AND (LEFT(lastname, 1) LIKE '$letter_select%') ";
				}
			}

			//filter by a query string
			if ( $q )
			{
				$whereClause .= "AND( user.firstname LIKE '%".$q."%' OR user.lastname LIKE '%".$q."%' OR "."user.primary_email LIKE '%".$q."%' ";
				$whereClause .= " OR "."franchise.franchise_name LIKE '%".$q."%' OR "."franchise.franchise_description LIKE '%".$q."%' ) ";
			}

			$Owner->query($select . $joins . $whereClause . $groupBy . $orderBy);

			$rows = array();

			while ($Owner->fetch())
			{
				$OwnerInfo = $Owner->toArray();
				$OwnerInfo['telephone_1'] = $tpl->telephoneFormat($Owner->telephone_1);
				$OwnerInfo['franchise_name'] = str_replace('Franchise ','',$OwnerInfo['franchise_name']);
				$rows []= $OwnerInfo;
			}

			$labels = $Owner->getFieldLabels($fieldlist);

			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows );
			$tpl->assign('rowcount', count($rows));
		}

		$formArray = $Form->render();

		$tpl->assign('page_title','List Franchisees');
		$tpl->assign('form_list_franchisees', $formArray);
	}

}
?>