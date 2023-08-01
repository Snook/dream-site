<?php
require_once('includes/DAO/Food_testing.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');

class CFoodTesting extends DAO_Food_testing
{
	function __construct()
	{
		parent::__construct();
	}

	static function getRecipesForStore($store_id)
	{
		$recipe = DAO_CFactory::create('food_testing');
		$recipe->query("SELECT
			fts.id AS food_testing_survey_id,
			fts.food_testing_id,
			ft.title,
			ft.survey_start,
			ft.survey_end
			FROM food_testing_survey AS fts
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_closed = '0' AND ft.is_deleted = '0'
			WHERE fts.store_id = '" . $store_id . "' AND fts.is_closed = '0' AND fts.is_deleted = '0'
			ORDER BY ft.id DESC");

		$surveys = array();

		while ($recipe->fetch())
		{
			$surveys[] = array(
				'food_testing_survey_id' => $recipe->food_testing_survey_id,
				'title' => $recipe->title,
				'select_option_title' => $recipe->title . ' - Medium',
				'select_option_value' => $recipe->food_testing_survey_id . '-medium'
			);

			$surveys[] = array(
				'food_testing_survey_id' => $recipe->food_testing_survey_id,
				'title' => $recipe->title,
				'select_option_title' => $recipe->title . ' - Large',
				'select_option_value' => $recipe->food_testing_survey_id . '-large'
			);

		}

		if (!empty($surveys))
		{
			return $surveys;
		}

		return false;
	}

	static function getUserSessionRecipe($user_id, $session_id)
	{
		$survey = DAO_CFactory::create('food_testing_survey_submission');
		$survey->user_id = $user_id;
		$survey->session_id = $session_id;

		if ($survey->find(true))
		{
			return $survey;
		}

		return false;
	}

	static function sendFirstReminder($invite)
	{
		require_once('CMail.inc');

		$Mail = new CMail();
		$Mail->to_name = $invite->firstname . ' ' . $invite->lastname;
		$Mail->to_email = $invite->primary_email;
		$Mail->to_id = $invite->user_id;
		$Mail->subject = 'What did you think?';
		$Mail->body_html = CMail::mailMerge('food_testing/guest_reminder_1st.html.php', $invite);
		$Mail->body_text = CMail::mailMerge('food_testing/guest_reminder_1st.txt.php', $invite);
		$Mail->template_name = 'guest_reminder_1st';

		$Mail->sendEmail();
	}


	static function sendSecondReminder($invite)
	{
		require_once('CMail.inc');

		$Mail = new CMail();
		$Mail->to_name = $invite->firstname . ' ' . $invite->lastname;
		$Mail->to_email = $invite->primary_email;
		$Mail->to_id = $invite->user_id;
		$Mail->subject = 'Last Chance: Tell us what you think';
		$Mail->body_html = CMail::mailMerge('food_testing/guest_reminder_2nd.html.php', $invite);
		$Mail->body_text = CMail::mailMerge('food_testing/guest_reminder_2nd.txt.php', $invite);
		$Mail->template_name = 'guest_reminder_2nd';

		$Mail->sendEmail();
	}
}
?>