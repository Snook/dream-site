<?php
//set default calendar name
if ( !isset($this->calendarName) )
	$this->calendarName = 'myCalendar';
?>

<script type="text/javascript">
var selectedCell = null;
var orgColor = null;
var dayClickHandler = null;

<?php if (isset($this->support_cell_selection) && $this->support_cell_selection) { ?>
// Universal Behavior
function onDayClick(obj)
{
	if (obj == selectedCell)
		return;

	if (selectedCell)
		//selectedCell.bgColor = orgColor;
		selectedCell.style.backgroundColor = orgColor;

	//orgColor = obj.bgColor;
	orgColor = obj.style.backgroundColor;

	//obj.bgColor = "#CAA9DE";
	obj.style.backgroundColor = "#2db5b7";
	selectedCell = obj;

	if (dayClickHandler)
		dayClickHandler(selectedCell);

}
<?php } else { ?>

function onDayClick(obj)
{
}
<?php } ?>
</script>


<?php if( isset($this->calendarPrevious) || isset($this->calendarNext) ) { ?>
<table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
<tr>
	<td align="left" style="padding-left: 2px; width: 50%;">
		<?php if( isset($this->calendarPrevious) ) { ?>
			<img src="<?=ADMIN_IMAGES_PATH?>/calendar/prev_month.gif" style="width: 16px; height: 18px;" class="img_valign" onclick='window.location="<?=$this->calendarPrevious?>";'>
			<a href="<?=$this->calendarPrevious?>" class="button">Previous Month</a>
		<?php } ?>
	</td>
	<td align="right" style="padding-right: 2px;">&nbsp;
		<?php if( isset($this->calendarNext) ) { ?>
			<a href="<?=$this->calendarNext?>" class="button">Next Month</a>
			<img src="<?=ADMIN_IMAGES_PATH?>/calendar/next_month.gif" style="width: 16px; height: 18px;" class="img_valign" onclick="window.location = '<?=$this->calendarNext?>';">
		<?php } ?>
	</td>
</tr>
</table>
<?php } ?>


<?php if (isset($this->doBannerBS) && $this->doBannerBS) { ?>
	<div id="banner_kludge" style="position:absolute; left:0px; top:0px;">
	<img src="<?=ADMIN_IMAGES_PATH?>/calendar/cal_menu_next_sessions_jan_lg.gif"></img>
	</div>
<?php } ?>

<!-- The HTML Loops for Rows(weeks) and columns (days)-->
<table id="calendar" border="0" cellpadding="0" cellspacing="0" bgcolor="#999999" style="width: 100%;">
<!-- Calendar Header -->
<tr>
	<td>
    <table width="100%" border="0" cellpadding="0" cellspacing="1" id="<?=$this->calendarName;?>">
    <tbody>
		<tr>
			<td colspan="7" valign="absmiddle"  class="calendarTitleDelivered"><?php if ( isset($this->calendarTitle) ) echo $this->calendarTitle; ?></td>
		</tr>
		<tr class="subheads">
			<td width="130" class="dayHeader"><div align="center">Sunday</div></td>
			<td width="130" class="dayHeader"><div align="center">Monday</div></td>
			<td width="130" class="dayHeader"><div align="center">Tuesday</div></td>
			<td width="130" class="dayHeader"><div align="center">Wednesday</div></td>
			<td width="130" class="dayHeader"><div align="center">Thursday</div></td>
			<td width="130" class="dayHeader"><div align="center">Friday</div></td>
			<td width="130" class="dayHeader"><div align="center">Saturday</div></td>
		</tr>

<?php for ($numRow = 0; $numRow < count($this->rows); $numRow++){ ?>
		<tr id="<?=$numRow?>">
	<?php for( $numCol = 0; $numCol < 7; $numCol++) {
		$name = "d" . $numRow . $numCol;
	?>
			<td id="<?=$this->rows[$numRow][$numCol]["date"]?>"
			 class='<?=$this->rows[$numRow][$numCol]["emphasize"] ? "dayCellEmphasized" : "dayCell"?>'
			 bgColor="<?=$this->rows[$numRow][$numCol]["color"]?>"
			<?php if (isset($this->rows[$numRow][$numCol]["styleOverride"]))
				echo $this->rows[$numRow][$numCol]["styleOverride"]; ?>
			 valign="top"  onMouseUp= "onDayClick(this)" align="left" nowrap>
			 <span class='<?=$this->rows[$numRow][$numCol]["isTargetMonth"] ? "dayNumber" : "dayNumberDim"?> '>
				<?php echo $this->rows[$numRow][$numCol]["dayNumber"] ?></span>
				<?php if (isset($this->rows[$numRow][$numCol]['header']))  echo $this->rows[$numRow][$numCol]['header'];?>
				<br />

			<?php if (isset($this->rows[$numRow][$numCol]["items"]) && $this->rows[$numRow][$numCol]["items"])
				 { foreach ($this->rows[$numRow][$numCol]["items"] as $item){ ?>
				<div class="itemRow"><?php echo $item ?></div>
			<?php } }/* items */
			?>
			</td>
	<?php } /* days */
	?>
		</tr>
<?php } /* weeks */ ?>
		<tr>
			<td colspan="7" style="height:1px;"></td>
		</tr>
		</tbody>
		</table>
	</td>
</tr>
</table>