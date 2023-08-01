<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">
<?php if (!$this->print_view) { ?>
  <tr>
  	<td colspan="5"class="space_section_head" style="text-align:center; padding-top:3px; border-bottom:1px solid black">Top 30 Ranking   <br /><span style="font-size:11px;">Note: Though the metrics above reflect real time activity the Top 30 rankings are calculated once a day at approx. 2:00 am EST and so may differ.</span>
  	 <img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" alt="Processing" /></td>
  </tr>
  <?php } else { ?>
  <tr>
  	<td colspan="5"class="space_section_head" style="text-align:center;">My Rankings </td>
  </tr>
   <?php } ?>
  <tr>
    <td id="rank_met-agr" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer; width:60%;"><div id="disc_rank_met-agr" class="disc_closed"></div>
    <span data-help="dashboard-adjusted_gross_revenue" style="float:left;">Adjusted Gross Revenue by Sales</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold; width:20%;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center; width:12%;" >$<?php echo CTemplate::number_format( $this->myRankings['agr'], 2);?></td>
    <td class="value_delimited" style="text-align:center; width:8%;" ><?php echo $this->myRankings['agr_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-agr" style="display:none">
  	<td id="td_rank_met-agr" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>
  <tr>
    <td  id="rank_met-agr_percent_change" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="disc_rank_met-agr_percent_change" class="disc_closed"></div>
    <span data-help="dashboard-ranking-percent_increase">Adjusted Gross Revenue by % Increase</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::number_format( $this->myRankings['agr_percent_change'], 2);?>%</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['agr_percent_change_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-agr_percent_change" style="display:none">
  	<td id="td_rank_met-agr_percent_change" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>
    <tr>
    <td  id="rank_met-in_store_signup" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="disc_rank_met-in_store_signup" class="disc_closed"></div><span data-help="dashboard-guests-in_store_signup">In-Store Sign up (Existing Guests w/ Regular Orders)</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::number_format( $this->myRankings['in_store_signup'], 2);?>%</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['in_store_signup_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-in_store_signup" style="display:none">
  	<td id="td_rank_met-in_store_signup" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>
    <tr>
    <td id="rank_met-converted_guests" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="disc_rank_met-converted_guests" class="disc_closed"></div><span data-help="dashboard-retention-conversion_rate">Conversion Rate (New and Re-Acquired Guests)</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::number_format( $this->myRankings['converted_guests'], 2);?>%</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['converted_guests_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-converted_guests" style="display:none">
  	<td id="td_rank_met-converted_guests" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>

  <tr>
    <td  id="rank_met-guest_visits" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="disc_rank_met-guest_visits" class="disc_closed"></div><span data-help="dashboard-guests-guest_count">Guest Count</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['guest_visits'];?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['guest_visits_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-guest_visits" style="display:none">
  	<td id="td_rank_met-guest_visits" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>

<tr>
	<td  id="rank_met-new_guest_visits" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="disc_rank_met-new_guest_visits" class="disc_closed"></div><span data-help="dashboard-guests-new_guest_count">New Guest Count</span></td>
	<td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
	<td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['new_guest_visits'];?></td>
	<td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['new_guest_visits_rank'];?></td>
</tr>
<tr id="tr_rank_met-new_guest_visits" style="display:none">
	<td id="td_rank_met-new_guest_visits" colspan="4" style="background-color:#e0e0e0;"></td>
</tr>


	<tr>
    <td id="rank_met-avg_visits_per_session" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="discrank_met-avg_visits_per_session" class="disc_closed"></div><span data-help="dashboard-sessions-orders_per_session">Average Guest Visits per Session</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::number_format( $this->myRankings['avg_visits_per_session'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['avg_visits_per_session_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-avg_visits_per_session" style="display:none">
  	<td id="td_rank_met-avg_visits_per_session" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>
    <tr>
		<td id="rank_met-addon_sales" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;">
			<div id="disc_rank_met-addon_sales" class="disc_closed"></div>
			<span data-help="dashboard-gross_revenue_breakdown-avg_addon_sales_to_date">Average Sides &amp; Sweets Sales</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format( $this->myRankings['addon_sales'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['addon_sales_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-addon_sales" style="display:none">
  	<td id="td_rank_met-addon_sales" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>
   <tr>
    <td id="rank_met-avg_ticket" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="disc_rank_met-avg_ticket" class="disc_closed"></div><span data-help="dashboard-gross_revenue_breakdown-average_ticket">Average Ticket (excludes starter pack and workshop orders)</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format( $this->myRankings['avg_ticket'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['avg_ticket_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-avg_ticket" style="display:none">
  	<td id="td_rank_met-avg_ticket" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>
  <tr>
    <td id="rank_met-servings_per_guest" class="value_delimited" style="text-align:left; padding-right:5px; font-weight:bold; cursor:pointer;" ><div id="disc_rank_met-servings_per_guest" class="disc_closed"></div><span data-help="dashboard-guests-servings_per_guest">Servings per Guest (excludes starter pack and workshop orders)</span></td>
    <td class="value_delimited" style="text-align:right; padding-right:5px; font-weight:bold;" >My Store Rank</td>
    <td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::number_format( $this->myRankings['servings_per_guest'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->myRankings['servings_per_guest_rank'];?></td>
  </tr>
  <tr id="tr_rank_met-servings_per_guest" style="display:none">
  	<td id="td_rank_met-servings_per_guest" colspan="4" style="background-color:#e0e0e0;"></td>
  </tr>



</table>

