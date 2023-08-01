<div style="background-color:#f0f0f0;">


<form name="frm" action="" method="POST" onSubmit="return _check_form(this);" >


<div id='calendar'>
	<table style="width:700px;" align="center" >
		<tr>
			<td>
				<table >
					<tr>
						<td>
							<table border="1" align="left" cellspacing="0" cellpadding="3">
								<tr>
									<td align="center">
										<table  style="text-align:center">
											<tr>
												<td align="left">
													<table style="width:100%;">
														<tr>
															<td style="text-align:center"><b>
															<?= $this->monthtitle ?> <?= $this->currentYear ?></b></td>
														</tr>
													</table>
											</tr>
											<tr>
												<td style="text-align:center;">
				 								 <?php if (isset($this->rows) ) include $this->loadTemplate('admin/subtemplate/calendar.tpl.php'); ?>
				 								 </td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

<div>


<div style="height:30px; text-align:center;"><b>Selected Day: </b><span style="font-size: 18px;line-height: 24px;	color: #337a68;	font-weight: 900"" id="selected_day"></span>
<span style="display:none;" id="selected_id"></span>
<span style="display:none;" id="selected_session_id"></span>
</div>


<div>

<table style="width:100%; border:thin gray solid;">
<tr  style="background-color:#A8B355">
	<th style="width:160px;;">Type</th>
	<th style="width:50px;">Expense</th>
	<th style="width:392px;">Comments</th>
</tr>

<tr data-section="for_store">
	<td colspan="3" style="background-color:#A4CAAD; text-align:center; font-weight:bold;">Store Adjustments</td>
</tr>
<tr  data-section="for_store" >
	<td>
		 <img src="<?php echo ADMIN_IMAGES_PATH ?>/icon/turkey.png" class="img_valign">&nbsp;<b>Food and Packaging</b>
	</td>
	<td>
		<input id="SYSCO" type="text" />
	</td>
	<td>
		<input style="width:380px;" type="text" id="food_note" />
	</td>
</tr>

<tr  data-section="for_store">
	<td>
		<img src="<?php echo ADMIN_IMAGES_PATH ?>/icon/user.png" class="img_valign">&nbsp;<b>Labor</b>
	</td>
	<td>
		<input id="LABOR" type="text" />
	</td>
	<td>
		<input style="width:380px;" type="text"  id="labor_note" />
	</td>
</tr>
<?php if ($this->isHomeOfficeAccess) { ?>

<tr data-section="for_HO">
	<td colspan="3" style="background-color:#A4CAAD; text-align:center; font-weight:bold;">Home Office Adjustments</td>
</tr>
<tr data-section="for_HO">
	<td>
		<img src="<?php echo ADMIN_IMAGES_PATH ?>/icon/dollar_sign.png" class="img_valign">&nbsp;<b>Sales Adjustments</b>
	</td>
	<td>
		<input id="SALES_ADJUSTMENTS" type="text" />
	</td>
	<td>
		<input id="adjustment_note" style="width:380px;" type="text" />
	</td>
</tr>

<tr data-section="for_HO">
	<td>
		<img src="<?php echo ADMIN_IMAGES_PATH ?>/icon/star_gold.png" class="img_valign">&nbsp;<b>Fundraising</b>
	</td>
	<td>
		<input id="FUNDRAISER_DOLLARS" type="text" />
	</td>
	<td>
		<input id="fundraising_note" style="width:380px;" type="text" />
	</td>
</tr>
<?php } ?>

<tr><td style="text-align:right;" colspan="3"><span id="cost_inputter_status_msg" style="display:none; color:red;"></span> <button id="cost_inputter_close">Close</button>&nbsp;&nbsp;<button id="cost_inputter_save">Save</button>&nbsp;&nbsp;</td></tr>





</table>
</div>
</div>
</form>