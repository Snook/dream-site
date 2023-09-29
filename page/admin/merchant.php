<?php
require_once("includes/CPageAdminOnly.inc");
require_once('CCrypto.inc');

class page_admin_merchant extends CPageAdminOnly
{

	function runSiteAdmin($id = null)
	{
		$tpl = CApp::instance()->template();

		// Lock it down to 2 key accounts
		$AdminUser = CUser::getCurrentUser();
		if ($AdminUser->id != 212112) // josh thayer access only
		{
			CApp::bounce('/?page=admin_access_error&topnavname=stores&pagename=Edit Merchant Info');
		}

		if (array_key_exists('store', $_REQUEST) && $_REQUEST['store'])
		{
			$store_id = CGPC::do_clean( $_REQUEST['store'],TYPE_INT);
		}

		if (!$store_id)
		{
			CApp::bounce('/?page=admin_list_stores');
		}

		if ($store_id)
		{

			$store = DAO_CFactory::create('store');
			$store->id = $store_id;
			$store->find(true);

			$tpl->assign('store', $store->toArray());

			$Form = new CForm();
			$Form->Repost = true;
			$Form->Bootstrap = true;

			$MerchantInfo = DAO_CFactory::create('merchant_accounts');
			$MerchantInfo->store_id = $store_id;
			$MerchantInfo->franchise_id = $store->franchise_id;
			$found = $MerchantInfo->find(true);
			CLog::Record($found . " " . $store_id);
			if ($found > 1)
			{
				throw new Exception('invalid merchant account');
			}

			if ($found)
			{
				$Form->DefaultValues['partner_id'] = $MerchantInfo->partner_id;
				$Form->DefaultValues['ma_username'] = $MerchantInfo->ma_username;
				$Form->DefaultValues['ma_password'] = CCrypto::decode($MerchantInfo->ma_password);
				$Form->DefaultValues['ma_login_account'] = $MerchantInfo->ma_login_account;

			}

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::required => true,
				CForm::name => 'partner_id'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::required => true,
				CForm::name => 'ma_username'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'ma_login_account'
			));


			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::required => true,
				CForm::name => 'ma_password'
			));

			if ($_POST)
			{

				if ($MerchantInfo->id)
				{

					// Change per CES 12/30/14
					// Now always mark as deleted the existing row and add a new one. This way each payment can be tracked to a specific account

					if ($MerchantInfo->ma_username == $Form->value('ma_username'))
					{// but only update if partner id or username (account) changes

						$originalInfo = clone($MerchantInfo);

						$incomingLoginAccount = $Form->value('ma_login_account');
						if (empty($incomingLoginAccount))
						{
							$incomingLoginAccount = 'null';
						}

						$MerchantInfo->store_id = $store_id;
						$MerchantInfo->ma_password = CCrypto::encode($Form->value('ma_password'));
						$MerchantInfo->ma_login_account = $incomingLoginAccount;

						$MerchantInfo->update($originalInfo);
						$tpl->setStatusMsg('merchant info updated - password, login account or partner id only');

						CLog::RecordNew(CLog::SECURITY, "Merchant Account Password or Partner updated. Store = " . $store_id, "", "", true);
					}
					else
					{
						// delete any with this store id  - obj->delete() failed in some cases where the franchise id had changed
						$MerchantInfo->query("update merchant_accounts set is_deleted = 1 where store_id = $store_id");


						$incomingLoginAccount = $Form->value('ma_login_account');
						if (empty($incomingLoginAccount))
						{
							$incomingLoginAccount = 'null';
						}


						$NewMerchantInfo = DAO_CFactory::create('merchant_accounts');
						$NewMerchantInfo->store_id = $store_id;
						$NewMerchantInfo->franchise_id = $store->franchise_id;
						$NewMerchantInfo->partner_id = $Form->value('partner_id');
						$NewMerchantInfo->ma_username = $Form->value('ma_username');
						$NewMerchantInfo->ma_password = CCrypto::encode($Form->value('ma_password'));
						$NewMerchantInfo->ma_login_account = $incomingLoginAccount;

						$NewMerchantInfo->insert();
						$tpl->setStatusMsg('merchant info updated - including user name (account id)');

						CLog::RecordNew(CLog::SECURITY, "Merchant Account User Name updated. Store = " . $store_id, "", "", true);
					}
				}
				else
				{
					$MerchantInfo->partner_id = $Form->value('partner_id');
					$MerchantInfo->ma_username = $Form->value('ma_username');
					$MerchantInfo->ma_password = CCrypto::encode($Form->value('ma_password'));
					$incomingLoginAccount = $Form->value('ma_login_account');
					if (empty($incomingLoginAccount))
					{
						$incomingLoginAccount = 'null';
					}
					$MerchantInfo->ma_login_account = $incomingLoginAccount;

					$MerchantInfo->franchise_id = $store->franchise_id;

					$inserted = $MerchantInfo->insert();
					if (!$inserted)
					{
						$tpl->setErrorMsg('A merchant account could not be created.');
						CLog::RecordNew(CLog::SECURITY, "Merchant Account Creation attempt failed. Store = " . $store_id, "", "", true);
					}
					else
					{
						$tpl->setStatusMsg('new merchant account created');
						CLog::RecordNew(CLog::SECURITY, "Merchant Account Created. Store = " . $store_id, "", "", true);
					}
				}

				//reload the page
				CApp::bounce($_POST['back']);
			}
			else
			{
				CLog::RecordNew(CLog::SECURITY, "Merchant Account info accessed. Store = " . $store_id, "", "", true);
			}

			if ($found)
			{
				$tpl->assign('updated_by', $MerchantInfo->updated_by);
				$tpl->assign('timestamp_updated', $MerchantInfo->timestamp_updated);
			}
			else
			{
				$tpl->assign('updated_by', 'null');
				$tpl->assign('timestamp_updated', 'null');
			}

			$tpl->assign('form_merchant', $Form->render());
		}

		if (!empty($_SERVER['HTTP_REFERER']))
		{
			$tpl->assign('back', $_SERVER['HTTP_REFERER']);
		}
	}
}

?>