<?php $this->assign('page_title','PLATEPOINTS Guest Status and Rewards Due'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports_plate_points_gifts.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/main.min.js'); ?>
<?php $this->setOnload('handle_platepoints_gifts();'); ?>


<?php if (isset($_REQUEST['print']) && $_REQUEST['print'] == "true")
{
	$this->assign('print_view', true);
}
else
{
	$this->assign('print_view', false);
}
?>


<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>


<?php if ($this->print_view) { ?>

<style>
<!--

.bgcolor_medium,
.header_row,
.bgcolor_dark,
.bgcolor_lighter_gray_text,
.bgcolor_light
{
	color: #000000;
	background: none;
	background-color: #ffffff;
	border:1px solid black;
}


-->
</style>

<?php } ?>


<div style="text-align:center">

<h2>PLATEPOINTS Guest Status and Rewards Due</h2>
<?php if (isset($this->date_header)) {
	echo "<h2>" . $this->date_header . "</h2>";
} ?>

<?php if (!$this->print_view) { ?>
<div style="float:right;">
<a href="javascript:print_GSRD();">Printer-Friendly Version <img style="vertical-align:middle;margin-bottom:.25em;" alt="Print" src="<?php echo IMAGES_PATH;?>/admin/icon/printer.png"></a>
</div>
<?php } ?>
<br />

<?php if ($this->print_view) { ?>
<table style="width: 100%; border-spacing: 0px; border:2px solid black;">
<?php  } else { ?>
<table style="width: 100%;">
<?php  } ?>

<?php foreach ($this->sections as $header => $rows) { ?>
<tr>
	<th colspan="5" class="bgcolor_dark catagory_row"><?php echo $header;?></th>
</tr>
<tr>
	<th class="bgcolor_medium header_row">Guest Name</th>
	<th class="bgcolor_medium header_row">Status</th>
	<th class="bgcolor_medium header_row">Lifetime Points</th>
	<th class="bgcolor_medium header_row">Pending Points</th>
	<th class="bgcolor_medium header_row">Current Level</th>
</tr>
<?php foreach ($rows as $id => $data) {
	$css = 'bgcolor_light';
	if ($data['status'] == 'in_DR2')
		$css = 'bgcolor_lighter_gray_text';
	?>
<tr>
	<td style="font-weight:bold;" <?php if ($data['status'] == 'active') echo 'rowspan="2"'?> class="<?php echo $css;?>"><?php echo $data['guest_name'];?></td>
	<td class="<?php echo $css;?>"><?php echo ucfirst($data['status_display_str']); ?></td>
	<td class="<?php echo $css;?>"><?php echo $data['lifetime_points']; ?></td>
	<td class="<?php echo $css;?>"><?php echo $data['pending_points']; ?></td>
	<td class="<?php echo $css;?>"><?php echo (is_array($data['current_level']) ? $data['current_level']['title'] : "");?></td>
</tr>
<?php if ($data['status'] == 'active') { ?>
<tr>
    <?php if ($data['current_level']['level'] != 'enrolled') { ?>
	<td class="<?php echo $css;?>" colspan="4" style="text-align:left; padding-left:10px;">
		<span id="gft_<?php echo $id;?>">
		<?php if ($data['user_is_preferred']) { ?>
			This guest has preferred status.
		<?php } else if (!$data['due_reward_for_current_level']) { ?>
			<?php if (!$data['due_reward_for_current_level'] && !$data['due_reward_for_current_level_received'] && !empty($data['due_reward_for_current_level_received_notes'])) { ?>
				<?php echo $data['due_reward_for_current_level_received_notes'];?>
			<?php } else if (!empty($data['gift_display_str'])) { ?>
				The guest has received their current level gift: <?php echo $data['gift_display_str'];?>
			<?php } else { ?>
				&nbsp;
			<?php  } ?>
		<?php } else if ($data['current_level']['rewards']['gift_id'] != "none") { ?>

	<?php if (!$this->print_view) { ?>
		<button class="button" onclick="markGiftReceived('<?php echo $data['current_level']['level']?>', <?php echo $id;?>, '<?php echo $data['current_level']['rewards']['gift_id']?>');">Confirm Receipt of Gift</button>
	<?php } ?>

		The guest is due to receive: <?php echo $data['gift_display_str'];?>
		<?php } else { ?>
		There is no gift due for this level.
		<?php } ?>
		</span>
	</td>
    <?php } else {
        //  enrolled level ?>
    <td class="<?php echo $css;?>" colspan="4" style="text-align:left; padding-left:10px;">

    <?php if (count($data['orderBasedGiftData'])) {
        $hasSeenOrderful = false;
        $hasSeenOrderless = false;
        foreach($data['orderBasedGiftData'] as $thisReward) {
        ?>

        <?php if ($thisReward['rewardDue'] and !$hasSeenOrderful) {
            $hasSeenOrderful = true;
            ?>
            <div id="rewardsdueheader_<?php echo $data['user_id']; ?>" style="text-align: left; font-weight: bold; margin-top:5px;">PLATEPOINTS Reward Due</div>
        <?php } else if (!$thisReward['rewardDue'] and !$hasSeenOrderless)  {
            $hasSeenOrderless = true;
            ?>
            <div id="rewardsnotdueheader_<?php echo $data['user_id']; ?>" style="text-align: left; font-weight: bold; font-size:smaller; margin-top:5px;">PLATEPOINTS Reward With Order</div>
        <?php } ?>

        <div data-pp_gift_reward_div="<?php echo $data['user_id'] . "_" . $thisReward['gift_id']; ?>" data-is_due="<?php echo ($thisReward['rewardDue'] ? 'true' : 'false') ?>" style="bottom: 8px; width: 96%; text-align: left; margin-top: 2px;">

            <span class="button red" data-pp_gift_reward="<?php echo $data['user_id']; ?>"  data-user_id="<?php echo $data['user_id']; ?>"
                  data-gift_id="<?php echo $thisReward['gift_id']; ?>" data-level="<?php echo $data['current_level']['level']; ?>"
                  data-order_sequence_number="<?php echo $thisReward['orderBasedRewardOrderNumber']; ?>"  data-order_id="<?php echo $data['order_id']; ?>">
                <?php echo $thisReward['display_str']; ?>
            </span>
        </div>
    <?php } } ?>

        <div id="received_order_based_gifts_<?php echo $data['order_id']; ?>">
    <?php if (count($data['receivedGifts'])) {
        foreach($data['receivedGifts'] as $thisGift) {?>

        <div><?php echo "Guest has received " . CPointsUserHistory::getOrderBasedGiftDisplayString($thisGift); ?></div>
        <?php } ?>
        </div>

    </td>

</tr>

<?php } } } } } ?>
</table>


</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>