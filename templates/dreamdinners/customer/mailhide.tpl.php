<?php $this->setScript('foot', 'https://www.google.com/recaptcha/api.js'); ?>
<?php $this->assign('page_title', 'Mail Hide');?>
<?php include $this->loadTemplate('customer/subtemplate/page_popup_header.tpl.php'); ?>

	<div class="media_container" style="padding: 2rem !important;">

		<?php if (!$this->show_string) { ?>
			<form id="mailhide_form" method="POST">
				<input type="submit" class="g-recaptcha button buttonlg" data-sitekey="6LeIGFUUAAAAALYLeejbCRa9ECVhD8C_rJWKYCBz" data-callback='mailHideOnSubmit' value="Reveal Email Address" />
			</form>
		<?php } else { ?>

			<p>The email address is:</p>
			<h2><a href="mailto:<?php echo $this->show_string; ?>"><?php echo $this->show_string; ?></a></h2>

		<?php } ?>
	</div>

<?php include $this->loadTemplate('customer/subtemplate/page_popup_footer.tpl.php'); ?>