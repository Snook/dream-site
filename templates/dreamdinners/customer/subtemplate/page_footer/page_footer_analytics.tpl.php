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

<?php if (!empty($this->head_analytics_array['smfcArray'])) { ?>
	<!--salesforce pixel-->
	<img src="//click.s10.exacttarget.com/conversion.aspx?xml=<system><system_name>tracking</system_name><action>conversion</action><member_id><?php echo $this->head_analytics_array['smfcArray']['mid']; ?></member_id><job_id><?php echo $this->head_analytics_array['smfcArray']['j']; ?></job_id><sub_id><?php echo $this->head_analytics_array['smfcArray']['sfmc_sub']; ?></sub_id><list><?php echo $this->head_analytics_array['smfcArray']['l']; ?></list><original_link_id><?php echo $this->head_analytics_array['smfcArray']['u']; ?></original_link_id><BatchID><?php echo $this->head_analytics_array['smfcArray']['jb']; ?></BatchID><conversion_link_id>1</conversion_link_id><link_alias><?php echo (!empty($this->page_title)) ? $this->page_title : 'DreamDinners.com'; ?></link_alias><display_order>3</display_order><email></email><data_set></data_set></system>" width="1" height="1">
<?php } ?>