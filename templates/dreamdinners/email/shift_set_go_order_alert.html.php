<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
ShiftSetGo Order Alert<br><br>

Guest Name: <?php echo $this->guest_name; ?><br>
Guest Phone 1: <?php echo $this->guestObj->telephone_1 . ' '.$this->guestObj->telephone_1_type; ?><br>
Guest Phone 2: <?php echo $this->guestObj->telephone_2 . ' '.$this->guestObj->telephone_2_type; ?><br>
Guest Email: <?php echo $this->guestObj->primary_email; ?><br>
Order ID: <?php echo $this->order_id; ?><br>
Session: <?php echo $this->session_time; ?><br>
Session Type: <?php echo $this->sessionDetails['session_type_title']; ?><br>
</body>
</html>
