<h3> P&L Financial Summary Input </h3>
<table width="100%" border="0">
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px; width: 300px;">
        Store:
    </td>
    <td class="bgcolor_light"  colspan="3">
        <?php echo $this->storeInfo['home_office_id']; ?>&nbsp;<?php echo $this->storeInfo['store_name']; ?>,&nbsp;<?php echo $this->storeInfo['state_id']; ?>
    </td>
</tr>
<tr>
    <td class="bgcolor_light" colspan="4">
         &nbsp;
    </td>
</tr>
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">     
         <span data-help="p_and_l-gross_sales">Gross Sales</span>
    </td>
    <td colspan="3" class="bgcolor_light">
           $<?php echo CTemplate::number_format($this->storeInfo['gross_sales']); ?>
    </td>
</tr>
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
          <span data-help="p_and_l-markups">&nbsp;&nbsp;&nbsp;+ Mark-Ups</span>
    </td>
    <td colspan="3" class="bgcolor_light">
           $<?php echo CTemplate::number_format($this->storeInfo['mark_up']); ?>
    </td>
</tr>
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
         <span data-help="p_and_l-adj_and_discounts">&nbsp;&nbsp;&nbsp;- Adjustments & Discounts</span>
    </td>
    <td colspan="3" class="bgcolor_light">
           ($<?php echo CTemplate::number_format($this->storeInfo['adjustments_and_discounts']); ?>)
    </td>
</tr>
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
             <span data-help="p_and_l-sales_w_adj">Total Sales with Adjustments & Discounts</span>
    </td>
    <td colspan="3" class="bgcolor_light">
           $<span id="p_and_l_total_agr"><?php echo CTemplate::number_format($this->storeInfo['adjusted_gross_revenue']); ?></span>
    </td>
</tr>
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;" colspan="4">
         &nbsp;
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-cogs">COGS (Food + Pkg - Discounts)</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['cost_of_goods_and_services_html'];?>
          <?php if (isset($this->COGsMsg)) echo $this->COGsMsg; ?>
    </td>
</tr>
<tr>
    <td class="bgcolor_light" colspan="2">
         &nbsp;
    </td>
   <td class="bgcolor_light" colspan="2" style="text-align: left; padding-left:50px; font-weight:bold">
         Total Hours Worked
    </td>

</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-employee_wages">Employee Wages</span>
    </td>
    <td colspan="1" class="bgcolor_light" style="width:80px;">
          <?php echo $this->p_and_l_form['employee_wages_html'];?>
     </td>
     <td colspan="1" class="bgcolor_light" style="text-align: right; width:100px;">
          <?php echo $this->p_and_l_form['employee_hours_html'];?>
    </td>
    <td colspan="1" class="bgcolor_light" style="text-align: left;">
         Employee
    </td>
</tr>


<tr>

    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-manager_salaries">Manager Salaries & Incentive Bonuses</span>
    </td>
    
    <td colspan="1" class="bgcolor_light">
          <?php echo $this->p_and_l_form['manager_salaries_html'];?>
    </td>
  
    <td colspan="1" class="bgcolor_light" style="text-align: right; width:100px;">
          <?php echo $this->p_and_l_form['manager_hours_html'];?>
    </td>
    
    <td colspan="1" class="bgcolor_light" style="text-align: left;">
         Manager
    </td>

</tr>

<tr>

    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-owner_salaries">Owner Salaries</span>
    </td>
    
    <td colspan="1" class="bgcolor_light">
          <?php echo $this->p_and_l_form['owner_salaries_html'];?>
    </td>

    <td colspan="1" class="bgcolor_light" style="text-align: right; width:100px;">
          <?php echo $this->p_and_l_form['owner_hours_html'];?>
    </td>
    
    <td colspan="1" class="bgcolor_light" style="text-align: left;">
         Owner
    </td>

</tr>
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-payroll_taxes">Payroll Taxes</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['payroll_taxes_html'];?>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" colspan="4">
         &nbsp;
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-bank_card_merchant_fees">Bank Card Merchant Fees</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['bank_card_merchant_fees_html'];?>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
       <span data-help="p_and_l-kitchen_and_office_supplies"> Kitchen & Office Supplies</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['kitchen_and_office_supplies_html'];?>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-national_marketing_fee">National Marketing Fee</span>
    </td>
    <td colspan="3" class="bgcolor_light">
           $<span id="p_and_l_marketing_total"><?php echo CTemplate::number_format($this->storeInfo['marketing_total']); ?></span>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span>SalesForce Fee</span>
    </td>
    <td colspan="3" class="bgcolor_light">
           $<span id="p_and_l_salesforce_fee"><?php echo CTemplate::number_format($this->storeInfo['salesforce_fee']); ?></span>
    </td>
</tr>


<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-royalty_fee">Royalty Fee</span>
    </td>
    <td colspan="3" class="bgcolor_light">
           $<span id="p_and_l_royalty_total"><?php echo CTemplate::number_format($this->storeInfo['royalty']); ?></span>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-total_marketing_and_advertising_expense">Local Marketing & Advertising Expense</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['total_marketing_and_advertising_expense_html'];?>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-rent_expense">Rent Expense</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['rent_expense_html'];?>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-repairs_and_maintenance">Repairs & Maintenance</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['repairs_and_maintenance_html'];?>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-utilities">Utilities</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['utilities_html'];?>
    </td>
</tr>
<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-other_expenses">All Other Expenses</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['other_expenses_html'];?>&nbsp;<span style="color:red">NOTE: This field is auto-calculated for you, and includes all remaining expenses from your P&L that are not already listed as a separate field above.  The calculation looks at your Net Sales, Expenses and Net Profit to back into this number.
          </span>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" colspan="4">
         &nbsp;
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-net_income">Net Income</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['net_income_html'];?>
    </td>
</tr>
<tr>
    <td class="bgcolor_light" colspan="4">
         &nbsp;
    </td>
</tr>


<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
        <span data-help="p_and_l-monthly_debt_payments">Monthly Loan Payments (Principal Only)</span>
    </td>
    <td colspan="3" class="bgcolor_light">
          <?php echo $this->p_and_l_form['monthly_debt_service_html'];?>
    </td>
</tr>

<tr>
    <td class="bgcolor_light" style="vertical-align: top; text-align: right; padding-right:5px;">
    </td>
    <td colspan="3" class="bgcolor_light">
          <button id="update_p_and_l" class="button" type="button">Save</button>
    </td>
</tr>

</table>
