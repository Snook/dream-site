<h4>Store Performance</h4>
<table width="100%" style="border-collapse: collapse; padding:0px; border:1px solid black; margin-bottom:12px;">
  <tr>
    <td class="space_right_delimited" style="width:16%;">Month</td>
    <td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="dashboard-adjusted_gross_revenue">Adj. Gross Revenue</span></td>
    <td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-performance-last_year_agr">Last Year Adj. Gross Revenue</span></td>
    <td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-performance-revenue_increase">$ Change</span></td>
    <td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-performance-percent_increase">% Change</span></td>
    <td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-performance-average_ticket">Average Ticket</span></td>
    <td class="space_right_delimited" style="width:8%; text-align:center;" ><span data-help="trending-performance-average_orders_per_session">Avg. Orders / Session</span></td>
    <td class="space_right_delimited" style="width:8%; text-align:center;" ><span data-help="trending-performance-unique_orders">Unique Orders</span></td>
    <td class="space_right_delimited" style="width:8%; text-align:center;" ><span data-help="trending-performance-unique_guests">Unique Guests</span></td>
    <td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-performance-new_to_total">% New to Total Guests</span></td>
  </tr>
  <?php foreach ($this->store_performance_data as $thisRow) { ?>
  <tr>
    <td class="label_delimited" style="text-align:right;"><?php echo CTemplate::dateTimeFormat($thisRow['date'], VERBOSE_MONTH_YEAR)?></td>
    <td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($thisRow['total_agr'], 2)?></td>
    <td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($thisRow['prev_agr'], 2)?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo (($thisRow['diff'] < 0) ?  "-$" . CTemplate::number_format(abs((float)$thisRow['diff']), 2) : "$" . CTemplate::number_format($thisRow['diff'], 2))?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format((float)$thisRow['percent_diff'] * 100, 2)?>%</td>
    <td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($thisRow['avg_ticket_regular'], 2)?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($thisRow['orders_per_session'], 2)?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo $thisRow['orders_count_all']?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo $thisRow['guest_count_total']?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format((float)$thisRow['percent_new'] * 100, 2)?>%</td>
   </tr>
   <?php  } ?>

    <?php if ($this->curReportType == 'single_store') { ?>
    <tr>
    <td class="space_right_delimited" style="text-align:right;"><span data-help="trending-performance-12_month_average">12 mo. Store Avg.</span></td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['store_avg']['total_agr'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['store_avg']['prev_agr'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['store_avg']['diff'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( (float)$this->rollups['store_avg']['percent_diff'] * 100, 2)?>%</td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['store_avg']['avg_ticket_regular'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['orders_per_session'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['orders_count_all'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['guest_count_total'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( (float)$this->rollups['store_avg']['percent_new']  * 100, 2)?>%</td>
   </tr>
   <?php } ?>

  <tr>
    <td class="space_right_delimited" style="text-align:right;"><span data-help="trending-performance-12_month_national_average">12 mo. National Avg.</span></td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['national_avg']['total_agr'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['national_avg']['prev_agr'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['national_avg']['diff'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( (float)$this->rollups['national_avg']['percent_diff'] * 100, 2)?>%</td>
    <td class="space_right_delimited" style="text-align:center;">$<?php echo CTemplate::number_format( $this->rollups['national_avg']['avg_ticket_regular'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['orders_per_session'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['orders_count_all'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['guest_count_total'], 2)?></td>
    <td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( (float)$this->rollups['national_avg']['percent_new'] * 100, 2)?>%</td>
  </tr>

</table>