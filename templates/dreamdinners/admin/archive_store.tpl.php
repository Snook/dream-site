<?php
$this->assign('page_title','Archive (Re-Open) Store Page');
	$this->assign('topnav','store');

include $this->loadTemplate('admin/page_header.tpl.php');
?>

Use this page to archive an old store and allow for a store to be "Re-opened" with new data such as the grand opening date and home office id.

<br/><br/>
<hr/>

<font color="#990000"><b>Current Store Details:</b></font>
<table>
<tr>

<td width=180><strong>Current Store Home Office ID:</strong></td>
<td><?=$this->store->home_office_id?></td>
</tr>

<tr>

<td width=180><strong>Current Store Grand Open Date:</strong></td>
<td><?=$this->store->grand_opening_date?></td>
</tr>

</table>


<hr/>
<form action="" method="post" onSubmit="return _check_form(this);" >




<table>

<tr><td><font color="#990000">Step 1: Enter a new "Re-Opening" - "Re-Sale" store date:</font></td></tr>

</table>

<table>

<tr>

<td width=20>Month:</td>
<td><?=$this->form_create['grandOpeningMonth_html']?></td>

<td width=20>Day:</td>
<td><?=$this->form_create['grandOpeningDay_html']?></td>



<td>Year:</td>
<td><?=$this->form_create['grandOpeningYear_html']?>&nbsp;<i>(4 digit value)</i></td>
</tr>

</table>

<hr/>

<table>
<tr><td><font color="#990000">Step 2: Enter original store closed (if available):</font></td></tr>
</table>

<table>
<tr>
<td width=20>Month:</td>
<td><?=$this->form_create['storeClosedMonth_html']?></td>

<td width=20>Day:</td>
<td><?=$this->form_create['storeClosedDay_html']?></td>
<td>Year:</td>
<td><?=$this->form_create['storeClosedYear_html']?>&nbsp;<i>(4 digit value)</i></td>
</tr>
</table>

<hr/>

<table>

<tr><td><font color="#990000">Step 3: Enter the new home office ID and any needed notes:</font></td></tr>

</table>

<table>


<tr>
<td>New Home Office ID:</td>
<td><?=$this->form_create['homeOfficeID_html']?>&nbsp;</td>
</tr>


<tr>
<td>Comments:</td>
<td><?=$this->form_create['admin_notes_html']?></td>
</tr>


<tr><td>
<?=$this->form_create['user_submit_html']?>
</td></tr>

</table>

</form>

<br/><br/>
<hr/>


<font color="#990000">All Stores -- Archived Store Detail Info</font>
<br/><br/>
<i>The information below shows <font color=red>all stores</font> that have been archived in the past.  To become an archived store, the form above needs to be used to record when the store
was originally closed and when it was re-opened by a new owner.  A re-opened store needs a new Grand Opening date and Home Office ID.</i>
<hr/>

<?php
if (!empty($this->archived_stores)) {
	echo "<table>";

	echo "<tr><td  class='paymentFormTables'></td><td  class='paymentFormTables'>Store ID</td><td width=40 class='paymentFormTables'>Info Recorded By</td><td  class='paymentFormTables'>Archived Grand Opening Date</td><td width=80  class='paymentFormTables'>Archived Home Office ID</td><td  class='paymentFormTables'>Store Close Date</td><td  class='paymentFormTables' width=200>Details</td></tr>";
	$counter = 1;
	foreach($this->archived_stores as $elements){
	echo "<tr>";
	echo "<td class='paymentFormTables'>" . $counter . "</td>";
	echo "<td class='paymentFormTables'>" . $elements['store_id'] . "</td>";

	echo "<td  class='paymentFormTables'>" . $elements['info_recorded_by'] . "</td>";
	echo "<td  class='paymentFormTables'>" . $elements['recorded_grand_opening_date'] . "</td>";
	echo "<td  class='paymentFormTables'>" . $elements['recorded_home_office_id'] . "</td>";
	echo "<td  class='paymentFormTables'>" . $elements['store_closure_date'] . "</td>";
	echo "<td  class='paymentFormTables'>" . $elements['details'] . "</td>";
	$counter++;
	echo "</tr>";
	}



	echo "</table>";
}
else
{
	echo "No archived store information exists";

}
?>




<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
