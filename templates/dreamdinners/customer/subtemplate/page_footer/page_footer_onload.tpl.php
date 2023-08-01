<script type="text/javascript">
	(function() {
		<?php if (empty($this->page_popup) || $this->page_popup != true) { ?>
		<?php if ($msg = $this->getErrorMsg()) { CLog::RecordNew(CLog::DEBUG, 'Get Error Message: ' . $msg); ?>
		dd_message({
			title: 'Error',
			type: 'error_msg',
			message: '<?php echo $msg; ?>',
			div_id: 'dd_ErrorMessage'
		});
		<?php } ?>
		<?php if ($msg = $this->getStatusMsg()) { ?>
		dd_message({
			type: 'status_msg',
			message: '<?php echo $msg; ?>',
			div_id: 'dd_StatusMessage'
		});
		<?php } ?>
		<?php } ?>
		<?php
		if (!empty($this->head_onload))
		{
			foreach ($this->head_onload as $onload)
			{
				echo "\t" . $onload . "\n";
			}
		}
		?>
	})();
</script>
