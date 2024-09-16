var _gaq = _gaq || []; // google
var _iub = _iub || []; // iubenda
window.ytagQ = window.ytagQ || []; //Yext

(function () {

	if (ANALYTICS.analytics)
	{
		// accessibe.com
		var s = document.createElement('script');
		var h = document.querySelector('head') || document.body;
		s.src = 'https://acsbapp.com/apps/app/dist/js/app.js';
		s.async = true;
		s.onload = function () {
			acsbJS.init({
				statementLink: '',
				footerHtml: '',
				hideMobile: false,
				hideTrigger: false,
				disableBgProcess: false,
				language: 'en',
				position: 'left',
				leadColor: '#767b21',
				triggerColor: '#767b21',
				triggerRadius: '50%',
				triggerPositionX: 'left',
				triggerPositionY: 'bottom',
				triggerIcon: 'people',
				triggerSize: 'medium',
				triggerOffsetX: 20,
				triggerOffsetY: 20,
				mobile: {
					triggerSize: 'small',
					triggerPositionX: 'left',
					triggerPositionY: 'center',
					triggerOffsetX: 10,
					triggerOffsetY: 0,
					triggerRadius: '50%'
				}
			});
		};
		h.appendChild(s);

		// GA3
		_gaq.push([
			'_setAccount',
			'UA-425666-1'
		]);
		_gaq.push(['_trackPageview']);
		_gaq.push([
			'_setDomainName',
			'dreamdinners.com'
		]);
		_gaq.push([
			'_addIgnoredOrganic',
			'www.dreamdinners.com'
		]);
		_gaq.push([
			'_addIgnoredOrganic',
			'dreamdinners.com'
		]);
		_gaq.push([
			'_addIgnoredRef',
			'dreamdinners.com'
		]);
		_gaq.push([
			'_addIgnoredRef',
			'www.dreamdinners.com'
		]);
		_gaq.push([
			'_setCampaignCookieTimeout',
			2419200000
		]);

		if (ANALYTICS.dd_thank_you !== null && ANALYTICS.page == 'order_details')
		{
			// GA3
			_gaq.push([
				'_addTrans',
				ANALYTICS.order_id,
				'',
				ANALYTICS.order_total,
				'',
				'',
				'',
				'',
				''
			]);
			_gaq.push([
				'_addItem',
				ANALYTICS.order_id,
				'1',
				ANALYTICS.order_type_title,
				ANALYTICS.menu_id,
				ANALYTICS.order_total,
				'1'
			]);
			_gaq.push(['_trackTrans']);

			// GA4
			$.each(ANALYTICS.vendor.google.gtag.purchase, function (id, purchase) {
				gtag('event', 'purchase', purchase);
			});

			//Yext Conversion Tracking
			function ytag()
			{
				window.ytagQ.push(arguments);
			}

			ytag('conversion', {
				'cid': 'fff98dde-1e06-4be9-a696-629bdf698584',
				'cv': ANALYTICS.order_total
			});
		}
		else if (ANALYTICS.dd_thank_you !== null && ANALYTICS.page == 'order_details_gift_card')
		{
			// GA3
			_gaq.push([
				'_addTrans',
				ANALYTICS.gift_card_orders,
				'',
				ANALYTICS.gift_card_total,
				'',
				'',
				'',
				'',
				''
			]);

			if (ANALYTICS.gift_card_orders.length !== 0)
			{
				$.each(ANALYTICS.gift_card_array, function (id, item) {
					_gaq.push([
						'_addItem',
						item.id,
						'GC_' + item.media_type,
						item.gc_media_type + ' Order',
						'design_type_id-' + item.design_type_id,
						item.gc_amount,
						'1'
					]);
				});
			}

			_gaq.push(['_trackTrans']);

			// GA4
			$.each(ANALYTICS.vendor.google.gtag.purchase, function (id, purchase) {
				gtag('event', 'purchase', purchase);
			});
		}

		// adwords
		if (ANALYTICS.dd_thank_you != null)
		{
			gtag('event', 'conversion', {'send_to': 'AW-963692398/b9fdCO301I0CEO6Ow8sD'});
		}

		// facebook
		!function (f, b, e, v, n, t, s) {
			if (f.fbq)
			{
				return;
			}
			n = f.fbq = function () {
				n.callMethod ?
					n.callMethod.apply(n, arguments) : n.queue.push(arguments)
			};
			if (!f._fbq)
			{
				f._fbq = n;
			}
			n.push = n;
			n.loaded = !0;
			n.version = '2.0';
			n.queue = [];
			t = b.createElement(e);
			t.async = !0;
			t.src = v;
			s = b.getElementsByTagName(e)[0];
			s.parentNode.insertBefore(t, s)
		}(window,
			document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
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

		// dailystory
		if (ANALYTICS.vendor.dailystory.enabled)
		{
			(function (d, a, i, l, y, s, t, o, r, y) {
				d._dsSettings = i;
				r = a.createElement('script');
				o = a.getElementsByTagName('script')[0];
				r.src = '//us-1.dailystory.com/ds/ds' + i + '.js';
				r.async = true;
				r.id = 'ds-sitescript';
				o.parentNode.insertBefore(r, o);
			})(window, document, 'x9q2zysosonhbmxb');

			if (ANALYTICS.dd_thank_you !== null && ANALYTICS.page == 'order_details')
			{
				if (ANALYTICS.store_DS_tenant !== null)
				{
					(function (d, a, i, l, y, s, t, o, r, y) {
						d._dsSettings = i;
						r = a.createElement('script');
						o = a.getElementsByTagName('script')[0];
						r.src = '//us-1.dailystory.com/ds/ds' + i + '.js';
						r.async = true;
						r.id = 'ds-sitescript';
						o.parentNode.insertBefore(r, o);
					})(window, document, ANALYTICS.store_DS_tenant);
				}

				window.addEventListener('ds_ready', function () {

					let customerInfo = {
						email: ANALYTICS.email,
						phone: ANALYTICS.telephone_1
					};

					window.Ds && window.Ds.conversion('13a01a1a-4135-4550-9c39-3a7244f15e66', ANALYTICS.total, customerInfo);

				});
			}
		}

		// iUbenda new
		_iub.csConfiguration = {
			"cookiePolicyInOtherWindow": true,
			"countryDetection": true,
			"enableGdpr": false,
			"enableUspr": true,
			"lang": "en",
			"siteId": 1685553,
			"showBannerForUS": true,
			"usprPurposes": "s,sh,adv",
			"whitelabel": false,
			"cookiePolicyId": 48982534,
			"floatingPreferencesButtonDisplay": "anchored-center-right",
			"banner": {
				"backgroundColor": "#4c4c4c",
				"closeButtonRejects": true,
				"position": "float-bottom-left",
				"textColor": "white"
			}
		};
		(function () {
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = false;
			ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.iubenda.com/cs/gpp/stub.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();

		(function () {
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = true;
			ga.charset = 'UTF-8';
			ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.iubenda.com/cs/iubenda_cs.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();

		// end
		if (ANALYTICS.dd_thank_you)
		{
			$.removeCookie('dd_thank_you', {
				domain: COOKIE.domain,
				path: '/'
			});
		}
	}

})();