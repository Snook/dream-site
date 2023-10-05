<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/tinyeditor.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/email.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/email.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/tinyeditor.css'); ?>
<?php $this->setScriptVar("$.phpVar = { extensions: '" . $this->extensions . "', js_extensions: [" . $this->js_extensions . "], page_back: '" . $this->back . "' };"); ?>
<?php $this->setOnload('email_init();'); ?>
<?php $this->assign('topnav', 'guests'); ?>
<?php $this->assign('page_title', 'Send Email'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<form enctype="multipart/form-data" id="email_form" name="form_email" action="" method="post">
<?php echo $this->email_form['hidden_html']; ?>

<table style="width:100%;">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2">Email Guest</td>
</tr>
<?php if (!empty($this->email_form['sender_name_html'])) { ?>
<tr>
	<td align="right" class="bgcolor_light">From Name</td>
	<td class="bgcolor_light"><?php echo $this->email_form['sender_name_html']; ?></td>
</tr>
<?php } ?>
<tr>
	<td align="right" class="bgcolor_light" style="width: 150px;">From Email</td>
	<td class="bgcolor_light"><?php echo (!empty($this->email_form['sender_email_html']) ? $this->email_form['sender_email_html'] : $this->email_form['sender_email_dropdown_html']); ?></td>
</tr>
<tr>
	<td align="right" class="bgcolor_light">To Recipient</td>
	<td class="bgcolor_light">
		<div id="recipient_list">
		<?php if (!empty($this->recipient_list)) { ?>
		<?php foreach($this->recipient_list AS $user_id => $recipient) { ?>
			<div class="recipient">
				<span class="name" data-tooltip="<?php echo $recipient['primary_email']; ?>"><?php echo $recipient['firstname']; ?> <?php echo $recipient['lastname']; ?> <svg class="icon icon-cancel-circle delete" data-user_id="<?php echo $recipient['id']; ?>"><use xlink:href="#icon-cancel-circle"></use></svg></span>
			</div>
		<?php } ?>
		<?php } ?>
			<div id="recipient_list_end" class="clear"></div>
		</div>
		<span data-guestsearch="add_recipient" data-select_button_title="Add Recipient" data-all_stores_checked="false" data-select_function="addRecipient" class="btn btn-primary btn-sm">Add Recipient</span>
		<div class="clear"></div>
	</td>
</tr>
<tr>
	<td align="right" class="bgcolor_light"><span data-tooltip="BCC field provided to help with the use of CRM tools">Store CRM Email<sup>?</sup></span></td>
	<td class="bgcolor_light"><?php echo $this->email_form['bcc_email_html']; ?></td>
</tr>
<tr>
	<td align="right" class="bgcolor_light">Subject</td>
	<td class="bgcolor_light"><?php echo $this->email_form['email_subject_html']; ?></td>
</tr>
<tr>
	<td valign="top" align="right" class="bgcolor_light">Message</td>
	<td class="bgcolor_light"><?php echo $this->email_form['email_body_html']; ?></td>
</tr>
<tr>
	<td valign="top" align="right" class="bgcolor_light">Attachment<br/><span style="font-size:x-small;"><a href="javascript:resetAttachment();">[remove]</a></span></td>
	<td class="bgcolor_light">
		<table style="width:100%;">
		<tr>
			<td valign="top"><?php echo $this->email_form['email_attachment_html']; ?></td>
			<td width="100%" style="padding-left:6px;"><?php echo $this->sizelimit; ?> <?php echo $this->extensions; ?></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td colspan="2" align="center" class="bgcolor_light">
		<div id="email_submit"><?php echo $this->email_form['email_send_html']; ?><?php echo $this->email_form['email_cancel_html']; ?></div>
		<div id="processing_message" style="display:none;"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/throbber_processing_noborder.gif" alt="Processing" /></div>
	</td>
</tr>
</table>

</form>

<svg>
	<symbol id="icon-cancel-circle" viewBox="0 0 1024 1024">
		<title>cancel-circle</title>
		<path class="path1" d="M512 0c-282.77 0-512 229.23-512 512s229.23 512 512 512 512-229.23 512-512-229.23-512-512-512zM512 928c-229.75 0-416-186.25-416-416s186.25-416 416-416 416 186.25 416 416-186.25 416-416 416z"></path>
		<path class="path2" d="M672 256l-160 160-160-160-96 96 160 160-160 160 96 96 160-160 160 160 96-96-160-160 160-160z"></path>
	</symbol>
</svg>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>