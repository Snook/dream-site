	<table width="100%" border="0">

			<tr>
				<td>
				<br /><br />

<!-- TAG: DBENSON: BEGIN //-->

				<table align=center class="tools" cellspacing="0" width="80%">

					<tr><td colspan="3"class="header">Inactive Summary</td></tr>

					<!-- INACTIVE: 60-89 DAYS //-->
					<tr class="normal">
						<td rowspan="3" class="range">60-89<br />Days</td>
						<td>Number of Total Records:</td>
						<td><?php if (!empty($this->rows['InactiveReport6080'])) echo $this->rows['InactiveReport6080']['total_records']; else echo '0'; ?></td>
					</tr>
					<tr class="normal">
						<td>Number of records requiring attention (i.e. no data in f/up action field):</td>
						<td><?php  if (!empty($this->rows['InactiveReport6080'])) echo $this->rows['InactiveReport6080']['needing_attention']; else echo '0'; ?></td>
					</tr>
					<tr class="normal">
						<td>Number of records needing results (i.e. no data in f/up result field):</td>
						<td><?php if (!empty($this->rows['InactiveReport6080'])) echo $this->rows['InactiveReport6080']['resutls_followup_needed']; else echo '0'; ?></td>
					</tr>
					<tr style="background-color: #efefef;"><td colspan="3" style="border-top: #e1e1e1 1px solid; border-bottom: #e1e1e1 1px solid; height: 3px;"></td></tr>

					<!-- INACTIVE: 90-119 DAYS //-->
					<tr class="normal">
						<td rowspan="3" class="range">90-119<br />Days</td>
						<td>Number of Total Records:</td>
						<td><?php if (!empty($this->rows['InactiveReport90119'])) echo $this->rows['InactiveReport90119']['total_records']; else echo '0'; ?></td>
					</tr>
					<tr class="normal">
						<td>Number of records requiring attention (i.e. no data in f/up action field):</td>
						<td><?php  if (!empty($this->rows['InactiveReport90119'])) echo $this->rows['InactiveReport90119']['needing_attention']; else echo '0'; ?></td>
					</tr>
					<tr class="normal">
						<td>Number of records needing results (i.e. no data in f/up result field):</td>
						<td><?php if (!empty($this->rows['InactiveReport90119'])) echo $this->rows['InactiveReport90119']['resutls_followup_needed']; else echo '0'; ?></td>
					</tr>

					<tr><td colspan="3" class="footer">&nbsp;</td></tr>




					<tr><td colspan="3"class="header">Inactive to Active Summary</td></tr>

					<!-- INACTIVE TO ACTIVE: 60-89 DAYS //-->
					<tr class="normal">
						<td class="range">60-89<br />Days</td>
						<td>Number of Total Records:</td>
						<td><?php if (!empty($this->rows) && !is_null($this->rows['ActiveReport6080']['total_records'])) echo $this->rows['ActiveReport6080']['total_records']; else echo '0'; ?></td>
					</tr>
					<tr style="background-color: #efefef;"><td colspan="3" style="border-top: #e1e1e1 1px solid; border-bottom: #e1e1e1 1px solid; height: 3px;"></td></tr>

					<!-- INACTIVE TO ACTIVE: 90-119 DAYS //-->
					<tr class="normal">
						<td class="range">90-119<br />Days</td>
						<td>Number of Total Records:</td>
						<td><?php if (!empty($this->rows) && !is_null($this->rows['ActiveReport90119']['total_records'])) echo $this->rows['ActiveReport90119']['total_records']; else echo '0'; ?></td>
					</tr>


					<tr><td colspan="3" class="footer">&nbsp;</td></tr>




				</table>

<!-- TAG: DBENSON: END //-->




<?php
/***************************************************************************************************************




				<table align=center class="tools" cellspacing="0">

						<tr><td colspan="3"class="header">60 - 89 Days Inactive</td></tr>

						<tr class="normal">
						<td>Number of Total Records:</td>
						<td>&nbsp;</td>
						<td><?php if (!empty($this->rows['InactiveReport6080'])) echo $this->rows['InactiveReport6080']['total_records']; else echo '0'; ?></td>
						</tr>
						<tr class="normal">
						<td>Number of records requiring attention (i.e. no data in f/up action field):</td>
						<td>&nbsp;</td>
						<td><?php  if (!empty($this->rows['InactiveReport6080'])) echo $this->rows['InactiveReport6080']['needing_attention']; else echo '0'; ?></td>
						</tr>
						<tr class="normal">
						<td>Number or records needing results (i.e. no data in f/up result field):</td>
						<td>&nbsp;</td>
						<td><?php if (!empty($this->rows['InactiveReport6080'])) echo $this->rows['InactiveReport6080']['resutls_followup_needed']; else echo '0'; ?></td>
						</tr>

						<tr><td colspan="3" class="footer">&nbsp;</td></tr>



						<tr><td colspan="3" class="header">90 - 119 Days Inactive</td></tr>

						<tr class="normal">
						<td>Number of Total Records:</td>
						<td>&nbsp;</td>
						<td><?php if (!empty($this->rows['InactiveReport90119'])) echo $this->rows['InactiveReport90119']['total_records']; else echo '0'; ?></td>
						</tr>
						<tr class="normal">
						<td>Number of records requiring attention (i.e. no data in f/up action field):</td>
						<td>&nbsp;</td>
						<td><?php  if (!empty($this->rows['InactiveReport90119'])) echo $this->rows['InactiveReport90119']['needing_attention']; else echo '0'; ?></td>
						</tr>
						<tr class="normal">
						<td>Number or records needing results (i.e. no data in f/up result field):</td>
						<td>&nbsp;</td>
						<td><?php if (!empty($this->rows['InactiveReport90119'])) echo $this->rows['InactiveReport90119']['resutls_followup_needed']; else echo '0'; ?></td>
						</tr>

						<tr><td colspan="3" class="footer">&nbsp;</td></tr>


						<tr><td colspan="3" class="header">60 - 89 Inactive to Active Report</td></tr>


						<tr class="normal">
						<td>Number of Total Records:</td>
						<td>&nbsp;</td>
						<td><?php if (!empty($this->rows) && !is_null($this->rows['ActiveReport6080']['total_records'])) echo $this->rows['ActiveReport6080']['total_records']; else echo '0'; ?></td>
						</tr>

						<tr><td colspan="3" class="footer">&nbsp;</td></tr>



						<tr><td colspan="3" class="header">90 - 119 Inactive to Active Report</td></tr>


						<tr class="normal">
						<td >Number of Total Records:</td>
						<td >&nbsp;</td>
						<td ><?php if (!empty($this->rows) && !is_null($this->rows['ActiveReport90119']['total_records'])) echo $this->rows['ActiveReport90119']['total_records']; else echo '0'; ?></td>
						</tr>

						<tr><td colspan="3" class="footer">&nbsp;</td></tr>



				</table>
***************************************************************************************************************/
?>
				</td>
			</tr>
			</table>
