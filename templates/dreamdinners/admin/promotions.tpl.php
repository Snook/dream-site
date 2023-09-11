<?php $this->assign('page_title','Promotions'); ?>
<?php $this->assign('topnav','store'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-styles-reports.css'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php $isSiteAdmin = ($this->form_login['user_type'] == 'SITE_ADMIN'); ?>

<script type="text/javascript">
<?php if ($isSiteAdmin) { ?>
		function deletePromoConfirm(myId) {
			if ( confirm('Are you sure you want to delete this promo?') ) {
				window.location = '<?=$_SERVER['REQUEST_URI'].'&'?>delete=' + myId;
			}
		}
<?php } ?>
</script>

<table width="100%"><tr>
<td align=center>
<?php if ($this->show_active_menus == true) { ?>
<a href="#" class="fadmin_nav fadmin_subnav_active">Active</a>
<a href="/?page=admin_promotions&show_active_menus=true" class="fadmin_nav fadmin_subnav">Archived</a>
<?php } else { ?>
<a href="/?page=admin_promotions" class="fadmin_nav fadmin_subnav">Active</a>
<a href="#" class="fadmin_nav fadmin_subnav_active">Archived</a>
<?php }  ?>
</td>
</tr></table>


 <?php if (isset($this->promos) && $this->promos) { ?>
<table width="100%">
  <tbody>

            <tr>
                <td class="bgcolor_medium header_row"><div align="left">Promo Code </div></td>
                <td class="bgcolor_medium header_row"><div align="left">Promo Title </div></td>
                <td class="bgcolor_medium header_row"><div align="left">Discounted Item</div></td>
				<td class="bgcolor_medium header_row"><div align="left">Menu Item Size</div></td>
              </tr>
		<?php foreach($this->promos as $menu_id_object) { ?>
		    <?php foreach($menu_id_object as $promo) { ?>
		   	  <tr>
		      	<td class="bgcolor_light"><div align="left"><?php if ($isSiteAdmin) { ?> <a href="javascript:deletePromoConfirm('<?=$promo['id'];?>');">delete</a>&nbsp;&nbsp;<?php } ?><strong><?=$promo['promo_code'];?></strong></div></td>
		      	<td class="bgcolor_light"><div align="left"><?php echo $promo['promo_title']; ?></div></td>
		      	<td class="bgcolor_light"><div align="left"><?php echo CAppUtil::truncate($promo['menu_item_name'], 50); ?></div></td>
				<td class="bgcolor_light"><div align="left"><?php echo $promo['pricing_type'] === CMenuItem::HALF ?  "3 Servings" :  "6 Servings"; ?></div></td>
		      </tr>
		    <?php } ?>
		 <?php } ?>
  </tbody>
</table>
<?php } else { ?>
	<p><i>there are currently no promotions available</i></center></p>
<?php } ?>
<p>&nbsp;</p>
   	<?php if ((!isset($this->displayOnly)) || (!$this->displayOnly) ) { ?>
		<form name="promoForm" action="" method="post">
		<table width="100%">
		    <tbody>
		      <tr>
		         <td class="bgcolor_dark catagory_row" colspan="2"><b>Create Promotion</b></td>
		       <tr>
		      <tr>
		         <td class="bgcolor_light"> Promotion Title: </td><td class="bgcolor_light">
		          <?= $this->form_promos['promo_title_html'] ?>
			      </td>
		       <tr>
		        <td class="bgcolor_light"> Menu Item: </td><td class="bgcolor_light">
		          <?= $this->form_promos['promo_menu_item_id_html'] ?>
		        </td>
		      </tr>
		       <tr>
		        <td class="bgcolor_light"> Description: </td><td class="bgcolor_light">
		          <?= $this->form_promos['promo_description_html'] ?>
		        </td>
		      </tr>
		      <tr>
		        <td class="bgcolor_light"> Promotion Code: </td><td class="bgcolor_light">
		          <?= $this->form_promos['promo_code_html'] ?>
		        </td>
		      </tr>
		       <tr>
		        <td class="bgcolor_light"> Promotion Type: </td><td class="bgcolor_light">
		          <?= $this->form_promos['promo_type_html'] ?>
		        </td>
		      </tr>
<?php /*
		       <tr>
		        <td class="bgcolor_light"> Active: </td><td class="bgcolor_light">
		          <?= $this->form_promos['promo_code_active_html'] ?>
		        </td>
		      </tr>
		       <tr>
		        <td class="bgcolor_light"> Value: </td><td class="bgcolor_light">
		          <?= $this->form_promos['promo_var_html'] ?>
		        </td>
		      </tr>
<?php */ ?>
		      <tr>
		        <td class="bgcolor_light"> Notes: </td><td class="bgcolor_light">
		          <?= $this->form_promos['note_html'] ?>
		        </td>
		      </tr>
		        <td class="bgcolor_light" align="right">
		        </td>
		        <td class="bgcolor_light" align="right">
		        <?= $this->form_promos['new_submit_html'] ?>
		        </td>
		      </tr>
		    </tbody>
		  </table>
		 </form>
	  <?php } ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>