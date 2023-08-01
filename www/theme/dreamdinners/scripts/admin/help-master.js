var ddHelpData = new Object();
ddHelpData['dashboard'] = new Object();
ddHelpData['dashboard']['adjusted_gross_revenue'] =
{title: "Adjusted Gross Revenue",
 help_text: "Adjusted Gross Revenue is the total store revenue for the given month minus sales tax and with a few other adjustments. It is equal to the amount that royalties are based on. Amounts are based on session date.",
 tech_text: "Formuala: Sum(grand_total) - Sum(total_sales_tax) + Sum(sales_adjustments) - Sum(Gift Certificates of type Voucher or Donated) - Sum(expenses of type FUNDRAISER) - SUM(Gift Certificates of type SCRIP * 12%) - SUM(Referral Store Credit redeemed)"
 };
ddHelpData['dashboard']['adjusted_gross_revenue_month_start'] =
{title: "Month Start Adjusted Gross Revenue",
    help_text: "This the amount of Revenue already booked for the current menu/month at the start of that menu/month",
    tech_text: ""
}
ddHelpData['dashboard']['adjusted_gross_revenue_history'] =
{title: "Adjusted Gross Revenue Historical Values",
 help_text: "When comparing the current month AGR remember that the historical values are for the entire month.",
 tech_text: ""
 };
ddHelpData['dashboard']['adjusted_gross_revenue_month_start_history'] =
{title: "Adjusted Gross Revenue Month Start Historical Values",
    help_text: "This the amount of Revenue already booked for the given menu/month at the start of that menu/month.",
    tech_text: ""
};
ddHelpData['dashboard']['gross_revenue_breakdown'] =
{title: "Gross Revenue Break Down",
 help_text: "Here you can examine how revenue, average ticket and Sides &amp; Sweets sales breakdown by user type (new, reacquired and existing) and order type (Workshop, Starter Pack, Regular, etc.).",
 tech_text: ""
 };
ddHelpData['dashboard']['gross_revenue_breakdown']['average_ticket'] =
{title: "Average Ticket",
 help_text: "Average ticket is the average of the gross revenue per order. For the \"Totals\" row the average is  the gross revenue of every order divided by the number of orders otherwise only the revenue and count of orders of the given user and order type are used.",
 tech_text: "Totals Formula: Average(gross revenue of every order in the time period)<br />Breakdown Formula: Average(every order of this type for the user type)"
 };
ddHelpData['dashboard']['gross_revenue_breakdown']['addon_sales'] =
{title: "Sides &amp; Sweets Sales",
 help_text: "Sides &amp; Sweets sales are sales of Sides &amp; Sweets and Misc Food and Misc Non-food. " +
 		"This is independent of whether the items were purchased prior to or at the session. The Sides and Sweets sales as percentage of Total AGR is shown in parentheses. The Sides and Sweet sales do not reflect Dinner Dollar or other discounts.",
 tech_text: "Formula: Sum(Sides &amp; Sweets purchased + Misc Food Cost + Misc Nonfood Cost)"
 };
ddHelpData['dashboard']['gross_revenue_breakdown']['avg_addon_sales'] =
{title: "Average Sides &amp; Sweets Sales",
 help_text: "Average Sides &amp; Sweets sales is the average cost of Sides &amp; Sweets and Misc Food and Misc Non-food per order",
 tech_text: "Formula: Sum(Sides &amp; Sweets purchased + Misc Food Cost + Misc Nonfood Cost) / Total Order Count"
 };
ddHelpData['dashboard']['gross_revenue_breakdown']['avg_addon_sales_to_date'] =
{title: "Average Sides &amp; Sweets Sales",
 help_text: "Average Sides &amp; Sweets sales is the average cost of Sides &amp; Sweets and Misc Food and Misc Non-food per order",
 tech_text: "Formula: Sum(Sides &amp; Sweets purchased + Misc Food Cost + Misc Nonfood Cost) / Total To Date Order Count"
 };
