<?php // page_admin_create_store.php

/**
 * @author ToddW
 */
require_once("includes/CPageAdminOnly.inc");

class page_admin_create_store extends CPageAdminOnly {


	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = TRUE;

		$Form->DefaultValues['close_session_hours'] = 24;
		$Form->DefaultValues['observes_DST'] = 1;

		$startTS = time();
		if (isset($_REQUEST["selectedCell"]) && $_REQUEST["selectedCell"])
		{
			$startTS = strtotime(CGPC::do_clean($_REQUEST["selectedCell"],TYPE_STR));
		}

		if ( isset($_GET['franchise_id']) )
		{
			$Form->DefaultValues['franchise_id'] = CGPC::do_clean($_GET['franchise_id'], TYPE_INT);
		}

		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "store_name",
								CForm::required => true,
								CForm::length => 80));
		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "address_line1",
								CForm::required => true,
								CForm::length => 80));
		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "address_line2",
								CForm::required => false,
								CForm::length => 80));
		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "city",
								CForm::required => true,
								CForm::length => 80));
		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "county",
								CForm::length => 80));
		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "postal_code",
								CForm::required => true,
								CForm::length => 10));
		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "store_name",
								CForm::required => true,
								CForm::length => 80));

		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "home_office_id",
								CForm::required => true,
								CForm::length => 80));

		$Form->AddElement(array(CForm::type=> CForm::TextArea,
								CForm::name => "store_description",
								CForm::required => true,
								CForm::height => 100,
								CForm::width => 400,
								CForm::onKeyUp => "updatePreview(this);"));
		$Form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::default_value => 1,
								CForm::name => "active",
								CForm::options => array( 0 => 'No', 1 => 'Yes' )));
		$Form->AddElement(array(CForm::type=> CForm::Tel,
							    CForm::name => 'telephone_day',
							    CForm::required => true,
							    CForm::length => 18));
		$Form->AddElement(array(CForm::type=> CForm::Tel,
							    CForm::name => 'telephone_evening',
							    CForm::required => true,
							    CForm::length => 18));
		$Form->AddElement(array(CForm::type=> CForm::Tel,
								CForm::name => 'telephone_sms',
								CForm::required => false,
								CForm::length => 18));
		$Form->AddElement(array(CForm::type=> CForm::Tel,
							    CForm::name => 'fax',
							    CForm::length => 18));
		$Form->AddElement(array(CForm::type=> CForm::StatesProvinceDropDown,
							    CForm::name => 'state_id',
							    CForm::required => true));
		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "email_address",
								CForm::required => true,
								CForm::email => true,
								CForm::length => 50));
		$Form->AddElement(array(CForm::type=> CForm::Text,
							    CForm::name => 'usps_adc',
							    CForm::length => 18));
		$Form->AddElement(array(CForm::type=> CForm::TimezoneDropDown,
							    CForm::name => 'timezone_id'));
		$Form->AddElement(array(CForm::type=> CForm::Text,
							    CForm::name => 'close_session_hours',
							    CForm::length => 4,
							    CForm::number => true));
		$Form->AddElement(array(CForm::type=> CForm::CheckBox,
							    CForm::name => 'observes_DST'));

		$Form->AddElement(array(CForm::type=> CForm::FranchiseDropDown,
							    CForm::name => 'franchise_id',
							    CForm::required => true));

		$Form->AddElement(array(CForm::type=> CForm::Submit,
								CForm::name => "createStore",
								CForm::css_class => 'button',
								CForm::value => "Save"));

		$Form->AddElement(array(CForm::type=> CForm::Text,
							    CForm::name => 'food_tax',
							    CForm::number => true,
							    CForm::required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "total_tax",
							    CForm::number => true,
							    CForm::required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "other1_tax",
							    CForm::number => true,
							    CForm::required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "other2_tax",
							    CForm::number => true,
							    CForm::required => false));

		$tpl->assign('initDate', date("m/d/Y", $startTS));

		if ( $Form->value('createStore') )
		{
			$Store = DAO_CFactory::create('store');
			$Store->setFrom($Form->values());

			if (isset( $_POST['grand_opening_date']))
			{
				$dateParts = explode("/", CGPC::do_clean($_POST['grand_opening_date'],TYPE_STR));
				$grandAsTS = mktime(0, 0, 0, $dateParts[0], $dateParts[1], $dateParts[2]);
				$Store->grand_opening_date = date("Y-m-d H:i:s", $grandAsTS);
			}

			$Store->publish_session_details = 1;
		 	$Store->default_intro_slots = 2;
		 	$Store->supports_intro_orders = 1;
		 	$Store->supports_special_events = 0;
		 	$Store->do_run_dream_rewards_cron_tasks = 1;
		 	$Store->supports_free_assembly_promotion = 0;
		 	$Store->supports_dream_rewards = 0;
		 	$Store->supports_plate_points = 1;
		 	$Store->supports_order_manager = 1;
		 	$Store->ddu_id = str_replace("&", "and", $Store->store_name);

		 	if ($Store->franchise_id == 220)
		 	{
		 	    $Store->is_corporate_owned = 1;
		 	}
		 	else
		 	{
		 	    $Store->is_corporate_owned = 0;
		 	}

		 	if ($Store->exists())
		 	{
		 		$tpl->setErrorMsg('A store with that name already exists.');
		 	}
		 	else
		 	{
				if ($Store->insert())
				{
					$Store->setCurrentSalesTax($Form->value('food_tax'), $Form->value('total_tax'), $Form->value('other1_tax'), $Form->value('other2_tax'));
					$Store->setPremium(17.00);
					$tpl->setStatusMsg('The store has been created.  Please remember to obtain both the gift card merchant ID and GP account ID and update the store ');

					// add franchise owner to user_to_store
					$utf = DAO_CFactory::create('user_to_franchise');
					$utf->franchise_id = $Store->franchise_id;
					$utf->find();

					while($utf->fetch())
					{
						$uts = DAO_CFactory::create('user_to_store');
						$uts->user_id = $utf->user_id;
						$uts->store_id = $Store->id;
						$uts->display_to_public = 0;
						$uts->insert();
					}

					if (isset($_GET['back']))
					{
						CApp::bounce($_GET['back']);
					}
					else
					{
						CApp::bounce('main.php?page=admin_list_stores');
					}
				}
				else
				{
					$tpl->setErrorMsg('The store could not be created');
				}
		 	}
		}

		$tpl->assign('form_create_store', $Form->Render());

		$back = 'main.php?page=admin_list_stores';

		if ( array_key_exists('back', $_GET) && $_GET['back'] )
		{
			$back = $_GET['back'];
		}

		$tpl->assign('back', $back);
	}
}
?>