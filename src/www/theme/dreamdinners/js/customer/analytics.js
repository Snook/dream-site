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