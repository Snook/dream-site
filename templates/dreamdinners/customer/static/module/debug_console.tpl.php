<table style="width:100%;">
	<tr>
		<td colspan="2"><h3>DebugInfo</h3></td>
	</tr>
	<tr>
		<td>Cookies</td>
		<td><pre><?php print_r($_COOKIE);?></pre></td>
	</tr>
	<tr>
		<td>User</td>
		<td><pre><?php print_r(CUser::getCurrentUser()->toArray());?></pre></td>
	</tr>
	<tr>
		<td>Franchise Store</td>
		<td><pre><?php print_r(CStore::getFranchiseStore() != null ? CStore::getFranchiseStore()->toArray() : 'Not Set');?></pre></td>
	</tr>
</table>

<?php
if (isset($this->aDebugStrings))
{
	foreach( $this->aDebugStrings as $strDebug )
	{
		echo $strDebug;
	}
}
?>