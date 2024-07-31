
<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">
  <tr>
  	<td colspan="5"class="space_section_head" style="text-align:center;">Retention</td>
  </tr>
  <tr>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><span data-help="dashboard-retention-converted_guests">Converted Guests</span></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><span data-help="dashboard-retention-conversion_rate">Conversion Rate</span></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><span data-help="dashboard-retention-retention_rate">Retention Rate</span></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><span data-help="dashboard-retention-45_day_lost_guests">45 Day Lost Guests</span></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><span data-help="dashboard-retention-average_annual_visits">Average Annual Visits</span></td>
  </tr>
   <tr>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['converted_guests'];?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['conversion_rate'], 2);?>%</td>
    <td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['retention_count'] * 100, $this->curMonthGuestMetrics['existing_regular_to_date_orders'], 2);?>%
    ( <?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['retention_count'] * 100, $this->curMonthGuestMetrics['guest_count_existing_regular'], 2);?>% )

    </td>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['lost_guests_at_45_days'];?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['average_annual_visits'], 2);?></td>
  </tr>
</table>