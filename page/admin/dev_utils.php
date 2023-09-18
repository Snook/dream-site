<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/COrdersDigest.php");


class page_admin_dev_utils extends CPageAdminOnly
{

	function runSiteAdmin()
	{
		if (CUser::getCurrentUser()->id == 400252)
		{
			$this->runPage();
		}
		else
		{
			CApp::bounce('/?page=admin_home');
		}
	}

	function runPage()
	{

		$tpl = CApp::instance()->template();

		$Form = new CForm();

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::value => 'Submit',
			CForm::css_class => 'button',
			CForm::name => 'submit_function'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::value => 'Submit',
			CForm::css_class => 'button',
			CForm::name => 'submit_bal_function'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'order_id'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'bal_order_id'
		));


		if (isset($_POST['submit_function']))
		{

			$order_id = $Form->value('order_id');

			if (isset($order_id) && is_numeric($order_id))
			{

				$OrderObj = DAO_CFactory::create('orders');
				$OrderObj->id = $order_id;
				$OrderObj->find(true);

				$AGRTotal = COrdersDigest::calculateAGRTotal($OrderObj->id, $OrderObj->grand_total, $OrderObj->subtotal_all_taxes, $OrderObj->fundraiser_value, $OrderObj->subtotal_ltd_menu_item_value, $OrderObj->subtotal_bag_fee);


				$tpl->assign('result', "AGR = $" . CTemplate::moneyFormat($AGRTotal));
			}



		}

		if (isset($_POST['submit_bal_function']))
		{

			$order_id = $Form->value('bal_order_id');

			if (isset($order_id) && is_numeric($order_id))
			{

				$OrderObj = DAO_CFactory::create('orders');
				$OrderObj->id = $order_id;
				$OrderObj->find(true);

				$Balance = COrdersDigest::calculateAndAddBalanceDue($OrderObj->id, $OrderObj->grand_total);


				$tpl->assign('result', "Balance = $" . CTemplate::moneyFormat($Balance));
			}
		}


		if (isset($_GET['s_det']) && is_numeric($_GET['s_det']))
		{





		}


		//set template vars
		$tpl->assign('dev_form', $Form->Render());
	}
}

?>