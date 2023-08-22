<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CFile.php");

class page_admin_food_testing_survey extends CPageAdminOnly
{
	private $needsStoreSelector = null;
	private $store_id = null;

	function runFranchiseManager()
	{
		$this->cansubmitfoodtestingw9 = true;
		$this->canrequeststoreid = false;
		$this->needsStoreSelector = false;
		$this->canDeleteGuest = false;
		$this->foodTestingSurvey();
	}
	function runOpsLead()
	{
		$this->cansubmitfoodtestingw9 = true;
		$this->canrequeststoreid = false;
		$this->needsStoreSelector = false;
		$this->canDeleteGuest = false;
		$this->foodTestingSurvey();
	}
	function runFranchiseOwner()
	{
		$this->cansubmitfoodtestingw9 = true;
		$this->canrequeststoreid = false;
		$this->needsStoreSelector = false;
		$this->canDeleteGuest = false;
		$this->foodTestingSurvey();
	}

	function runHomeOfficeStaff()
	{
		$this->cansubmitfoodtestingw9 = false;
		$this->canrequeststoreid = true;
		$this->needsStoreSelector = true;
		$this->canDeleteGuest = true;
		$this->foodTestingSurvey();
	}

	function runHomeOfficeManager()
	{
		$this->cansubmitfoodtestingw9 = false;
		$this->canrequeststoreid = true;
		$this->needsStoreSelector = true;
		$this->canDeleteGuest = true;
		$this->foodTestingSurvey();
	}

	function runSiteAdmin()
	{
		$this->cansubmitfoodtestingw9 = true;
		$this->canrequeststoreid = true;
		$this->needsStoreSelector = true;
		$this->canDeleteGuest = true;
		$this->foodTestingSurvey();
	}

	function foodTestingSurvey()
	{
		if (!empty($_REQUEST['recipe_files']) && is_numeric($_REQUEST['recipe_files']))
		{
			$File = CFile::downloadFile($_REQUEST['recipe_files']);
		}

		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		// ------------------------------ figure out active store and create store widget if necessary
		if ($this->canrequeststoreid && !empty($_GET['store_id']))
		{
			$Form->DefaultValues['store'] = CGPC::do_clean($_GET['store_id'],TYPE_INT);
		}
		else
		{
			$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();
		}

		if ($this->needsStoreSelector)
		{
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));

