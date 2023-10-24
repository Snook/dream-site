<noscript>
	<!--Facebook pixel-->
	<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=2559596927648235&ev=PageView&noscript=1" alt=""/>
	<!--Nextdoor pixel-->
	<img height="1" width="1" style="display:none" src="https://flask.nextdoor.com/pixel?pid=c729dacc-8cf3-4306-8042-7aa458ce5648&ev=PAGE_VIEW&noscript=1" alt=""/>
	<?php if (CBrowserSession::getValue('dd_thank_you')) { ?>
		<img height="1" width="1" style="display:none" src="https://flask.nextdoor.com/pixel?pid=c729dacc-8cf3-4306-8042-7aa458ce5648&ev=CONVERSION&noscript=1" alt=""/>
	<?php } ?>
	<!--Pinterest pixel-->
	<img height="1" width="1" style="display:none;" alt="" src="https://ct.pinterest.com/v3/?event=init&tid=2614481882104&pd[em]=<?php echo $this->head_analytics_array['email_sha256']; ?>&noscript=1" />
</noscript>

<?php if (CBrowserSession::getValue('dd_thank_you')) { // Vertical Response ?>
	<!--vertical response pixel-->
	<?php if ($this->head_analytics_array['store_VR_code'])  { ?>
		<img src="https://cts.vresp.com/s.gif?h=<?php echo $this->head_analytics_array['store_VR_code']; ?>&amount=<?php echo $this->head_analytics_array['total']; ?>" height="1" width="1" />
	<?php } ?>
	<img src="https://cts.vresp.com/s.gif?h=46b82cf4cc&amount=<?php echo $this->head_analytics_array['total']; ?>" height="1" width="1" />
<?php } ?>