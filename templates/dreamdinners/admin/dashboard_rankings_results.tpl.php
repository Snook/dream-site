<table width="100%" style="border-collapse: collapse; padding:0px; margin-bottom:12px; border:solid 1px black;" class="top30resultsBG">
  <tr>
  	<td class="space_right_delimited_top30" style="text-align:center; width:280px;">Store Name</td>
  	<td class="space_right_delimited_top30" style="text-align:center; width:200px;">City</td>
  	<td class="space_right_delimited_top30" style="text-align:center; width:88px;">State</td>
  	<td class="space_right_delimited_top30" style="text-align:center; width:305px;"><?php echo $this->metric_display_name;?></td>
  	<td class="space_right_delimited_top30" style="text-align:center;">Rank</td>
  </tr>

 <?php foreach ($this->printArray as $rank => $thisStore) { ?>

  <tr>
    <td class="value_delimited_top30" style="text-align:left; padding-left:10px;"> <?php echo $thisStore['store_name'];?> </td>
    <td class="value_delimited_top30" style="text-align:left; padding-left:10px;"><?php echo $thisStore['city'];?></td>
    <td class="value_delimited_top30" style="text-align:center;"><?php echo $thisStore['state'];?></td>
    <td class="value_delimited_top30" style="text-align:center; padding-left:10px; width:120px;"><?php echo $this->preVal, $thisStore['value'], $this->postVal;?></td>
    <td class="value_delimited_top30" style="text-align:center;"><?php echo $thisStore['rank'];?></td>
  </tr>

<?php } ?>

</table>

