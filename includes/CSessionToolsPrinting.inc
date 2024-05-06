<?php
require_once('fpdf/class_multicelltag.php');
require_once('page/customer/session_menu.php');
require_once 'DAO/BusinessObject/CBundle.php';
require_once('phplib/phpqrcode/qrlib.php');

class CSessionToolsData
{
	public $menu_id = false;
	public $menu_id_next = false;
	public $store_id = false;
	public $user_id = false;

	public $storeInfo = false;
	public $menuInfo = false;
	public $bundleInfo = false;
	public $menuItems = false;
	public $menuItems_Next = false;
	public $orderItems = false;
	public $recipeIds = false;
	public $menuItemIds = false;
	public $nutritionalItems = false;
	public $userInfo = false;
	public $globalRatings = false;

	public $customizations = null;

	public $boxes = false;

	function __construct($menu_id, $store_id)
	{
		$this->menu_id = $menu_id;
		$this->menu_id_next = $menu_id + 1;
		$this->store_id = $store_id;
	}

	function loadStaticData()
	{
		$this->storeInfo = DAO_CFactory::create('store');
		$this->storeInfo->id = $this->store_id;
		$this->storeInfo->find(true);

		$this->menuInfo[$this->menu_id] = DAO_CFactory::create('menu');
		$this->menuInfo[$this->menu_id]->id = $this->menu_id;
		$this->menuInfo[$this->menu_id]->find(true);

		$this->menuInfo[$this->menu_id_next] = DAO_CFactory::create('menu');
		$this->menuInfo[$this->menu_id_next]->id = $this->menu_id_next;
		$this->menuInfo[$this->menu_id_next]->find(true);

		$this->globalRatings = array();

		$globalRatingsDAO = DAO_CFactory::create('menu_item');
		$globalRatingsDAO->query("SELECT mmi.menu_id, mi.recipe_id, grrc.global_rating as avg_rating
				FROM menu_to_menu_item AS mmi
				JOIN menu_item AS mi ON mi.id = mmi.menu_item_id AND mi.menu_item_category_id < 5 AND mi.is_store_special = 0 AND mi.is_deleted = 0
				LEFT JOIN global_recipe_ratings_cache grrc on grrc.recipe_id = mi.recipe_id
				WHERE mmi.store_id = '{$this->store_id}' AND (mmi.menu_id = '{$this->menu_id}' OR mmi.menu_id = '{$this->menu_id_next}') AND mmi.is_deleted = 0  
				GROUP BY mmi.menu_id, mi.recipe_id
				ORDER BY mi.recipe_id ASC");

		while ($globalRatingsDAO->fetch())
		{
			$this->globalRatings[$globalRatingsDAO->recipe_id] = $globalRatingsDAO->avg_rating;
		}

		$this->menuInfo[$this->menu_id]->marketingMessage = $this->marketingMessage($this->menuInfo[$this->menu_id]);
		$this->menuInfo[$this->menu_id_next]->marketingMessage = $this->marketingMessage($this->menuInfo[$this->menu_id_next]);

		if (CStore::storeSupportsMealCustomization($this->storeInfo))
		{
			$this->customizations = OrdersCustomization::createDefaultMealCustomizationObj();
		}
	}

	function loadPartialUserData($user_id)
	{
		$this->user_id = $user_id;
		$this->userInfo = true;

		$this->menuInfo[$this->menu_id]->marketingMessage = $this->marketingMessage($this->menuInfo[$this->menu_id]);
		$this->menuInfo[$this->menu_id_next]->marketingMessage = $this->marketingMessage($this->menuInfo[$this->menu_id_next]);
	}

	function loadUserData($booking)
	{
		$this->user_id = $booking['user_id'];

		$this->userInfo = $booking['user'];

		$this->userInfo->booking = $booking;

		if ($booking['order_id'])
		{
			$order = DAO_CFactory::create('orders');
			$order->id = $booking['order_id'];
			$order->find(true);

			$this->userInfo->getPlatePointsSummary($order);

			$pending = $order->getPaymentsPending();
			$balanceDue = COrders::std_round($order->grand_total - $pending);
			$this->userInfo->orderInfo = COrders::buildOrderDetailArrays($this->userInfo, $order);
			$this->userInfo->referencePayment = CPayment::getPaymentsStoredForReference($this->user_id, $this->store_id);
			$this->userInfo->userData = $booking['user']->userData;
			$this->userInfo->nextSession = $booking['user']->nextSession;
		}

		if (CStore::storeSupportsMealCustomization($booking['store_id']))
		{
			if (is_object($this->userInfo))
			{
				//set based on user
				$this->customizations = $this->userInfo->getMealCustomizationPreferences();
			}
		}

		$this->menuInfo[$this->menu_id]->marketingMessage = $this->marketingMessage($this->menuInfo[$this->menu_id]);
		$this->menuInfo[$this->menu_id_next]->marketingMessage = $this->marketingMessage($this->menuInfo[$this->menu_id_next]);
	}

	function marketingMessage($menuInfo)
	{
		// get month for menu for determining which messages are displayed
		$month = date('M', strtotime($menuInfo->menu_name));
		$year = date('Y', strtotime($menuInfo->menu_name));

		// user specific messages
		if ($this->userInfo)
		{
			/* 1st Visit: */
			if ($this->userInfo->booking['this_is_first_session'])
			{
				return "Welcome to Dream Dinners! We look forward to helping you serve easy, homemade meals to your family.";
			}

			/* Reacquired Guest (hasn't been to a session for 1 year or more): */
			if (strtotime($this->userInfo->booking['last_session_attended']) < strtotime($this->userInfo->booking['session_start'] . '-1 year'))
			{
				return "Welcome Back! Let us help you spend less time in the kitchen and more time doing what you love.";
			}

			/* Happy Birthday (guest's birthday month): */
			if (date('M', strtotime($this->userInfo->booking['session_start'])) == $this->userInfo->userData[BIRTH_MONTH_FIELD_ID] || date('n', strtotime($this->userInfo->booking['session_start'])) == $this->userInfo->userData[BIRTH_MONTH_FIELD_ID])
			{
				return "Happy Birthday! Savor some special moments with your family and of course, a slice of birthday cake.";
			}

			/* 2nd Visit, if not enrolled in PLATEPOINTS: */
			if ($this->userInfo->booking['bookings_made'] == '1' && $this->userInfo->platePointsData['status'] != 'active')
			{
				return "Have you signed up for PLATEPOINTS? Enroll today to start earning rewards.";
			}

			/* 2nd Visit, if they are enrolled in PLATEPOINTS: */
			if ($this->userInfo->booking['bookings_made'] == '1' && $this->userInfo->platePointsData['status'] == 'active')
			{
				return "Let us help you keep track of your favorites by rating your meals at DreamDinners.com.";
			}

			/* 3rd Visit: */
			if ($this->userInfo->booking['bookings_made'] == '2')
			{
				return "Don't forget to round out your plate with sides and sweets from our Sides & Sweets Freezer.";
			}

			/* 4th Visit: */
			if ($this->userInfo->booking['bookings_made'] == '3')
			{
				//return "Don't forget to round out your plate with sides and sweets from our Sides & Sweets Freezer.";
				return "";
			}

			/* 12th Visit: */
			if ($this->userInfo->booking['bookings_made'] == '11')
			{
				return "You've reached your 12th visit! Thanks for letting us help you serve easy, homemade meals to your family.";
			}

			/* 15th Visit, if they have not hosted a TASTE: */
			if ($this->userInfo->booking['bookings_made'] == '15')
			{
				return "Let us help you keep track of your favorites by rating your meals at DreamDinners.com.";
			}

			/* 50th Visit: */
			if ($this->userInfo->booking['bookings_made'] == '49')
			{
				return "Thank you for sharing your family dinner time with us! Congrats on your 50th visit.";
			}

			/* 100th Visit: */
			if ($this->userInfo->booking['bookings_made'] == '99')
			{
				return "You've reached your 100th visit! Thanks for letting us help you serve easy, homemade meals to your family.";
			}

			/* 150th Visit: */
			if ($this->userInfo->booking['bookings_made'] == '149')
			{
				return "Thank you for sharing your family dinnertime with us! Congrats on your 150th visit.";
			}

			/* 200th Visit: */
			if ($this->userInfo->booking['bookings_made'] == '199')
			{
				return "You've reached your 200th visit! Thanks for letting us help you serve easy, homemade meals to your family.";
			}
		}

		/* Specific Monthly Messages for anyone who doesn't fit into the above categories: */

		/* Jan: */
		if ($month == 'Jan')
		{
			return "Don't let your friends miss out on easy, homemade dinners. Invite them to your next session & get perks.";
		}

		/* Feb: */
		if ($month == 'Feb')
		{
			return "Need a night out with food & fun? Get your friends together & assemble your meals at a private party at Dream Dinners.";
		}

		/* Mar: */
		if ($month == 'Mar')
		{
			return "Let us help you keep track of your favorites by rating your meals at DreamDinners.com.";
		}

		/* Apr: */
		if ($month == 'Apr')
		{
			return "Round out your plate with your favorite veggies, breads and desserts from the Sides & Sweets Freezer.";
		}

		/* May: */
		if ($month == 'May')
		{
			return "Need a night out with food & fun? Get your friends together & assemble your meals at a private party at Dream Dinners.";
		}

		/* Jun: */
		if ($month == 'Jun')
		{
			return "Let us help you keep track of your favorites by rating your meals at DreamDinners.com.";
		}

		/* July: */
		if ($month == 'Jul')
		{
			//return "Relax in the Sun, Dinner's Done. Sign up for your July session & earn more chances to win great prizes!";
			return "";
		}

		/* August: */
		if ($month == 'Aug')
		{
			return "Let us help you keep track of your favorites by rating your meals at DreamDinners.com.";
		}

		/* Sept.: */
		if ($month == 'Sep')
		{
			return "Don't let your friends miss out on easy, homemade dinners. Invite them to your next session & get perks.";
		}

		/* Oct: */
		if ($month == 'Oct')
		{
			return "Round out your plate with your favorite veggies, breads and desserts from the Sides & Sweets Freezer.";
		}

		/* Nov: */
		if ($month == 'Nov')
		{
			return "Need a night out with food & fun? Get your friends together & assemble your meals at a private party at Dream Dinners.";
		}

		/* Dec: */
		if ($month == 'Dec')
		{
			return "Turn your favorite Dream Dinners into easy, homemade appetizers for all your holiday entertaining.";
		}
	}

	function loadMenuData($booking = false, $next_month = false, $user_id = false)
	{
		$is_visible_only = true;

		if ($next_month)
		{
			$menu_id = $this->menu_id_next;
		}
		else
		{
			$menu_id = $this->menu_id;
		}

		if ($booking)
		{
			$user_id = $booking['user_id'];
		}

		if ($user_id)
		{
			$is_visible_only = false;
		}

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->store_id,
			'join_food_survey_user_id' => $user_id,
			'menu_to_menu_item_is_visible' => $is_visible_only,
			'exclude_menu_item_is_bundle' => true,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => true,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$menuItems = array();

		while ($DAO_menu_item->fetch())
		{
			if ($DAO_menu_item->isVisible())
			{
				if (empty($menuItems[$DAO_menu_item->recipe_id]['entree']) || $DAO_menu_item->id == $DAO_menu_item->entree_id)
				{
					$menuItems[$DAO_menu_item->recipe_id]['entree'] = $DAO_menu_item->cloneObj();
				}

				$menuItems[$DAO_menu_item->recipe_id]['menu_item'][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();

				// track all recipes used
				$this->recipeIds[$DAO_menu_item->recipe_id] = $DAO_menu_item->recipe_id;
				$this->menuItemIds[$DAO_menu_item->id] = $DAO_menu_item->id;
			}
		}
		$DAO_menu_item->free();

		if ($next_month)
		{
			$this->menuItems_Next = $menuItems;
		}
		else
		{
			$this->menuItems = $menuItems;
		}

		$this->minimumNote = 'Standard order size is 6 Lrg or 12 Med dinners or a combination of both.';
		$minimum = COrderMinimum::fetchInstance(CorderMinimum::STANDARD_ORDER_TYPE, $this->store_id, $menu_id);
		if (!is_null($minimum))
		{
			$this->minimumNote = $this->formulateStandardMinimumMessage($minimum);
		}
	}

	function loadBundleMenuData($bundle_type = 'TV_OFFER')
	{
		$this->bundleInfo = CBundle::getBundleInfoByMenuAndType($this->menu_id, $bundle_type);

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $this->menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->store_id,
			'join_bundle_to_menu_item_bundle_id' => $this->bundleInfo->id
		));

		$menuItems = array();

		while ($DAO_menu_item->fetch())
		{
			if ($DAO_menu_item->isVisible())
			{
				if (empty($menuItems[$DAO_menu_item->recipe_id]['entree']) || $DAO_menu_item->id == $DAO_menu_item->entree_id)
				{
					$menuItems[$DAO_menu_item->recipe_id]['entree'] = $DAO_menu_item->cloneObj();
				}

				$menuItems[$DAO_menu_item->recipe_id]['menu_item'][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();

				// track all recipes used
				$this->recipeIds[$DAO_menu_item->recipe_id] = $DAO_menu_item->recipe_id;
				$this->menuItemIds[$DAO_menu_item->id] = $DAO_menu_item->id;
			}
		}
		$DAO_menu_item->free();

		$this->menuItems = $menuItems;
	}

	private function formulateStandardMinimumMessage($minimum)
	{

		if ($minimum->getMinimumType() == COrderMinimum::ITEM)
		{
			return 'Standard order is ' . $minimum->getMinimum() . ' meals.';
		}

		if ($minimum->getMinimumType() == COrderMinimum::SERVING)
		{
			$minimumMessage = 'Standard order size is %s or a combination of both.';
			$medium = ceil($minimum->getMinimum() / 3);
			$large = floor($minimum->getMinimum() / 6);

			$additionalMediumOnLarge = $minimum->getMinimum() % 6;

			if ($additionalMediumOnLarge >= 3)
			{
				$large++;
			}

			$mediumPlural = $medium > 1 ? 's' : '';
			$largePlural = $large > 1 ? 's' : '';

			$servingMessage = '%s medium meal' . $mediumPlural;

			$largeMessage = ' or %s large meal' . $largePlural;
			if ($large > 0)
			{
				$servingMessage .= $largeMessage;
			}
			$servingMessage = sprintf($servingMessage, $medium, $large);

			if ($minimum->getMinimum() >= 6 && ($minimum->getMinimum() % 6) > 3)
			{
				$servingMessage = '%s medium meal' . $mediumPlural . ' or %s large %s meal' . $largePlural . '.';
				$servingMessage = sprintf($servingMessage, $medium, $large, '');
			}
			else if ($minimum->getMinimum() >= 6 && ($minimum->getMinimum() % 6) > 0)
			{
				$servingMessage = '%s medium meal' . $mediumPlural . ' or %s large %s meal' . $largePlural . '.';
				$servingMessage = sprintf($servingMessage, $medium, $large, 'and 1 medium');
			}
			$minimumMessage = sprintf($minimumMessage, $servingMessage);

			return $minimumMessage;
		}

		return '';
	}

	function loadOrderedItemData($order_ids)
	{
		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $this->menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->store_id,
			'join_order_item_order_id' => $order_ids,
			'join_order_item_order' => 'INNER',
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
			'groupBy' => 'order_item.id'
		));

