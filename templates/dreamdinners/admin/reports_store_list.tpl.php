<?php $this->assign('page_title','Browse/Edit Stores'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<h2>Active Stores</h2>
<form method="post">
<div>

<input type="submit" name="submit_top_5" value="Submit" />
</div>

<table width="100%" style="border-collapse: collapse; padding:0px; margin-bottom:12px; border:solid 1px black; background-color:#e0e0e0;">
  <tr>
    <td class="space_right_delimited_top30" style="text-align:center; border-bottom:1px solid black;">Is in<br />current<br />top 5</td>
  <td class="space_right_delimited_top30" style="text-align:left; border-bottom:1px solid black;">Home Office<br />ID</td>
  	<td class="space_right_delimited_top30" style="text-align:left; border-bottom:1px solid black;">Store Name</td>
  	<td class="space_right_delimited_top30" style="text-align:left; border-bottom:1px solid black;">City</td>
  	<td class="space_right_delimited_top30" style="text-align:center; border-bottom:1px solid black;">State</td>
  	<td class="space_right_delimited_top30" style="text-align:center; border-bottom:1px solid black;"><?php echo $this->metric_display_name;?></td>
  	<td class="space_right_delimited_top30" style="text-align:center; border-bottom:1px solid black;">Rank</td>

  </tr>

 <?php foreach ($this->printArray as $rank => $thisStore) { ?>

  <tr>

      <td class="value_delimited_top30" style="text-align:center;"><?php echo $this->form_list_stores["sid_" . $thisStore['store_id'] . "_html"];?></td>
    <td class="value_delimited_top30" style="text-align:left; padding-left:10px;"> <?php echo $thisStore['hoid'];?> </td>
          <td class="value_delimited_top30" style="text-align:left; padding-left:10px;"> <?php echo $thisStore['store_name'];?> </td>
    <td class="value_delimited_top30" style="text-align:left; padding-left:10px;"><?php echo $thisStore['city'];?></td>
    <td class="value_delimited_top30" style="text-align:center;"><?php echo $thisStore['state'];?></td>
    <td class="value_delimited_top30" style="text-align:center; padding-left:10px; width:120px;"><?php echo $this->preVal, $thisStore['value'], $this->postVal;?></td>
    <td class="value_delimited_top30" style="text-align:center;"><?php echo $thisStore['rank'];?></td>

  </tr>

<?php } ?>

</table>
</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>