<?php $this->assign('page_title', 'Cookie Policies');?>
<?php $this->assign('page_description','Dream Dinners cookie policies.'); ?>
<?php $this->assign('page_keywords','policies, cookie policy'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
			<h1>Dream Dinners Cookie Policies</h1>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

		</div>
	</div>
</header>
<?php /*
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 mx-auto">
			<a href="https://www.iubenda.com/privacy-policy/48982534/cookie-policy" class="btn btn-lg btn-green btn-block iubenda-white no-brand iubenda-embed iub-body-embed" title="Cookie Policy" target="_blank">Cookie Policy</a>
			<script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script>
		</div>
	</div>
</div>
*/ ?>
<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>
