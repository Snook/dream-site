<?php $this->setScript('head', SCRIPT_PATH . '/admin/session_template_mgr.min.js'); ?>
<?php $this->setOnload('admin_session_template_mgr_init();'); ?>
<?php $this->assign('page_title','Template Manager'); ?>
<?php $this->assign('topnav','sessions'); ?>
<?php $this->assign('helpLinkSection','SP'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/session_details_popup.tpl.php'); ?>


	<style type="text/css">
		.dayHeader
		{
			font-size:14px;
			font-weight:bold;
			width:80px;
			background-color:#A4CAAD;
			text-align:center;
			}
		.dayCell
		{
			height:200px;
			width:90px;
			cursor:hand;
			position:relative;
			top:2px;
			background-color:#EFF3E5;
			}
	</style>

	<form action="" method="post">
		<table style="width: 100%;">
			<tr>
				<td style="text-align:center;font-weight:bold;background-color:#A4CAAD;">Create a New Template</td>
				<td style="text-align:center;font-weight:bold;background-color:#A4CAAD;">Use Saved Template</td>
			</tr>
			<tr>
				<td style="text-align:center;background-color:#EFF3E5;">
					New Template Name <?php echo $this->form_template_set['set_name_html']; ?> <?php echo $this->form_template_set['set_store_template_html']; ?> Store Template
					<?php
					echo $this->form_template_set['set_submit_html'];
					echo $this->form_template_set['hidden_html'];
					?>
				</td>
				<td style="text-align:center;background-color:#EFF3E5;">
					<?php echo $this->form_template_set['set_id_html']; ?>
					<?php echo $this->form_template_set['clear_set_submit_html']; ?>
					<?php echo $this->form_template_set['delete_set_submit_html']; ?>
				</td>
			</tr>
		</table>
	</form>

	<br />

<?php if (!empty($this->current_set['store_id'])) { ?>
	<div style="text-align: center; color: red; font-weight: bold;">Store template, edits to this template are collaborative.</div>
<?php } else { ?>
	<div style="text-align: center; color: green; font-weight: bold;">Personal template, only you have access to this template.</div>
<?php } ?>

	<br />

	<table width="100%" name="weekTemplate">
		<tr>
			<td class="dayHeader">Sunday</td>
			<td class="dayHeader">Monday</td>
			<td class="dayHeader">Tuesday</td>
			<td class="dayHeader">Wednesday</td>
			<td class="dayHeader">Thursday</td>
			<td class="dayHeader">Friday</td>
			<td class="dayHeader">Saturday</td>
		</tr>
		<tr>
			<?php for ($dayNum = 0; $dayNum < 7; $dayNum++){ ?>
				<td class="dayCell" valign="top" onmouseup="onDayClick(this);">
					<?php if (array_key_exists($dayNum, $this->items)) { foreach ($this->items[$dayNum] as $item => $data ){ ?>
						<a href="javascript:editSession(<?php echo $item; ?>, <?php echo $this->this_set_id?>);"> <?php echo $data['date']; ?> <?php echo $data['type']; ?></a><br />
					<?php } /* session */ ?>
					<?php } // if array_key_ exists ?>
					&nbsp;
				</td>
			<?php } /* day */ ?>
		</tr>
	</table>

	<br />

	<form id="itemEditor" action="/backoffice/session_template_mgr" method="post" onSubmit="return handleItemSubmit(this);">
		<?php echo $this->form_template_item['hidden_html']; ?>

		<table width="100%" name="edit/create" border="0" cellSpacing="0" cellpadding="3" bgcolor="<?php echo $this->editingItem ? "#F4ECDC" : "#EFF3E5"; ?>">
			<tr>
				<td align="right" style="border: 0px;">
					<label id="start_time_lbl" message="Please enter valid minutes.">Start time</label>
				</td>
				<td style="border: 0px;">
					<?php	 echo $this->form_template_item['start_time_html'];?>

				</td>
				<td align="right" style="border: 0px;" rowspan="2">Day of the Week </td>
				<td style="border: 0px;" rowspan="2"><?php echo $this->form_template_item['start_day_html']; ?></td>
			</tr>
			<tr>
				<td align="right" style="border: 0px;">
					<label id="end_time_lbl" message="Please enter a valid hour.">End time</label>
				</td>
				<td style="border: 0px;">
					<?php	 echo $this->form_template_item['end_time_html'];?>
				</td>
			</tr>

			<tr>
				<td align="right" style="border: 0px;"><label id="available_slots_lbl" for="available_slots" message="Please enter the maximum number of standard orders."></label>Maximum Order Capacity</td>
				<td style="border: 0px;"><?php echo $this->form_template_item['available_slots_html']; ?>*</td>
				<td align="right" style="border: 0px;"><label id="duration_minutes_lbl" for="duration_minutes" message="Please enter a Duration.">Duration </label></td>
				<td style="border: 0px;"><?php echo $this->form_template_item['duration_minutes_html']; ?>* Minutes</td>
			</tr>
			<?php if (isset($this->form_template_item['introductory_slots_html'])) { ?>
				<tr>
					<td align="right" style="border: 0px;"><label id="introductory_slots_lbl" for="introductory_slots" message="Please enter the maximum number of intro slots."></label>Meal Prep Starter Pack Order Capacity</td>
					<td style="border: 0px;"><?php echo $this->form_template_item['introductory_slots_html']; ?>*</td>
					<td align="right" style="border: 0px;"></td>
					<td style="border: 0px;"></td>
				</tr>
			<?php } ?>
			<tr>
				<td align="right" style="border: 0px;">Session Type </td>
				<?php if (isset($this->form_template_item['session_type_html'])) { ?>
					<td style="border: 0px;"><?php echo $this->form_template_item['session_type_html']; ?></td>
				<?php } else { ?>
					<td style="border: 0px;">:&nbsp;&nbsp;Standard</td>
				<?php } ?>
				<td style="border: 0px;"></td>
				<td style="border: 0px;"></td>
			</tr>
			<tr>
				<td align="right" style="border: 0px;">Session Closes</td>
				<td style="border: 0px;"><?php echo $this->form_template_item['close_interval_type_html'][CSession::HOURS];
					echo $this->form_template_item['close_interval_hours_html'] . "hours prior&nbsp;&nbsp;";
					echo $this->form_template_item['close_interval_type_html'][CSession::ONE_FULL_DAY] . "1 day prior"; ?></td>
				<td style="border: 0px;"></td>
				<td style="border: 0px;"></td>
			</tr>
			<tr id="meal_customization_row">
				<?php if ($this->allowsMealCustomization) { ?>
					<td align="right" style="border: 0px;">Close For Customization</td>
					<td style="border: 0px;"><?php echo $this->form_template_item['meal_customization_close_interval_type_html'][CSession::HOURS];
						echo $this->form_template_item['meal_customization_close_interval_html'] . "hours prior&nbsp;&nbsp;";;
						echo $this->form_template_item['meal_customization_close_interval_type_html'][CSession::FOUR_FULL_DAYS] . "4 day prior"; ?></td>
				<?php }else{ ?>
					<td style="border: 0px;"></td>
					<td style="border: 0px;"></td>
				<?php } ?>
			</tr>
			<tr>
				<td align="right" style="border: 0px;">Additional Info</td>
				<td colspan="3" style="border: 0px;">
					<?php echo $this->form_template_item['session_title_html']; ?><br />
					(Optional. Shows on store landing page calendar only. E.g. $20 delivery fee. No customization.)
				</td>
			</tr>
			<tr>
				<td style="border: 0px;" colspan='4'>
					<?php
					if ($this->editingItem)
					{
						echo $this->form_template_item['item_update_html']. '&nbsp;';
						echo $this->form_template_item['item_delete_html']. '&nbsp;';
						echo $this->form_template_item['item_update_cancel_html'];
					}
					else
						echo $this->form_template_item['item_submit_html'];
					?>
				</td>
			</tr>
		</table>
	</form>

	<div class="mt-2">
		<?php include $this->loadTemplate('admin/help/help_session_mgr.tpl.php'); ?>
	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>