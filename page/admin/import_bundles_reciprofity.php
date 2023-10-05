<?php
require_once("includes/CPageAdminOnly.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/Menu_item_inventory.php");
require_once("DAO/CFactory.php");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once("CLog.inc");
require_once 'includes/class.Diff.php';
require_once("DAO/BusinessObject/CImportReciprofity.php");

class page_admin_import_bundles_reciprofity extends CPageAdminOnly
{

	static $testMode = false;
	static $testResults = array();
	static $entities_used = array();
	static $changelog = array();

	static $taste_bundle = array();
	static $intro_bundle = array();

	function runSiteAdmin()
	{
		$this->importBundles();
	}

	function importBundles()
	{
		$tpl = CApp::instance()->template();

		$didImport = false;

		if (!empty($_POST['menu']) && !empty($_FILES['bundles']) && $_FILES['bundles']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['bundles']['tmp_name']))
		{
			set_time_limit(100000);

			$menu_id = CGPC::do_clean((!empty($_POST['menu']) ? $_POST['menu'] : false), TYPE_INT);

			if (isset($_REQUEST['testmode']))
			{
				self::$testMode = true;
			}

			$MenuItem = DAO_CFactory::create('menu_item');
			$MenuItem->query("SELECT
				mi.recipe_id,
				mi.entree_id,
				mi.menu_item_name
				FROM menu_item mi
				JOIN menu_to_menu_item mmi ON mmi.menu_item_id = mi.id AND mmi.store_id IS NULL AND mmi.menu_id = $menu_id AND mi.is_deleted = 0
				GROUP BY mi.entree_id
				ORDER BY recipe_id");

			$menuItemArray = array();

			while ($MenuItem->fetch())
			{
				$menuItemArray[$MenuItem->recipe_id] = array(
					'recipe_id' => $MenuItem->recipe_id,
					'entree_id' => $MenuItem->entree_id,
					'menu_item_name' => $MenuItem->menu_item_name
				);
			}

			try
			{
				$didImport = true;

				$uberObject = DAO_CFActory::create('menu_item');
				$uberObject->query('START TRANSACTION;');

				$rows = CImportReciprofity::distillCSVImport($_FILES['bundles'], $tpl);

				// check data for problems
				$sanityResult = CImportReciprofity::sanityCheck($rows);
				if ($sanityResult !== true)
				{
					throw new Exception($sanityResult);
				}

				foreach ($rows as $row => $col)
				{
					if (!empty($col[INCLUDE_ON_INTRO]) && strtolower($col[INCLUDE_ON_INTRO]) == 'yes')
					{
						self::$intro_bundle[$col['recipe_id']] = $col['recipe_id'];
					}

					if (!empty($col[INCLUDE_ON_TASTE]) && strtolower($col[INCLUDE_ON_TASTE]) == 'yes')
					{
						self::$taste_bundle[$col['recipe_id']] = $col['recipe_id'];
					}
				}

				// get date string
				$DAO_menu = DAO_CFactory::create('menu');
				$DAO_menu->id = $menu_id;
				$DAO_menu->find(true);
				$date_str = date("_Y_m", strtotime($DAO_menu->menu_start));
				$date_str_m = date("m", strtotime($DAO_menu->menu_start));
				$date_str_no_leading_underscore = date("Y_m", strtotime($DAO_menu->menu_start));

				// create  bundles
				$introBundle = DAO_CFactory::create('bundle');
				$introBundle->menu_id = $menu_id;
				$introBundle->bundle_type = 'TV_OFFER';

				if (!$introBundle->find(true))
				{
					$introBundle->bundle_name = 'Meal Prep Starter Pack';
					$introBundle->number_items_required = 0;
					$introBundle->number_servings_required = 12;
					$introBundle->price = 79.00;
					$introBundle->price_shipping = 0.00;

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => 'Will Add Intro Bundle'
						);
					}
					else
					{
						$introBundle->insert();
						self::$changelog[] = array(
							'event' => 'Added Intro Bundle'
						);
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => 'Intro Bundle exists - no action'
					);
				}

				$tasteBundle = DAO_CFactory::create('bundle');
				$tasteBundle->menu_id = $menu_id;
				$tasteBundle->bundle_type = 'DREAM_TASTE';

				if (!$tasteBundle->find(true))
				{
					$tasteBundle->bundle_name = 'Meal Prep Workshop';
					$tasteBundle->number_items_required = 0;
					$tasteBundle->number_servings_required = 9;
					$tasteBundle->price = 60.00;
					$tasteBundle->price_shipping = 0.00;

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => 'Will Add Meal Prep Workshop Bundle'
						);
					}
					else
					{
						$tasteBundle->insert();
						self::$changelog[] = array(
							'event' => 'Added Meal Prep Workshop Bundle'
						);
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => 'Meal Prep Workshop Bundle exists - no action'
					);
				}

				if ($DAO_menu->isEnabled_Bundle_Fundraiser())
				{
					$fundraiserBundle = DAO_CFactory::create('bundle');
					$fundraiserBundle->menu_id = $menu_id;
					$fundraiserBundle->bundle_type = 'FUNDRAISER';

					if (!$fundraiserBundle->find(true))
					{
						$fundraiserBundle->bundle_name = 'Fundraiser';
						$fundraiserBundle->number_items_required = 0;
						$fundraiserBundle->number_servings_required = 9;
						$fundraiserBundle->price = 60.00;
						$fundraiserBundle->price_shipping = 0.00;

						if (self::$testMode)
						{
							self::$changelog[] = array(
								'event' => 'Will Add Fundraiser Bundle'
							);
						}
						else
						{
							$fundraiserBundle->insert();
							self::$changelog[] = array(
								'event' => 'Added Fundraiser Bundle'
							);
						}
					}
					else
					{
						self::$changelog[] = array(
							'event' => 'Fundraiser Bundle exists - no action'
						);
					}
				}

				// create mappings to items
				foreach (self::$intro_bundle as $recipeID)
				{
					$tempMenuItem = DAO_CFactory::create('menu_item');
					$tempMenuItem->query("select mi.id, mi.menu_item_name, mi.recipe_id, mi.pricing_type from menu_item mi
						join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and isnull(mmi.store_id) and mmi.menu_id = $menu_id
						where mi.recipe_id = $recipeID and mi.is_deleted = 0");
					while ($tempMenuItem->fetch())
					{
						$tempMappingObject = DAO_CFactory::create('bundle_to_menu_item');
						$tempMappingObject->bundle_id = $introBundle->id;
						$tempMappingObject->menu_item_id = $tempMenuItem->id;
						if (!$tempMappingObject->find(true))
						{
							if (self::$testMode)
							{
								self::$changelog[] = array(
									'event' => "Will Map (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " to Intro Bundle"
								);
							}
							else
							{
								$tempMappingObject->insert();
								self::$changelog[] = array(
									'event' => "Mapped (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " to Intro Bundle"
								);
							}
						}
						else
						{
							self::$changelog[] = array(
								'event' => "Item Exists (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " in Intro Bundle - no action"
							);
						}
					}
				}

				foreach (self::$taste_bundle as $recipeID)
				{
					$tempMenuItem = DAO_CFactory::create('menu_item');
					$tempMenuItem->query("select mi.id, mi.menu_item_name, mi.recipe_id, mi.pricing_type  from menu_item mi
						join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and isnull(mmi.store_id) and mmi.menu_id = $menu_id
						where mi.recipe_id = $recipeID and mi.is_deleted = 0 and mi.pricing_type = 'HALF'");
					if ($tempMenuItem->fetch())
					{
						$tempMappingObject = DAO_CFactory::create('bundle_to_menu_item');
						$tempMappingObject->bundle_id = $tasteBundle->id;
						$tempMappingObject->menu_item_id = $tempMenuItem->id;
						if (!$tempMappingObject->find(true))
						{
							if (self::$testMode)
							{
								self::$changelog[] = array(
									'event' => "Will Map (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " to Taste Bundle"
								);
							}
							else
							{
								$tempMappingObject->insert();
								self::$changelog[] = array(
									'event' => "Mapped (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " to Taste Bundle"
								);
							}
						}
						else
						{
							self::$changelog[] = array(
								'event' => "Item Exists (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " in Taste Bundle - no action"
							);
						}
					}
				}

				if ($DAO_menu->isEnabled_Bundle_Fundraiser())
				{
					foreach (self::$taste_bundle as $recipeID)
					{
						$tempMenuItem = DAO_CFactory::create('menu_item');
						$tempMenuItem->query("select mi.id, mi.menu_item_name, mi.recipe_id, mi.pricing_type  from menu_item mi
						join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and isnull(mmi.store_id) and mmi.menu_id = $menu_id
						where mi.recipe_id = $recipeID and mi.is_deleted = 0 and mi.pricing_type = 'HALF'");
						if ($tempMenuItem->fetch())
						{
							$tempMappingObject = DAO_CFactory::create('bundle_to_menu_item');
							$tempMappingObject->bundle_id = $fundraiserBundle->id;
							$tempMappingObject->menu_item_id = $tempMenuItem->id;
							if (!$tempMappingObject->find(true))
							{
								if (self::$testMode)
								{
									self::$changelog[] = array(
										'event' => "Will Map (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " to Fundraiser Bundle"
									);
								}
								else
								{
									$tempMappingObject->insert();
									self::$changelog[] = array(
										'event' => "Mapped (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " to Fundraiser Bundle"
									);
								}
							}
							else
							{
								self::$changelog[] = array(
									'event' => "Item Exists (" . $tempMenuItem->recipe_id . ") " . $tempMenuItem->menu_item_name . " - " . $tempMenuItem->pricing_type . " in Fundraiser Bundle - no action"
								);
							}
						}
					}
				}

				// insert themes and properties

				// STD Dream Taste
				$dt_props = DAO_CFactory::create('dream_taste_event_properties');
				$dt_props->menu_id = $menu_id;
				$dt_props->bundle_id = $tasteBundle->id;
				$dt_props->host_required = 1;
				$dt_props->can_rsvp_only = 0;
				$dt_props->can_rsvp_upgrade = 0;
				$dt_props->host_platepoints_eligible = 1;
				$dt_props->customer_coupon_eligible = 0;

				if (!$dt_props->find(true))
				{
					// create standard
					$theme_string = "dream_taste/standard/standard/" . $date_str_no_leading_underscore;

					$dt_theme = DAO_CFactory::create('dream_taste_event_theme');
					$dt_theme->title = "Meal Prep Workshop Standard";
					$dt_theme->title_public = "Meal Prep Workshop";
					$dt_theme->sort = '10';
					$dt_theme->sub_theme = 'standard';
					$dt_theme->sub_sub_theme = 'standard';
					$dt_theme->theme_string = $theme_string;
					$dt_theme->session_type = "DREAM_TASTE";
					$dt_theme->fadmin_acronym = "MPW";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Standard Meal Prep Workshop Theme and Properties"
						);
					}
					else
					{
						$dt_theme->insert();

						$dt_props->dream_taste_event_theme = $dt_theme->id;
						$dt_props->available_on_customer_site = 1;
						$dt_props->fundraiser_value = 0;
						$dt_props->password_required = 1;
						$dt_props->host_platepoints_eligible = 1;
						$dt_props->customer_coupon_eligible = 0;
						$dt_props->insert();
						self::$changelog[] = array(
							'event' => "Created Standard Meal Prep Workshop Theme and Properties"
						);
					}

					// create curbside
					$theme_string = "dream_taste/standard/curbside/" . $date_str_no_leading_underscore;

					$dt_theme = DAO_CFactory::create('dream_taste_event_theme');
					$dt_theme->title = "Meal Prep Workshop Curbside";
					$dt_theme->title_public = "Meal Prep Workshop Pick Up";
					$dt_theme->sort = '20';
					$dt_theme->sub_theme = 'standard';
					$dt_theme->sub_sub_theme = 'curbside';
					$dt_theme->theme_string = $theme_string;
					$dt_theme->session_type = "DREAM_TASTE";
					$dt_theme->fadmin_acronym = "MPWC";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Curbside Meal Prep Workshop Theme and Properties"
						);
					}
					else
					{
						$dt_theme->insert();

						$dt_props->dream_taste_event_theme = $dt_theme->id;
						$dt_props->available_on_customer_site = 1;
						$dt_props->fundraiser_value = 0;
						$dt_props->password_required = 1;
						$dt_props->host_platepoints_eligible = 1;
						$dt_props->customer_coupon_eligible = 0;
						$dt_props->insert();
						self::$changelog[] = array(
							'event' => "Created Curbside Meal Prep Workshop Theme and Properties"
						);
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => "Standard Meal Prep Workshop Theme and Properties exist - no action"
					);
				}

				// Donated Dream Taste
				$ddt_props = DAO_CFactory::create('dream_taste_event_properties');
				$ddt_props->menu_id = $menu_id;
				$ddt_props->bundle_id = $tasteBundle->id;
				$ddt_props->host_required = 1;
				$ddt_props->can_rsvp_only = 0;
				$ddt_props->can_rsvp_upgrade = 0;
				$ddt_props->host_platepoints_eligible = 0;
				$ddt_props->customer_coupon_eligible = 1;

				if (!$ddt_props->find(true))
				{

					$theme_string = "dream_taste/donated/standard/" . $date_str_no_leading_underscore;

					$ddt_theme = DAO_CFactory::create('dream_taste_event_theme');
					$ddt_theme->title = "Meal Prep Workshop Donated";
					$ddt_theme->title_public = "Meal Prep Workshop";
					$ddt_theme->sort = '30';
					$ddt_theme->sub_theme = 'donated';
					$ddt_theme->sub_sub_theme = 'standard';
					$ddt_theme->theme_string = $theme_string;
					$ddt_theme->session_type = "DREAM_TASTE";
					$ddt_theme->fadmin_acronym = "MPWD";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Meal Prep Workshop Donated Theme and Properties"
						);
					}
					else
					{
						$ddt_theme->insert();

						$ddt_props->dream_taste_event_theme = $ddt_theme->id;
						$ddt_props->available_on_customer_site = 1;
						$ddt_props->fundraiser_value = 0;
						$ddt_props->password_required = 1;
						$ddt_props->host_platepoints_eligible = 0;
						$ddt_props->customer_coupon_eligible = 1;
						$ddt_props->insert();
						self::$changelog[] = array(
							'event' => "Created Meal Prep Workshop Donated Theme and Properties"
						);
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => "Standard Meal Prep Workshop Theme and Properties exist - no action"
					);
				}

				// Openhouse Dream Taste
				$oh_props = DAO_CFactory::create('dream_taste_event_properties');
				$oh_props->menu_id = $menu_id;
				$oh_props->bundle_id = $tasteBundle->id;
				$oh_props->host_required = 0;
				$oh_props->can_rsvp_only = 0;
				$oh_props->can_rsvp_upgrade = 0;

				if (!$oh_props->find(true))
				{
					// create standard theme
					$theme_string = "dream_taste/open_house/standard/" . $date_str_no_leading_underscore;

					$oh_theme = DAO_CFactory::create('dream_taste_event_theme');
					$oh_theme->title = "Open House Standard";
					$oh_theme->title_public = "Open House";
					$oh_theme->sort = '40';
					$oh_theme->sub_theme = 'open_house';
					$oh_theme->sub_sub_theme = 'standard';
					$oh_theme->theme_string = $theme_string;
					$oh_theme->session_type = "DREAM_TASTE";
					$oh_theme->fadmin_acronym = "OH";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Open House Meal Prep Workshop Theme and Properties"
						);
					}
					else
					{

						$oh_theme->insert();

						$oh_props->dream_taste_event_theme = $oh_theme->id;
						$oh_props->available_on_customer_site = 1;
						$oh_props->fundraiser_value = 0;
						$oh_props->password_required = 2; // 2 = optional
						$oh_props->insert();
						self::$changelog[] = array(
							'event' => "Created Open House Meal Prep Workshop Theme and Properties"
						);
					}

					// create curbside theme
					$theme_string = "dream_taste/open_house/curbside/" . $date_str_no_leading_underscore;

					$oh_theme = DAO_CFactory::create('dream_taste_event_theme');
					$oh_theme->title = "Open House Curbside";
					$oh_theme->title_public = "Open House Pick Up";
					$oh_theme->sort = '50';
					$oh_theme->sub_theme = 'open_house';
					$oh_theme->sub_sub_theme = 'curbside';
					$oh_theme->theme_string = $theme_string;
					$oh_theme->session_type = "DREAM_TASTE";
					$oh_theme->fadmin_acronym = "OHC";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Curbside Open House Meal Prep Workshop Theme and Properties"
						);
					}
					else
					{

						$oh_theme->insert();

						$oh_props->dream_taste_event_theme = $oh_theme->id;
						$oh_props->available_on_customer_site = 1;
						$oh_props->fundraiser_value = 0;
						$oh_props->password_required = 2; // 2 = optional
						$oh_props->insert();
						self::$changelog[] = array(
							'event' => "Created Curbside Open House Meal Prep Workshop Theme and Properties"
						);
					}

					// create RSVP only  - New Guests
					$theme_string = "dream_taste/open_house/rsvp_only/new/" . $date_str_no_leading_underscore;

					$oh_theme = DAO_CFactory::create('dream_taste_event_theme');
					$oh_theme->title = "Open House";
					$oh_theme->title_public = "Open House RSVP Only - New Guests";
					$oh_theme->sort = '51';
					$oh_theme->sub_theme = 'open_house';
					$oh_theme->sub_sub_theme = 'rsvp_only';
					$oh_theme->theme_string = $theme_string;
					$oh_theme->session_type = "DREAM_TASTE";
					$oh_theme->fadmin_acronym = "OHRA";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Curbside Open House RSVP Only - New Guests Meal Prep Workshop Theme and Properties"
						);
					}
					else
					{

						$oh_theme->insert();

						$oh_props->dream_taste_event_theme = $oh_theme->id;
						$oh_props->menu_used_with_theme = 0;
						$oh_props->available_on_customer_site = 1;
						$oh_props->host_required = 0;
						$oh_props->fundraiser_value = 0;
						$oh_props->password_required = 2; // 2 = optional
						$oh_props->can_rsvp_only = 1;
						$oh_props->can_rsvp_upgrade = 0;
						$oh_props->existing_guests_can_attend = 0;
						$oh_props->insert();
						self::$changelog[] = array(
							'event' => "Created Curbside Open House RSVP Only - New Guests Meal Prep Workshop Theme and Properties"
						);
					}

					// create RSVP only  - All Guests
					$theme_string = "dream_taste/open_house/rsvp_only/all/" . $date_str_no_leading_underscore;

					$oh_theme = DAO_CFactory::create('dream_taste_event_theme');
					$oh_theme->title = "Open House";
					$oh_theme->title_public = "Open House RSVP Only - All Guests";
					$oh_theme->sort = '52';
					$oh_theme->sub_theme = 'open_house';
					$oh_theme->sub_sub_theme = 'rsvp_only';
					$oh_theme->theme_string = $theme_string;
					$oh_theme->session_type = "DREAM_TASTE";
					$oh_theme->fadmin_acronym = "OHRA";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Curbside Open House RSVP Only - All Guests Meal Prep Workshop Theme and Properties"
						);
					}
					else
					{

						$oh_theme->insert();

						$oh_props->dream_taste_event_theme = $oh_theme->id;
						$oh_props->menu_used_with_theme = 0;
						$oh_props->available_on_customer_site = 1;
						$oh_props->host_required = 0;
						$oh_props->fundraiser_value = 0;
						$oh_props->password_required = 2; // 2 = optional
						$oh_props->can_rsvp_only = 1;
						$oh_props->can_rsvp_upgrade = 0;
						$oh_props->existing_guests_can_attend = 1;
						$oh_props->insert();
						self::$changelog[] = array(
							'event' => "Created Curbside Open House RSVP Only - All Guests Meal Prep Workshop Theme and Properties"
						);
					}

					// create holiday theme
					if ($date_str_m == '11' || $date_str_m == '12')
					{
						$theme_string = "dream_taste/open_house/holiday/" . $date_str_no_leading_underscore;

						$oh_theme = DAO_CFactory::create('dream_taste_event_theme');
						$oh_theme->title = "Holiday Pickup Event";
						$oh_theme->title_public = "Open House Pick Up";
						$oh_theme->sort = '100';
						$oh_theme->sub_theme = 'open_house';
						$oh_theme->sub_sub_theme = 'holiday';
						$oh_theme->theme_string = $theme_string;
						$oh_theme->session_type = "DREAM_TASTE";
						$oh_theme->fadmin_acronym = "OHH";

						if (self::$testMode)
						{
							self::$changelog[] = array(
								'event' => "Will create Holiday Open House Meal Prep Workshop Theme and Properties"
							);
						}
						else
						{

							$oh_theme->insert();

							$oh_props->dream_taste_event_theme = $oh_theme->id;
							$oh_props->available_on_customer_site = 1;
							$oh_props->fundraiser_value = 0;
							$oh_props->password_required = 2; // 2 = optional
							$oh_props->insert();
							self::$changelog[] = array(
								'event' => "Created Holiday Open House Meal Prep Workshop Theme and Properties"
							);
						}
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => "Open House Meal Prep Workshop and Properties exist - no action"
					);
				}

				// Friends Night Out + Upgrade
				$fno_props = DAO_CFactory::create('dream_taste_event_properties');
				$fno_props->menu_id = $menu_id;
				$fno_props->bundle_id = $tasteBundle->id;
				$fno_props->host_required = 1;
				$fno_props->can_rsvp_only = 1;
				$fno_props->can_rsvp_upgrade = 1;

				if (!$fno_props->find(true))
				{

					$theme_string = "dream_taste/friends_night_out/standard/" . $date_str_no_leading_underscore;

					$fno_theme = DAO_CFactory::create('dream_taste_event_theme');
					$fno_theme->title = "Friends Night Out + Upgrade";
					$fno_theme->title_public = "Friends Night Out";
					$fno_theme->sort = '70';
					$fno_theme->sub_theme = 'friends_night_out';
					$fno_theme->sub_sub_theme = 'standard';
					$fno_theme->theme_string = $theme_string;
					$fno_theme->session_type = "DREAM_TASTE";
					$fno_theme->fadmin_acronym = "FNO";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Friends Night Out Theme and Properties"
						);
					}
					else
					{

						$fno_theme->insert();

						$fno_props->dream_taste_event_theme = $fno_theme->id;
						$fno_props->available_on_customer_site = 1;
						$fno_props->fundraiser_value = 0;
						$fno_props->password_required = 1;
						$fno_props->insert();
						self::$changelog[] = array(
							'event' => "Created Friends Night Out Theme and Properties"
						);
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => "Friends Night Out Theme and Properties exist - no action"
					);
				}

				// Friends Night Out + Upgrade Curbside
				$fno_props = DAO_CFactory::create('dream_taste_event_properties');
				$fno_props->menu_id = $menu_id;
				$fno_props->bundle_id = $tasteBundle->id;
				$fno_props->host_required = 1;
				$fno_props->can_rsvp_only = 1;
				$fno_props->can_rsvp_upgrade = 1;

				if (!$fno_props->find(true))
				{

					$theme_string = "dream_taste/friends_night_out/curbside/" . $date_str_no_leading_underscore;

					$fno_theme = DAO_CFactory::create('dream_taste_event_theme');
					$fno_theme->title = "Friends Night Out + Upgrade Curbside";
					$fno_theme->title_public = "Friends Night Out";
					$fno_theme->sort = '73';
					$fno_theme->sub_theme = 'friends_night_out';
					$fno_theme->sub_sub_theme = 'curbside';
					$fno_theme->theme_string = $theme_string;
					$fno_theme->session_type = "DREAM_TASTE";
					$fno_theme->fadmin_acronym = "FNOC";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Friends Night Out Theme and Properties"
						);
					}
					else
					{
						$fno_theme->insert();

						$fno_props->dream_taste_event_theme = $fno_theme->id;
						$fno_props->available_on_customer_site = 1;
						$fno_props->fundraiser_value = 0;
						$fno_props->password_required = 1;
						$fno_props->insert();
						self::$changelog[] = array(
							'event' => "Created Friends Night Out Theme and Properties"
						);
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => "Friends Night Out Theme and Properties exist - no action"
					);
				}

				// Friends Night Out RSVP Only
				$fno_props = DAO_CFactory::create('dream_taste_event_properties');
				$fno_props->menu_id = $menu_id;
				$fno_props->bundle_id = $tasteBundle->id;
				$fno_props->host_required = 1;
				$fno_props->can_rsvp_only = 1;
				$fno_props->can_rsvp_upgrade = 0;

				if (!$fno_props->find(true))
				{

					$theme_string = "dream_taste/friends_night_out/rsvp_only/" . $date_str_no_leading_underscore;

					$fno_theme = DAO_CFactory::create('dream_taste_event_theme');
					$fno_theme->title = "Friends Night Out RSVP Only";
					$fno_theme->title_public = "Friends Night Out";
					$fno_theme->sort = '75';
					$fno_theme->sub_theme = 'friends_night_out';
					$fno_theme->sub_sub_theme = 'rsvp_only';
					$fno_theme->theme_string = $theme_string;
					$fno_theme->session_type = "DREAM_TASTE";
					$fno_theme->fadmin_acronym = "FNO";

					if (self::$testMode)
					{
						self::$changelog[] = array(
							'event' => "Will create Friends Night Out Theme and Properties"
						);
					}
					else
					{

						$fno_theme->insert();

						$fno_props->dream_taste_event_theme = $fno_theme->id;
						$fno_props->available_on_customer_site = 1;
						$fno_props->fundraiser_value = 0;
						$fno_props->password_required = 1;
						$fno_props->insert();
						self::$changelog[] = array(
							'event' => "Created Friends Night Out Theme and Properties"
						);
					}
				}
				else
				{
					self::$changelog[] = array(
						'event' => "Friends Night Out Theme and Properties exist - no action"
					);
				}

				if ($DAO_menu->isEnabled_Bundle_Fundraiser())
				{
					// Fundraiser
					$fr_props = DAO_CFactory::create('dream_taste_event_properties');
					$fr_props->menu_id = $menu_id;
					$fr_props->bundle_id = $fundraiserBundle->id;
					$fr_props->host_required = 2;
					$fr_props->can_rsvp_only = 0;
					$fr_props->can_rsvp_upgrade = 0;

					if (!$fr_props->find(true))
					{
						// $10 standard theme
						$theme_string = "fundraiser/ten/standard/" . $date_str_no_leading_underscore;

						$fr_theme = DAO_CFactory::create('dream_taste_event_theme');
						$fr_theme->title = "Fundraiser $10";
						$fr_theme->title_public = "Fundraiser";
						$fr_theme->sort = '80';
						$fr_theme->sub_theme = 'ten';
						$fr_theme->sub_sub_theme = 'standard';
						$fr_theme->theme_string = $theme_string;
						$fr_theme->session_type = "FUNDRAISER";
						$fr_theme->fadmin_acronym = "F";

						if (self::$testMode)
						{
							self::$changelog[] = array(
								'event' => "Will create Fundraiser Theme and Properties"
							);
						}
						else
						{

							$fr_theme->insert();

							$fr_props->dream_taste_event_theme = $fr_theme->id;
							$fr_props->available_on_customer_site = 1;
							$fr_props->fundraiser_value = 10;
							$fr_props->password_required = 0;
							$fr_props->insert();
							self::$changelog[] = array(
								'event' => "Created Fundraiser Theme and Properties"
							);
						}

						// $10 curbside
						$theme_string = "fundraiser/ten/curbside/" . $date_str_no_leading_underscore;

						$fr_theme = DAO_CFactory::create('dream_taste_event_theme');
						$fr_theme->title = "Fundraiser $10 Curbside";
						$fr_theme->title_public = "Fundraiser Pick Up";
						$fr_theme->sort = '90';
						$fr_theme->sub_theme = 'ten';
						$fr_theme->sub_sub_theme = 'curbside';
						$fr_theme->theme_string = $theme_string;
						$fr_theme->session_type = "FUNDRAISER";
						$fr_theme->fadmin_acronym = "FC";

						if (self::$testMode)
						{
							self::$changelog[] = array(
								'event' => "Will create Curbside Fundraiser Theme and Properties"
							);
						}
						else
						{

							$fr_theme->insert();

							$fr_props->dream_taste_event_theme = $fr_theme->id;
							$fr_props->available_on_customer_site = 1;
							$fr_props->fundraiser_value = 10;
							$fr_props->password_required = 0;
							$fr_props->insert();
							self::$changelog[] = array(
								'event' => "Created Curbside Fundraiser Theme and Properties"
							);
						}
					}
					else
					{
						self::$changelog[] = array(
							'event' => "Curbside Fundraiser Theme and Properties exist - no action"
						);
					}
				}

				$uberObject->query('COMMIT;');

				if (!self::$testMode)
				{
					$tpl->setStatusMsg('<p>Bundles imported: <a class="btn btn-primary btn-sm" href="/backoffice/manage_bundle" target="_blank">Review</a></p>');
				}
			}
			catch (exception $e)
			{
				$uberObject->query('ROLLBACK;');
				$tpl->setErrorMsg('Bundles import failed: exception occurred</br>Reason: ' . $e->getMessage());
				CLog::RecordException($e);
			}
		}

		/* Import menu fadmin template stuff below */

		$Menu = DAO_CFactory::create('menu');
		$Menu->query("SELECT
			IQ.id,
			IQ.menu_name,
			IF(ISNULL(IQ.bundle_id),FALSE,TRUE) AS imported
			FROM (SELECT
				menu.id,
				menu.menu_name,
				bundle.id as bundle_id
				FROM menu
				LEFT JOIN bundle ON bundle.menu_id = menu.id AND bundle.is_deleted = 0
			WHERE menu.is_deleted = '0'
				GROUP BY menu.id
				ORDER BY menu.id DESC
				LIMIT 10) AS IQ
			ORDER BY IQ.id DESC
			LIMIT 6");

		$menuArray = array(0 => 'Select Menu');
		$menu_count = 0;

		while ($Menu->fetch())
		{
			$menuArray[$Menu->id] = array(
				'title' => $Menu->menu_name . (!empty($Menu->imported) ? ' (imported)' : ''),
				'data' => array(
					'data-imported' => (!empty($Menu->imported) ? 'true' : 'false')
				)
			);
			$menu_count++;
		}

		$Form = new CForm();

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "menu",
			CForm::options => $menuArray
		));

		$tpl->assign('form_menu', $Form->Render());
		$tpl->assign('menu_count', $menu_count);

		if ($didImport)
		{
			$tpl->assign('changelog', self::$changelog);
		}
	}
}

?>