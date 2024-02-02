Dream Dinners
Home Delivery Order Reminder

Dear <?= $this->firstname ?>,

Your delivery is coming up soon. We're looking forward to seeing you during your home delivery window on <?=$this->dateTimeFormat($this->session_start, NORMAL);?> to <?= date("g:i A", strtotime($this->session_end))?> at our <?=$this->store_name?> location.

We have your delivery address as 
%%delivery_address_line_1%%,%%delivery_address_line_2%%
%%delivery_address_city%%, %%delivery_address_state%% %%delivery_address_postal_code%%

What to Expect
  - Be home and ready to place your first 3 meals in the fridge to thaw and the rest in the freezer.
  - Add on a few of our delicious sides, breakfast and sweets <?=HTTPS_BASE?>freezer from our Sides and Sweets Freezer.
  - Place your next order to reserve your preferred delivery time. <?=HTTPS_BASE?>session-menu


---------------------------------------------------
Delivery Policy
You understand that someone will have to be home in order accept the delivery and if the driver arrives and no one is available to accept the delivery, the driver may leave the order at my front door and take a photo before they leave. If the driver cannot leave the order for any reason, they may have to return the order back to the store. This will incur an additional return delivery fee and a possible restocking fee. Delivery fees are non-refundable within 48 hours of the scheduled delivery date and time. If a new date is selected to deliver the order again, a new delivery fee will be charged.

Reschedule and Cancelation Policy
If you need to reschedule or cancel your order, contact us six days prior to your order date. Cancelations with six or more days’ notice will receive a full refund. Cancelations within five or fewer days’ notice will be subject to a 25% restocking fee.

---------------------------------------------------

We look forward to seeing you soon.

Enjoy!
 Dream Dinners


Contact your local store: <?=HTTPS_BASE?>locations/<?=$this->store_id?>
View Dream Dinners Policy, Terms and Conditions: <?=HTTPS_BASE?>terms