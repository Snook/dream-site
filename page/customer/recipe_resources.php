<?php

class page_recipe_resources extends CPage
{

	function runPublic()
	{
		$req_search = CGPC::do_clean(((!empty($_REQUEST['q']) && is_string($_REQUEST['q'])) ? substr($_REQUEST['q'], 0, 255) : false), TYPE_NOHTML, true);
		$req_page = CGPC::do_clean((empty($_REQUEST['p']) ? 1 : $_REQUEST['p']), TYPE_INT, true);
		$req_video_only = CGPC::do_clean((!empty($_REQUEST['video']) ? $_REQUEST['video'] : false), TYPE_BOOL, true);

		if(strlen(preg_replace("/[^a-zA-Z0-9]+/", "", $req_search)) < 3)
		{
			$req_search = null;
		}

		$tpl = CApp::instance()->template();

		$tpl->assign('search_query', false);
		$tpl->assign('search_results', false);
		$tpl->assign('page_total', false);
		$tpl->assign('videos_only', false);

		// search for recipes with videos by keyword
		if (!empty($req_search))
		{
			$recipe = DAO_CFactory::create('recipe');
			$recipe->whereAdd("ISNULL(recipe.override_menu_id)");
			$recipe->whereAdd("(recipe.recipe_name LIKE '%" . $recipe->escape($req_search, true) . "%' OR menu_item.menu_item_description LIKE '%" . $recipe->escape($req_search, true) . "%')");
			$recipe->whereAdd("recipe.recipe_name IS NOT NULL");
			if ($req_video_only)
			{
				$recipe->whereAdd("recipe.cooking_instruction_youtube_id IS NOT NULL AND recipe.cooking_instruction_youtube_id != '0' AND recipe.cooking_instruction_youtube_id != ''");
				$tpl->assign('videos_only', true);
			}

			$menuItem = DAO_CFactory::create('menu_item');
			$menuItem->selectAdd();
			$menuItem->selectAdd('recipe.recipe_name');
			$menuItem->selectAdd('recipe.recipe_id');
			$menuItem->selectAdd('recipe.cooking_instruction_youtube_id');
			$menuItem->selectAdd('menu_item.menu_item_description');
			$menuItem->joinAdd($recipe, array('useWhereAsOn' => true));
			$menuItem->groupBy("menu_item.recipe_id");
			$menuItem->orderBy("recipe.id DESC");

			$menuItem->find();

			$total_rows = $menuItem->N;

			// request per page
			$per_page = 20;
			$limit_start = ($req_page - 1) * $per_page;
			$total_pages = floor($total_rows / $per_page);
			$next_page = $req_page + 1;
			$prev_page = $req_page - 1;

			if ($total_pages > 1)
			{
				$tpl->assign('page_total', $total_pages);
				$tpl->assign('page_req', $req_page);
				$tpl->assign('page_next', $next_page);
				$tpl->assign('page_prev', (($prev_page < 1) ? false : $prev_page));
			}

			$menuItem->limit($limit_start, $per_page);
			$menuItem->find();

			$search_results = array();

			while ($menuItem->fetch())
			{
				$search_results[$menuItem->recipe_id]['recipe_name'] = $menuItem->recipe_name;
				$search_results[$menuItem->recipe_id]['recipe_description'] = $menuItem->menu_item_description;
				$search_results[$menuItem->recipe_id]['cooking_instruction_youtube_id'] = $menuItem->cooking_instruction_youtube_id;
				$search_results[$menuItem->recipe_id]['override_menu_id'] = $menuItem->override_menu_id;
				$search_results[$menuItem->recipe_id]['recipe_id'] = $menuItem->recipe_id;
			}

			if (empty($search_results))
			{
				header('status: 404 Not Found');
			}

			$tpl->assign('search_query', $req_search);
			$tpl->assign('search_results', $search_results);
		}

		$activeMenus = CMenu::getLastXMenus(3, true, 'ASC');

		$activeMenuArray = array();

		foreach ($activeMenus as $DAO_menu)
		{
			$activeMenuArray[$DAO_menu->id] = CMenu::buildPreviewMenuArray(null, $DAO_menu->id, 'NameAZ');
		}

		$tpl->assign('activeMenus', $activeMenuArray);
	}
}

?>