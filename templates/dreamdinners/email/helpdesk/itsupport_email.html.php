<html>
<head></head>
<body>
<p>Priority: <?php echo $this->priority; ?></p>
<p>&nbsp;</p>
<p>Problem URL: <?php echo $this->problem_url; ?></p>
<p>&nbsp;</p>
<p>Issue: <?php echo nl2br($this->description); ?></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>#### Reporter Information ####</p>
<p>Reporter: <?php echo $this->first_name; ?> <?php echo $this->last_name; ?></p>
<p>User ID: <?php echo $this->user_id; ?></p>
<p>Email: <?php echo $this->email_address; ?></p>
<p>Telephone: <?php echo $this->phone_number; ?></p>
<p>Store ID: <?php echo $this->store_id; ?>; Name: <?php echo $this->store_info['store_name']; ?>; Location: <?php echo $this->store_info['city']; ?>, <?php echo $this->store_info['state_id']; ?></p>
<p>Reporting Page: <?php echo $this->reporting_page; ?></p>
<p>Browser: <?php echo $this->browser; ?></p>
<p>&nbsp;</p>
</body>
</html>