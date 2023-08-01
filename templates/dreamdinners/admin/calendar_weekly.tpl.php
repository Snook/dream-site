<?php
/*
 */

//set default calendar name
if ( !isset($this->calendarName) )
	$this->calendarName = 'myCalendar';
?>


<script type="text/javascript">

	var selectedCell = null;
	var selectedRow = null;
	var orgColor = null;

<?php if (isset($this->support_row_selection) && $this->support_row_selection) { ?>

	function onRowClick(obj)
	{
		if (obj == selectedRow)
			return;

		if (selectedRow)
			selectedRow.bgColor = orgColor;

		orgColor = obj.bgColor;
		obj.bgColor = "#CAA9DE";
		selectedRow = obj;
		<?php if (isset($this->add_call_back)) ?>
			rowClick(obj);


	}
	<?php } else { ?>

	function onRowClick(obj)
	{
	}
	<?php } ?>


	<?php if (isset($this->support_cell_selection) && $this->support_cell_selection) { ?>
	// Universal Behavior
	function onDayClick(obj)
	{

		if (obj == selectedCell)
			return;

		if (selectedCell)
			selectedCell.bgColor = orgColor;

		orgColor = obj.bgColor;

		obj.bgColor = "#CAA9DE";
		selectedCell = obj;

		<?php if (isset($this->add_call_back)) ?>
			cellClick(obj);
	}
	<?php } else { ?>

	function onDayClick(obj)
	{
	}
	<?php } ?>



</script>




<!-- The HTML Loops for Rows(weeks) and columns (days)-->

<table id="calendar"  border="0" cellpadding="0" cellspacing="0"  height="<?=isset($this->calHeight) ? $this->calHeight : '510';?>" width="<?=isset($this->calWidth) ? $this->calWidth : '510';?>" >

  <!-- Calendar Header -->
   <tr>
    <td>
    <table width="100%" border="1" cellpadding="0" cellspacing="1" id="<?=$this->calendarName;?>">
    <tbody>
	    <tr>
	      <td align="left" valign="absmiddle" class="calendar_nav_bg"><?php if ( isset($this->calendarPrevious) ) echo '&nbsp;<img src="' . ADMIN_IMAGES_PATH . '/prev.gif" onclick="window.location = \''.$this->calendarPrevious.'\'" >&nbsp;<a href="'.$this->calendarPrevious.'">previous</a>';?></td>
	      <td colspan="5" ><?php if ( isset($this->calendarTitle) ) echo $this->calendarTitle; ?></td>
	      <td align="right" valign="absmiddle" class="calendar_nav_bg"><?php if ( isset($this->calendarNext) ) echo '<a href="'.$this->calendarNext.'">next</a>&nbsp;<img src="' . ADMIN_IMAGES_PATH . '/next.gif" onclick="window.location = \''.$this->calendarNext.'\'" >&nbsp;';?></td>
	    </tr>
		<tr class="subheads">
				<td width="64" ><div align="center">&nbsp;&nbsp;SUN&nbsp;&nbsp;</div></td>
				<td width="34" ><div align="center">&nbsp;&nbsp;MON&nbsp;&nbsp;</div></td>
				<td width="34" ><div align="center">&nbsp;&nbsp;TUE&nbsp;&nbsp;</div></td>
				<td width="34" ><div align="center">&nbsp;&nbsp;WED&nbsp;&nbsp;</div></td>
				<td width="34" ><div align="center">&nbsp;&nbsp;THU&nbsp;&nbsp;</div></td>
				<td width="34" ><div align="center">&nbsp;&nbsp;FRI&nbsp;&nbsp;</div></td>
				<td width="34" ><div align="center">&nbsp;&nbsp;SAT&nbsp;&nbsp;</div></td>
		</tr>

<?php
$lastrowfound = FALSE;
$complete = FALSE;
for ($numRow = 0; $numRow < count($this->rows); $numRow++){ ?>
<tr onmouseover="this.style.cursor='pointer'" onMouseUp= "onRowClick(this)"  id=<?=$numRow?>>
	<?php for( $numCol = 0; $numCol < 7; $numCol++){
		$name = "d" . $numRow . $numCol;
	?>
		<?php
		$datevar = $this->rows[$numRow][$numCol]["date"];
		?>

		<td id="<?php echo $datevar; ?>"
			 class='<?=$this->rows[$numRow][$numCol]["emphasize"] ? "dayCellEmphasizedMini" : "dayCellMini"?>'
			 valign="top"  onMouseUp= "onDayClick(this)" align="left" nowrap>
				<?php
				if ($numRow < count($this->rows)-1) {
					if (!$this->rows[$numRow][$numCol]["isTargetMonth"] == FALSE)
					{
					if (in_array($this->rows[$numRow][$numCol]["date"], $this->calendar_items))
						{
					   	 echo "<img src='" . ADMIN_IMAGES_PATH . "/rounddot.gif'/><b>" . $this->rows[$numRow][$numCol]["dayNumber"] . "&nbsp;&nbsp;</b>" ;
						}
					 else
						{
					 	echo "<b>" . $this->rows[$numRow][$numCol]["dayNumber"] . "&nbsp;&nbsp;</b>" ;


					  }
					}
					}
				else if ($numRow == count($this->rows)-1)
				{
					if (!$this->rows[$numRow][$numCol]["isTargetMonth"] == FALSE)
					{
						 if (in_array($this->rows[$numRow][$numCol]["date"], $this->calendar_items))
						 {
							echo "<img src='" . ADMIN_IMAGES_PATH . "/rounddot.gif'/><b>&nbsp;&nbsp;"  . $this->rows[$numRow][$numCol]["dayNumber"]. "&nbsp;&nbsp;" ;
						 }
						else
						 {
							echo "<b>&nbsp;&nbsp;"  . $this->rows[$numRow][$numCol]["dayNumber"]. "&nbsp;&nbsp;" ;
					}
				}

				}

				?><br />

			<?php if (isset($this->rows[$numRow][$numCol]["items"]) && $this->rows[$numRow][$numCol]["items"])
				 { foreach ($this->rows[$numRow][$numCol]["items"] as $item){ ?>
				<div class="itemRow"><?php echo $item ?><br /></div>
			<?php } }/* items */
			?>
		</td>
	<?php } /* days */
	?>
</tr>
<?php } /* weeks */  ?>
</tbody></table></tr></table>

