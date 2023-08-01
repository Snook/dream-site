<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_recipe_database extends CPageAdminOnly
{

	function runHomeOfficeManager()
	{
		$this->runRecipeDatabase();
	}

	function runSiteAdmin()
	{
		$this->runRecipeDatabase();
	}

	function runRecipeDatabase()
	{
		$DAO_recipe = DAO_CFactory::create('recipe');
		$DAO_recipe->query("SELECT 
    		recipe.*, 
    		menu.menu_name
			FROM (SELECT recipe.*, max(recipe.override_menu_id) AS max_override_menu_id FROM recipe GROUP BY recipe_id) AS recipe
			LEFT JOIN menu ON menu.id=recipe.max_override_menu_id
			ORDER BY recipe.recipe_id");

		$recipeArray = array();
		while ($DAO_recipe->fetch())
		{
			$recipeArray[$DAO_recipe->recipe_id] = $DAO_recipe->cloneObj();
		}

		$this->Template->assign('recipeArray', $recipeArray);
	}

}