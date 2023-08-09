<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try {

    $processed = 0;


	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: cache_global_recipe_ratings called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::CACHE_GLOBAL_RECIPE_RATING, "cache_global_recipe_ratings called but cron is disabled.");
		exit;
	}

	$recipes = new DAO();
  $recipes->query("select distinct recipe_id from menu_item where is_deleted = 0 and not isnull(recipe_id) and recipe_id <> ''");

  $processed = 0;

   while($recipes->fetch())
   {
       $newRating = DAO_CFactory::create('global_recipe_ratings_cache');
       $newRating->recipe_id = $recipes->recipe_id;

       $Rater = new DAO();
       $Rater->query("select  fs.recipe_id, avg(fs.rating) as thisRating from food_survey fs
                            WHERE fs.recipe_id = {$recipes->recipe_id}  AND fs.is_active = 1 AND fs.is_deleted = 0");
       $Rater->fetch();

       if (!empty($Rater->thisRating))
       {
           if ($newRating->find(true))
           {
               $newRating->global_rating = $Rater->thisRating;

               $newRating->update();
           }
           else
           {
               $newRating->global_rating = $Rater->thisRating;

               $newRating->insert();
           }

           $processed++;
       }


   }

	CLog::RecordCronTask($processed, CLog::SUCCESS, CLog::CACHE_GLOBAL_RECIPE_RATING, "cache_global_recipe_ratings completed successfully.");
} catch (exception $e) {
	CLog::RecordCronTask($processed, CLog::PARTIAL_FAILURE, CLog::CACHE_GLOBAL_RECIPE_RATING, "cache_global_recipe_ratings: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>