		$menuItems = array();
		while ($DAO_menu_item->fetch())
		{
			$order_id_containing_item = explode(',',$DAO_menu_item->order_ids);
			foreach($order_id_containing_item as $order_id)
			{
				if (empty($menuItems[$order_id][$DAO_menu_item->recipe_id]['entree']) || $DAO_menu_item->id == $DAO_menu_item->entree_id)
				{
					$menuItems[$order_id][$DAO_menu_item->recipe_id]['entree'] = $DAO_menu_item->cloneObj();
				}

				$menuItems[$order_id][$DAO_menu_item->recipe_id]['menu_item'][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();

			}

			// track all recipes used
			$this->recipeIds[$DAO_menu_item->recipe_id] = $DAO_menu_item->recipe_id;
			$this->menuItemIds[$DAO_menu_item->id] = $DAO_menu_item->id;
		}

		$this->orderItems = $menuItems;
	}

	function loadOrderedItemDataDelivered($order_ids)
	{
		$order_ids = implode(',', $order_ids);

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $this->menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->store_id,
			'join_order_item_order_id' => $order_ids,
			'join_order_item_order' => 'INNER',
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
			'groupBy' => 'order_item.id'
		));

		$orderItems = DAO_CFactory::create('order_item');

		$q = "SELECT
    		oi.id as item_id,
			b.id AS booking_id,
			b.order_id,
			b.user_id,
			b.session_id,
			oi.menu_item_id,
			bu.menu_id,
			r.recipe_id,
			mi.pricing_type,
			mi.menu_item_category_id,
			oi.item_count,
			r.recipe_name,
			r.ltd_menu_item_value,
			r.cooking_method,
			b.`status`,
			b.booking_type,
			fs.rating,
			fs.would_order_again as favorite,
			mi.prep_time,
			mi.serving_suggestions,
			mi.best_prepared_by,
       		oi.box_instance_id,
       		oi.bundle_id,
       		box.title
			FROM booking AS b
			INNER JOIN order_item AS oi ON oi.order_id = b.order_id AND oi.is_deleted = '0'
			INNER JOIN menu_item AS mi ON mi.id = oi.menu_item_id AND mi.is_deleted = '0'
			INNER JOIN box_instance AS bi ON bi.id = oi.box_instance_id AND bi.is_deleted = '0' # moved this line up 
			INNER JOIN bundle bu on bu.id = bi.bundle_id # get the bundle from box_instance
			INNER JOIN recipe AS r ON r.override_menu_id = bu.menu_id AND r.recipe_id = mi.recipe_id AND r.is_deleted = '0' # then join menu on the bundle’s menu id
			LEFT JOIN box AS box ON box.id = bi.box_id AND box.is_deleted = '0'
			LEFT JOIN food_survey AS fs ON fs.user_id = b.user_id AND fs.recipe_id = r.recipe_id AND fs.is_active = '1' AND fs.is_deleted = '0'
			WHERE b.order_id IN (" . $order_ids . ")
			AND b.status = '" . CBooking::ACTIVE . "'
			AND b.is_deleted = '0'";
		$orderItems->query($q);
		$this->boxes = array();
		while ($orderItems->fetch())
		{
			$item = array();
			$item['item_id'] = $orderItems->item_id;
			$item['recipe_id'] = $orderItems->recipe_id;
			$item['recipe_name'] = $orderItems->recipe_name;
			$item['ltd_menu_item_value'] = $orderItems->ltd_menu_item_value;
			$item['cooking_method'] = $orderItems->cooking_method;
			$item['rating'] = $orderItems->rating;
			$item['favorite'] = $orderItems->favorite;
			$item['prep_time'] = $orderItems->prep_time;
			$item['serving_suggestions'] = $orderItems->serving_suggestions;
			$item['best_prepared_by'] = $orderItems->best_prepared_by;
			$item['menu_item_category_id'] = $orderItems->menu_item_category_id;
			$item[$orderItems->pricing_type] = $orderItems->item_count;
			if (array_key_exists($orderItems->title, $this->boxes))
			{
				//add to box
				$this->boxes[$orderItems->title][] = $item;
			}
			else
			{
				//create box and add item
				$this->boxes[$orderItems->title] = array();
				$this->boxes[$orderItems->title][] = $item;
			}

			// track all recipes used
			$this->recipeIds[$orderItems->recipe_id] = $orderItems->recipe_id;
			$this->menuItemIds[$orderItems->menu_item_id] = $orderItems->menu_item_id;
		}
	}
}

class CSessionToolsPDF extends FPDF_MULTICELLTAG
{
	const kPAGE_WIDTH = 215.9;
	const kPAGE_HEIGHT = 279.4;
	const kLEFT_MARGIN = 8.0;
	const kRIGHT_MARGIN = 207.9;
	const kTOP_MARGIN = 5.0;
	const kHORZ_CENTER = 107.95;
	const lPAGE_WIDTH = 279.4;
	const lPAGE_HEIGHT = 215.9;

	function __construct()
	{
		parent::__construct('P', 'mm', array(
			self::kPAGE_WIDTH,
			self::kPAGE_HEIGHT
		));
	}

	function RotatedText($txt, $angle)
	{
		$txt = str_replace("\t", "<ttags>\t</ttags>", $txt);
		$txt = str_replace("\r", "", $txt);

		$sWork = new String_TAGS(5);

		//get the string divisions by tags
		$this->wt_DataInfo = $sWork->get_tags($txt);

		$this->ApplyStyle($this->wt_DataInfo[0]['tag']);

		//Text rotated around its origin
		$this->Rotate($angle, $this->x, $this->y);
		$this->Text($this->x, $this->y, $this->wt_DataInfo[0]['text']);
		$this->Rotate(0);
	}

	function Rotate($angle, $x = -1, $y = -1)
	{
		if ($x == -1)
		{
			$x = $this->x;
		}

		if ($y == -1)
		{
			$y = $this->y;
		}

		if (isset($this->angle) && $this->angle != 0)
		{
			$this->_out('Q');
		}

		$this->angle = $angle;

		if ($angle != 0)
		{
			$angle *= M_PI / 180;
			$c = cos($angle);
			$s = sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}
}

class CSessionToolsPDFDrawer
{
	const kHEADER_ROW_HEIGHT = 13;
	const kKEADER_ROW_Y = 26;

	private $data = null;

	private $pdf = null;

	private $showBorders = false; // for debugging

	public $color = array(

		// #512B1B
		'brown' => array(
			'r' => '81',
			'g' => '43',
			'b' => '27'
		),
		// #5c6670
		'new_gray' => array(
			'r' => '92',
			'g' => '102',
			'b' => '112'
		),
		// #00583D
		'dark_green' => array(
			'r' => '0',
			'g' => '88',
			'b' => '61'
		),
		// #5b5e18
		'new_dark_green' => array(
			'r' => '91',
			'g' => '94',
			'b' => '24'
		),
		// #B0BC22
		'light_green' => array(
			'r' => '176',
			'g' => '188',
			'b' => '34'
		),
		// #444444
		'grey' => array(
			'r' => '68',
			'g' => '68',
			'b' => '68'
		),
		// #f5f5f5
		'grey_price' => array(
			'r' => '245',
			'g' => '245',
			'b' => '245'
		),
		// #45cfd1;
		'cyan' => array(
			'r' => '69',
			'g' => '207',
			'b' => '209'
		)

	);

	function __construct()
	{
		$this->pdf = new CSessionToolsPDF();

		$this->pdf->SetAutoPageBreak(false);

		$this->pdf->SetStyle("hs", "helvetica", "", 11, "0,0,0");
		$this->pdf->SetStyle("hsb", "helvetica", "B", 11, "0,0,0");
		$this->pdf->SetStyle("hsi", "helvetica", "BI", 10, "0,0,0");
		$this->pdf->SetStyle("h8b", "helvetica", "B", 8, "0,0,0");
		$this->pdf->SetStyle("h8", "helvetica", "", 8, "0,0,0");
		$this->pdf->SetStyle("h8w", "helvetica", "", 8, "255,255,255");
		$this->pdf->SetStyle("h9b", "helvetica", "B", 9, "0,0,0");
		$this->pdf->SetStyle("h9", "helvetica", "", 9, "0,0,0");
		$this->pdf->SetStyle("h7b", "helvetica", "B", 7, "0,0,0");
		$this->pdf->SetStyle("h7bl", "helvetica", "B", 7, "232,119,34"); // donation color
		$this->pdf->SetStyle("h7", "helvetica", "", 7, "0,0,0");
		$this->pdf->SetStyle("h6", "helvetica", "", 6, "0,0,0");
		$this->pdf->SetStyle("h5", "helvetica", "", 5, "0,0,0");

		/*
				$this->pdf->SetStyle("tvlb", "times", "B", 18, "0,0,0");
				$this->pdf->SetStyle("tlb", "times", "B", 15, "0,0,0");
				$this->pdf->SetStyle("t14", "times", "", 14, "0,0,0");
				$this->pdf->SetStyle("thb", "times", "B", 12, "0,0,0");
				$this->pdf->SetStyle("t", "times", "B", 11, "0,0,0");
				$this->pdf->SetStyle("tm", "times", "", 10, "0,0,0");
				$this->pdf->SetStyle("t10b", "times", "B", 10, "0,0,0");
				$this->pdf->SetStyle("t12b", "times", "B", 12, "0,0,0");
				$this->pdf->SetStyle("t9b", "times", "B", 9, "0,0,0");
				$this->pdf->SetStyle("t8", "times", "", 8, "0,0,0");
		 */

		$this->pdf->SetStyle("tvlb", "arial", "B", 18, "0,0,0");
		$this->pdf->SetStyle("tlb", "arial", "B", 15, "0,0,0");
		$this->pdf->SetStyle("t14", "arial", "", 14, "0,0,0");
		$this->pdf->SetStyle("t14b", "arial", "B", 14, "0,0,0");
		$this->pdf->SetStyle("t12", "arial", "", 12, "0,0,0");
		$this->pdf->SetStyle("t11", "arial", "", 11, "0,0,0");
		$this->pdf->SetStyle("thb", "arial", "B", 12, "0,0,0");
		$this->pdf->SetStyle("t", "arial", "B", 11, "0,0,0");
		$this->pdf->SetStyle("tm", "arial", "", 10, "0,0,0");
		$this->pdf->SetStyle("tmwb", "arial", "b", 9.5, "255,255,255");
		$this->pdf->SetStyle("tmwbs", "arial", "b", 8.5, "255,255,255");
		$this->pdf->SetStyle("t1b", "arial", "B", 10, "0,0,0");
		$this->pdf->SetStyle("t12b", "arial", "B", 12, "0,0,0");
		$this->pdf->SetStyle("t9", "arial", "", 9, "0,0,0");
		$this->pdf->SetStyle("t9w", "arial", "", 9, "255,255,255");
		$this->pdf->SetStyle("t9b", "arial", "B", 9, "0,0,0");
		$this->pdf->SetStyle("t9bo", "arial", "B", 9, "197,104,22");
		$this->pdf->SetStyle("t8", "arial", "", 8, "0,0,0");
		$this->pdf->SetStyle("t8b", "arial", "B", 8, "0,0,0");
		$this->pdf->SetStyle("t8bl", "arial", "B", 8, "232,119,34"); // ltd menu item color
		$this->pdf->SetStyle("t8bb", "arial", "B", 8, "0,0,0"); // fast lane title color
		$this->pdf->SetStyle("t8bo", "arial", "B", 8, "197,104,22");
		$this->pdf->SetStyle("t6", "arial", "", 6, "0,0,0");
		$this->pdf->SetStyle("t7", "arial", "", 7, "0,0,0");
		$this->pdf->SetStyle("t7b", "arial", "B", 7, "0,0,0");
		$this->pdf->SetStyle("t8g", "arial", "", 8, "46,92,66");
		$this->pdf->SetStyle("t8br", "arial", "B", 8, "81,43,27");  // brown #512B1B
		$this->pdf->SetStyle("t8gr", "arial", "B", 8, "92,102,112");  // gray #5c6670
		$this->pdf->SetStyle("t8gb", "arial", "B", 8, "46,92,66");
		$this->pdf->SetStyle("t9rb", "arial", "B", 9, "217,38,28"); // red
		$this->pdf->SetStyle("t9lg", "arial", "B", 9, "176,188,34"); // light_green
		$this->pdf->SetStyle("pt8b", "arial", "B", 7.5, "255,255,255");
	}

	public function setData($inData)
	{
		$this->data = $inData;
	}

	protected function printLogo($x, $y, $w, $h, $menu_id, $printMonth = false)
	{
		$colorSelect = 0;
		$logoX = $x;

		if ($menu_id)
		{
			$colorSelect = $menu_id % 3;
		}

		switch ($colorSelect)
		{
			case 0:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/blue_logo.jpg';
				break;
			case 1:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/brown_logo.jpg';
				break;
			case 2:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/light_green_logo.jpg';
				break;
			default:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/light_green_logo.jpg';
				break;
		}

		$this->pdf->Image($logo_image_path, $logoX, $y, $w, $h);

		if ($printMonth)
		{
			$this->pdf->SetXY(CSessionToolsPDF::kRIGHT_MARGIN - 60, $y + 4);
			$this->pdf->MultiCellTag(60, 3, "<tvlb>{$this->data->menuInfo[$menu_id]->menu_name}</tvlb>", $this->showBorders, 'R');
		}
	}