ddHelpData['dashboard']['gross_revenue_breakdown']['totals'] =
{title: "Gross Revenue Break Down Totals",
 help_text: "Note: That the Average Ticket total excludes orders of type Starter Pack, Workshop or Fundraiser.",
 tech_text: ""
 };

ddHelpData['dashboard']['taste_occupied_count'] =
{title: "Workshop Session Count",
 help_text: "The amount in parentheses is the number of Workshop sessions that had at least 1 guest.",
 tech_text: ""
 };

ddHelpData['dashboard']['taste_occupied_rate'] =
 {title: "Workshop Session Average Guest Rate",
		 help_text: "The amount in parentheses is the Average number of guests that attended an occupied Workshop session.",
		 tech_text: "Note: The number not in parentheses includes all sessions whether occupied or not."
 };

ddHelpData['dashboard']['fundraiser_occupied_count'] =
{title: "Fundraiser Session Count",
 help_text: "The amount in parentheses is the number of Fundraiser sessions that had at least 1 guest.",
 tech_text: ""
 };

ddHelpData['dashboard']['fundraiser_occupied_rate'] =
 {title: "Fundraiser Session Average Guest Rate",
		 help_text: "The amount in parentheses is the Average number of guests that attended an occupied Fundraiser session.",
		 tech_text: "Note: The number not in parentheses includes all sessions whether occupied or not."
 };


ddHelpData['dashboard']['sales_adjustments'] =
{title: "Sales Adjustment",
 help_text: "Sales adjustments are applied to Gross Revenue to derive Adjusted Gross Sales (AGR). Sales adjustment are listed in the formula for AGR. Briefly, sales adjustments are adjustments entered using the Dream Report Data Entry page plus certain vouchers and gift certificates and referral reward store credit.",
 tech_text: ""
 };

ddHelpData['dashboard']['gross_revenue'] =
{title: "Gross Revenue",
 help_text: "Gross Revenue is all revenue minus sales tax.",
 tech_text: ""
 };



ddHelpData['dashboard']['guests'] =
{title: "Guests",
 help_text: "This section displays guest counts by type, instore signups of these guests and the average number of servings purchased.",
 tech_text: ""
 };
ddHelpData['dashboard']['guests']['reacquired_guest'] =
{title: "Reacquired Guests",
 help_text: "A Reacquired guest is a guest who has an order history but has not attended a session in the past 12 months.",
 tech_text: ""
 };
ddHelpData['dashboard']['guests']['new_guest'] =
{title: "New Guests",
 help_text: "A New guest is a guest having placed 1 or more orders in the current month but has no prior order history with Dream Dinners.",
 tech_text: ""
 };
ddHelpData['dashboard']['guests']['new_guest_count'] =
{title: "New Guests Count",
help_text: "The New Guest Count is the number of guests that have their first session in the current menu month.",
tech_text: ""
};
ddHelpData['dashboard']['guests']['existing_guest'] =
{title: "Existing Guests",
 help_text: "An Existing guest has attended a session within the prior 12 months.",
 tech_text: ""
 };
ddHelpData['dashboard']['guests']['guest_count'] =
{title: "Guest Count",
 help_text: "Guest counts are based on unique guest accounts with sessions attended per the time period. This count can differ from order counts. For example, A guest may attend a Workshop session early in the month and then return for a regular session later that same month. In this case the guest count is incremented by one.",
 tech_text: "Note: If a guest does have more than 1 order in the month and was either reacquired or new for the first order then the guest is assign to the new or reacquired counts."
 };
