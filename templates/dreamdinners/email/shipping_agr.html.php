<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="650" border="0" cellspacing="0" cellpadding="8">
<tr>
<td>Cristen,<br><br>

Here is the total AGR collected for the <?php echo $this->month; ?> <?php echo $this->year; ?> menu in the <?php echo $this->storeName; ?>.<br><br>

  $<?php echo $this->agr; ?><br><br>

Please add this as a sales adjustment to the parent store before collecting royalties.<br><br>

Thank you!

</td>
</tr>
</table>

</body>
</html>