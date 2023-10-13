<?php
//$statusMsg = $this->getStatusMsg();
$errorMsg = $this->getErrorMsg();
$debugMsg = $this->getDebugMsg();
?>

<div class="container">
	<div class="row">
		<div id="statusMsg" role="alert" class="col-12 alert alert-warning alert-dismissible fade collapse <?php echo (!empty($statusMsg) && strlen($statusMsg) > 0) ? 'show' : ''; ?>">
			<div class="font-weight-bold">Server Message</div>
			<p><?php echo $errorMsg; ?></p>
			<p><?php if (!empty($statusMsg)) { echo stripcslashes($statusMsg);} ?></p>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div id="errorMsg" role="alert" class="col-12 alert alert-danger alert-dismissible fade collapse <?php echo (strlen($errorMsg.(DEBUG ? $debugMsg:'')) > 0) ? 'show' : ''; ?>">
			<div class="font-weight-bold">Error</div>
			<div id="errorMsgText">
				<?php echo $errorMsg; ?>
				<?php echo $debugMsg; ?>
			</div>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
</div>