	protected function printLogo_RecipeExpert($x, $y, $w, $h, $menu_id, $printMonth = false)
	{
		$colorSelect = 0;

		if ($menu_id)
		{
			$colorSelect = $menu_id % 3;
		}

		switch ($colorSelect)
		{
			case 0:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/blue_logo.jpg';
				break;
			case 1:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/brown_logo.jpg';
				break;
			case 2:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/light_green_logo.jpg';
				break;
			default:
				$logo_image_path = ASSETS_PATH . '/pdf_label/paper_menu/light_green_logo.jpg';
				break;
		}

		$this->pdf->Image($logo_image_path, $x, $y, $w, $h);

		if ($printMonth)
		{
			$this->pdf->SetXY($x + 108, $y + 4);
			$this->pdf->MultiCellTag($w + 20, 3, "<tvlb>{$this->data->menuInfo[$menu_id]->menu_name}</tvlb>", $this->showBorders, 'R');
		}
	}

	protected function printHeader($x, $y, $menu_id)
	{
		$width = 140;

		$salutation = '';
		if (!empty($this->data->userInfo->firstname))
		{
			$salutation = $this->data->userInfo->firstname . ' ' . $this->data->userInfo->lastname;
		}

		$this->pdf->SetXY(40, $y + 1.5);
		$this->pdf->MultiCellTag($width, 6, "<t14>Welcome</t14> <t14b>{$salutation}</t14b>", $this->showBorders, 'L');

		$this->pdf->SetXY(40, $y + 7.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>" . iconv('UTF-8', 'windows-1252', $this->data->menuInfo[$menu_id]->marketingMessage) . "</t11>", $this->showBorders, 'L');
	}

	protected function printMenuToolHeader($x, $y, $menu_id)
	{
		$width = 140;

		$salutation = '';
		if (!empty($this->data->userInfo->firstname))
		{
			$salutation = $this->data->userInfo->firstname . ' ' . $this->data->userInfo->lastname;
		}

		$this->pdf->SetXY($x, $y + 1.5);
		$this->pdf->MultiCellTag($width, 6, "<t14>Welcome</t14> <t14b>{$salutation}</t14b>", $this->showBorders, 'L');

		// Guest ID
		$this->pdf->SetXY($x, $y + 7);
		$this->pdf->MultiCellTag(70, 4, "<h8>Email: {$this->data->userInfo->primary_email}</h8>", $this->showBorders, 'L');
	}

	protected function printHeader_RecipeExpert($x, $y, $menu_id)
	{
		$this->pdf->SetXY(40, $y + 2);
		$this->pdf->MultiCellTag(140, 6, "<t14>Recipe Expert</t14>", $this->showBorders, 'L');
	}

	protected function printHeader_Freezer($x, $y, $menu_id)
	{
		$width = 140;

		$salutation = '';
		if (!empty($this->data->userInfo->firstname))
		{
			$salutation = $this->data->userInfo->firstname . ' ' . $this->data->userInfo->lastname;

			if (substr($this->data->userInfo->lastname, -1) == 's')
			{
				$salutation .= "'";
			}
			else
			{
				$salutation .= "'s";
			}

			$salutation .= " "; // space at end before menu title
		}

		$this->pdf->SetXY(40, $y + 2);
		$this->pdf->MultiCellTag($width, 6, "<t14b>{$salutation}Freezer List</t14b>", $this->showBorders, 'L');

		$this->pdf->SetXY(40, $y + 8.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>See you at your next session!</t11>", $this->showBorders, 'L');

		$this->pdf->SetXY(40, $y + 14);
		$this->pdf->MultiCellTag($width - 29, 3, "<t9>Next Session Date/Time: " . (!empty($this->data->userInfo->nextSession[0]->session_start) ? CTemplate::dateTimeFormat($this->data->userInfo->nextSession[0]->session_start, VERBOSE) : '') . "</t9>", $this->showBorders, 'L');
	}

	protected function printHeader_Nutritional($x, $y, $menu_id)
	{
		$width = 140;

		$salutation = '';
		if (!empty($this->data->userInfo->firstname))
		{
			$salutation = $this->data->userInfo->firstname . ' ' . $this->data->userInfo->lastname;

			if (substr($this->data->userInfo->lastname, -1) == 's')
			{
				$salutation .= "'";
			}
			else
			{
				$salutation .= "'s";
			}

			$salutation .= " "; // space at end before menu title
		}

		$this->pdf->SetXY(40, $y + 2);
		$this->pdf->MultiCellTag($width, 6, "<t14b>{$salutation}Nutritional Information</t14b>", $this->showBorders, 'L');

		$this->pdf->SetXY(40, $y + 8.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>With Dream Dinners, healthy eating is easy for everyone.</t11>", $this->showBorders, 'L');
	}

	protected function printHeaderBox_Nutritional($x, $y, $box_name)
	{
		$width = 140;

		$this->pdf->SetXY(40, $y + 2);
		$this->pdf->MultiCellTag($width, 6, "<t14b>{$box_name}</t14b>", $this->showBorders, 'L');
	}

	protected function printHeader_Intro($x, $y, $includeQRcode = false, $qrData)
	{
		$width = 140;

		$this->pdf->SetXY(40, $y + 2);
		$this->pdf->MultiCellTag($width, 6, "<t14b>Meal Prep Starter Pack</t14b>", $this->showBorders, 'L');

		$this->pdf->SetXY(40, $y + 8.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>Try out ready-to-cook meals for only $" . $this->data->bundleInfo->price . "!</t11>", $this->showBorders, 'L');
		if ($includeQRcode)
		{
			$this->pdf->Image($this->getStoreMenuQrCodePath($qrData->store_id, $qrData->menu_id, false), CSessionToolsPDF::kPAGE_WIDTH - 23, 13, 13);
		}
	}

	protected function printHeader_DreamTaste($x, $y, $menu_id)
	{
		$width = 140;

		$this->pdf->SetXY(40, $y + 2);
		$this->pdf->MultiCellTag($width, 6, "<t14b>Event</t14b>", $this->showBorders, 'L');

		$this->pdf->SetXY(40, $y + 8.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>Date:</t11>", $this->showBorders, 'L');

		$this->pdf->Line(51, $y + 12, 91, $y + 12);

		$this->pdf->SetXY(92, $y + 8.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>Time:</t11>", $this->showBorders, 'L');

		$this->pdf->Line(103, $y + 12, 150, $y + 12);

		$this->pdf->SetXY(40, $y + 14.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>Password:</t11>", $this->showBorders, 'L');

		$this->pdf->Line(60, $y + 18, 91, $y + 18);

		$this->pdf->SetXY(92, $y + 14.5);
		$this->pdf->MultiCellTag($width - 30, 4, "<t11>Hosted by:</t11>", $this->showBorders, 'L');

		$this->pdf->Line(112.5, $y + 18, 150, $y + 18);
	}

	function printMenuToolHeaderRow()
	{
		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);

		$this->pdf->SetFillColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, self::kKEADER_ROW_Y, $width, 1, 'F');
	}

	function printMenuToolHeaderRow_RecipeExpert()
	{
		$ypos = self::kKEADER_ROW_Y;

		$width = CSessionToolsPDF::lPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);

		$this->pdf->SetFillColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		// header line
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, $ypos, $width, 1, 'F');

		$this->pdf->RoundedRect(170, $ypos - 20, 102, 21, 4, '12', 'F');
		/*
				$this->pdf->SetXY(174, $ypos - 1);
				$this->pdf->RotatedText("<h8w>Video</h8w>", 90);
		*/
		$this->pdf->SetXY(182, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Grill Option</h8w>", 90);

		$this->pdf->SetXY(186, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Under Thirty</h8w>", 90);

		$this->pdf->SetXY(190, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Everyday Din.</h8w>", 90);

		$this->pdf->SetXY(194, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Gourmet</h8w>", 90);

		$this->pdf->SetXY(198, $ypos - 1);
		$this->pdf->RotatedText("<h8w>From Frozen</h8w>", 90);

		$this->pdf->SetXY(202, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Kid Friendly</h8w>", 90);

		$this->pdf->SetXY(206, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Flavor Profile</h8w>", 90);

		$this->pdf->SetXY(210, $ypos - 4);
		$this->pdf->MultiCellTag(30, 3.5, "<h8w>Cooking Method</h8w>", $this->showBorders, 'L');

		$this->pdf->SetXY(234, $ypos - 4);
		$this->pdf->MultiCellTag(30, 3.5, "<h8w>Time to Table</h8w>", $this->showBorders, 'L');

		$this->pdf->SetXY(254, $ypos - 4);
		$this->pdf->MultiCellTag(30, 3.5, "<h8w>Packaging</h8w>", $this->showBorders, 'L');
	}

	function printMenuToolHeaderRow_Nutritional($offset = 14)
	{
		$ypos = self::kKEADER_ROW_Y + $offset;

		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);

		$this->pdf->SetFillColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		// header line
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, $ypos, $width, 1, 'F');

		$this->pdf->RoundedRect(44, $ypos - 20, 164, 21, 4, '12', 'F');

		$this->pdf->SetXY(50, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Serving Size</h8w>", 90);

		$this->pdf->SetXY(60, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Calories</h8w>", 90);

		$this->pdf->SetXY(70, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Total Fat</h8w>", 90);

		$this->pdf->SetXY(80, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Saturated Fat</h8w>", 90);

		$this->pdf->SetXY(92, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Cholesterol</h8w>", 90);

		$this->pdf->SetXY(106, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Sodium</h8w>", 90);

		$this->pdf->SetXY(118, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Carbs</h8w>", 90);

		$this->pdf->SetXY(128, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Fiber</h8w>", 90);

		$this->pdf->SetXY(138, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Sugar</h8w>", 90);

		$this->pdf->SetXY(148, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Added Sugars</h8w>", 90);

		$this->pdf->SetXY(158, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Protein</h8w>", 90);

		$this->pdf->SetXY(168, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Vitamin D</h8w>", 90);

		$this->pdf->SetXY(180, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Calcium</h8w>", 90);

		$this->pdf->SetXY(192, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Iron</h8w>", 90);

		$this->pdf->SetXY(202, $ypos - 1);
		$this->pdf->RotatedText("<h8w>Potassium</h8w>", 90);
	}

	function printMenuToolHeaderRow_Freezer()
	{
		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);

		$this->pdf->SetFillColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, self::kKEADER_ROW_Y, $width, 1, 'F');

		$this->pdf->RoundedRect(153, self::kKEADER_ROW_Y - 9, 28, 10, 4, '12', 'F');
		$this->pdf->RoundedRect(180, self::kKEADER_ROW_Y - 9, 28, 10, 4, '12', 'F');

		$this->pdf->SetXY(152, self::kKEADER_ROW_Y - 7.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>My Meals or</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(152, self::kKEADER_ROW_Y - 3.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Need to Rate</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(179, self::kKEADER_ROW_Y - 5.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Quantity</tmwb>", $this->showBorders, 'C');
	}

	function printMenuToolHeaderRow_Intro()
	{
		$this->pdf->SetFillColor($this->color['new_grey']['r'], $this->color['new_grey']['g'], $this->color['new_grey']['b']);

		$this->pdf->RoundedRect(10, self::kKEADER_ROW_Y + 1, 56, 16, 1, '1234', 'F');
		$this->pdf->RoundedRect(79, self::kKEADER_ROW_Y + 1, 56, 16, 1, '1234', 'F');
		$this->pdf->RoundedRect(149, self::kKEADER_ROW_Y + 1, 56, 16, 1, '1234', 'F');

		$this->pdf->SetXY(13, self::kKEADER_ROW_Y + 3.5);
		$this->pdf->MultiCellTag(50, 3.5, "<tmwb>STEP 1</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(13, self::kKEADER_ROW_Y + 7.5);
		$this->pdf->MultiCellTag(50, 3.5, "<t9w>Complete the form below or go online to dreamdinners.com</t9w>", $this->showBorders, 'C');

		$this->pdf->SetXY(82, self::kKEADER_ROW_Y + 3.5);
		$this->pdf->MultiCellTag(50, 3.5, "<tmwb>STEP 2</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(82, self::kKEADER_ROW_Y + 7.5);
		if ($this->data->menu_id < 253)
		{
			$this->pdf->MultiCellTag(50, 3.5, "<t9w>Choose 3 Lrg or 6 Med dinners (or a combination of both), to try.</t9w>", $this->showBorders, 'C');
		}
		else
		{
			$this->pdf->MultiCellTag(50, 3.5, "<t9w>Choose 2 Lrg or 4 Med dinners (or a combination of both), to try.</t9w>", $this->showBorders, 'C');
		}

		$this->pdf->SetXY(152, self::kKEADER_ROW_Y + 3.5);
		$this->pdf->MultiCellTag(50, 3.5, "<tmwb>STEP 3</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(152, self::kKEADER_ROW_Y + 7.5);
		$this->pdf->MultiCellTag(50, 3.5, "<t9w>Visit our store to pick up your meals. Take home & enjoy!</t9w>", $this->showBorders, 'C');

		$yPos = self::kKEADER_ROW_Y + 27;

		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);

		$this->pdf->SetFillColor($this->color['light_green']['r'], $this->color['light_green']['g'], $this->color['light_green']['b']);

		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, $yPos, $width, 1, 'F');

		$this->pdf->RoundedRect(153, $yPos - 9, 28, 10, 4, '12', 'F');
		$this->pdf->RoundedRect(180, $yPos - 9, 28, 10, 4, '12', 'F');

		$this->pdf->SetXY(152, $yPos - 7.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Avg. Guest</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(152, $yPos - 3.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Rating</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(179, $yPos - 7.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Dinner Size</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(183, $yPos - 3.5);
		$this->pdf->MultiCellTag(12, 3.5, "<tmwb>Med</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(195, $yPos - 3.5);
		$this->pdf->MultiCellTag(12, 3.5, "<tmwb>Lrg</tmwb>", $this->showBorders, 'C');
	}

	function printMenuToolHeaderRow_DreamTaste()
	{
		$this->pdf->SetFillColor($this->color['new_grey']['r'], $this->color['new_grey']['g'], $this->color['new_grey']['b']);

		$this->pdf->RoundedRect(10, self::kKEADER_ROW_Y + 1, 56, 16, 1, '1234', 'F');
		$this->pdf->RoundedRect(79, self::kKEADER_ROW_Y + 1, 56, 16, 1, '1234', 'F');
		$this->pdf->RoundedRect(149, self::kKEADER_ROW_Y + 1, 56, 16, 1, '1234', 'F');

		$this->pdf->SetXY(13, self::kKEADER_ROW_Y + 3.5);
		$this->pdf->MultiCellTag(50, 3.5, "<tmwb>STEP 1</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(13, self::kKEADER_ROW_Y + 7.5);
		$this->pdf->MultiCellTag(50, 3.5, "<t9w>Complete the form below or go online to dreamdinners.com</t9w>", $this->showBorders, 'C');

		$this->pdf->SetXY(82, self::kKEADER_ROW_Y + 3.5);
		$this->pdf->MultiCellTag(50, 3.5, "<tmwb>STEP 2</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(82, self::kKEADER_ROW_Y + 7.5);
		$this->pdf->MultiCellTag(50, 3.5, "<t9w>Choose 3 medium dinners for just $" . $this->data->bundleInfo->price . " for your family to try.</t9w>", $this->showBorders, 'C');

		$this->pdf->SetXY(152, self::kKEADER_ROW_Y + 3.5);
		$this->pdf->MultiCellTag(50, 3.5, "<tmwb>STEP 3</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(152, self::kKEADER_ROW_Y + 7.5);
		$this->pdf->MultiCellTag(50, 3.5, "<t9w>Visit our store to pick up your meals. Take home & enjoy!</t9w>", $this->showBorders, 'C');

		$this->pdf->SetFillColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		$yPos = self::kKEADER_ROW_Y + 27;

		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);

		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, $yPos, $width, 1, 'F');

		$this->pdf->RoundedRect(153, $yPos - 9, 28, 10, 4, '12', 'F');
		$this->pdf->RoundedRect(180, $yPos - 9, 28, 10, 4, '12', 'F');

		$this->pdf->SetXY(152, $yPos - 7.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Avg. Guest</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(152, $yPos - 3.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Rating</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(179, $yPos - 7.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Select 3</tmwb>", $this->showBorders, 'C');

		$this->pdf->SetXY(179, $yPos - 3.5);
		$this->pdf->MultiCellTag(30, 3.5, "<tmwb>Med Dinners</tmwb>", $this->showBorders, 'C');
	}

	function printRatingsStars($xPos, $yPos, $width, $rating, $backGroundIsDark = false)
	{
		$height = $width;
		$unrating = 5 - $rating;

		if ($backGroundIsDark)
		{
			$stars_image_path = ASSETS_PATH . '/pdf_label/paper_menu/star_rate_dk.png';
			$stars_unrate_image_path = ASSETS_PATH . '/pdf_label/paper_menu/star_unrate_dk.png';
		}
		else
		{
			$stars_image_path = ASSETS_PATH . '/pdf_label/paper_menu/star_rate.png';
			$stars_unrate_image_path = ASSETS_PATH . '/pdf_label/paper_menu/star_unrate.png';
		}

		for ($i = 1; $i <= $rating; $i++)
		{
			$this->pdf->Image($stars_image_path, $xPos, $yPos, $width, $height);

			$xPos = $xPos + $width;
		}

		for ($i = 1; $i <= $unrating; $i++)
		{
			$this->pdf->Image($stars_unrate_image_path, $xPos, $yPos, $width, $height);

			$xPos = $xPos + $width;
		}
	}

	function printRatingsValue($xPos, $yPos, $width, $rating, $backGroundIsDark = false, $menuItemArray = false, $showLabel = true)
	{
		$height = $width;

		if ($backGroundIsDark)
		{
			$stars_image_path = ASSETS_PATH . '/pdf_label/paper_menu/star_unrate_dk.png';
		}
		else
		{
			$stars_image_path = ASSETS_PATH . '/pdf_label/paper_menu/star_unrate.png';
		}

		$this->pdf->SetXY($xPos, $yPos);
		if ($showLabel)
		{
			$this->pdf->MultiCellTag(120, 4, (($menuItemArray && !empty($menuItemArray['entree']->menu_label)) ? '<t9lg>' . $menuItemArray['entree']->menu_label . '</t9lg> ' : '') . "<t8gr>" . $rating . " of 5</t8gr>", $this->showBorders, 'L');
		}
		else
		{
			$this->pdf->MultiCellTag(20, 4, "<t8gr>" . $rating . " of 5</t8gr>", $this->showBorders, 'L');
		}

		$star_xPos = $xPos + 11.5;

		if ($menuItemArray && !empty($menuItemArray['entree']->menu_label) && $showLabel)
		{
			$strlen_star = strlen($menuItemArray['entree']->menu_label) * 1.9;

			$star_xPos = $star_xPos + $strlen_star;
		}

		$this->pdf->Image($stars_image_path, $star_xPos, $yPos + .25, $width, $height);
	}

	function printMenuToolRow($menuItemArray, $yPos, $count, $globalRating, $includeFooterHr = false)
	{
		$this->showBorders = 0;

		$cellLeftMargin = CSessionToolsPDF::kLEFT_MARGIN;
		if ($count >= 12)
		{
			$cellLeftMargin = CSessionToolsPDF::kLEFT_MARGIN + 100;
		}

		if ($count != 11)
		{
			$this->pdf->SetFillColor($this->color['light_green']['r'], $this->color['light_green']['g'], $this->color['light_green']['b']);
			$this->pdf->Rect($cellLeftMargin, $yPos + self::kHEADER_ROW_HEIGHT + 6, 100, .5, 'F');
		}

		// Menu Item Name
		$fontStyle = 't8b';

		// ltd menu item is orange text
		if (!empty($this->data->storeInfo->supports_ltd_roundup) && $menuItemArray['entree']->icons['ltd_menu_item']['show'])
		{
			$fontStyle = 't8bl';
		}

		$menuItemArray['entree']->menu_item_name = str_replace("&eacute;", "\xC9", $menuItemArray['entree']->menu_item_name);

		if (strlen($menuItemArray['entree']->menu_item_name) > 80)
		{
			$menu_item_title = "<" . $fontStyle . ">" . CAppUtil::truncate(strtoupper($menuItemArray['entree']->menu_item_name), 80) . "</" . $fontStyle . ">";
		}
		else
		{
			$menu_item_title = "<" . $fontStyle . ">" . strtoupper($menuItemArray['entree']->menu_item_name) . "</" . $fontStyle . ">";
		}

		$this->pdf->SetXY($cellLeftMargin, $yPos);
		$this->pdf->MultiCellTag(76, 3, $menu_item_title, $this->showBorders, 'L');


		$RatingyPos = $yPos + 6;

		// ICONS
		$icon_xpos_offset = $cellLeftMargin + 70;
		$icon_ypos_offset = $RatingyPos + .25;
		$icon_size = 3.5;

		// The array is in the desired order, reverse the icon sort since we draw from right to left
		$reverse_icons = array_reverse($menuItemArray['entree']->icons);
		foreach ($reverse_icons as $icon)
		{
			if ($icon['print_meal_detail_enabled'] && $icon['show'])
			{
				$this->pdf->Image(ASSETS_PATH . '/pdf_label/' . $icon['png_icon'], $icon_xpos_offset, $icon_ypos_offset, $icon_size);
				$icon_xpos_offset = $icon_xpos_offset - $icon_size;
			}
		}

		if (!empty($this->data->userInfo) && !empty($menuItemArray['entree']->DAO_food_survey->would_order_again) && $menuItemArray['entree']->DAO_food_survey->would_order_again == 1)
		{
			$stars_image_path = ASSETS_PATH . '/pdf_label/paper_menu/star_rate.png';
			$this->pdf->Image($stars_image_path, $icon_xpos_offset, $icon_ypos_offset, $icon_size);
		}


		if (!empty($menuItemArray['entree']->menu_label))
		{
			$this->pdf->SetXY($cellLeftMargin, $RatingyPos + .25);
			$this->pdf->MultiCellTag(76, 3, "<t9lg>" . $menuItemArray['entree']->menu_label . "</t9lg>", $this->showBorders, 'L');
		}


		// Description
		$this->pdf->SetXY($cellLeftMargin, $yPos + 9.5);
		$this->pdf->MultiCellTag(76, 3, "<h8>" . html_entity_decode(CAppUtil::truncate($menuItemArray['entree']->menu_item_description, 162), ENT_QUOTES, 'WINDOWS-1252') . " (" . number_format($menuItemArray['entree']->nutrition_array['component'][1]['element']['Calories']['value']) . " cal)</h8> ", $this->showBorders, 'L');

		// prices
		$price_yPos = $yPos;
		$height = 4;
		$font = "<h9>";
		$fontClose = "</h9>";
		$halfLeftMargin = 77;

		$this->pdf->SetFillColor($this->color['grey_price']['r'], $this->color['grey_price']['g'], $this->color['grey_price']['b']);
		$this->pdf->Rect($cellLeftMargin + $halfLeftMargin - .5, $yPos - .25, 23, 19, 'F');

		$price_yPos = $price_yPos + 10;
		$price_xPos = $cellLeftMargin + $halfLeftMargin;
		foreach ($menuItemArray['menu_item'] as $menuItem)
		{
			$this->pdf->Line($price_xPos, $price_yPos + 1, $price_xPos + 11, $price_yPos + 1);
			$this->pdf->SetXY($price_xPos, $price_yPos + 1);
			$this->pdf->MultiCellTag(12, $height, $font . "{$menuItem->store_price}\n{$menuItem->pricing_type_info['pricing_type_name_short']}" . $fontClose, $this->showBorders, 'C');
			$price_xPos += 11;
		}

		if($includeFooterHr)
		{
			// footer hr
			$this->pdf->SetFillColor($this->color['light_green']['r'], $this->color['light_green']['g'], $this->color['light_green']['b']);
			$this->pdf->Rect(5, CSessionToolsPDF::kPAGE_HEIGHT - 24, 205, .5, 'F');
		}

	}

	function printMenuToolRow_RecipeExpert($menuItemArray, $yPos, $count, $row_height)
	{
		if ($count % 2 == 0)
		{
			$this->pdf->SetFillColor(255, 255, 255);
		}
		else
		{
			$this->pdf->SetFillColor(222, 222, 222);
		}

		// Background
		$width = CSessionToolsPDF::lPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, $yPos, $width, $row_height, 'F');

		$menu_label = '';
		if ($menuItemArray['menu_item_category_id'] == 4 && $menuItemArray['is_store_special'] == 0)
		{
			$menu_label = ' <t8bb>(Pre-Assembled)</t8bb>';
		}

		$menuItemArray['entree']->menu_item_name = str_replace("&eacute;", "\xC9", $menuItemArray['entree']->menu_item_name);

		// Menu Item Name
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $yPos);
		$this->pdf->MultiCellTag(162, 4, "<t9b>" . strtoupper($menuItemArray['entree']->menu_item_name) . "</t9b>" . $menu_label, $this->showBorders, 'L');

		// Description
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 2, $yPos + 3.65);
		$this->pdf->MultiCellTag(160, 3, "<h9>" . html_entity_decode($menuItemArray['entree']->DAO_recipe->recipe_expert, ENT_QUOTES, 'WINDOWS-1252') . "</h9>", $this->showBorders, 'L');

		// ICONS
		$icon_ypos_offset = $yPos + 1;
		$icon_size = 3.5;

		// The array is in the desired order, reverse the icon sort since we draw from right to left
		rsort($menuItemArray['entree']->icons);
		foreach ($menuItemArray['entree']->icons as $icon)
		{
			if ($icon['print_meal_detail_enabled'] && $icon['show'])
			{
				$this->pdf->Image(ASSETS_PATH . '/pdf_label/' . $icon['png_icon'], $icon_xpos_offset, $icon_ypos_offset, $icon_size);
				$icon_xpos_offset = $icon_xpos_offset - $icon_size;
			}
		}

		//
		$this->pdf->SetXY(202, $icon_ypos_offset);
		$this->pdf->MultiCellTag(6, 3, "<h9>" . $menuItemArray['flavor_profile'] . "</h9>", $this->showBorders, 'C');

		//
		$this->pdf->SetXY(214, $icon_ypos_offset);
		$this->pdf->MultiCellTag(18, 3, "<h9>" . $menuItemArray['cooking_method'] . "</h9>", $this->showBorders, 'C');

		//
		$this->pdf->SetXY(234, $icon_ypos_offset);
		$this->pdf->MultiCellTag(18, 3, "<h9>" . $menuItemArray['prep_time'] . "</h9>", $this->showBorders, 'C');

		//
		$this->pdf->SetXY(253, $icon_ypos_offset);
		$this->pdf->MultiCellTag(18, 3, "<h9>" . $menuItemArray['packaging'] . "</h9>", $this->showBorders, 'C');
	}

	function printMenuToolRow_Freezer($menuItemArray, $yPos, $count, $globalRating)
	{
		$backGroundIsDark = true;

		if ($count % 2 == 0)
		{
			$this->pdf->SetFillColor(255, 255, 255);
			$backGroundIsDark = false;
		}
		else
		{
			$this->pdf->SetFillColor(222, 222, 222);
		}

		//$rows = $this->calculateFreezerSheetRowHeight($menuItemArray);
		$rows = null;
		if (is_null($rows))
		{
			$rows = self::kHEADER_ROW_HEIGHT;
		}
		else
		{
			$rows += self::kHEADER_ROW_HEIGHT + 18;
		}
		// Background
		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2) - 7;
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos, $width, $rows, 'F');

		$menuItemArray['entree']->menu_item_name = str_replace("&eacute;", "\xC9", $menuItemArray['entree']->menu_item_name);

		// Menu Item Name
		if (strlen($menuItemArray['entree']->menu_item_name) >= 60)
		{
			$menu_item_title = "<t8b>" . CAppUtil::truncate(strtoupper($menuItemArray['entree']->menu_item_name), 70) . "</t8b>";
		}
		else
		{
			$menu_item_title = "<t9b>" . strtoupper($menuItemArray['entree']->menu_item_name) . "</t9b>";
		}

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos);
		$this->pdf->MultiCellTag(149, 4, $menu_item_title, $this->showBorders, 'L');

		// Time to Table
		$cooking_method = '';
		if (!empty($menuItemArray['entree']->DAO_recipe->cooking_method))
		{
			$cooking_method = 'Method: ' . trim($menuItemArray['entree']->DAO_recipe->cooking_method) . ', ';
		}

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 10, $yPos + 3.5);
		$this->pdf->MultiCellTag(146, 3, "<h8>" . $cooking_method . "Time to Table: " . $menuItemArray['entree']->prep_time . "</h8>", $this->showBorders, 'L');

		if (!empty($menuItemArray['entree']->serving_suggestions))
		{
			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 10, $yPos + 6.8);
			$this->pdf->MultiCellTag(146, 3, "<h8>Serving Suggestions: " . html_entity_decode($menuItemArray['entree']->serving_suggestions, ENT_QUOTES, 'WINDOWS-1252') . "</h8>", $this->showBorders, 'L');
		}

		// ICONS
		$icon_xpos_offset = CSessionToolsPDF::kLEFT_MARGIN + 140;
		$icon_ypos_offset = $yPos + .25;
		$icon_size = 3.5;

		// The array is in the desired order, reverse the icon sort since we draw from right to left
		$reverse_icons = array_reverse($menuItemArray['entree']->icons);
		foreach ($reverse_icons as $icon)
		{
			if ($icon['print_meal_detail_enabled'] && $icon['show'])
			{
				$this->pdf->Image(ASSETS_PATH . '/pdf_label/' . $icon['png_icon'], $icon_xpos_offset, $icon_ypos_offset, $icon_size);
				$icon_xpos_offset = $icon_xpos_offset - $icon_size;
			}
		}

		// Ratings
		if ($this->data->userInfo && !empty($menuItemArray['entree']->DAO_food_survey->rating))
		{
			$this->printRatingsStars(CSessionToolsPDF::kLEFT_MARGIN + 155, $yPos + 1, 3, $menuItemArray['entree']->DAO_food_survey->rating, $backGroundIsDark);
		}
		else
		{
			$this->printRatingsStars(CSessionToolsPDF::kLEFT_MARGIN + 155, $yPos + 1, 3, 0, $backGroundIsDark);

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 145, $yPos + .75);
			$this->pdf->MultiCellTag(10, 4, "<t9>Rate</t9>", $this->showBorders, 'R');
		}

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 150, $yPos + 5.5);
		$this->pdf->MultiCellTag(20, 2, "<t8>Would order again</t8>", $this->showBorders, 'L');

		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 146, $yPos + 6.5, 3, 3);

		if (!empty($this->data->userInfo) && !empty($menuItemArray['entree']->DAO_food_survey->would_order_again) && $menuItemArray['entree']->DAO_food_survey->would_order_again == 1)
		{
			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 145.6, $yPos + 7.2);
			$this->pdf->MultiCellTag(20, 2, "<t9>Y</t9>", $this->showBorders, 'L');
		}

		$yPosDelta = 0;
		$block_row_count = 1;//== all row in all blocks
		$total_block_row_count = 1;//== all row in all blocks

		$srv_box_xpos_start = 186;
		$srv_box_ypos_start = $yPos;
		$y_offest = $srv_box_ypos_start;

		foreach($menuItemArray['menu_item'] AS $menuItem)
		{
			$item_count = $menuItem->DAO_order_item->item_count;

			if ($menuItem->isMenuItem_SidesSweets())
			{
				// is Sides & Sweets
				$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 186, $y_offest);
				$this->pdf->MultiCellTag(14, 4, "<t9b>Qty</t9b>", $this->showBorders, 'R');
			}
			else
			{
				$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 186, $y_offest);
				$this->pdf->MultiCellTag(14, 4, "<t9b>" . $menuItem->pricing_type_info['pricing_type_name_short'] . "</t9b>", $this->showBorders, 'R');
			}

			//draw item count boxes
			$x_offset = $srv_box_xpos_start;
			$block_row_count = 1;
			for ($i = 1; $i <= $item_count && $i < 100; $i++)
			{

				if ($i > 1)
				{
					$x_offset = $x_offset - 3;
				}

				$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + $x_offset, $y_offest + 1, 2, 2);
				if ($i % 6 == 0)
				{
					$block_row_count++;
					$x_offset = $srv_box_xpos_start + 3;
					if ($i < $item_count)
					{
						$y_offest = $y_offest + 2.75;
					}//only add when not at last row
				}
			}

			$total_block_row_count += $block_row_count;

			//add space between blocks
			$y_offest += 5;
		}

		if ($total_block_row_count > 2)
		{
			//More that
			$yPosDelta = 3.5 * $total_block_row_count;
		}
		else
		{
			$yPosDelta = $yPosDelta + 15;
		}

		return $yPosDelta;
	}

	function calculateFreezerSheetRowHeight($orderItem)
	{
		$quantityData = (!empty($orderItem['entree']->DAO_order_item->item_count) ? $orderItem['entree']->DAO_order_item->item_count : false);

		$total_item_count = 3;
		$rows = 0;

		if (!empty($quantityData))
		{
			foreach ($quantityData as $pricing_type => $attrs)
			{
				$rows++;
				$total_item_count += $attrs['item_count'];
			}
		}
		if ($total_item_count > 1)
		{
			$rows += floor($total_item_count / 6);
		}

		if ($rows < 3)
		{
			$rows = 5;
		}

		return $rows;
	}

	function printMenuToolRow_Nutritional($menuItemArray, $yPos, $count, $row_height)
	{
		$backGroundIsDark = true;

		if ($count % 2 == 0)
		{
			$this->pdf->SetFillColor(255, 255, 255);
			$backGroundIsDark = false;
		}
		else
		{
			$this->pdf->SetFillColor(222, 222, 222);
		}

		// Background
		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2);
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN, $yPos, $width, $row_height, 'F');

		$menuItemArray['entree']->menu_item_name = str_replace("&eacute;", "\xC9", $menuItemArray['entree']->menu_item_name);

		// Menu Item Name
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $yPos);
		$this->pdf->MultiCellTag(200, 4, "<t9b>" . strtoupper($menuItemArray['entree']->menu_item_name) . "</t9b>", $this->showBorders, 'L');

		// Notes
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, ($yPos + $row_height) - 3.5);
		$this->pdf->MultiCellTag(200, 3, "<t7>" . $menuItemArray['entree']->nutrition_array['component'][1]['info']['notes'] . "</t7>", $this->showBorders, 'R');

		$this->pdf->SetDrawColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		$component_count = 0;

		foreach ($menuItemArray['entree']->nutrition_array['component'] as $component_id => $component)
		{
			$component_ypos = $yPos + ((++$component_count - 1) * 4) + 4;

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $component_ypos);
			$component['info']['serving'] = str_replace("&eacute;", "\xE9", $component['info']['serving']);

			if (strlen($component['info']['serving']) >= 50)
			{
				$this->pdf->MultiCellTag(45, 2, "<t6>" . ucfirst($component['info']['serving']) . "</t6>", $this->showBorders, 'R');
			}
			else
			{
				$this->pdf->MultiCellTag(45, 2, "<t8>" . ucfirst($component['info']['serving']) . "</t8>", $this->showBorders, 'R');
			}

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 46, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Calories']['value']) . $component['element']['Calories']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 56, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Fat']['value']) . $component['element']['Fat']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 66, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Sat Fat']['value']) . $component['element']['Sat Fat']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 76, $component_ypos);
			$this->pdf->MultiCellTag(14, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Cholesterol']['value']) . $component['element']['Cholesterol']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 90, $component_ypos);
			$this->pdf->MultiCellTag(14, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Sodium']['value']) . $component['element']['Sodium']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 104, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Carbs']['value']) . $component['element']['Carbs']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 114, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Fiber']['value']) . $component['element']['Fiber']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 124, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Sugars']['value']) . $component['element']['Sugars']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 134, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Added Sugar']['value']) . $component['element']['Added Sugar']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 144, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Protein']['value']) . $component['element']['Protein']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 154, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Vit D']['value']) . utf8_decode($component['element']['Vit D']['measure_label']) . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 164, $component_ypos);
			$this->pdf->MultiCellTag(14, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Calcium']['value']) . $component['element']['Calcium']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 178, $component_ypos);
			$this->pdf->MultiCellTag(10, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Iron']['value']) . $component['element']['Iron']['measure_label'] . "</t8>", 'L', 'C');

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 188, $component_ypos);
			$this->pdf->MultiCellTag(13, 4, "<t8>" . CTemplate::formatDecimal($component['element']['Potassium (K)']['value']) . $component['element']['Potassium (K)']['measure_label'] . "</t8>", 'L', 'C');
		}

		$this->pdf->SetDrawColor(0);
	}

	function printMenuToolRow_Intro($menuItemArray, $yPos, $count, $globalRating)
	{
		$backGroundIsDark = true;

		if ($count % 2 == 0)
		{
			$this->pdf->SetFillColor(255, 255, 255);
			$backGroundIsDark = false;
		}
		else
		{
			$this->pdf->SetFillColor(222, 222, 222);
		}

		// Background
		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2) - 7;
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos, $width, self::kHEADER_ROW_HEIGHT, 'F');

		// Menu Item Name
		if (strlen($menuItemArray['entree']->menu_item_name) >= 62)
		{
			$menu_item_title = "<t8b>" . CAppUtil::truncate(strtoupper($menuItemArray['entree']->menu_item_name), 70) . "</t8b>";
		}
		else
		{
			$menu_item_title = "<t9b>" . strtoupper($menuItemArray['entree']->menu_item_name) . "</t9b>";
		}

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos);
		$this->pdf->MultiCellTag(128, 4, $menu_item_title, $this->showBorders, 'L');

		// Description
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 10, $yPos + 3.65);
		$this->pdf->MultiCellTag(146, 3, ((!empty($menuItemArray['entree']->menu_label)) ? '<h8b>' . $menuItemArray['entree']->menu_label . ':</h8b> ' : '') . "<h8>" . html_entity_decode($menuItemArray['entree']->menu_item_description, ENT_QUOTES, 'WINDOWS-1252') . "</h8> <t8>(" . number_format($menuItemArray['entree']->nutrition_array['component'][1]['element']['Calories']['value']) . " cal)</t8>", $this->showBorders, 'L');
		//$this->pdf->MultiCellTag(146, 3, "<h8>" . html_entity_decode($menuItemArray['entree']->menu_item_description, ENT_QUOTES, 'WINDOWS-1252') . "</h8>", $this->showBorders, 'L');

		// ICONS
		$icon_xpos_offset = CSessionToolsPDF::kLEFT_MARGIN + 140;
		$icon_ypos_offset = $yPos + .25;
		$icon_size = 3.5;

		// The array is in the desired order, reverse the icon sort since we draw from right to left
		$reverse_icons = array_reverse($menuItemArray['entree']->icons);
		foreach ($reverse_icons as $icon)
		{
			if ($icon['print_meal_detail_enabled'] && $icon['show'])
			{
				$this->pdf->Image(ASSETS_PATH . '/pdf_label/' . $icon['png_icon'], $icon_xpos_offset, $icon_ypos_offset, $icon_size);
				$icon_xpos_offset = $icon_xpos_offset - $icon_size;
			}
		}

		// Ratings
		if (!empty($globalRating))
		{
			$this->printRatingsValue(CSessionToolsPDF::kLEFT_MARGIN + 157.5, $yPos + 1, 3, number_format(round($globalRating, 1), 1), $backGroundIsDark);
		}
		else
		{
			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 158, $yPos + 1);
			$this->pdf->MultiCellTag(15, 4, "<t9rb>New!</t9rb>", $this->showBorders, 'R');
		}

		foreach ($menuItemArray['menu_item'] as $menuItem)
		{
			if ($menuItem->isInBundle($this->data->bundleInfo) && $menuItem->pricing_type == CMenuItem::HALF)
			{
				$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 179, $yPos + 5, 3, 3);

				$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 183.5, $yPos + 4.5);
				$this->pdf->MultiCellTag(6, 4, "<t9b>or</t9b>", $this->showBorders, 'C');
			}

			if ($menuItem->isInBundle($this->data->bundleInfo) && $menuItem->pricing_type == CMenuItem::FULL)
			{
				$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 191, $yPos + 5, 3, 3);
			}
		}

	}

	function printMenuToolRow_DreamTaste($menuItemArray, $yPos, $count, $globalRating)
	{
		$backGroundIsDark = true;

		if ($count % 2 == 0)
		{
			$this->pdf->SetFillColor(255, 255, 255);
			$backGroundIsDark = false;
		}
		else
		{
			$this->pdf->SetFillColor(222, 222, 222);
		}

		// Background
		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2) - 7;
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos, $width, self::kHEADER_ROW_HEIGHT, 'F');

		// Menu Item Name
		if (strlen($menuItemArray['entree']->menu_item_name) >= 62)
		{
			$menu_item_title = "<t8b>" . CAppUtil::truncate(strtoupper($menuItemArray['entree']->menu_item_name), 70) . "</t8b>";
		}
		else
		{
			$menu_item_title = "<t9b>" . strtoupper($menuItemArray['entree']->menu_item_name) . "</t9b>";
		}

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos);
		$this->pdf->MultiCellTag(126, 4, $menu_item_title, $this->showBorders, 'L');

		// Description
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 10, $yPos + 3.65);
		$this->pdf->MultiCellTag(146, 3, "<h8>" . html_entity_decode($menuItemArray['entree']->menu_item_description, ENT_QUOTES, 'WINDOWS-1252') . "</h8> <t8>(" . number_format($menuItemArray['entree']->nutrition_array['component'][1]['element']['Calories']['value']) . " cal)</t8>", $this->showBorders, 'L');
		//$this->pdf->MultiCellTag(146, 3, "<h8>" . html_entity_decode($menuItemArray['entree']->menu_item_description, ENT_QUOTES, 'WINDOWS-1252') . "</h8>", $this->showBorders, 'L');

		// ICONS
		$icon_xpos_offset = CSessionToolsPDF::kLEFT_MARGIN + 140;
		$icon_ypos_offset = $yPos + .25;
		$icon_size = 3.5;

		// The array is in the desired order, reverse the icon sort since we draw from right to left
		$reverse_icons = array_reverse($menuItemArray['entree']->icons);
		foreach ($reverse_icons as $icon)
		{
			if ($icon['print_meal_detail_enabled'] && $icon['show'])
			{
				$this->pdf->Image(ASSETS_PATH . '/pdf_label/' . $icon['png_icon'], $icon_xpos_offset, $icon_ypos_offset, $icon_size);
				$icon_xpos_offset = $icon_xpos_offset - $icon_size;
			}
		}

		// Ratings
		if (!empty($globalRating))
		{
			$this->printRatingsValue(CSessionToolsPDF::kLEFT_MARGIN + 157.5, $yPos + 1, 3, number_format(round($globalRating, 1), 1), $backGroundIsDark);
		}
		else
		{
			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 158, $yPos + 1);
			$this->pdf->MultiCellTag(15, 4, "<t9rb>New!</t9rb>", $this->showBorders, 'R');
		}

		// price 6-serving
		//$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 184, $yPos + 4, 4, 4);

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 175, $yPos + 4);
		$this->pdf->MultiCellTag(10, 4, "<t9>Qty</t9>", $this->showBorders, 'L');

		$this->pdf->Line(190, $yPos + 7.5, 198, $yPos + 7.5);

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 190, $yPos + 4);
		$this->pdf->MultiCellTag(10, 4, "<t9>Med</t9>", $this->showBorders, 'L');
	}

	function printRecipeExpertKey($yPos)
	{
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $yPos);
		$this->pdf->MultiCellTag(CSessionToolsPDF::lPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2), 4, "<t9>Flavor Profile Key:     As - Asian     A - American     C - Classic Comfort     I - Italian     M - Mexican</t9>", $this->showBorders, 'C');
	}

	function printMenuNotes($yPos, $includePrintedOn = false, $includeQRcode = false, $qrData = null)
	{

		$printedNote = '';
		if ($includePrintedOn)
		{
			$dt = new DateTime("now");
			$printedNote = "Pricing subject to change. Printed on: {$dt->format('F d, Y')}";
		}

		$note = "Foods prepared at Dream Dinners may contain allergens introduced during the assembly process. Potential food allergies include: milk, casein, whey, egg, albumin, shellfish, tree nuts, wheat, gluten, peanuts and soy beans. {$this->data->minimumNote} {$printedNote}";

		$textAlign = 'C';
		if ($includeQRcode)
		{
			$textAlign = 'L';
		}
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $yPos + 6);
		$this->pdf->MultiCellTag(CSessionToolsPDF::kPAGE_WIDTH - 30, 3, "<t7>{$note}</t7>", $this->showBorders, $textAlign);

		if ($includeQRcode)
		{
			$this->pdf->Image($this->getStoreMenuQrCodePath($qrData->store_id, $qrData->menu_id, false), CSessionToolsPDF::kPAGE_WIDTH - 22, $yPos, 18);
		}

	}

	function printMenuNotes_Intro($yPos)
	{
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 2, $yPos);
		$this->pdf->RotatedText("<h8>Foods prepared at Dream Dinners may contain allergens introduced during the assembly process. Potential food</h8>", 90);

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 5, $yPos);
		$this->pdf->RotatedText("<h8>allergies include: milk, casein, whey, egg, albumin, shellfish, tree nuts, wheat, gluten, peanuts and soy beans.</h8>", 90);

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 2, $yPos - 143);
		$this->pdf->RotatedText("<h8>Meal Prep Starter Pack is available to new guests only.</h8>", 90);
	}

	function printMenuNotes_DreamTaste($yPos)
	{
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 2, $yPos);
		$this->pdf->RotatedText("<h8>Foods prepared at Dream Dinners may contain allergens introduced during the assembly process. Potential food</h8>", 90);

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 5, $yPos);
		$this->pdf->RotatedText("<h8>allergies include: milk, casein, whey, egg, albumin, shellfish, tree nuts, wheat, gluten, peanuts and soy beans.</h8>", 90);

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 2, $yPos - 143);
		$this->pdf->RotatedText("<h8>Events are available to new guests,</h8>", 90);

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 5, $yPos - 143);
		$this->pdf->RotatedText("<h8>by invitation only.</h8>", 90);
	}

	function printStoreSummary_Freezer($yPos)
	{
		$streetAddress = $this->data->storeInfo->address_line1;
		if (!empty($this->data->storeInfo->address_line2))
		{
			$streetAddress .= ', ' . $this->data->storeInfo->address_line2;
		}

		$addressLine = $streetAddress . " - " . $this->data->storeInfo->city . ", " . $this->data->storeInfo->state_id . " " . $this->data->storeInfo->postal_code;

		$contactLine = $this->data->storeInfo->email_address . " - " . $this->data->storeInfo->telephone_day;

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + 2, $yPos);
		$this->pdf->RotatedText("<h8>{$this->data->storeInfo->store_name} - {$addressLine} - {$contactLine}</h8>", 90);
	}

	function printIconLegend($yPos, $xPos, $addFavoriteStar = false)
	{
		$x_offset = 0;
		$count = 0;

		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->id = $this->data->menu_id;

		foreach (CRecipe::getIconSchematic($DAO_menu) as $icon)
		{
			if ($count++ == 7)
			{
				$yPos += 4;
				$x_offset = 0;
			}

			if ($icon['print_menu_legend_enabled'] && !empty($icon['png_icon']))
			{
				$this->pdf->Image(ASSETS_PATH . '/pdf_label/' . $icon['png_icon'], CSessionToolsPDF::kLEFT_MARGIN + $xPos + $x_offset, $yPos, 3.5);

				$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + $xPos + $x_offset + 3.5, $yPos);
				$this->pdf->SetFont('helvetica', '', 6);
				$this->pdf->SetTextColor(0);
				$cell_width = $this->pdf->GetStringWidth($icon['label']) + 6;
				$this->pdf->MultiCellTag($cell_width, 4, $icon['label'], $this->showBorders, 'L');
				$x_offset += $cell_width;
			}
		}

		if($addFavoriteStar)
		{
			$this->pdf->Image(ASSETS_PATH . '/pdf_label/paper_menu/star_rate.png', CSessionToolsPDF::kLEFT_MARGIN + $xPos + $x_offset, $yPos, 3.5);

			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN + $xPos + $x_offset + 3.5, $yPos);
			$this->pdf->SetFont('helvetica', '', 6);
			$this->pdf->SetTextColor(0);
			$cell_width = $this->pdf->GetStringWidth('Personal Favorite') + 6;
			$this->pdf->MultiCellTag($cell_width, 4, 'Personal Favorite', $this->showBorders, 'L');
		}
	}

	function printLeftTabbedContainer($xPos, $yPos, $width, $height, $title = false, $text_rotate = 90, $text_y_offset = 0)
	{
		$this->pdf->SetFillColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		$this->pdf->RoundedRect($xPos - 6, $yPos + .5, 5, $height + 1, 2, '14', 'F');

		$this->pdf->SetFillColor(255, 255, 255);
		$this->pdf->SetDrawColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);
		$this->pdf->SetLineWidth(1);
		$this->pdf->Rect($xPos - 1, $yPos + 1, $width, $height, 'DF');

		if ($title)
		{
			$this->pdf->SetXY($xPos - 2, $yPos + 20 + $text_y_offset);
			$this->pdf->RotatedText($title, $text_rotate);
		}

		$this->pdf->SetFillColor(0);
		$this->pdf->SetDrawColor(0);
		$this->pdf->SetLineWidth(0.2);
	}

	function printPlatePointsSummary($xPos, $yPos)
	{
		if ($this->data->userInfo->platePointsData['status'] != 'active')
		{
			return;
		}

		// Credit Available
		$this->pdf->SetXY($xPos, $yPos + 6);
		$formatedCredit = CTemplate::moneyFormat($this->data->userInfo->platePointsData['available_credit'], 0);
		$this->pdf->MultiCellTag(70, 4, "<h8>$$formatedCredit Available Dinner Dollars</h8>", $this->showBorders, 'L');
	}

	function printMembershipSummary($yPos, $xPos)
	{
		if ($this->data->userInfo->membershipData['status'] != CUser::MEMBERSHIP_STATUS_CURRENT)
		{
			return;
		}

		// Guest ID
		$this->pdf->SetXY($xPos, $yPos + 2);
		$this->pdf->MultiCellTag(70, 4, "<h8>Email: {$this->data->userInfo->primary_email}</h8>", $this->showBorders, 'L');

		//Progress
		$this->pdf->SetXY($xPos, $yPos + 5);
		$this->pdf->MultiCellTag(70, 4, "<h8>MP+ Progress: {$this->data->userInfo->membershipData['display_strings']['progress']}</h8>", $this->showBorders, 'L');

		// ending month
		$this->pdf->SetXY($xPos, $yPos + 8);
		$this->pdf->MultiCellTag(70, 4, "<h8>MP+ Ends: {$this->data->userInfo->membershipData['completion_month']}</h8>", $this->showBorders, 'L');

		// total saved
		$formatedCredit = CTemplate::moneyFormat($this->data->userInfo->membershipData['display_strings']['total_savings'], 0);
		$this->pdf->SetXY($xPos, $yPos + 11);
		$this->pdf->MultiCellTag(70, 4, "<h8>Total Saved: $$formatedCredit</h8>", $this->showBorders, 'L');
	}

	function printNewAccountInfo($yPos, $xPos)
	{
		$this->printLeftTabbedContainer($xPos, $yPos, 88, 44, "<pt8b>GUEST INFO</pt8b>", 90, 12);

		$this->pdf->SetDrawColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		// guest anme line
		$this->pdf->Line($xPos + 1, $yPos + 10, $xPos + 85, $yPos + 10);

		// email addy
		$this->pdf->SetXY($xPos, $yPos + 10);
		$this->pdf->MultiCellTag(34, 4, "<h8>First and Last Name</h8>", $this->showBorders, 'L');

		// email addy line
		$this->pdf->Line($xPos + 1, $yPos + 20, $xPos + 85, $yPos + 20);

		// email addy
		$this->pdf->SetXY($xPos, $yPos + 20);
		$this->pdf->MultiCellTag(34, 4, "<h8>Email Address</h8>", $this->showBorders, 'L');

		// phone num
		$this->pdf->SetXY($xPos + 66, $yPos + 20);
		$this->pdf->MultiCellTag(34, 4, "<h8>Phone</h8>", $this->showBorders, 'L');

		// street addy line
		$this->pdf->Line($xPos + 1, $yPos + 30, $xPos + 85, $yPos + 30);

		// street addy
		$this->pdf->SetXY($xPos, $yPos + 30);
		$this->pdf->MultiCellTag(34, 4, "<h8>Street Address</h8>", $this->showBorders, 'L');

		// city line
		$this->pdf->Line($xPos + 1, $yPos + 40, $xPos + 85, $yPos + 40);

		// city name
		$this->pdf->SetXY($xPos, $yPos + 40);
		$this->pdf->MultiCellTag(34, 4, "<h8>City</h8>", $this->showBorders, 'L');

		// city name
		$this->pdf->SetXY($xPos + 40, $yPos + 40);
		$this->pdf->MultiCellTag(34, 4, "<h8>State</h8>", $this->showBorders, 'L');

		// city name
		$this->pdf->SetXY($xPos + 66, $yPos + 40);
		$this->pdf->MultiCellTag(34, 4, "<h8>Zip</h8>", $this->showBorders, 'L');

		$this->pdf->SetDrawColor(0);
	}

	function printNewAccountInfo_Menu($yPos, $xPos)
	{
		// Rect hides welcome message
		$this->pdf->SetFillColor(255, 255, 255);
		$this->pdf->Rect($xPos, $yPos, 100, 40, 'F');

		$this->pdf->SetDrawColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		// guest name line
		$this->pdf->Line($xPos + 1, $yPos + 8, $xPos + 75, $yPos + 8);

		// Name
		$this->pdf->SetXY($xPos, $yPos + 8);
		$this->pdf->MultiCellTag(34, 4, "<h8>First and Last Name</h8>", $this->showBorders, 'L');

		$this->pdf->SetXY($xPos, $yPos + 8);
		$this->pdf->MultiCellTag(86, 4, "", $this->showBorders, 'R');

		// info line
		$this->pdf->Line($xPos + 1, $yPos + 17, $xPos + 130, $yPos + 17);

		// email addy
		$this->pdf->SetXY($xPos, $yPos + 17);
		$this->pdf->MultiCellTag(34, 4, "<h8>Email Address</h8>", $this->showBorders, 'L');

		// phone num
		$this->pdf->SetXY($xPos, $yPos + 17);
		$this->pdf->MultiCellTag(86, 4, "<h8>Phone</h8>", $this->showBorders, 'R');

		$this->pdf->SetDrawColor(0);
	}

	function printSessionAndPaymentSummary($yPos, $xPos, $count)
	{
		// background
		$this->pdf->SetLineWidth(.5);
		$this->pdf->SetDrawColor($this->color['cyan']['r'], $this->color['cyan']['g'], $this->color['cyan']['b']);

		//for testing max spacing: $count = 19;
		$yStart = $yPos - 55;
		$scaleBasedOnItems = 13;

		$customiztionFontsize = '8';
		$customizationScale = 0;

		if ($count <= 17)
		{
			$this->pdf->Rect($xPos, $yPos - 60, 99.8, 102);
			$scaleBasedOnItems = 10;
			$customizationScale = 2;
		}
		else if ($count <= 18)
		{
			//if 18 menu items
			$this->pdf->Rect($xPos, $yPos - 40, 99.8, 81.6);
			$yStart = $yPos - 37;
			$scaleBasedOnItems = 8;
		}
		else
		{
			//if 19 menu items Max!!!
			$this->pdf->Rect($xPos, $yPos - 20, 99.8, 63);
			$yStart = $yPos - 18;
			$scaleBasedOnItems = 4;
			$customiztionFontsize = '7';
		}

		$showFeeFootnote = false;

		$this->pdf->SetDrawColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		//$yStart = $yPos - 55;
		$this->pdf->SetLineWidth(.25);

		// Next Visit Date
		$yStart += 3;
		$this->pdf->Line($xPos + 26, $yStart + 3, $xPos + 89, $yStart + 3);
		$this->pdf->SetXY($xPos + 5, $yStart);
		$this->pdf->MultiCellTag(34, 4, "<h8>Visit Date/Time:</h8>", $this->showBorders, 'L');

		// Next Visit Type
		$yStart += $scaleBasedOnItems;
		$this->pdf->Line($xPos + 20, $yStart + 3, $xPos + 89, $yStart + 3);
		$this->pdf->SetXY($xPos + 5, $yStart);
		$this->pdf->MultiCellTag(34, 4, "<h8>Visit Type:</h8>", $this->showBorders, 'L');

		if (!empty($this->data->userInfo->referencePayment))
		{
			foreach ($this->data->userInfo->referencePayment as $card => $cardInfo)
			{
				// $cardInfo is most recent card
				break;
			}
		}

		$yStart += $scaleBasedOnItems;

		$this->pdf->SetFillColor($this->color['grey_price']['r'], $this->color['grey_price']['g'], $this->color['grey_price']['b']);
		$this->pdf->Rect($xPos + 8, $yStart - .25, 89, 7.5, 'F');

		$this->pdf->SetFillColor(255, 255, 255);
		$this->pdf->Rect($xPos + 10, $yStart, 3, 3, 'FD');
		$this->pdf->SetXY($xPos + 13, $yStart);
		if (!empty($cardInfo['cc_number']))
		{
			$this->pdf->MultiCellTag(65, 4, "<h7b>Pay w/ " . substr($cardInfo['card_type'], 0, 11) . " " . $cardInfo['cc_number'] . "</h7b>", $this->showBorders, 'L');
		}
		else
		{
			$this->pdf->MultiCellTag(65, 4, "<h7b>Credit card:</h7b>", $this->showBorders, 'L');
		}

		$this->pdf->Rect($xPos + 47, $yStart, 3, 3, 'FD');
		$this->pdf->SetXY($xPos + 50, $yStart);
		$this->pdf->MultiCellTag(30, 4, "<h7b>Pay in Full Now</h7b>", $this->showBorders, 'L');

		$yStart += 4;
		$this->pdf->Rect($xPos + 10, $yStart, 3, 3, 'FD');
		$this->pdf->SetXY($xPos + 13, $yStart);
		$this->pdf->MultiCellTag(30, 4, "<h7b>Other:</h7b>", $this->showBorders, 'L');

		if ($this->data->storeInfo->supportsDelayedPayment())
		{
			$this->pdf->Rect($xPos + 47, $yStart, 3, 3, 'FD');
			$this->pdf->SetXY($xPos + 50, $yStart);
			$this->pdf->MultiCellTag(55, 4, "<h7b>Delayed Pay with Deposit if Qualifying</h7b>", $this->showBorders, 'L');
		}

		$yStart += 4;
		if (empty($this->data->userInfo) || ($this->data->userInfo->platePointsData['status'] == "active" && !$this->data->userInfo->platePointsData['user_is_preferred']))
		{
			$this->pdf->Rect($xPos + 47, $yStart, 3, 3, 'FD');
			$this->pdf->SetXY($xPos + 50, $yStart);
			$this->pdf->MultiCellTag(55, 4, "<h7b>Apply available Dinner Dollars</h7b>", $this->showBorders, 'L');
		}

		if (!empty($this->data->storeInfo) && $this->data->storeInfo->supports_bag_fee)
		{
			$this->pdf->Rect($xPos + 10, $yStart, 3, 3, 'FD');
			$this->pdf->SetXY($xPos + 13, $yStart);
			$this->pdf->MultiCellTag(34, 4, "<h7b>I will bring my bags/cooler*</h7b>", $this->showBorders, 'L');
			$yStart += 4;
			$showFeeFootnote = true;
		}
		if (empty($this->data->userInfo) || (!is_bool($this->data->userInfo) && $this->data->userInfo->platePointsData['status'] != "active" && !$this->data->userInfo->platePointsData['user_is_preferred']))
		{
			$this->pdf->Rect($xPos + 10, $yStart, 3, 3, 'FD');
			$this->pdf->SetXY($xPos + 13, $yStart);
			$this->pdf->MultiCellTag(30, 4, "<h7b>Enroll in PlatePoints</h7b>", $this->showBorders, 'L');
			// PP - Birthday
			$yStart += 7;
			$this->pdf->SetXY($xPos + 9, $yStart);
			$this->pdf->MultiCellTag(32, 4, "<h8>Birthday (optional)</h8>", $this->showBorders, 'L');

			$this->pdf->Line($xPos + 44, $yStart + 3, $xPos + 64, $yStart + 3);
			$this->pdf->SetXY($xPos + 34, $yStart);
			$this->pdf->MultiCellTag(32, 4, "<h8>Month:</h8>", $this->showBorders, 'L');

			$this->pdf->Line($xPos + 74, $yStart + 3, $xPos + 89, $yStart + 3);
			$this->pdf->SetXY($xPos + 66, $yStart);
			$this->pdf->MultiCellTag(32, 4, "<h8>Year:</h8>", $this->showBorders, 'L');
		}

		//not needed if known guest
		if (empty($this->data->userInfo))
		{
			$yStart += $scaleBasedOnItems;
			$this->pdf->Line($xPos + 22, $yStart + 3, $xPos + 89, $yStart + 3);
			$this->pdf->SetXY($xPos + 9, $yStart);
			$this->pdf->MultiCellTag(32, 4, "<h8>Address:</h8>", $this->showBorders, 'L');

			$yStart += $scaleBasedOnItems;
			$this->pdf->Line($xPos + 24, $yStart + 3, $xPos + 89, $yStart + 3);
			$this->pdf->SetXY($xPos + 9, $yStart);
			$this->pdf->MultiCellTag(32, 4, "<h8>City & Zip:</h8>", $this->showBorders, 'L');
		}

		$orderCustomizationObj = $this->data->customizations;
		if (!is_null($orderCustomizationObj))
		{
			$out = $orderCustomizationObj->toString(', ', false, '|', false, "*Circle Customization for this order: No added ");
			$parts = explode('|', $out);

			$yStart = 240 - $scaleBasedOnItems;
			//$this->pdf->SetFillColor($this->color['grey_price']['r'], $this->color['grey_price']['g'], $this->color['grey_price']['b']);
			$this->pdf->SetFillColor(224, 255, 255);
			$this->pdf->Rect($xPos + 2, $yStart, 97, 14, 'F');
			$this->pdf->SetXY($xPos + 2, $yStart);
			$this->pdf->MultiCellTag(98, 3, "<h" . $customiztionFontsize . ">" . $parts[0] . ".</h" . $customiztionFontsize . ">", $this->showBorders, 'L');
			$yStart += 4 + $customizationScale;

			if (count($parts) > 1)
			{
				$this->pdf->SetXY($xPos + 2, $yStart + 2);
				$this->pdf->MultiCellTag(98, 3, "<h" . $customiztionFontsize . ">" . $parts[1] . ".</h" . $customiztionFontsize . ">", $this->showBorders, 'L');
			}

			$showFeeFootnote = true;
		}

		if ($showFeeFootnote)
		{
			$this->pdf->SetXY($xPos + 2, 250);
			$this->pdf->MultiCellTag(98, 3, "<h6>*A nominal fee may apply</h6>", $this->showBorders, 'L');
		}

		$this->pdf->SetDrawColor(0);
	}

	function printNewAccountSessionAndPaymentSummary($yPos, $xPos)
	{
		$this->printLeftTabbedContainer($xPos, $yPos, 88, 44, "<pt8b>PAYMENT</pt8b>", 90, 10);

		$xPos = $xPos + 0;
		$yPos = $yPos + 1.5;

		$this->pdf->SetDrawColor($this->color['new_gray']['r'], $this->color['new_gray']['g'], $this->color['new_gray']['b']);

		$this->pdf->Line($xPos + 1, $yPos + 10, $xPos + 85, $yPos + 10);

		$this->pdf->Line($xPos + 45, $yPos + 6, $xPos + 45, $yPos + 10);

		$this->pdf->SetXY($xPos + 10, $yPos + 11);
		$this->pdf->MultiCellTag(35, 3, "<h6>Session Date</h6>", $this->showBorders, 'C');

		$this->pdf->SetXY($xPos + 45, $yPos + 11);
		$this->pdf->MultiCellTag(35, 3, "<h6>Session Time</h6>", $this->showBorders, 'C');

		$this->pdf->Rect($xPos + 3, $yPos + 17, 3, 3);
		$this->pdf->SetXY($xPos + 6, $yPos + 17);
		$this->pdf->MultiCellTag(65, 4, "<h7b>Cash</h7b>", $this->showBorders, 'L');

		$this->pdf->Rect($xPos + 18, $yPos + 17, 3, 3);
		$this->pdf->SetXY($xPos + 22, $yPos + 17);
		$this->pdf->MultiCellTag(30, 4, "<h7b>Check</h7b>", $this->showBorders, 'L');

		$this->pdf->Rect($xPos + 36, $yPos + 17, 3, 3);
		$this->pdf->SetXY($xPos + 40, $yPos + 17);
		$this->pdf->MultiCellTag(30, 4, "<h7b>Credit Card*</h7b>", $this->showBorders, 'L');

		$this->pdf->Rect($xPos + 60, $yPos + 17, 3, 3);
		$this->pdf->SetXY($xPos + 64, $yPos + 17);
		$this->pdf->MultiCellTag(40, 4, "<h7b>Paid in Full</h7b>", $this->showBorders, 'L');

		$this->pdf->Rect($xPos + 3, $yPos + 28, 3, 3);
		$this->pdf->SetXY($xPos + 6, $yPos + 28);
		$this->pdf->MultiCellTag(65, 4, "<h7b>Agree to Session Terms and Conditions</h7b>", $this->showBorders, 'L');

		$this->pdf->SetXY($xPos + 2, $yPos + 38);
		$this->pdf->MultiCellTag(87, 4, "<h6>*You will be contacted for credit card information by phone prior to your session.</h6>", $this->showBorders, 'L');

		$this->pdf->SetDrawColor(0);
	}

	function printStoreSummary($yPos, $includePrintedOn = false, $align = 'C', $includeStoreName = true)
	{
		$streetAddress = $this->data->storeInfo->address_line1;
		if (!empty($this->data->storeInfo->address_line2))
		{
			$streetAddress .= ', ' . $this->data->storeInfo->address_line2;
		}

		$addressLine = $streetAddress . " - " . $this->data->storeInfo->city . ", " . $this->data->storeInfo->state_id . " " . $this->data->storeInfo->postal_code;

		$contactLine = $this->data->storeInfo->email_address . " - " . $this->data->storeInfo->telephone_day;

		$printedOnPadding = -1;
		if ($includePrintedOn)
		{
			$printedOnPadding = 3;
		}

		$storeName = $this->data->storeInfo->store_name . ' - ';
		if(!$includeStoreName)
		{
			$storeName = '';
		}

		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $yPos - $printedOnPadding);
		$this->pdf->MultiCellTag(CSessionToolsPDF::kPAGE_WIDTH - 16, 4, "<t7>{$storeName}{$addressLine} - {$contactLine}</t7>", $this->showBorders, $align);

		if ($includePrintedOn)
		{
			$dt = new DateTime("now");
			$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $yPos + 2);
			$this->pdf->MultiCellTag(CSessionToolsPDF::kPAGE_WIDTH - 16, 4, "<t7>Pricing subject to change. Printed on: {$dt->format('F d, Y')}</t7>", $this->showBorders, $align);
		}
	}

	function printThisMonthsMenuPDF()
	{
		$this->pdf->AddPage();

		//$this->pdf->Rect(1, 1, 213.9, 277.4);

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, false, 20, $this->data->menu_id, true);
		$this->printMenuToolHeader(40, 5, $this->data->menu_id);
		$this->printPlatePointsSummary(40, 10);
		$this->printMenuToolHeaderRow();

		$yPos = 27.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		foreach ($this->data->menuItems as $rid => $menuItemArray)
		{
			if ($menuItemArray['entree']->isMenuItem_Core())
			{
				$globalRating = false;
				if (!empty($this->data->globalRatings[$rid]))
				{
					$globalRating = number_format($this->data->globalRatings[$rid], 2);
				}

				if (++$count == 12)
				{
					$yPos = 27.5;
				}

				$this->printMenuToolRow($menuItemArray, $yPos, $count, $globalRating);
				$yPos += self::kHEADER_ROW_HEIGHT + 7.5;
			}
		}

		$this->printSessionAndPaymentSummary(CSessionToolsPDF::kPAGE_HEIGHT - 68, CSessionToolsPDF::kLEFT_MARGIN + 100, $count);
		$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 28, 0, true);
		$this->printMenuNotes(CSessionToolsPDF::kPAGE_HEIGHT - 21, true, true, $this->data);
		$this->printStoreSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 15.5, false, 'L', false);
	}

	function printNextMonthsMenuPDF()
	{
		$this->pdf->AddPage();

		//$this->pdf->Rect(1, 1, 213.9, 277.4);

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, false, 20, $this->data->menu_id_next, true);
		$this->printMenuToolHeader(40, 5, $this->data->menu_id_next);
		$this->printPlatePointsSummary(40, 10);
		$this->printMenuToolHeaderRow();

		$yPos = 27.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		foreach ($this->data->menuItems_Next as $rid => $menuItemArray)
		{
			if ($menuItemArray['entree']->isMenuItem_Core())
			{
				$globalRating = false;
				if (!empty($this->data->globalRatings[$rid]))
				{
					$globalRating = number_format($this->data->globalRatings[$rid], 2);
				}

				if (++$count == 12)
				{
					$yPos = 27.5;
				}

				$this->printMenuToolRow($menuItemArray, $yPos, $count, $globalRating);
				$yPos += self::kHEADER_ROW_HEIGHT + 7.5;
			}
		}

		$this->printSessionAndPaymentSummary(CSessionToolsPDF::kPAGE_HEIGHT - 68, CSessionToolsPDF::kLEFT_MARGIN + 100, $count);
		$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 28, 0, true);
		$this->printMenuNotes(CSessionToolsPDF::kPAGE_HEIGHT - 21, true, true, $this->data);
		$this->printStoreSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 15.5, false, 'L', false);
	}

	function printFreezerListPDF()
	{
		$this->pdf->AddPage();

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
		$this->printHeader_Freezer(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
		$this->printMenuToolHeaderRow_Freezer();

		$yPos = 27.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		$menu_items_per_page = 17;

		if (!empty($this->data->orderItems[$this->data->userInfo->orderInfo['orderInfo']['id']]))
		{
			foreach ($this->data->orderItems[$this->data->userInfo->orderInfo['orderInfo']['id']] as $orderItem)
			{
				if ($count > 1 && ($count % $menu_items_per_page) == 0)
				{
					$this->pdf->AddPage();

					$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
					$this->printHeader_Freezer(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
					$this->printMenuToolHeaderRow_Freezer();

					$yPos = 27.5;
				}

				$globalRating = false;
				if (!empty($this->data->globalRatings[$orderItem['entree']->recipe_id]))
				{
					$globalRating = number_format($this->data->globalRatings[$orderItem['entree']->recipe_id], 2);
				}

				$delta = $this->printMenuToolRow_Freezer($orderItem, $yPos, $count++, $globalRating);
				$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 28, 8);
				//$this->printTextMessageReminders(14, CSessionToolsPDF::kPAGE_HEIGHT - 15);
				$this->printStoreSummary_Freezer(CSessionToolsPDF::kTOP_MARGIN + 264);

				if ($delta == 0)
				{
					$delta = self::kHEADER_ROW_HEIGHT;
				}

				$yPos += $delta;
			}
		}

		if ($yPos <= 232)
		{
			$this->printFreezerList_Notes(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos);
		}
	}

	function calculateRowBasedOnItems()
	{

	}

	function printFreezerList_Notes($xPos, $yPos)
	{

		$this->pdf->SetFillColor(255, 255, 255);
		$width = CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2) - 7;
		$this->pdf->Rect(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos, $width, 30, 'F');

		$this->pdf->SetXY($xPos, $yPos + 2);
		$this->pdf->MultiCellTag(60, 4, "<t11>Dinner notes and tips:</t11>", $this->showBorders, 'L');

		$this->pdf->SetXY($xPos, $yPos + 6);
		$this->pdf->MultiCellTag(CSessionToolsPDF::kPAGE_WIDTH - 23, 4, "<h8>Go to DreamDinners.com/MyMeals to add your star ratings & public comments to your My Meals page and earn PlatePoints.</h8>", $this->showBorders, 'L');
		$this->pdf->SetXY($xPos, $yPos + 12);
		$this->pdf->MultiCellTag(CSessionToolsPDF::kPAGE_WIDTH - 23, 4, "<h8>Quick Thaw Tip: Submerged your meal in cold tap water, changing the water every 30 minutes so it continues to thaw. The meal should thaw in an hour or less. To speed things up, separate the bags of sauce and meat and place them directly in the cold water. Once thawed, cook the meal immediately.</h8>", $this->showBorders, 'L');

		$this->pdf->Rect($xPos, $yPos + 20.5, CSessionToolsPDF::kPAGE_WIDTH - $xPos - 8, CSessionToolsPDF::kPAGE_HEIGHT - $yPos - 45);
	}

	function printNutritionalPDF()
	{
		if ($this->data->storeInfo->store_type == CStore::DISTRIBUTION_CENTER)
		{
			//for each box - print page

			foreach ($this->data->boxes as $boxTitle => $items)
			{
				$this->pdf->AddPage();

				$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
				$this->printHeader_Nutritional(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
				$this->printHeaderBox_Nutritional(CSessionToolsPDF::kHORZ_CENTER, 18, $boxTitle);
				$this->printMenuToolHeaderRow_Nutritional(20);

				$yPos = 48.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
				$count = 0;

				foreach ($items as $key => $orderItem)
				{
					$num_components = count($this->data->nutritionalItems[$orderItem['recipe_id']]['component']);

					$row_height = ($num_components * 4) + 5;

					if (!empty($this->data->nutritionalItems[$orderItem['recipe_id']]['info']['notes']))
					{
						$row_height += 4;
					}

					$this->printMenuToolRow_Nutritional($orderItem, $yPos, $count++, $row_height);

					$yPos += $row_height;
				}

				$this->printNutritional_Notes(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 20);
			}
		}
		else
		{
			$this->pdf->AddPage();

			$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
			$this->printHeader_Nutritional(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
			$this->printMenuToolHeaderRow_Nutritional(14);

			$yPos = 41.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
			$count = 0;

			if (!empty($this->data->orderItems[$this->data->userInfo->orderInfo['orderInfo']['id']]))
			{
				foreach ($this->data->orderItems[$this->data->userInfo->orderInfo['orderInfo']['id']] as $orderItem)
				{
					$num_components = count($orderItem['entree']->nutrition_array['component']);

					$row_height = ($num_components * 4) + 5;

					if (!empty($orderItem['entree']->nutrition_array['component'][1]['info']['notes']))
					{
						$row_height += 4;
					}

					$this->printMenuToolRow_Nutritional($orderItem, $yPos, $count++, $row_height);

					$yPos += $row_height;
				}
			}

			$this->printNutritional_Notes(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 20);
		}
	}

	function printNutritional_Notes($yPos)
	{
		$this->pdf->SetXY(CSessionToolsPDF::kLEFT_MARGIN, $yPos);
		$this->pdf->MultiCellTag(CSessionToolsPDF::kPAGE_WIDTH - 16, 4, "<t8>Nutritional information is per serving unless otherwise stated and are based on standard formulations. For example, if one Large dinner was divided into 6 equal portions, the nutritional information is accurate for one portion. Variations may occur due to manufacture/supplier alterations and individual assembly and preparation.</t8>", $this->showBorders, 'C');
	}

	function printReceipt($customerOnly)
	{
		$this->pdf->AddPage();
		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id);
	}

	/* Generic pages */
	function printGenericRecipeExpertPDF()
	{
		$this->pdf->AddPage('L');

		//$this->pdf->Rect(1, 1, 277.4, 213.9);

		$this->printLogo_RecipeExpert(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
		$this->printHeader_RecipeExpert(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
		$this->printMenuToolHeaderRow_RecipeExpert();
		$this->printRecipeExpertKey(CSessionToolsPDF::lPAGE_HEIGHT - 8);

		$yPos = 27.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		foreach ($this->data->menuItems as $rid => $menuItemArray)
		{
			if ($yPos >= 190 && $yPos <= CSessionToolsPDF::lPAGE_HEIGHT)
			{
				$this->pdf->AddPage('L');

				$this->printLogo_RecipeExpert(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
				$this->printHeader_RecipeExpert(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
				$this->printMenuToolHeaderRow_RecipeExpert();
				$this->printRecipeExpertKey(CSessionToolsPDF::lPAGE_HEIGHT - 8);

				$yPos = 27.5;
			}

			$num_desc_lines = $this->pdf->NbLines(160, "<h9>" . html_entity_decode($menuItemArray['entree']->DAO_recipe->recipe_expert, ENT_QUOTES, 'WINDOWS-1252') . "</h9>");

			$row_height = 4.5 + ($num_desc_lines * 3);

			$num_cooking_lines = $this->pdf->NbLines(18, "<h9>" . $menuItemArray['cooking_method'] . "</h9>");
			$num_prep_time_lines = $this->pdf->NbLines(18, "<h9>" . $menuItemArray['prep_time'] . "</h9>");
			$num_packaging_lines = $this->pdf->NbLines(18, "<h9>" . $menuItemArray['packaging'] . "</h9>");

			$highest_lines = max($num_cooking_lines, $num_prep_time_lines, $num_packaging_lines);

			if ($highest_lines > $num_desc_lines)
			{
				$row_height = 1.5 + ($highest_lines * 3);
			}

			if ($menuItemArray['entree']->isVisible() && !$menuItemArray['entree']->isHiddenEverywhere())
			{
				$this->printMenuToolRow_RecipeExpert($menuItemArray, $yPos, $count++, $row_height);
				$yPos += $row_height;
			}
		}
	}

	function printGenericCoreMenuPDF()
	{
		$this->pdf->AddPage();

		//$this->pdf->Rect(1, 1, 213.9, 277.4);

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, false, 20, $this->data->menu_id, true);
		$this->printMenuToolHeader(CSessionToolsPDF::kHORZ_CENTER, 5, $this->data->menu_id);
		$this->printNewAccountInfo_Menu(CSessionToolsPDF::kTOP_MARGIN, CSessionToolsPDF::kLEFT_MARGIN + 30);
		$this->printMenuToolHeaderRow();

		$yPos = 27.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		foreach ($this->data->menuItems as $rid => $menuItemArray)
		{
			if ($menuItemArray['entree']->isMenuItem_Core())
			{
				$globalRating = false;
				if (!empty($this->data->globalRatings[$rid]))
				{
					$globalRating = number_format($this->data->globalRatings[$rid], 2);
				}

				if ($menuItemArray['entree']->isVisible() && !$menuItemArray['entree']->isHiddenEverywhere())
				{
					if (++$count == 12)
					{
						$yPos = 27.5;
					}

					$this->printMenuToolRow($menuItemArray, $yPos, $count, $globalRating);
					$yPos += self::kHEADER_ROW_HEIGHT + 7.5;
				}
			}
		}

//		$this->printSessionAndPaymentSummary(CSessionToolsPDF::kPAGE_HEIGHT - 68, CSessionToolsPDF::kLEFT_MARGIN + 100, $count);
//		$this->printStoreSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 15.5, true, 'L');
//		$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 28, 0, true);
//		$this->printMenuNotes(CSessionToolsPDF::kPAGE_HEIGHT - 21, true, true, $this->data);

		$this->printSessionAndPaymentSummary(CSessionToolsPDF::kPAGE_HEIGHT - 68, CSessionToolsPDF::kLEFT_MARGIN + 100, $count);
		$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 28, 0, true);
		$this->printMenuNotes(CSessionToolsPDF::kPAGE_HEIGHT - 21, true, true, $this->data);
		$this->printStoreSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 15.5, false, 'L', false);
	}

	function printTextMessageReminders($xPos, $yPos)
	{
		$this->pdf->SetXY($xPos, $yPos);
		$this->pdf->MultiCellTag(CSessionToolsPDF::kPAGE_WIDTH - (CSessionToolsPDF::kLEFT_MARGIN * 2) - 7, 3, "<h8>Sign up to receive weekly text thaw reminders, session reminders and exclusive promotions. Log in to your account on DreamDinners.com to select your text message preferences. *Up to 12Msgs/Month. Reply <h8b>STOP</h8b> to cancel, <h8b>HELP</h8b> for help. Msg & data rates may apply.</h8>", $this->showBorders, 'L');
	}

	function printGenericFreezerListPDF()
	{
		$this->pdf->AddPage();

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
		$this->printHeader_Freezer(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
		$this->printMenuToolHeaderRow_Freezer();

		$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 28, 8);
		//$this->printTextMessageReminders(14, CSessionToolsPDF::kPAGE_HEIGHT - 15);
		$this->printStoreSummary_Freezer(CSessionToolsPDF::kTOP_MARGIN + 264);

		$yPos = 27.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		$menu_items_per_page = 17;

		foreach ($this->data->menuItems as $rid => $menuItemArray)
		{
			if ($count > 1 && ($count % $menu_items_per_page) == 0)
			{
				$this->pdf->AddPage();

				$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
				$this->printHeader_Freezer(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
				$this->printMenuToolHeaderRow_Freezer();

				$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 28, 8);
				//$this->printTextMessageReminders(14, CSessionToolsPDF::kPAGE_HEIGHT - 15);
				$this->printStoreSummary_Freezer(CSessionToolsPDF::kTOP_MARGIN + 264);

				$yPos = 27.5;
			}

			$globalRating = false;
			if (!empty($this->data->globalRatings[$rid]))
			{
				$globalRating = number_format($this->data->globalRatings[$rid], 2);
			}

			if ($menuItemArray['entree']->isVisible() && !$menuItemArray['entree']->isHiddenEverywhere())
			{
				$delta = $this->printMenuToolRow_Freezer($menuItemArray, $yPos, $count++, $globalRating);
				//				if(is_null($delta)){
				//					$delta = self::kHEADER_ROW_HEIGHT;
				//				}
				$yPos += self::kHEADER_ROW_HEIGHT;
			}
		}

		if ($yPos <= 232)
		{
			$this->printFreezerList_Notes(CSessionToolsPDF::kLEFT_MARGIN + 7, $yPos);
		}
	}

	function printGenericNutritionPDF()
	{
		$this->pdf->AddPage();

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
		$this->printHeader_Nutritional(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
		$this->printMenuToolHeaderRow_Nutritional(14);

		$yPos = 41.5; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		foreach ($this->data->menuItems as $rid => $menuItem)
		{
			if ($menuItem['entree']->isMenuItem_Core())
			{
				$num_components = count($menuItem['entree']->nutrition_array['component']);

				$row_height = ($num_components * 4) + 5;

				if (!empty($menuItem['entree']->nutrition_array['component'][1]['info']['notes']))
				{
					$row_height += 4;
				}

				if ($menuItem['entree']->isVisible() && !$menuItem['entree']->isHiddenEverywhere())
				{
					$this->printMenuToolRow_Nutritional($menuItem, $yPos, $count++, $row_height);
					$yPos += $row_height;
				}
			}
		}

		$this->printNutritional_Notes(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 20);
	}

	function printGenericIntroMenuPDF()
	{
		$this->pdf->AddPage();

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
		$this->printHeader_Intro(CSessionToolsPDF::kHORZ_CENTER, 6, true, $this->data);
		$this->printMenuToolHeaderRow_Intro();

		$yPos = 27.5 + 27; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		foreach ($this->data->menuItems as $rid => $menuItem)
		{
			if ($count == 12)
			{
				break;
			}

			if ($menuItem['menu_item_id'] == 18691)
			{
				continue;
			}

			$globalRating = false;
			if (!empty($this->data->globalRatings[$rid]))
			{
				$globalRating = number_format($this->data->globalRatings[$rid], 2);
			}

			if ($menuItem['entree']->isVisible() && !$menuItem['entree']->isHiddenEverywhere())
			{
				$this->printMenuToolRow_Intro($menuItem, $yPos, $count++, $globalRating);
				$yPos += self::kHEADER_ROW_HEIGHT;
			}
		}

		$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 68, 8);
		$this->printNewAccountInfo(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 61, CSessionToolsPDF::kLEFT_MARGIN + 14);
		$this->printNewAccountSessionAndPaymentSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 61, CSessionToolsPDF::kLEFT_MARGIN + 112);
		$this->printStoreSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 15.5);
		$this->printMenuNotes_Intro(CSessionToolsPDF::kTOP_MARGIN + 266);
	}

	function printGenericDreamTasteMenuPDF()
	{
		$this->pdf->AddPage();

		$this->printLogo(CSessionToolsPDF::kLEFT_MARGIN, CSessionToolsPDF::kTOP_MARGIN, 31.8, false, $this->data->menu_id, true);
		$this->printHeader_DreamTaste(CSessionToolsPDF::kHORZ_CENTER, 6, $this->data->menu_id);
		$this->printMenuToolHeaderRow_DreamTaste();

		$yPos = 27.5 + 27; //self::kHEADER_ROW_HEIGHT + self::kKEADER_ROW_Y;
		$count = 0;
		foreach ($this->data->menuItems as $rid => $menuItem)
		{
			$globalRating = false;
			if (!empty($this->data->globalRatings[$rid]))
			{
				$globalRating = number_format($this->data->globalRatings[$rid], 2);
			}

			if ($menuItem['entree']->isVisible() && !$menuItem['entree']->isHiddenEverywhere())
			{
				$this->printMenuToolRow_DreamTaste($menuItem, $yPos, $count++, $globalRating);
				$yPos += self::kHEADER_ROW_HEIGHT;
			}
		}

		$this->printIconLegend(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 68, 8);
		$this->printNewAccountInfo(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 61, CSessionToolsPDF::kLEFT_MARGIN + 14);
		$this->printNewAccountSessionAndPaymentSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 61, CSessionToolsPDF::kLEFT_MARGIN + 112);
		$this->printStoreSummary(CSessionToolsPDF::kTOP_MARGIN + CSessionToolsPDF::kPAGE_HEIGHT - 15.5);
		$this->printMenuNotes_DreamTaste(CSessionToolsPDF::kTOP_MARGIN + 266);
	}

	function getStoreMenuQrCodePath($store_id, $menu_id, $forceQrRegen = false)
	{
		$path = ASSETS_PATH . '/pdf_label/qrcode/qr-store-menu-' . $store_id . '-' . $menu_id . '.png';

		if (!file_exists($path) || $forceQrRegen)
		{
			QRcode::png(HTTPS_BASE . 'menu/' . $store_id . '-' . $menu_id, $path, QR_ECLEVEL_L, 3, 4, false);
		}

		return $path;
	}

	function output($target = 'print')
	{
		$this->pdf->output();
	}
}

?>