ddHelpData['dashboard']['guests']['percent_to_total'] =
{title: "% of Guest to Total Guests",
 help_text: "Of the total number of guests for the period this is the percentage of the type of guest and order attending in the same period.",
tech_text: ""
};
ddHelpData['dashboard']['guests']['in_store_signup'] =
{title: "In-Store Sign-Up",
 help_text: "This is the percentage of guests attending sessions for the period that have signed up for a future session.",
tech_text: "Formula: count(in store signups) / count(guests attending sessions and purchasing full orders).<br />In Store Signups: An order is determined to be 'in store' at the time the order is placed. There are 2 ways an order is considered in-store: " +
	"<ol><li>The order is placed through direct order on the day the guest attended a session (or up to six days later. This is so drop in customers whose orders are placed after the fact can also be counted.)</li>" +
	"<li>The order was placed with an existing future session. Orders placed either through the customer site or through Direct Order can be counted through this method.</li></ol>"
};
ddHelpData['dashboard']['guests']['servings_per_guest'] =
{title: "Servings per Guest",
 help_text: "The average number of servings purchased by guest type and order type.",
tech_text: ""
};

ddHelpData['dashboard']['orders'] =
{title: "Orders",
 help_text: "This section displays order counts by type, instore signups of resulting from attendance of these orders and the average number of servings purchased.",
 tech_text: ""
 };
ddHelpData['dashboard']['orders']['reacquired_guest'] =
{title: "Reacquired Guests",
 help_text: "A Reacquired guest is a guest who has an order history but has not attended a session in the past 12 months.",
 tech_text: ""
 };
ddHelpData['dashboard']['orders']['new_guest'] =
{title: "New Guests",
 help_text: "A New guest is a guest having placed 1 or more orders in the current month but has no prior order history with Dream Dinners.",
 tech_text: ""
 };
ddHelpData['dashboard']['orders']['existing_guest'] =
{title: "Existing Guests",
 help_text: "An Existing guest has attended a session within the prior 12 months.",
 tech_text: ""
 };
ddHelpData['dashboard']['orders']['order_count'] =
{title: "Order Count",
 help_text: "The number of orders placed for sessions of the current month.",
 tech_text: ""
 };
ddHelpData['dashboard']['orders']['percent_to_total'] =
{title: "% of Orders to Total Orders",
 help_text: "Of the total number of orders for the period this is the percentage of the type of guest and order attending in the same period.",
tech_text: ""
};
ddHelpData['dashboard']['orders']['in_store_signup'] =
{title: "In-Store Sign-Up",
 help_text: "This is the percentage of guests attending sessions for the period that have signed up for a future session.",
tech_text: "Formula: count(in store signups) / count(guests attending sessions).<br />In Store Signups: An order is determined to be 'in store' at the time the order is placed. There are 2 ways an order is considered in-store: " +
	"<ol><li>The order is placed through direct order on the day the guest attended a session (or up to six days later. This is so drop in customers whose orders are placed after the fact can also be counted.)</li>" +
	"<li>The order was placed with an existing future session. Orders placed either through the customer site or through Direct Order can be counted through this method.</li></ol>" +
	"Also for the order to count as in-store the new order must be for 36 servings or more unless the current order is a Workshop or Fundraiser order in which case the new order must be for 18 servings or more."
};
ddHelpData['dashboard']['orders']['servings_per_guest'] =
{title: "Servings per Order",
 help_text: "The average number of servings purchased per order by guest type and order type.",
tech_text: ""
};
ddHelpData['dashboard']['orders']['completed_order_count'] =
{title: "Completed Order Count",
 help_text: "The number of orders that are completed. An order is completed if it is active and in the past. All other order counts are for the entire month regardless of the time.",
tech_text: ""
};
ddHelpData['dashboard']['orders']['unique_guests'] =
{title: "Unique Guests",
 help_text: "For each category this is the number of individual guests. This can be different than the number of orders when 1 guest places multiple orders. Also, for the same reason, the total unique guests for the month may be different than the sum of the category totals.",
tech_text: ""
};

