<?php
class page_my_surveys extends CPage {

	function runPublic()
	{
		CApp::forceLogin('/my-surveys');
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();
		$User = CUser::getCurrentUser();

		if (!empty($_POST['submit_my_survey']))
		{
			$recipeUpdate = DAO_CFactory::create('food_testing_survey_submission');
			$recipeUpdate->id = $_POST['survey_id'];
			$recipeUpdate->user_id = CUser::getCurrentUser()->id;
			$recipeUpdate->ease_of_prep = $_POST['ease_of_prep'];
			$recipeUpdate->look_appealing = $_POST['look_appealing'];
			$recipeUpdate->i_liked_taste = $_POST['i_liked_taste'];
			$recipeUpdate->family_liked_taste = $_POST['family_liked_taste'];
			$recipeUpdate->salty_taste = $_POST['salty_taste'];
			$recipeUpdate->spicy_taste = $_POST['spicy_taste'];
			$recipeUpdate->kid_friendly = $_POST['kid_friendly'];
			$recipeUpdate->would_like_on_menu = $_POST['would_like_on_menu'];
			$recipeUpdate->order_as_is = $_POST['order_as_is'];

			if ($_POST['order_as_is'] == '0')
			{
				$order_as_detail = array();

				foreach ($_POST as $key => $value)
				{
					if (preg_match("/order_as_is_no_/i", $key))
					{
						$order_as_detail[] = $value;
					}
				}
			}
			else if ($_POST['order_as_is'] == '1')
			{
				$order_as_detail = array();

				foreach ($_POST as $key => $value)
				{
					if (preg_match("/order_as_is_yes_/i", $key))
					{
						$order_as_detail[] = $value;
					}
				}
			}

			$order_as_detail = implode(', ', $order_as_detail);

			$recipeUpdate->order_as_is_detail = $order_as_detail;

			$recipeUpdate->overall_satisfaction = $_POST['overall_satisfaction'];
			$recipeUpdate->timestamp_completed = CTemplate::unix_to_mysql_timestamp(time());

			if (!empty($_POST['liked_best']))
			{
				$recipeUpdate->liked_best = $_POST['liked_best'];
			}

			if (!empty($_POST['suggest_improvements']))
			{
				$recipeUpdate->suggest_improvements = $_POST['suggest_improvements'];
			}

			$recipeUpdate->update();

			$tpl->setStatusMsg('Thank you for completing the survey.');
		}

		$tpl->assign('edit_survey', false);
		$tpl->assign('review_survey', false);

		if (!empty($_REQUEST['survey']) && is_numeric($_REQUEST['survey']))
		{
			$survey_id = $_REQUEST['survey'];
			$tpl->assign('edit_survey', true);
		}
		else if (!empty($_REQUEST['review']) && $_REQUEST['review'] == 'true' && CUser::getCurrentUser()->user_type != CUser::CUSTOMER)
		{
			// preview mode for home office reviewing the survey
			$recipe = array(
				title => '{Dinner Title}',
				id => '',
				food_testing_id => '',
				food_testing_survey_id => '',
				timestamp_received => '',
				timestamp_completed => '',
				timestamp_updated => '',
				timestamp_created => ''
			);

			$tpl->assign('recipe', $recipe);
			$tpl->assign('edit_survey', true);
			$tpl->assign('review_survey', true);
		}

		if (!empty($survey_id))
		{
			$recipe = DAO_CFactory::create('food_testing_survey_submission');
			$recipe->query("SELECT
				ft.title,
				ftss.id,
				fts.food_testing_id,
				ftss.food_testing_survey_id,
				ftss.timestamp_received,
				ftss.timestamp_completed,
				ftss.timestamp_updated,
				ftss.timestamp_created
				FROM food_testing_survey_submission AS ftss
				INNER JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id
				INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id
				WHERE ftss.id = '" . $survey_id . "'
				AND ftss.user_id = '" . $User->id . "'
				AND (ISNULL(ftss.timestamp_completed) OR ftss.timestamp_completed = '1970-01-01 00:00:01')
				AND ftss.timestamp_received IS NOT NULL
				AND ftss.is_deleted = '0'
				AND fts.is_deleted = '0'
				AND ft.is_deleted = '0'");

			if ($recipe->fetch())
			{
				$recipe = $recipe->toArray();
				if ($recipe['timestamp_created'] == '1970-01-01 00:00:01')
				{
					$recipe['timestamp_created'] = $recipe['timestamp_updated'];
				}

				$tpl->assign('recipe', $recipe);
			}
			else
			{
				CApp::bounce('/my-surveys');
			}
		}

		$UserTestRecipes = CUser::getUserTestRecipes($User);

		$tpl->assign('userTestRecipes', $UserTestRecipes);
	}
}
?>