<html>
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>

<div itemprop="reservationFor" itemscope itemtype="http://schema.org/Event">
	<meta itemprop="name" content="Dream Dinners Session"/>
	<time itemprop="startDate" datetime="<?php echo $this->dateTimeFormat($this->session_info->session_start, DATE_TIME_ITEMPROP); ?>"/>
	<div itemprop="location" itemscope itemtype="http://schema.org/Place">
		<meta itemprop="name" content="Dream Dinners <?php echo $this->store_info->store_name; ?>"/>
		<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
			<meta itemprop="streetAddress" content="<?php echo $this->store_info->address_line1; ?><?php echo !empty($this->store_info->address_line2) ? ' ' . $this->store_info->address_line2 : ''; ?>"/>
			<meta itemprop="addressLocality" content="<?php echo $this->store_info->city; ?>"/>
			<meta itemprop="addressRegion" content="<?php echo $this->store_info->state_id; ?>"/>
			<meta itemprop="postalCode" content="<?php echo $this->store_info->postal_code; ?>"/>
			<meta itemprop="addressCountry" content="<?php echo $this->store_info->country_id; ?>"/>
			<meta itemprop="telephone" content="<?php echo $this->store_info->telephone_day; ?>"/>
			<meta itemprop="email" content="<?php echo $this->store_info->email_address; ?>"/>
		</div>
	</div>
</div>

<table width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?php echo HTTPS_BASE; ?>how_it_works">How It Works</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">RSVP Confirmation</span></p></td>
	</tr>
</table>
<table width="650"  border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>We're thrilled you'll be joining us! Be ready to learn how Dream Dinners can be the solution to your dinnertime challenges! </p>
			<p>Here's what to expect. You will...</p>
		  <ul>
				<li>Experience how to save time and money with our simple cook-at-home meals.</li>
			  <li>Sample delicious appetizers.</li>
			  <li>Learn from our helpful team members who can answer any questions you have.</li>
			</ul>
			<p>If you have any questions about this event, please contact the store by using the contact information below.</p>
			<p>We look forward to meeting you!</p>
			<hr />
		</td>
	</tr>
	<tr>
		<td>
			<p><strong>Event Details</strong></p>
			<ul>
				<li>Time:
					<?php echo $this->dateTimeFormat($this->session_info->session_start, VERBOSE) . "\n"; ?>
				</li>
				<li>Location:
					<?php echo $this->store_info->store_name . "\n"; ?>
				</li>
				<li>Address:
					<?php echo $this->store_info->address_line1; ?><?php echo !empty($this->store_info->address_line2) ? '' . $this->store_info->address_line2 : ''; ?>, <?php echo $this->store_info->city; ?>, <?php echo $this->store_info->state_id; ?> <?php echo $this->store_info->postal_code; ?>
				</li>
				<li>Phone: 
					<?php echo $this->store_info->telephone_day . "\n"; ?>
				</li>
			</ul>
		</td>
	</tr>
</table>

</body>
</html>