ddHelpData['dashboard']['sessions'] =
{title: "Sessions",
 help_text: "An overview of sessions for the current period.",
tech_text: ""
};
ddHelpData['dashboard']['sessions']['session_count'] =
{title: "Session Counts",
 help_text: "The count of sessions - total and by session type. A session is counted if it was ever open for orders unless it was deleted.",
tech_text: ""
};
ddHelpData['dashboard']['sessions']['orders_count'] =
{title: "Order Counts",
 help_text: "The count of orders - total and by session type. All order types are counted.",
tech_text: ""
};
ddHelpData['dashboard']['sessions']['orders_per_session'] =
{title: "Orders per Session",
 help_text: "The order count divided by the session count.",
tech_text: ""
};
ddHelpData['dashboard']['sessions']['adjusted_gross_revenue'] =
{title: "Gross Revenue",
 help_text: "The amount of adjusted gross revenue broken out by session type. (Made for You revenue also displays the percentage of total AGR in parentheses.)",
tech_text: ""
};

ddHelpData['dashboard']['RSVPs'] = new Object();
ddHelpData['dashboard']['RSVPs']['rsvp_count'] =
    {title: "RSVP Guest Count",
        help_text: "An RSVP guest is a potential guest invited to a Friend's Night Out Workshop.",
        tech_text: "This count reflects the count of guests with a non-cancelled RSVP or a now deleted RSVP that was upgraded to a MPW order."
    };
ddHelpData['dashboard']['RSVPs']['rsvp_upgrade_count'] =
    {title: "RSVP Upgrade Guest Count",
        help_text: "An RSVP Upgrade guest is a guest that converts the RSVP into a Workshop order.",
        tech_text: ""
    };

