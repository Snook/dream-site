var _gaq = _gaq || []; // google
var _iub = _iub || []; // iubenda
window.ytagQ = window.ytagQ || []; //Yext


(function() {

	if (ANALYTICS.analytics)
	{
		// accessibe.com
		var s    = document.createElement('script');
		var h    = document.querySelector('head') || document.body;
		s.src    = 'https://acsbapp.com/apps/app/dist/js/app.js';
		s.async  = true;
		s.onload = function(){
			acsbJS.init({
				statementLink    : '',
				footerHtml       : '',
				hideMobile       : false,
				hideTrigger      : false,
				disableBgProcess : false,
				language         : 'en',
				position         : 'left',
				leadColor        : '#767b21',
				triggerColor     : '#767b21',
				triggerRadius    : '50%',
				triggerPositionX : 'left',
				triggerPositionY : 'bottom',
				triggerIcon      : 'people',
				triggerSize      : 'medium',
				triggerOffsetX   : 20,
				triggerOffsetY   : 20,
				mobile           : {
					triggerSize      : 'small',
					triggerPositionX : 'left',
					triggerPositionY : 'center',
					triggerOffsetX   : 10,
					triggerOffsetY   : 0,
					triggerRadius    : '50%'
				}
			});
		};
		h.appendChild(s);

		// GA3
		_gaq.push(['_setAccount', 'UA-425666-1']);
		_gaq.push(['_trackPageview']);
		_gaq.push(['_setDomainName', 'dreamdinners.com']);
		_gaq.push(['_addIgnoredOrganic', 'www.dreamdinners.com']);
		_gaq.push(['_addIgnoredOrganic', 'dreamdinners.com']);
		_gaq.push(['_addIgnoredRef', 'dreamdinners.com']);
		_gaq.push(['_addIgnoredRef', 'www.dreamdinners.com']);
		_gaq.push(['_setCampaignCookieTimeout', 2419200000]);

		if (ANALYTICS.dd_thank_you !== null && ANALYTICS.page == 'order_details')
		{
			// GA3
			_gaq.push(['_addTrans',ANALYTICS.order_id,'',ANALYTICS.order_total,'','','','','']);
			_gaq.push(['_addItem',ANALYTICS.order_id,'1',ANALYTICS.order_type_title,ANALYTICS.menu_id,ANALYTICS.order_total,'1']);
			_gaq.push(['_trackTrans']);

			// GA4
			$.each(ANALYTICS.vendor.google.gtag.purchase, function (id, purchase)
			{
				gtag('event', 'purchase', purchase);
			});

			//Yext Conversion Tracking
			function ytag() {window.ytagQ.push(arguments);}
			ytag('conversion', {'cid': 'fff98dde-1e06-4be9-a696-629bdf698584', 'cv': ANALYTICS.order_total});
		}
		else if (ANALYTICS.dd_thank_you !== null && ANALYTICS.page == 'order_details_gift_card')
		{
			// GA3
			_gaq.push(['_addTrans',ANALYTICS.gift_card_orders,'',ANALYTICS.gift_card_total,'','','','','']);

			if (ANALYTICS.gift_card_orders.length !== 0)
			{
				$.each(ANALYTICS.gift_card_array, function (id, item)
				{
					_gaq.push(['_addItem',item.id,'GC_' + item.media_type,item.gc_media_type + ' Order','design_type_id-' + item.design_type_id,item.gc_amount,'1']);
				});
			}

			_gaq.push(['_trackTrans']);

			// GA4
			$.each(ANALYTICS.vendor.google.gtag.purchase, function (id, purchase)
			{
				gtag('event', 'purchase', purchase);
			});
		}

		// adwords
		if (ANALYTICS.dd_thank_you != null)
		{
			gtag('event', 'conversion', {'send_to': 'AW-963692398/b9fdCO301I0CEO6Ow8sD'});
		}

		// facebook
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
			n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
			t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
			document,'script','https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '2559596927648235');
		fbq('track', 'PageView');
		if (ANALYTICS.dd_thank_you != null)
		{
			fbq('track', 'Purchase', {
				value: ANALYTICS.total,
				currency: 'USD',
				content_type: ANALYTICS.order_type
			});
		}

		// addroll
		adroll_adv_id = "IPBFW4MX5NAUHO645M5YHJ";
		adroll_pix_id = "UQIIARZWNRHU3DYPL6PXLZ";
		adroll_version = "2.0";

		if (ANALYTICS.dd_thank_you != null)
		{
			adroll_conversion_value = ANALYTICS.total;
			adroll_currency = "USD";
		}

		(function(w, d, e, o, a) {
			w.__adroll_loaded = true;
			w.adroll = w.adroll || [];
			w.adroll.f = ['setProperties', 'identify', 'track'];
			var roundtripUrl = "https://s.adroll.com/j/" + adroll_adv_id + "/roundtrip.js";
			for (a = 0; a < w.adroll.f.length; a++) {
				w.adroll[w.adroll.f[a]] = w.adroll[w.adroll.f[a]] || (function(n) { return function() { w.adroll.push([n, arguments]) } })(w.adroll.f[a])
			}
			e = d.createElement('script');
			o = d.getElementsByTagName('script')[0];
			e.async = 1;
			e.src = roundtripUrl;
			o.parentNode.insertBefore(e, o);
		})(window, document);

		adroll.track("pageView");

		if (ANALYTICS.page == 'item')
		{
			adroll.track("productView", {"products":[{"product_id": itemRecipeID.toString()}]})
		}

		// dailystory
		if (ANALYTICS.vendor.dailystory.enabled)
		{
			(function(d,a,i,l,y,s,t,o,r,y){
				d._dsSettings=i;
				r = a.createElement('script');
				o = a.getElementsByTagName('script')[0];
				r.src= '//us-1.dailystory.com/ds/ds' + i + '.js';
				r.async = true;
				r.id = 'ds-sitescript';
				o.parentNode.insertBefore(r, o);
			})(window,document,'x9q2zysosonhbmxb');
		}

		// nextdoor
		if (ANALYTICS.vendor.nextdoor.enabled)
		{
			(function(win, doc, sdk_url){
				if(win.ndp) return;
				var tr=win.ndp=function(){
					tr.handleRequest? tr.handleRequest.apply(tr, arguments):tr.queue.push(arguments);
				};
				tr.queue = [];
				var s='script';
				var new_script_section=doc.createElement(s);
				new_script_section.async=!0;
				new_script_section.src=sdk_url;
				var insert_pos=doc.getElementsByTagName(s)[0];
				insert_pos.parentNode.insertBefore(new_script_section, insert_pos);
			})(window, document, 'https://ads.nextdoor.com/public/pixel/ndp.js');

			ndp('init', 'c729dacc-8cf3-4306-8042-7aa458ce5648', {})
			ndp('track', 'PAGE_VIEW');

			if (ANALYTICS.dd_thank_you != null)
			{
				ndp('track', 'CONVERSION');
			}
		}

		// wisepop
		if (ANALYTICS.vendor.wisepop.enabled)
		{
			(function(W,i,s,e,P,o,p){W['WisePopsObject']=P;W[P]=W[P]||function(){
				(W[P].q=W[P].q||[]).push(arguments)},W[P].l=1*new Date();o=i.createElement(s),
				p=i.getElementsByTagName(s)[0];o.async=1;o.src=e;p.parentNode.insertBefore(o,p)
			})(window,document,'script','//loader.wisepops.com/get-loader.js?v=1&site=bHSYpBzPX4','wisepops');
		}

		// twitter
		if (ANALYTICS.vendor.twitter.enabled)
		{
			!function (e, t, n, s, u, a) {
				e.twq || (s = e.twq = function () {
					s.exe ? s.exe.apply(s, arguments) : s.queue.push(arguments);
				}, s.version = '1.1', s.queue = [], u = t.createElement(n), u.async = !0, u.src = '//static.ads-twitter.com/uwt.js',
					a = t.getElementsByTagName(n)[0], a.parentNode.insertBefore(u, a))
			}(window, document, 'script');
			// Insert Twitter Pixel ID and Standard Event data below
			twq('init', 'o57c2');
			if (ANALYTICS.dd_thank_you != null)
			{
				twq('track', 'Purchase', {
					//required parameters
					value: ANALYTICS.total,
					currency: 'USD',
					num_items: '1',
				});
			}
			else
			{
				twq('track', 'PageView');
			}
		}

		// Pinterest
		!function(e){if(!window.pintrk){window.pintrk = function () {
			window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
			n=window.pintrk;n.queue=[],n.version="3.0";var
			t=document.createElement("script");t.async=!0,t.src=e;var
			r=document.getElementsByTagName("script")[0];
			r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
		pintrk('load', '2614481882104', {em: ANALYTICS.email_sha256});
		pintrk('page');
		if (ANALYTICS.dd_thank_you != null)
		{
			pintrk('track', 'checkout', {
				value: ANALYTICS.total,
				order_id: ANALYTICS.order_id,
				order_quantity: 1,
				product_id: ANALYTICS.session_type_title_string,
				product_name: ANALYTICS.session_type_title,
				currency: 'USD'
			});
		}

		// iUbenda new
		_iub.csConfiguration = {"cookiePolicyInOtherWindow":true,
			"countryDetection":true,"enableGdpr":false,"enableUspr":true,
			"lang":"en","siteId":1685553,"showBannerForUS":true,
			"usprPurposes":"s,sh,adv","whitelabel":false,
			"cookiePolicyId":48982534,
			"floatingPreferencesButtonDisplay":"anchored-center-right",
			"banner":{"backgroundColor":"#4c4c4c","closeButtonRejects":true,"position":"float-bottom-left","textColor":"white"}};
		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = false;
			ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.iubenda.com/cs/gpp/stub.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.charset = 'UTF-8';
			ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.iubenda.com/cs/iubenda_cs.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();

		// end
		if (ANALYTICS.dd_thank_you)
		{
			$.removeCookie('dd_thank_you', {domain: COOKIE.domain, path: '/'});
		}
	}

})();