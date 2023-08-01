<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table width="650"  border="0" cellspacing="0" cellpadding="0">
<tr bgcolor="#afbd21">
<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinnners.gif" alt="Dream Dinners" width="325" height="75"></td>
<td width="300" align="right" style="padding: 5px"></td>
</tr>
<tr bgcolor="#663300">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">P&L Financial Compliance Reminder</span></p></td>
</tr>
</table>
<table width="650"  border="0" cellspacing="0" cellpadding="8">
<tr>
<td><p>Dear Dream Dinners Owner,</p>

<p>You are receiving this email reminder because the deadline for entering your <?php echo $this->prevMonth;?> P&L financial data
into the input template on BackOffice was <?php echo $this->curMonth;?> 20th.  As of today, we show you have not yet entered
all of your information.   Please use a copy of your Profit & Loss Statement (P&L) to enter this required
data into the template, ensuring that every line item in the template has been populated.  Enter a &ldquo;0&rdquo; (zero)
in any field if your P&L statement shows zero for that line item.  Please also ensure your P&L financial information
for all previous months has been input into the template as well.  To avoid further escalation, your
data needs to be entered no later than <?php echo $this->curMonth;?> 30th.</p>

<p>A <a href="https://dreamdinners.box.com/s/9qnprsz53zygv27htbmjxj7gqqaja6cz">guide</a> with step-by-step instructions can be found at:
https://dreamdinners.box.com/s/9qnprsz53zygv27htbmjxj7gqqaja6cz.</p>

<p>If you need assistance, please reach out to Support at support@dreamdinners.com.</p>

<p>Thank you for your prompt attention to this matter.</p>
</td>
</tr>
</table>

</body>
</html>
