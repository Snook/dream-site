<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CFile.php");

class page_admin_food_testing extends CPageAdminOnly
{

	private $currentStore = null;

	function runHomeOfficeStaff()
	{
		$this->runFoodTesting();
	}

	function runHomeOfficeManager()
	{
		$this->runFoodTesting();
	}

	function runSiteAdmin()
	{
		$this->runFoodTesting();
	}

	function runFoodTesting()
	{
		$tpl = CApp::instance()->template();

		if (!empty($_REQUEST['export']) && $_REQUEST['export'] == 'xlsx')
		{
			// store surveys
			if (!empty($_REQUEST['export_store']))
			{
				if ($_REQUEST['export_store'] == 'all')
				{
					$export_where = "";
				}
				else if (is_numeric($_REQUEST['export_store']))
				{
					$export_where = " AND fts.food_testing_id = '" . $_REQUEST['export_store'] . "' ";
				}

				$get_closed = 0;
				if (!empty($_REQUEST['export_closed']))
				{
					$get_closed = 1;
				}

				$storeSurvey = DAO_CFactory::create('food_testing_survey');
				$storeSurvey->query("SELECT
					fts.food_testing_id,
					ft.title,
					s.home_office_id,
					s.store_name,
					fts.schematic_accurate,
					fts.schematic_easy_to_understand,
					fts.schematic_notes,
					fts.honeydew_accurate,
					fts.honeydew_easy_to_understand,
					fts.honeydew_notes,
					fts.recipe_assembly_card_accurate,
					fts.recipe_assembly_card_easy_to_understand,
					fts.selling_features_notes,
					fts.timestamp_paid,
					fts.timestamp_completed
					FROM food_testing_survey AS fts
					INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_deleted = '0'
					INNER JOIN store AS s ON s.id = fts.store_id
					WHERE fts.is_deleted = '0'
					AND ft.is_closed = '" . $get_closed . "'
					" . $export_where . "
					ORDER BY fts.food_testing_id DESC, s.store_name ASC");

				$labels = 'Test ID,Recipe,HO ID,Store,Schematics Accurate,Schematics Easy to Understand,Schematics Notes,Honeydew Accurate,Honeydew Easy to Understand,Honeydew Notes,Recipe Assembly Card Accurate,Recipe Assembly Card Easy to Understand,Selling Features Notes,Date Paid,Survey Completed';

				$labelArray = explode(',', $labels);

				$rows = array();
				while ($storeSurvey->fetch())
				{
					$tempArray = $storeSurvey->toArray();

					$rows[] = array_slice($tempArray, 0, count($labelArray));
				}

				if (!empty($rows))
				{

					$tpl->assign('labels', explode(',', $labels));
					$tpl->assign('rows', $rows);
				}
				else
				{
					$tpl->setErrorMsg('Server error, no store surveys found.');

					CApp::bounce('/?page=admin_food_testing');
				}
			}

			// guest surveys
			if (!empty($_REQUEST['export_guest']))
			{
				if ($_REQUEST['export_guest'] == 'all')
				{
					$export_where = "";
				}
				else if (is_numeric($_REQUEST['export_guest']))
				{
					$export_where = " AND fts.food_testing_id = '" . $_REQUEST['export_guest'] . "' ";
				}

				$get_closed = 0;
				if (!empty($_REQUEST['export_closed']))
				{
					$get_closed = 1;
				}

				$guestSurvey = DAO_CFactory::create('food_testing_survey_submission');
				$guestSurvey->query("SELECT
					fts.food_testing_id,
					ft.title,
					s.home_office_id,
					s.store_name,
					ftss.user_id,
					u.firstname,
					u.lastname,
					u.primary_email,
					ftss.serving_size,
					ftss.ease_of_prep,
					ftss.look_appealing,
					ftss.i_liked_taste,
					ftss.family_liked_taste,
					ftss.salty_taste,
					ftss.spicy_taste,
					ftss.kid_friendly,
					ftss.would_like_on_menu,
					ftss.order_as_is,
					ftss.order_as_is_detail,
					ftss.overall_satisfaction,
					ftss.liked_best,
					ftss.suggest_improvements,
					ftss.timestamp_received,
					ftss.timestamp_completed
					FROM food_testing_survey_submission AS ftss
					INNER JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id AND fts.is_deleted = '0'
					INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_deleted = '0'
					INNER JOIN store AS s ON s.id = fts.store_id
					INNER JOIN `user` AS u ON u.id = ftss.user_id
					WHERE ftss.is_deleted = '0'
					AND ft.is_closed = '" . $get_closed . "'
					" . $export_where . "
					ORDER BY fts.food_testing_id DESC, s.store_name ASC, ftss.timestamp_completed DESC, ftss.timestamp_received DESC, u.firstname ASC");

				$labels = 'Test ID,Recipe,HO ID,Store,User ID,First Name,Last Name,Primary Email,Serving Size,Ease of Prep,Looks Appealing,Liked Taste,Family Liked Taste,Salty Taste,Spicy Taste,Kid Friendly,Would like on menu,Order as is,Order as is detail,Overall Satisfaction,Liked Best,Suggest Improvements,Received Meal,Survey Completed';

				$labelArray = explode(',', $labels);

				$rows = array();
				while ($guestSurvey->fetch())
				{
					$tempArray = $guestSurvey->toArray();

					$rows[] = array_slice($tempArray, 0, count($labelArray));
				}

				if (!empty($rows))
				{
					$tpl->assign('labels', $labelArray);
					$tpl->assign('rows', $rows);
				}
				else
				{
					$tpl->setErrorMsg('Server error, no guest surveys found.');

					CApp::bounce('/?page=admin_food_testing');
				}
			}
		}

		if (!empty($_POST['new_recipe']))
		{
			$new_recipes = explode("\n", CGPC::do_clean($_POST['new_recipe'],TYPE_STR));

			foreach ($new_recipes AS $new_recipe)
			{
				$new_recipe = trim($new_recipe);

				if (!empty($new_recipe))
				{
					$insert = DAO_CFactory::create('food_testing');
					$insert->title = trim($new_recipe);
					$insert->insert();
				}
			}
		}

		if (!empty($_POST['add_file_survey_id']) && !empty($_FILES['add_survey_file']['tmp_name']))
		{
			$File = CFile::uploadFile('add_survey_file');
			$insertFile = DAO_CFactory::create('food_testing');
			$insertFile->id = CGPC::do_clean($_POST['add_file_survey_id'],TYPE_INT);

			$insertFile->find();

			if ($File)
			{
				$insertFile->file_id = $File->id;

				$insertFile->update();
			}
			else
			{
				$tpl->setErrorMsg('Server error, file did not get stored.');
			}
		}

		if (!empty($_POST['add_store_survey_id']))
		{
			$new_stores = explode("\n", CGPC::do_clean($_POST['add_store'],TYPE_STR));

			$store_error = '';

			foreach ($new_stores AS $new_store)
			{
				$new_store = trim($new_store);

				if (!empty($new_store) || $new_store == 0) // == 0 to allow for for store_id 21
				{
					$Store = DAO_CFactory::create('store');
					$Store->home_office_id = $new_store;

					if ($Store->find(true))
					{
						$insert = DAO_CFactory::create('food_testing_survey');
						$insert->food_testing_id = CGPC::do_clean($_POST['add_store_survey_id'],TYPE_INT);
						$insert->store_id = $Store->id;

						if (!$insert->find())
						{
							$insert->insert();

							$insert_id = $insert->id;

							// send email to store
							$Mail = new CMail();

							$email_data = array();

							$email_data['primary_email'] = $Store->email_address;
							$email_data['store_name'] = $Store->store_name;
							$email_data['store_id'] = $Store->id;
							$email_data['survey_id'] = $insert_id;

							$contentsHtml = CMail::mailMerge('food_testing/recipe_assigned_to_store.html.php', $email_data);
							$contentsText = CMail::mailMerge('food_testing/recipe_assigned_to_store.txt.php', $email_data);

							$Mail->send(null, null,
								$Store->store_name, $Store->email_address,
								"Dream Dinners Food Testing",
								$contentsHtml, $contentsText, '','', 0, 'food_test_recipe_assigned_to_store');
						}
						else
						{
							$store_error .= $new_store . ' already added.<br />';
						}
					}
					else
					{
						$store_error .= $new_store . ' not found.<br />';
					}
				}
			}

			if (!empty($store_error))
			{
				$tpl->setErrorMsg($store_error);
			}
		}

		$recipe = DAO_CFactory::create('food_testing');
		$recipe->query("SELECT
			ft.id,
			ft.title,
			ft.is_closed,
			ft.survey_start,
			ft.survey_end,
			ft.timestamp_updated,
			ft.timestamp_created,
			ft.created_by,
			ft.updated_by,
			f.id AS file_id,
			f.file_asset_name,
			f.file_name
			FROM food_testing AS ft
			LEFT JOIN file AS f ON f.id = ft.file_id AND f.is_deleted = '0'
			WHERE ft.is_deleted = '0'
			ORDER BY ft.is_closed ASC, ft.id DESC");

		$recipes = array();
		while ($recipe->fetch())
		{
			$recipes[$recipe->id] = $recipe->toArray();

			if ($recipes[$recipe->id]['timestamp_created'] == '1970-01-01 00:00:01')
			{
				$recipes[$recipe->id]['timestamp_created'] = $recipes[$recipe->id]['timestamp_updated'];
			}

			$recipes[$recipe->id]['total_stores'] = 0;
			$recipes[$recipe->id]['total_guests'] = 0;
			$recipes[$recipe->id]['pending_surveys'] = 0;
			$recipes[$recipe->id]['response_count'] = 0;
		}

		$recipe_ids = implode("','", array_keys($recipes));

		// recipe surveys
		$survey = DAO_CFactory::create('food_testing_survey');
		$survey->query("SELECT
			fts.id,
			fts.food_testing_id,
			fts.store_id,
			fts.timestamp_paid,
			fts.timestamp_completed,
			fts.timestamp_updated,
			fts.timestamp_created,
			ft.title,
			s.store_name,
			s.food_testing_w9,
			COUNT(ftss.user_id) AS guest_total,
			COUNT(ftss.timestamp_completed) AS guest_completed,
			COUNT(ftss.timestamp_received) - COUNT(ftss.timestamp_completed) AS guest_pending
			FROM
			food_testing_survey AS fts
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id
			INNER JOIN store AS s ON s.id = fts.store_id
			LEFT JOIN food_testing_survey_submission AS ftss ON ftss.food_testing_survey_id = fts.id
			WHERE
			fts.food_testing_id IN ('" . implode("','", array_keys($recipes)) . "') AND
			fts.is_deleted = '0'
			GROUP BY fts.id");

		$surveys = array();
		while ($survey->fetch())
		{
			$surveys[$survey->food_testing_id][$survey->id] = $survey->toArray();

			if ($surveys[$survey->food_testing_id][$survey->id]['timestamp_created'] == '1970-01-01 00:00:01')
			{
				$surveys[$survey->food_testing_id][$survey->id]['timestamp_created'] = $surveys[$survey->food_testing_id][$survey->id]['timestamp_updated'];
			}

			$recipes[$survey->food_testing_id]['total_stores']++;

			if (empty($survey->timestamp_completed))
			{
				$recipes[$survey->food_testing_id]['pending_surveys']++;
			}

			if (!empty($survey->timestamp_completed))
			{
				$recipes[$survey->food_testing_id]['response_count']++;
			}

			$tpl->assign('title', $survey->title);
		}

		$tpl->assign('recipes', $recipes);
		$tpl->assign('surveys', $surveys);
	}
}

?>