ddHelpData['dashboard']['retention'] = new Object();
ddHelpData['dashboard']['retention']['converted_guests'] =
{title: "Converted Guests",
 help_text: "A converted guest is a new or reacquired guest in the current period that has subsequently placed another order.",
tech_text: ""
};
ddHelpData['dashboard']['retention']['conversion_rate'] =
{title: "Conversion Rate",
help_text: "The conversion rate is the percentage of new or reacquired guests that place a qualifying order. The qualifying order can be in the current menu/month or subsequent menu/month. This count of new/reacquired " +
                    "guests with a qualifying order are compared to the total number of new/reacquired guests.",
tech_text: "Formula: converted guests (NEW and REACQUIRED guests that have session in the current month and a follow up order) / total new and reacquired guests.  Note: Counts are based on sessions that have already occurred."
};
ddHelpData['dashboard']['retention']['average_annual_visits'] =
{title: "Average Annual Visits",
 help_text: "Average annual visits is the average number of repeat orders by guests over the last year.",
tech_text: "Formula: total number of orders of past 12 months / number of unique guests of the past 12 months."
};
ddHelpData['dashboard']['retention']['45_day_lost_guests'] =
{title: "45 Day Lost Guest",
 help_text: "This metric is the number of guests who had attended a session in a 45 day window but have not placed an order for a session that would occur after the window. The window ends now and begins 45 days prior to that.",
tech_text: ""
};
ddHelpData['dashboard']['retention']['retention_rate'] =
{title: "Retention Rate",
 help_text: "This metric takes the number of existing guests with a regular order for this menu, who have placed an order for the next menu, and compares it to the total number of existing guests with a regular order for this menu.  The percentage noted in parentheses is based on ALL regular orders for this menu, whereas the main metric is only looking at regular orders for sessions that have already occurred.",
tech_text: "Note: In-store rules are not applied. The guest simply has to have a regular order in the following month regardless of when the order was placed."
};
ddHelpData['dashboard']['ranking'] = new Object();
ddHelpData['dashboard']['ranking']['percent_increase'] =
{title: "Gross Revenue by % Increase",
 help_text: "This the percent increase of the current period's adjusted gross revenue compared to the same month of last year.",
tech_text: ""
};
ddHelpData['trending'] = new Object();
ddHelpData['trending']['performance'] = new Object();
ddHelpData['trending']['performance']['last_year_agr'] =
{title: "Last Year Adjusted Gross Revenue",
help_text: "The adjsuted revenue for your store for the same month 1 year ago.",
tech_text: ""
};
ddHelpData['trending']['performance']['percent_increase'] =
{title: "Gross Revenue by % Increase",
 help_text: "This the percent increase of the month's adjusted gross revenue compared to the same month last year.",
tech_text: ""
};
ddHelpData['trending']['performance']['revenue_increase'] =
{title: "Gross Revenue by $ Increase",
 help_text: "This the revenue increase of the month's adjusted gross revenue compared to the same month last year.",
tech_text: ""
};
ddHelpData['trending']['performance']['average_ticket'] =
{title: "Average Ticket",
 help_text: "Average ticket is the average of the Gross Revenue per order for the month. Starter Pack, Workshop and Fundraiser orders are excluded from this metric.",
tech_text: ""
};
ddHelpData['trending']['performance']['average_orders_per_session'] =
{title: "Average Orders per Session",
 help_text: "This is the number of orders placed divided by the number of session offered. Event sessions and orders (Workshop and Fundraiser) are excluded from this metric.",
tech_text: ""
};
ddHelpData['trending']['performance']['unique_orders'] =
{title: "Unique Orders",
 help_text: "This is the total number of active orders. Active orders are orders that were placed and not canceled.",
tech_text: ""
};
ddHelpData['trending']['performance']['unique_guests'] =
{title: "Unique Guests",
 help_text: "This is the total number of unique guests that attended in the month.",
tech_text: ""
};
ddHelpData['trending']['performance']['new_to_total'] =
{title: "Percentage New Guests to Total Guests",
 help_text: "This is the pecentage of guests that were new when compared to the total number of guests.",
tech_text: ""
};
ddHelpData['trending']['performance']['12_month_average'] =
{title: "12 Month Average",
 help_text: "The average for each metric of the 12 months listed.",
tech_text: ""
};
ddHelpData['trending']['performance']['12_month_national_average'] =
{title: "12 Month National Average",
 help_text: "The average for each metric of the 12 months listed for all stores.",
tech_text: ""
};
ddHelpData['trending']['performance']['top_5_average'] =
{title: "Top 5 Average",
 help_text: "The average for each metric of the most current month (the month just previous to the current month) for the top 5 most improved stores.",
tech_text: ""
};
ddHelpData['trending']['guest_habits'] = new Object();
ddHelpData['trending']['guest_habits']['existing_count'] =
{title: "Existing Guest Count",
 help_text: "This is the number of existing guests that attended a session in the month.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['existing_signup'] =
{title: "Existing Guest In-Store Signups",
 help_text: "This is the percentage of existing guests that attended a session in the month and went on to order again in the store.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['new_count'] =
{title: "New Guest Count",
 help_text: "This is the number of new guests that attended a session in the month.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['new_signup'] =
{title: "New Guest In-Store Signups",
 help_text: "This is the percentage of new guests that attended a session in the month and went on to order again in the store.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['reac_count'] =
{title: "Reacquired Guest Count",
 help_text: "This is the number of reacquired guests that attended a session in the month.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['reac_signup'] =
{title: "Reacquired Guest In-Store Signups",
 help_text: "This is the percentage of reacquired guests that attended a session in the month and went on to order again in the store.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['45_day_lost_guests'] =
{title: "45 Day Lost Guest",
 help_text: "This metric is the number of guests who had attended a session in a 45 day window but have not placed an order for a session that would occur after the window. The window ends on the last day of the month in question and begins 45 days prior to that.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['servings_per_guest'] =
{title: "Servings per Guest",
 help_text: "This is the average number of servings purchased by guests for the month. Starter Pack, Workshop and Fundraiser orders are excluded from this metric.",
tech_text: ""
};
ddHelpData['trending']['guest_habits']['average_annual_visits'] =
{title: "Average Annual Visits",
help_text: "Average annual visits is the average number of repeat orders by guests over the last year. This metric is treated slightly differently here than in the Dashboard in that guests purchasing only Starter Pack orders and those Starter Pack and Workshop orders are excluded. This should mean a slightly high",
tech_text: "Formula: total number of regular orders (not Workshop or Fundraiser or Starter Pack) of past 12 months / number of unique guests purchasing regular orders over the past 12 months."
};

ddHelpData['trending']['guest_habits']['cancelled_orders'] =
{title: "Total Canceled Orders",
help_text: "The total number of canceled orders for sessions in the given month.",
tech_text: ""
};

ddHelpData['p_and_l'] = new Object();
ddHelpData['p_and_l']['cogs'] =
{title: "Cost of Goods Sold",
help_text: "Include these accounts from your P&L:  5000 - COGS Food, 5010 - COGS Packaging, 5020 - COGS Discounts",
tech_text: ""
};

ddHelpData['p_and_l']['employee_wages'] =
{title: "Employee Wages",
help_text: "Use P&L Account 6001 - Employee Wages (do not include owner(s)' or manager's pay on this line)",
tech_text: ""
};

ddHelpData['p_and_l']['manager_salaries'] =
{title: "Manager Salaries",
help_text: "Use P&L Accounts 6002 – Manager Salaries & 6004 - Mgr Incentive Bonus (do not include owner(s)’ or employees’ pay on this line)",
tech_text: ""
};

ddHelpData['p_and_l']['owner_salaries'] =
{title: "Owner Salaries",
help_text: "Use P&L Account 6011 - Owner(s) Salaries (do not include employees' or manager's pay on this line)",
tech_text: ""
};

ddHelpData['p_and_l']['payroll_taxes'] =
{title: "Payroll Taxes",
help_text: "Include Accounts 6003 Payroll Taxes & 6012 Owner Payroll Taxes",
tech_text: ""
};

ddHelpData['p_and_l']['bank_card_merchant_fees'] =
{title: "Bank Card Merchant Fees",
help_text: "Use P&L Account 7060 Bank Card Merchant Fees",
tech_text: ""
};

ddHelpData['p_and_l']['kitchen_and_office_supplies'] =
{title: "Kitchen & Office Supplies",
help_text: "Include Accounts 7130 Kitchen Supplies & 7170 Office Supplies",
tech_text: ""
};

ddHelpData['p_and_l']['total_marketing_and_advertising_expense'] =
{title: "Total Marketing & Advertising Expense",
help_text: "Include Accounts 7150 through 7155 (Advertising, Program Exp., Loyalty, Event, Promotional Meals & Discounts)",
tech_text: ""
};

ddHelpData['p_and_l']['rent_expense'] =
{title: "Rent Expense",
help_text: "Use P&L Account 7210 - Rent Expense (Rent/CAM/Triple Net, etc.)",
tech_text: ""
};

ddHelpData['p_and_l']['repairs_and_maintenance'] =
{title: "Repairs & Maintenance",
help_text: "Include Accounts 7220 through 7222 (Building/Equipment Repairs & Maintenance)",
tech_text: ""
};

ddHelpData['p_and_l']['utilities'] =
{title: "Utilities",
help_text: "Include Accounts 7280 through 7284 (Electric, Gas, Water/Sewer/Garbage, Other)",
tech_text: ""
};


ddHelpData['p_and_l']['monthly_debt_payments'] =
{title: "Monthly Loan Payments (Principal Only)",
help_text: "Enter only the principal portion of all monthly loan payments you make to banks, investors, etc.",
tech_text: ""
};

ddHelpData['p_and_l']['other_expenses'] =
{title: "All Other Expenses",
help_text: "Include any expenses that are outside of the provided categories.",
tech_text: ""
};

ddHelpData['p_and_l']['net_income'] =
{title: "Net Income",
help_text: "Net Income will be the last line shown on your P&L Statement.",
tech_text: ""
};

ddHelpData['p_and_l']['gross_sales'] =
{title: "Gross Sales",
help_text: "This field is prepopulated with data from BackOffice and can be viewed in detail by running a Financial Statistical Report.",
tech_text: ""
};

ddHelpData['p_and_l']['markups'] =
{title: "Mark-Ups",
help_text: "This field is prepopulated with data from BackOffice and can be viewed in detail by running a Financial Statistical Report.",
tech_text: ""
};

ddHelpData['p_and_l']['sales_w_adj'] =
{title: "Total Sales with Adjustments & Discounts",
help_text: "This field is prepopulated with data from BackOffice and can be viewed in detail by running a Financial Statistical Report.",
tech_text: ""
};

ddHelpData['p_and_l']['adj_and_discounts'] =
{title: "Adjustments & Discounts",
help_text: "This field is prepopulated with data from BackOffice and can be viewed in detail by running a Financial Statistical Report.",
tech_text: ""
};

ddHelpData['p_and_l']['national_marketing_fee'] =
{title: "National Marketing Fee",
help_text: "Use P&L Account 7112 – National Marketing Fund Fee.",
tech_text: ""
};

ddHelpData['p_and_l']['royalty_fee'] =
{title: "Royalty Fee",
help_text: "Use P&L Account 7111 – Royalty Fees.",
tech_text: ""
};


ddHelpData['inv_mgr'] = new Object();

ddHelpData['inv_mgr']['national_sales_mix'] =
    {title: "National Sales Mix",
     help_text: "Home Office projected meal sales percentages for all Dream Dinners stores nation-wide. Must equal 100%.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['store_sales_mix'] =
    {title: "Store Sales Mix",
     help_text: "Adjustable store specific meal sales percentages. Must equal 100%.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['projected_inventory'] =
    {
     title: "Projected Inventory",
     help_text: "Preordered Total Servings from combination of Projected Guest Counts and Store Sales Mix adjustments.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['sum_servings_remaining'] =
    {
     title: "Sum of Total Servings Remaining",
     help_text: "Total servings remaining for entire menu month.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['sum_weeks_inventory'] =
    {
     title: "Sum of Adjusted Weeks Inventory",
     help_text: "Sum of all manually adjusted inventory numbers for the entire menu month.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['bottom_line'] =
    {
     title: "Sum of Adj Weeks Inv/Remaining Available to Promise",
     help_text: "Sum of entire menu month manually adjusted inventory/ inventory available to sell.",
     tech_text: ""
    };


ddHelpData['inv_mgr']['proj_weeks_inventory'] =
    {
     title: "Projected Weeks Inventory",
     help_text: "Calculated projected weeks inventory based on nation-wide guest trends.",
     tech_text: ""
    };


ddHelpData['inv_mgr']['servings_remaining_1'] =
    {
     title: "Servings Remaining",
     help_text: "Week 1 servings remaining.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['adj_weeks_invntory_1'] =
    {
     title: "Adjusted Weeks Inventory",
     help_text: "Week 1 manually adjusted inventory numbers.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['servings_remaining_2'] =
    {
     title: "Servings Remaining",
     help_text: "Week 2 servings remaining.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['adj_weeks_invntory_2'] =
    {
     title: "Adjusted Weeks Inventory",
     help_text: "Week 2 manually adjusted inventory numbers.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['servings_remaining_3'] =
    {
     title: "Servings Remaining",
     help_text: "Week 3 servings remaining.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['adj_weeks_invntory_3'] =
    {
     title: "Adjusted Weeks Inventory",
     help_text: "Week 3 manually adjusted inventory numbers.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['servings_remaining_4'] =
    {
     title: "Servings Remaining",
     help_text: "Week 4 servings remaining.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['adj_weeks_invntory_4'] =
    {
     title: "Adjusted Weeks Inventory",
     help_text: "Week 4 manually adjusted inventory numbers.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['servings_remaining_5'] =
    {
     title: "Servings Remaining",
     help_text: "Week 5 servings remaining.",
     tech_text: ""
    };

ddHelpData['inv_mgr']['adj_weeks_invntory_5'] =
    {
     title: "Adjusted Weeks Inventory",
     help_text: "Week 5 manually adjusted inventory numbers.",
     tech_text: ""
    };

