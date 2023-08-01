<?php
//$statusMsg = $this->getStatusMsg();
$errorMsg = $this->getErrorMsg();
$debugMsg = $this->getDebugMsg();
?>

<div id="statusMsg" style="display:<?php echo (!empty($statusMsg) && strlen($statusMsg) > 0) ? 'block' : 'none'; ?>;">

	<div style="width:755px;margin:auto;border:1px solid #000;padding:20px;text-align:center;background-color:#ded6cb;color:#000;">
		<span style="float:right;"><a href="javascript:hideStatusMessage('statusMsg');"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/cross.png" alt="Close" /></a></span>
		<span class="warning_text">Server Message</span><?php echo $errorMsg; ?><br />
		<?php if (!empty($statusMsg)) { echo stripcslashes($statusMsg);} ?>
	</div>

	<br />

</div>

<div id="errorMsg" class="formerror_red" style="display:<?php echo (strlen($errorMsg.(DEBUG ? $debugMsg:'')) > 0) ? 'block' : 'none'; ?>;">

	<div style="width:755px;margin:auto;border:2px solid red;padding:20px;text-align:center;background-color:#ded6cb;color:#000;">
		<span style="float:right;"><a href="javascript:hideStatusMessage('errorMsg');"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/cross.png" alt="Close" /></a></span>
		<span class="warning_text">Error</span> <div id="errorMsgText" class="warning_text"><?php echo $errorMsg; ?></div><br />
		<?php echo $debugMsg; ?>
	</div>

	<br />

</div>