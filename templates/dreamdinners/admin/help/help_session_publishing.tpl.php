<h3>Publishing Sessions in the new Dream Dinners Web Site</h3>

<h2>Introduction</h2>

<p>We hope you will find our new session publishing system to be a powerful and time-saving method of getting your schedule to your customers. Before detailing the steps involved in publishing your sessions
 we would like to say a few words about some fundamental changes to the system.</p>
<p>First, we have changed the order that a menu is specified with respect to the the session time. In the old system, a date and time was specified and then based on this date/time a menu was selected.
If the session date fell within a period of 6 days either before or after the beginning of a month you were presented with a choice of menus. Now, all scheduling is done within the context of a menu.
That is, the menu is always selected first and then all scheduling will be to the selected menu.</p>
<p>Second, We have added to the session a property that controls its visible. Now a session can be "Published", tha t is, visible to your customers, and "Closed". Closed is different than canceled. A session may still be canceled but as in the old system, to be canceled a session must first be cleared of all pending orders. A "Closed" session however can still have pending orders. The only effect that closing a session has is it's visibility. A closed session will only be visible to you and your employees.


<h2>Weekly Templates</h2>

<p>The key to quickly publishing your schedule is the weekly template. The idea here is that your schedule often follows a weeekly pattern. By specifying a weekly template that follows this pattern you can
 quickly quickly "fill" a time span with this repeating pattern. (Note: It is still possible to create and publish a single session as before). Even if you cannot identify a common weekly pattern the template system is
 flexible enough to make publishing a menu's sessions a snap.</p>

 <center><h1>The Weekly Sessions Template Manager</h1></center>

 <img src="<?php echo ADMIN_IMAGES_PATH; ?>/help/week_temp_example.png"></img>

 <br />The above screen shot shows the weekly template manager. This is where you create and manage templates from which you will popuate your schedule. The Template Manager is roughly divided into three sections. The top portion is for managing your templates. Here you may type a new template name and select "create new" to create a new template. If a template is currently loaded the new template will inherit all of the existing sessions of the currently loaded template. If you would like to create a new blank template simply seletct "Clear" before creating the new template. You may save the current template at any time. You may also delete a template. A default template is created for you the first time you visit the Template Manager or if you delete all of your templates.
<br />
The second section is the "day of the week" view which shows all of the current templates sessions. You may select a session here to edit or delete it.
<br />
The third section is the template session editor (Note: It is important to undertand that the sessions seen here in Template Manager are not real sessions but are used when publishing to creae real sessions.) If a session was selected in the "Day of the Week" view then you may edit or delete the session here.

[Feature: Clicking a day in the "Day of the Week" view will set the Day property in the Template Session Editor.]

If a session is not currently being editing then you may use this form to create a new template session.

[Note to editor: I'm calling the sessions here "template sessions", I used to call them proto-sessions but than may be too "geeky" for the audience. You might want a new name  the main thing is that they are not confused with real sessions. Since they have no absolute date it should be clear to most folks.]


 <h2>Publishing</h2>
<p>Once you have set up a weekly template you are ready to publish your menu. you can navigate to Publish Sessions page by clicking the link at the top of the page. Publishing is accomplished in 3 steps.
<ol>
 <li>Specify a Time Span.
 	This is accomplished by selecting a date in the calendar and clicking the "Set Time Span Start" and "Set Time Span End" links. They can be specified in either order and if only one is specifed then the single date selected is considered the time span.</li>
  <li>Fill the Time Span from a template.
 	A template is selected from the template popup menu. The "Fill from Template" button is clicked. The new sesssions are shown with a Blue icon. These sessions do not yet exist and cannot be edited.</li>
  <li>Save or Publish the new sessions.
 	If you choose "Save" the new sessions are then created and saved as real sessions, however they are not yet published and are therefore not yet visible to your customers.
 	If you choose "Publish" the sessions are created and are immediately available to your customers</li>
 </ol></p>
 <ul>Notes
<li>The Save and Publish actions only act upon the current time range. This way you can choose to only save or publish a subset of the available sessions.</li>
<li>The publish action will act upon both saved and new sessions (Saved sessions appear with an orange icon).</li>
<li>Published sessions appear with a green icon.</li>
<li>"New" sessions (shown with a blue 'N' icon) cannot be edited. Also if the new sessions are not saved they will be lost when the page is exited.</li>
</ul>l
The Pubish Interface is shown below:
 <img src="<?php echo ADMIN_IMAGES_PATH; ?>/help/publish_sessions_example.png"></img>

<h2>Managing Sessions</h2>
<p>The Session Manager is the default view for the franchise owner and employees. The calendar defaults to the current menu. <p>You may click a session's time to see a detail of the session. This detail view shows the properties of the session and lists the orders that have been booked against it. You may select "view order" for any of the orders shown and will be presented with the "order detail" view. From here an order can be canceled or rescheduled.</p>
<p>You may click a sessions icon to edit a session. From the session editor you may edit the sessions properties, cancel the session or "close' the session.

<h2>Create a Session</h2>
This page is always accessible from Session section's top menu. If you select a day at the Session Manager before clicking the "Create a Session" link, the date will be pre-filled based on your selection. One thing to note is that you can create the session in either the "published" or "closed" states. If created as a closed session you will need to publish the session at a later date before it will be visible to your customers.
