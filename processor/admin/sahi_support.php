<?php
require_once("includes/CPageProcessor.inc");
require_once('includes/CMail.inc');

class processor_admin_sahi_support extends CPageProcessor
{
    
    
    // easier to run in public than to 
    // determine how to embed credentials in REST call
	function runPublic()
	{
		$this->sahi_supportProcessor();
	}

    
	function sahi_supportProcessor()
	{		
	    if (!empty($_POST['op']))
		{
		    
		    $menu_id = false;
		    if (!empty($_REQUEST['menu_id']) && is_numeric($_REQUEST['menu_id']))
		    {
		        $menu_id = $_REQUEST['menu_id'];
		    }
		    else
		    {
		        echo json_encode(array(
		            'processor_success' => false,
		            'processor_message' => 'The menu id is invalid.'
		        ));
		        exit;
		    }
		    
		    $store_id = false;
		    if (!empty($_REQUEST['store_id']) && is_numeric($_REQUEST['store_id']))
		    {
		        $store_id = $_REQUEST['store_id'];
		    }
		    else
		    {
		        echo json_encode(array(
		            'processor_success' => false,
		            'processor_message' => 'The store id is invalid.'
		        ));
		        exit;
		    }
		    
			// Get helpdesk form
			if ($_POST['op'] == 'get_random_session')
			{
			     
			  			   			     
			    $options = array();
			    
			    $sessions = DAO_CFactory::create('session');
			    $sessions->query("select * from session where  menu_id = $menu_id and store_id = $store_id
			        and session_type = 'STANDARD' and session_publish_state = 'PUBLISHED' and is_deleted = 0 and session_start > now()");
			    
			    while($sessions->fetch())
			    {
			        if ($sessions->getRemainingSlots() > 0)
			        {
			            $options[] = $sessions->id;
			        }
			    }
			    
			    if (count($options))
			    {
			        shuffle($options);
			        
			        $chosen = array_shift($options);
			        
			         
			        echo json_encode(array(
			            'processor_success' => true,
			            'processor_message' => 'Session found.',
			            'session_id' => $chosen
			        ));
			        exit;
			    }
			    
    			echo json_encode(array(
    				'processor_success' => false,
    				'processor_message' => 'Session not available.'
    			));
    			exit;
			    
			
			}
			else if ($_POST['op'] == 'get_random_core_items')
			{
			    $items = DAO_CFactory::create('menu_item');
			    $items->query("select mi.recipe_id, GROUP_CONCAT(mi.id) as mids, GROUP_CONCAT(mi.servings_per_item) as servings, mii.override_inventory - mii.number_sold as avail from menu_to_menu_item mmi
			        join menu_item mi on mmi.menu_item_id = mi.id and mi.menu_item_category_id < 5 and mi.is_deleted = 0
			        join menu_item_inventory mii on mi.recipe_id = mii.recipe_id and mii.store_id = $store_id and mii.menu_id = $menu_id
			        where mmi.store_id = $store_id and mmi.menu_id = $menu_id and mmi.is_visible = 1 and mii.is_deleted = 0 group by mi.recipe_id");
			    
			    $options = array();
			    
			    while($items->fetch())
			    {
			        $options[$items->recipe_id] = array('mids' => explode(",", $items->mids), 'servings' => explode(",", $items->servings), 'avail' => $items->avail);
			    }
			    
			    shuffle($options);
			    
			    $totalServings = 0;
			    
			    $itemArr = array();
			    
			    foreach($options as $rid => $data)
			    {
			    
			        if ($data['avail'] > 6)
			        {
			            if (count($data['mids']) == 2)
			            {
			    
			                if (rand(0, 100) > 50)
			                {
			                    $selectedMID = $data['mids'][1];
			                    $numServings = $data['servings'][1];
			                }
			                else
			                {
			                    $selectedMID = $data['mids'][0];
			                    $numServings = $data['servings'][0];
			    
			                }
			    
			            }
			            else
			            {
			                $selectedMID = $data['mids'][0];
			                $numServings = $data['servings'][0];
			            }
			    
			    
			            $totalServings += $numServings;
			            $itemArr[] = $selectedMID;
			    
			            if ($totalServings > 36)
			                break;
			        }
			    
			    }
			    
			    echo json_encode(array(
			        'processor_success' => true,
			        'processor_message' => 'items found.',
			        'items' => $itemArr
			    ));
			    exit;
			     
			    
			    
			}
			else if ($_POST['op'] == 'get_month_number_with_menu_id')
			{
			    CLog::Record("Okay Dokay!");
			     
			    $menu_id = false;
			    if (!empty($_REQUEST['menu_id']) && is_numeric($_REQUEST['menu_id']))
			    {
			        $menu_id = $_REQUEST['menu_id'];
			    }
			    else
			    {
			        echo json_encode(array(
			            'processor_success' => false,
			            'processor_message' => 'The menu id is invalid.'
			        ));
			        exit;
			    }
			     
			    $menuObj = 	DAO_CFactory::create('menu');
			    $menuObj->id = $menu_id;
			    $menuObj->find(true);
			    
			    $monthNum = strtotime($menuObj->menu_start);
			    
			    CLog::Record("Bloopp: " . $monthNum );
			    
			    echo json_encode(array(
			        'processor_success' => true,
			        'processor_message' => 'The menu id is valid.',
			        'menu_number' => $monthNum
			    ));
			    exit;
			     
			    
			}
		}
	    
	}
}
?>