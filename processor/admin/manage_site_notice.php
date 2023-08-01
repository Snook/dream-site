<?php
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CCouponCode.php');
require_once('includes/DAO/BusinessObject/CStore.php');

class processor_admin_manage_site_notice extends CPageProcessor
{

	private $singleStore = false;

	function __construct()
	{
		$this->inputTypeMap['notice_details'] = TYPE_NOCLEAN;
	}

	function runSiteAdmin()
	{
		$this->runManageSiteNotice();
	}

	function runHomeOfficeManager()
	{
		$this->runManageSiteNotice();
	}

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->singleStore = CBrowserSession::getCurrentFadminStore();
		$this->runManageSiteNotice();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->singleStore = CBrowserSession::getCurrentFadminStore();
		$this->runManageSiteNotice();
	}

	function runManageSiteNotice()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (!empty($_POST['op']) && $_POST['op'] == 'get_store_select')
		{
			$tpl = new CTemplate();

			$store_id_array = array();
			if (!empty($_POST['store_id']))
			{
				$store_id_array = explode(',', $_POST['store_id']);
			}

			$tpl->assign('store_id_array', $store_id_array);

			$store_select = $tpl->fetch('admin/subtemplate/manage_site_notice/manage_site_notice_select_stores.tpl.php');

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved store select form.',
				'html' => $store_select
			));
		}

		if (!empty($_POST['op']) && $_POST['op'] == 'get_notice_form')
		{
			$tpl = new CTemplate();

			$notice = DAO_CFactory::create('site_message');
			$notice->id = 'new-' . rand();
			$notice->store_id = (($this->singleStore) ? $this->singleStore : 0);
			$notice->audience = (($this->singleStore) ? 'STORE' : null);
			$notice->home_office_managed = (($this->singleStore) ? 0 : 1);
			$notice->message_start = date('Y-m-d') . ' 00:00:00';
			$notice->message_end = date('Y-m-d') . ' 00:00:00';
			$notice = (array)$notice;

			$tpl->assign('manageSingleStore', $this->singleStore);
			$tpl->assign('notice', $notice);
			$tpl->assign('create_new', true);

			$notice_form = $tpl->fetch('admin/subtemplate/manage_site_notice/manage_site_notice_form.tpl.php');

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved notice form.',
				'html' => $notice_form,
				'notice_id' => $notice['id']
			));
		}

		if (!empty($_POST['op']) && $_POST['op'] == 'delete_site_notice')
		{
			if (!empty($_POST['notice_id']) && is_numeric($_POST['notice_id']))
			{
				$notice = DAO_CFactory::create('site_message');
				$notice->id = $_POST['notice_id'];
				$notice->delete();

				$noticeToStore = DAO_CFactory::create('site_message_to_store');
				$noticeToStore->site_message_id = $_POST['notice_id'];
				$noticeToStore->find();

				while ($noticeToStore->fetch())
				{
					$noticeToStore->delete();
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Notice deleted.'
				));
			}
		}

		if (!empty($_POST['op']) && $_POST['op'] == 'save_site_notice')
		{
			$notice_details = json_decode($_POST['notice_details']);

			$notice = DAO_CFactory::create('site_message');

			if (is_numeric($notice_details->id))
			{
				$notice->id = $notice_details->id;
				$notice->find(true);
				$orgNotice = clone($notice);
			}

			$notice->audience = (($this->singleStore) ? 'STORE' : $notice_details->audience);
			$notice->title = $notice_details->title;
			$notice->message_type = 'SITE_MESSAGE';
			$notice->message = $notice_details->message;
			$notice->alert_css = $notice_details->alert_css;
			$notice->message_start = date('Y-m-d H:i:s', strtotime($notice_details->message_start_date . '' . $notice_details->message_start_time));
			$notice->message_end = date('Y-m-d H:i:s', strtotime($notice_details->message_end_date . '' . $notice_details->message_end_time));
			$notice->home_office_managed = $notice_details->home_office_managed;

			if (strpos($notice_details->id, 'new-') === 0)
			{
				$notice->insert(true);
			}
			else
			{
				$notice->update($orgNotice);
			}

			// not is_store_specific, delete any that exist
			if ($notice->audience != 'STORE')
			{
				$noticeStore = DAO_CFactory::create('site_message_to_store');
				$noticeStore->site_message_id = $notice->id;

				if ($noticeStore->find())
				{
					while ($noticeStore->fetch())
					{
						$noticeStore->delete();
					}
				}
			}
			else
			{
				$storeIds = explode(',', $notice_details->store_id);

				$noticeInfo = CStore::getSiteNotices($notice->id);

				$storeSpecificArray = explode(',', $noticeInfo[$notice->id]['store_id']);

				// add new IDs
				$newStoreIDs = array_diff($storeIds, $storeSpecificArray);

				if (!empty($newStoreIDs))
				{
					foreach ($newStoreIDs AS $store_id)
					{
						$noticeStore = DAO_CFactory::create('site_message_to_store');
						$noticeStore->site_message_id = $notice->id;
						$noticeStore->store_id = $store_id;
						$noticeStore->insert();
					}
				}

				// remove old IDs
				$oldStoreIDs = array_diff($storeSpecificArray, $storeIds);

				if (!empty($oldStoreIDs))
				{
					foreach ($oldStoreIDs AS $store_id)
					{
						$noticeStore = DAO_CFactory::create('site_message_to_store');
						$noticeStore->site_message_id = $notice->id;
						$noticeStore->store_id = $store_id;

						if ($noticeStore->find())
						{
							while ($noticeStore->fetch())
							{
								$noticeStore->delete();
							}
						}
					}
				}
			}

			if (strpos($notice_details->id, 'new-') === 0)
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Notice added.',
					'notice_id' => $notice->id
				));
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Notice updated.',
					'notice_id' => $notice->id
				));
			}
		}
		else
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'Notice details were not supplied'
			));
		}
	}
}

?>