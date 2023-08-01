<ul id="ddd_menu">
  <li id="#reset_tree" ><a href="#">Reset/Show All</a></li>
  <li id="#select_all_tree"><a href="#">Select All</a></li>
  <li id="#select_franchise_tree"><a href="#">Select All Franchise</a></li>
  <li id="#select_corporate_tree"><a href="#">Select All Corporate</a></li>
	<li id="#select_non_DC_tree"><a href="#">Select All Brick and Mortar</a></li>
	<li id="#select_dist_ctr_tree"><a href="#">Select All Distribution Centers</a></li>
</ul>



<div style="border:thin solid black; min-width:380px;  margin-left:10px; margin-top:10px; text-align:center; background-color:#BEB7AE; float:left;"><div style="margin-left:10px; margin-top:10px;" >
	<img id="tree_menu_button" src="<?php echo ADMIN_IMAGES_PATH?>/gear.png" style="position:relative;" />
	<h2 style="margin-left:10px; margin-top:10px; display:inline;">Select Store(s)</h2>
	<input type=checkbox name="hide_inactive" id="hide_inactive" checked="checked" /><label style="color:red; font-weight:bold;" for="hide_inactive">Hide Inactive Stores</label></div>
	<div id="store_selector" style="min-width:380px; max-width:380px; border:thin solid black; margin:10px; padding:10px; text-align:left; display:none">
		<?php echo $this->store_data;?>
	</div>
</div>