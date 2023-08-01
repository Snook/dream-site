<?php
$this->assign('page_title','Performance Page Override');
$this->assign('topnav','reports');

include $this->loadTemplate('admin/page_header.tpl.php');
?>



<br/>
<br/>
<hr/>

Add a Royalty Performance Override.  This screen allows the ability to add 1 to n Performance overrides for any store.
It is flexible in that you can add additional overrides if a store falls under new ownership.

Please be careful to review the list of Overrides at the bottom of the page before adding a new override.
<hr/>


<table>
<form action="" method="post" onSubmit="return _check_form(this);" >

<tr>
<td width=160>&nbsp;</td>
<td>&nbsp;</td>
</tr>

<tr>
<td width=160>
Select a Store:
</td>
<td><?=$this->form_create['performance_store_html']?></td>

</tr>


<tr>
<td width=160>&nbsp;</td>
<td>&nbsp;</td>
</tr>

<tr>
<td colspan=2>Enter the starting date of the performance override: </td>
</tr>


<tr>
<td width=160>Starting Month:</td>
<td><?=$this->form_create['month_dropdown_html']?></td>
</tr>

<tr>
<td>Starting Year:</td>
<td><?=$this->form_create['starting_year_html']?>&nbsp;<i>(4 digit value)</i></td>
</tr>

<tr>
<td colspan=2>&nbsp;</td>
</tr>

<tr>
<td colspan=2>Enter the ending date of the performance override: </td>
</tr>



<tr>
<td>Ending Month:</td>
<td><?=$this->form_create['end_month_dropdown_html']?></td>
</tr>

<tr>
<td>Ending Year:</td>
<td><?=$this->form_create['ending_year_html']?>&nbsp;<i>(4 digit value)</i></td>
</tr>


<tr><td>
<?=$this->form_create['user_submit_html']?>
</td></tr>

</table>

</form >
<?php if (!empty($this->userpages)) {  ?>

<br/>
<hr/>

List of all stores that have one-time Royalty Peformance Overrides
<hr/>
<form action="" method="post" onSubmit="return _check_form(this);" >


<table>

<tr>

<td class="paymentFormTables" width=90><strong>Store ID</strong></td>
<td class="paymentFormTables" width=90><strong>Home ID</strong></td>
<td class="paymentFormTables" width=200><strong>Name</strong></td>
<td class="paymentFormTables" width=90><strong>City</strong></td>
<td class="paymentFormTables" width=90><strong>State</strong></td>
<td class="paymentFormTables" width=90><strong>Perf ID</strong></td>
<td class="paymentFormTables" width=90><strong>Start Date</strong></td>
<td class="paymentFormTables" width=90><strong>End Date</strong></td>
<td class="paymentFormTables" width=90><strong>Added By</strong></td>

<td class="paymentFormTables" width=90><strong>Deactivate</strong></td>
</tr>

<?php foreach($this->userpages as $element ){   ?>
<tr>

<td class="paymentFormTables" ><?=$element['home_office_id']?></td>
<td class="paymentFormTables" ><?=$element['store_id']?></td>
<td class="paymentFormTables" ><?=$element['store_name']?></td>
<td class="paymentFormTables" ><?=$element['city']?></td>
<td class="paymentFormTables" ><?=$element['state_id']?></td>
<td class="paymentFormTables" ><?=$element['id']?></td>
<td class="paymentFormTables" ><?=$element['performance_start_date']?></td>
<td class="paymentFormTables" ><?=$element['performance_end_date']?></td>
<td class="paymentFormTables" ><?=$element['user_changed']?></td>
<td class="paymentFormTables" ><INPUT TYPE=CHECKBOX NAME="ch_<?=$element['id']?>"></td>
</tr>
<?php } ?>

<td align=right colspan=10><?=$this->form_create['remove_access_html']?>
</td>

</table>


</form>

<?php } ?>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
