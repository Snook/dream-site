<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_food_testing_survey_store extends CPageAdminOnly
{
	private $needsStoreSelector = null;
	private $store_id = null;

	function runFranchiseManager()
	{
		$this->foodTestingSurvey();
	}
	function runFranchiseOwner()
	{
		$this->foodTestingSurvey();
	}
	function runOpsLead()
	{
		$this->foodTestingSurvey();
	}
	function runSiteAdmin()
	{
		return $this->foodTestingSurvey();
	}
	
	function runHomeOfficeManager()
	{
		return $this->foodTestingSurvey();
	}
	

	function foodTestingSurvey()
	{
		$tpl = CApp::instance()->template();

		$this->store_id =  CBrowserSession::getCurrentStore();

		if (!empty($_POST['survey_id']) && is_numeric($_POST['survey_id']))
		{
			$survey = DAO_CFactory::create('food_testing_survey');
			$survey->id = $_POST['survey_id'];
			$survey->store_id = $this->store_id;

			if ($survey->find(true))
			{
				$survey->schematic_accurate = CGPC::do_clean($_POST['schematic_accurate'],TYPE_STR);
				$survey->schematic_easy_to_understand = CGPC::do_clean($_POST['schematic_easy_to_understand'],TYPE_STR);
				$survey->schematic_notes = CGPC::do_clean($_POST['schematic_notes'],TYPE_STR);
				$survey->honeydew_accurate = CGPC::do_clean($_POST['honeydew_accurate'],TYPE_STR);
				$survey->honeydew_easy_to_understand = CGPC::do_clean($_POST['honeydew_easy_to_understand'],TYPE_STR);
				$survey->honeydew_notes = CGPC::do_clean($_POST['honeydew_notes'],TYPE_STR);
				$survey->recipe_assembly_card_accurate = CGPC::do_clean($_POST['recipe_assembly_accurate'],TYPE_STR);
				$survey->recipe_assembly_card_easy_to_understand = CGPC::do_clean($_POST['recipe_assembly_easy_to_understand'],TYPE_STR);
				$survey->selling_features_notes = CGPC::do_clean($_POST['selling_features_notes'],TYPE_STR);
				$survey->timestamp_completed = CTemplate::unix_to_mysql_timestamp(time());

				$survey->update();

				$tpl->setErrorMsg('Survey complete thank you.');

				CApp::bounce('main.php?page=admin_food_testing_survey&recipe=' . CGPC::do_clean($_POST['survey_id'],TYPE_INT));
			}
		}

		if (!empty($_REQUEST['recipe']) && is_numeric($_REQUEST['recipe']))
		{
			$survey = DAO_CFactory::create('food_testing_survey');
			$survey->query("SELECT
				ft.title,
				fts.id,
				fts.timestamp_completed
				FROM food_testing_survey AS fts
				INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id
				WHERE fts.id = '" . $_REQUEST['recipe'] . "' AND fts.store_id = '" . $this->store_id . "' AND fts.is_deleted = '0' AND ft.is_deleted = '0'");

			if ($survey->fetch())
			{
				if (empty($survey->timestamp_completed))
				{
					$tpl->assign('survey', $survey->toArray());
				}
				else
				{
					$tpl->setErrorMsg('Survey already completed.');

					CApp::bounce('main.php?page=admin_food_testing_survey');
				}
			}
			else
			{
				$tpl->setErrorMsg('Survey not found.');

				CApp::bounce('main.php?page=admin_food_testing_survey');
			}
		}
	}
}
?>