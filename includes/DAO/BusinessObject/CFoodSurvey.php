<?php
require_once('includes/DAO/Food_survey.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');

class CFoodSurvey extends DAO_Food_survey {

	/**
	 * Returns an array of ratings a user has made
	 *
	 * @param object $User
	 * @param array  $limitRecipesArray : array of recipe ids to search
	 *
	 * @return array:
	 * @throws Exception
	 */
	static function getUsersRatedRecipes($User, $limitRecipesArray = false): array
	{
		$recipes = '';
		$recipesIn = '';
		$recipesInSub = '';

		if ($limitRecipesArray)
		{
			$recipes = implode(',', $limitRecipesArray);
			$recipesIn = "AND r.recipe_id IN (" . $recipes . ")";
			$recipesInSub = "fsc.recipe_id IN (" . $recipes . ")";
		}

		$RatedItems = DAO_CFactory::create('recipe');
		$RatedItems->query("SELECT
				CONCAT(r.recipe_id,'-',r.version) AS element_id,
				r.recipe_id,
				r.version,
				IFNULL(fs.rating, 0) AS rating,
				IFNULL(fs.would_order_again, 0) AS favorite,
				fsc.`comment`,
				fsc.`personal_note`
				FROM recipe r
				LEFT JOIN food_survey fs ON fs.recipe_id = r.recipe_id AND fs.recipe_version = r.version AND fs.user_id = '" . $User->id . "' AND fs.is_active = '1' AND fs.is_deleted = '0'
				LEFT JOIN (SELECT * FROM food_survey_comments fsc WHERE " . $recipesInSub . " AND fsc.user_id = '" . $User->id . "' AND fsc.is_active = '1' AND fsc.is_deleted = '0' ORDER BY fsc.id DESC) fsc ON fsc.recipe_id = r.recipe_id AND fsc.recipe_version = r.version
				WHERE r.is_deleted = '0'
				" . $recipesIn . "
				GROUP BY r.recipe_id, r.version");

		$ratings = array();

		while ($RatedItems->fetch())
		{
			$ratings[$RatedItems->element_id]['rating'] = $RatedItems->rating;
			$ratings[$RatedItems->element_id]['favorite'] = $RatedItems->favorite;
			$ratings[$RatedItems->element_id]['comment'] = $RatedItems->comment;
			$ratings[$RatedItems->element_id]['personal_note'] = $RatedItems->personal_note;
		}

		return $ratings;
	}

	/**
	 *
	 * @param int $User
	 * @param int $recipe_id
	 * @param int $recipe_version
	 * @param int $rating
	 * @param int $store_id          : recorded to capture if store is relevant
	 * @param int $menu_id           : optional finer rating control
	 * @param int $would_order_again : vip food survey
	 * @param int $vip_submission_id : vip food survey
	 *
	 * @throws Exception
	 */
	static function addMyMealsRating($User, $recipe_id, $recipe_version, $rating, $store_id = false, $menu_id = false): array
	{
		$hasPreviouslyRated = false;

		// get currently active
		$DAO_food_survey = DAO_CFactory::create('food_survey');
		$DAO_food_survey->user_id = $User->id;
		$DAO_food_survey->recipe_id = $recipe_id;
		$DAO_food_survey->recipe_version = $recipe_version;
		$DAO_food_survey->is_active = 1;

		if ($DAO_food_survey->find(true))
		{
			// check if they have rated before, if they have, this should never be able to be 0 as the minimum rating is 1
			if ($DAO_food_survey->rating != 0)
			{
				$hasPreviouslyRated = $DAO_food_survey->N;
			}

			$DAO_food_survey->delete();
			// unset $DAO_food_survey->is_deleted because we are going to use this object to do an insert
			unset($DAO_food_survey->is_deleted);
		}

		if ($store_id)
		{
			$DAO_food_survey->store_id = $store_id;
		}

		if ($menu_id)
		{
			$DAO_food_survey->menu_id = $menu_id;
		}

		$DAO_food_survey->rating = $rating;
		$DAO_food_survey->insert();

		// award platepoints
		if (empty($hasPreviouslyRated) && $User->isEnrolledInPlatePoints())
		{
			$recipe = DAO_CFactory::create('recipe');
			$recipe->recipe_id = $recipe_id;
			$recipe->orderBy('id DESC');
			$recipe->limit(1);
			$recipe->find(true);

			list($eventMetaData, $platePointsStatus) = CPointsUserHistory::handleEvent($User, CPointsUserHistory::MY_MEALS_RATED, array('comments' => 'Earned ' . CPointsUserHistory::$eventMetaData[CPointsUserHistory::MY_MEALS_RATED]['points'] . ' points for rating ' . $recipe->recipe_name, 'recipe_id' => $recipe_id));
		}

		$result = array(
			'processor_success' => true,
			'processor_message' => 'Rating updated.'
		);

		if (!empty($eventMetaData) && $User->isEnrolledInPlatePoints())
		{
			$result['platepoints_status'] = $platePointsStatus;

			$points_earned = $eventMetaData['points'];

			$result['dd_toasts'] = array(
				array(
					'message' => 'You earned '. $points_earned . ' PLATEPOINTS!',
					'position' => 'topcenter',
					'css_style' => 'platepoints'
				)
			);
		}

		return $result;
	}

	/**
	 *
	 * @param int $User
	 * @param int $recipe_id
	 * @param int $recipe_version
	 * @param int $set_favorite
	 * @param int $menu_id : optional finer rating control
	 *
	 * @throws Exception
	 */
	static function addMyMealsFavorite($User, $recipe_id, $recipe_version, $set_favorite, $menu_id = false, $store_id = false)
	{
		// get currently active
		$DAO_food_survey = DAO_CFactory::create('food_survey');
		$DAO_food_survey->user_id = $User->id;
		$DAO_food_survey->recipe_id = $recipe_id;
		$DAO_food_survey->recipe_version = $recipe_version;
		$DAO_food_survey->is_active = 1;

		if ($DAO_food_survey->find(true))
		{
			$DAO_food_survey->delete();
			// unset $DAO_food_survey->is_deleted because we are going to use this object to do an insert
			unset($DAO_food_survey->is_deleted);
		}

		if ($store_id)
		{
			$DAO_food_survey->store_id = $store_id;
		}

		if ($menu_id)
		{
			$DAO_food_survey->menu_id = $menu_id;
		}

		// favorite values: 0 = unanswered, 1 = yes, 2 = no.
		if ($set_favorite === 0)
		{
			$set_favorite = 2;
		}

		$DAO_food_survey->would_order_again = $set_favorite;
		$DAO_food_survey->insert();

		return array(
			'processor_success' => true,
			'processor_message' => 'Favorite updated.'
		);
	}

	/**
	 *
	 * @param int    $User
	 * @param int    $recipe_id
	 * @param int    $recipe_version
	 * @param string $comment
	 * @param int    $menu_id : optional finer rating control
	 *
	 * @throws Exception
	 */
	static function addMyMealsReview($User, $recipe_id, $recipe_version, $comment, $menu_id = false)
	{
		$comment = trim($comment);

		if (!empty($comment))
		{
			$xssFilter = new InputFilter();
			$comment = $xssFilter->process($comment);
		}

		// get currently active
		$DAO_food_survey_comments = DAO_CFactory::create('food_survey_comments');
		$DAO_food_survey_comments->user_id = $User->id;
		$DAO_food_survey_comments->recipe_id = $recipe_id;
		$DAO_food_survey_comments->recipe_version = $recipe_version;
		$DAO_food_survey_comments->is_active = 1;

		if ($DAO_food_survey_comments->find(true))
		{
			$DAO_food_survey_comments->delete();
			// unset $DAO_food_survey_comments->is_deleted because we are going to use this object to do an insert
			unset($DAO_food_survey_comments->is_deleted);

			if (empty($comment) && empty($DAO_food_survey_comments->personal_note))
			{
				return array(
					'processor_success' => true,
					'processor_message' => 'Personal note deleted.'
				);
			}
		}

		if ($menu_id)
		{
			$DAO_food_survey_comments->menu_id = $menu_id;
		}

		$DAO_food_survey_comments->comment_status = 'QUEUED';
		$DAO_food_survey_comments->comment = $comment;
		$DAO_food_survey_comments->insert();

		return array(
			'processor_success' => true,
			'processor_message' => 'Review submitted.',
			'dd_toasts' => array(
				array('message' => 'Review submitted, thank you.')
			)
		);
	}

	/**
	 *
	 * @param int    $User
	 * @param int    $recipe_id
	 * @param int    $recipe_version
	 * @param string $comment
	 * @param int    $menu_id : optional finer rating control
	 *
	 * @throws Exception
	 */
	static function addMyMealsPersonalNote($User, $recipe_id, $recipe_version, $comment, $menu_id = false)
	{
		// keep people from just entering a bunch of spaces
		$comment = trim($comment);

		// we can have empty comments, this is how a user "deletes" their comment
		if (!empty($comment))
		{
			$xssFilter = new InputFilter();
			$comment = $xssFilter->process($comment);
		}

		// get currently active
		$DAO_food_survey_comments = DAO_CFactory::create('food_survey_comments');
		$DAO_food_survey_comments->user_id = $User->id;
		$DAO_food_survey_comments->recipe_id = $recipe_id;
		$DAO_food_survey_comments->recipe_version = $recipe_version;
		$DAO_food_survey_comments->is_active = 1;

		if ($DAO_food_survey_comments->find(true))
		{
			$DAO_food_survey_comments->delete();
			// unset $DAO_food_survey_comments->is_deleted because we are going to use this object to do an insert
			unset($DAO_food_survey_comments->is_deleted);

			if (empty($comment) && empty($DAO_food_survey_comments->comment))
			{
				return array(
					'processor_success' => true,
					'processor_message' => 'Personal note deleted.'
				);
			}
		}

		if ($menu_id)
		{
			$DAO_food_survey_comments->menu_id = $menu_id;
		}

		$DAO_food_survey_comments->personal_note = $comment;
		$DAO_food_survey_comments->insert();

		return array(
			'processor_success' => true,
			'processor_message' => 'Personal note updated.',
			'dd_toasts' => array(
				array('message' => 'Personal note updated.')
			)
		);
	}
}
?>