			$this->store_id = $Form->value('store');
		}
		else
		{
			$this->store_id = CBrowserSession::getCurrentStore();
		}

		$formArray = $Form->render();
		$tpl->assign('form', $formArray);

		$Store = DAO_CFactory::create('store');
		$Store->id = $this->store_id;
		$Store->find(true);

		$tpl->assign('store', $Store->toArray());
		$tpl->assign('cansubmitfoodtestingw9', $this->cansubmitfoodtestingw9);
		$tpl->assign('canDeleteGuest', $this->canDeleteGuest);

		if (!empty($_POST['add_guest_survey_id']) && !empty($_POST['add_guest']))
		{
			$new_guests = explode("\n", CGPC::do_clean($_POST['add_guest'],TYPE_STR));

			$surveyInfo = DAO_CFactory::create('food_testing_survey');
			$surveyInfo->query("SELECT
								ft.title
								FROM food_testing_survey AS fts
								INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_deleted = '0'
								WHERE fts.id = '" . CGPC::do_clean($_POST['add_guest_survey_id'],TYPE_INT) . "'
								AND fts.is_deleted = '0'");
			$surveyInfo->fetch();

			$user_error = '';

			foreach ($new_guests AS $new_guest)
			{
				$new_guest = trim($new_guest);

				if (!empty($new_guest))
				{
					$User = DAO_CFactory::create('user');
					$User->id = $new_guest;

					if ($User->find(true))
					{
						$insert = DAO_CFactory::create('food_testing_survey_submission');
						$insert->food_testing_survey_id = CGPC::do_clean($_POST['add_guest_survey_id'],TYPE_INT);
						$insert->user_id = $User->id;

						if (!$insert->find())
						{
							$insert->insert();

							// send email to user
							$Mail = new CMail();

							$invite_array = array(
								'primary_email' => $User->primary_email,
								'firstname' => $User->firstname,
								'store_id' => $User->home_store_id,
								'recipe_name' => $surveyInfo->title
							);

							$contentsHtml = CMail::mailMerge('food_testing/guest_received_recipe.html.php', $invite_array);
							$contentsText = CMail::mailMerge('food_testing/guest_received_recipe.txt.php', $invite_array);

							$Mail->send(null, null, $User->firstname . ' ' . $User->lastname, $User->primary_email, "Dream Dinners Food Testing", $contentsHtml, $contentsText, '', '', 0, 'food_test_guest_received_recipe');
						}
						else
						{
							$user_error .= $new_guest . ' already added.<br />';
						}
					}
					else
					{
						$user_error .= $new_guest . ' not found.<br />';
					}
				}
			}

			if (!empty($user_error))
			{
				$tpl->setErrorMsg($user_error);
			}
		}

		$recipe = DAO_CFactory::create('food_testing_survey');
		$recipe->query("SELECT
			ft.title,
			f.id AS file_id,
			f.file_name,
			fts.id,
			fts.food_testing_id,
			fts.timestamp_paid,
			fts.timestamp_completed,
			fts.timestamp_updated,
			fts.timestamp_created
			FROM food_testing_survey AS fts
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_deleted = '0'
			LEFT JOIN file AS f ON f.id = ft.file_id AND f.is_deleted = '0'
			WHERE fts.store_id = '" . $this->store_id . "'
			AND fts.is_deleted = '0'
			ORDER BY fts.timestamp_created DESC");

		$recipes = array();
		while ($recipe->fetch())
		{
			$recipes[$recipe->id] = $recipe->toArray();

			if ($recipes[$recipe->id]['timestamp_created'] == '1970-01-01 00:00:01')
			{
				$recipes[$recipe->id]['timestamp_created'] = $recipes[$recipe->id]['timestamp_updated'];
			}

			$recipes[$recipe->id]['total_guests'] = 0;
			$recipes[$recipe->id]['pending_count'] = 0;
			$recipes[$recipe->id]['response_count'] = 0;
		}

		$recipe_ids = implode("','", array_keys($recipes));

		$recipeStatus = DAO_CFactory::create('food_testing_survey_submission');
		$recipeStatus->query("SELECT
			ftss.id,
			fts.food_testing_id,
			ftss.food_testing_survey_id,
			ft.title,
			fts.store_id,
			ftss.user_id,
			ftss.serving_size,
			ftss.timestamp_received,
			ftss.timestamp_completed,
			ftss.timestamp_created,
			ftss.timestamp_updated,
			u.firstname,
			u.lastname
			FROM food_testing_survey AS fts
			INNER JOIN food_testing_survey_submission AS ftss ON ftss.food_testing_survey_id = fts.id AND ftss.is_deleted = '0'
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_deleted = '0'
			INNER JOIN `user` AS u ON u.id = ftss.user_id
			WHERE ftss.food_testing_survey_id IN ('" . implode("','", array_keys($recipes)) . "')
			AND fts.store_id = '" . $this->store_id . "'
			AND ft.is_deleted =  '0'
			ORDER BY u.firstname ASC");

		$surveys = array();

		while ($recipeStatus->fetch())
		{
			$surveys[$recipeStatus->food_testing_survey_id][$recipeStatus->id] = $recipeStatus->toArray();

			if ($surveys[$recipeStatus->food_testing_survey_id][$recipeStatus->id]['timestamp_created'] == '1970-01-01 00:00:01')
			{
				$surveys[$recipeStatus->food_testing_survey_id][$recipeStatus->id]['timestamp_created'] = $surveys[$recipeStatus->food_testing_survey_id][$recipeStatus->id]['timestamp_updated'];
			}

			if (!empty($recipeStatus->timestamp_updated))
			{
				$recipes[$recipeStatus->food_testing_survey_id]['total_guests']++;
			}

			if (!empty($recipeStatus->timestamp_received) && empty($recipeStatus->timestamp_completed))
			{
				$recipes[$recipeStatus->food_testing_survey_id]['pending_count']++;
			}

			if (!empty($recipeStatus->timestamp_completed))
			{
				$recipes[$recipeStatus->food_testing_survey_id]['response_count']++;
			}
		}

		$tpl->assign('recipes', $recipes);
		$tpl->assign('surveys', $surveys);
	}
}

?>