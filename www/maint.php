<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Refresh: 300');

define('SITE_WIDE', true);

// Some vars for dynamic info if config file exists
$maint_message = '';

// Check if there is a config file so we can make the maintenance page more informative otherwise the page is static
if (file_exists("../includes/CApp.inc"))
{
	require_once("../includes/CApp.inc");

	// assume site is not down
	$site_disabled = false;
	$checkdb = false;

	// check if site is forced disabled in config
	if (defined('SITE_DISABLED') && SITE_DISABLED == true)
	{
		$site_disabled = true;
	}
	else
	{
		$checkdb = true;

		// check to see if disabled by database schedule or if database is down
		$mysqli = new mysqli(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}

	// if config file message, add it to array
	$maintenance_messages = array();

	if (defined('MAINT_PAGE_MESSAGE'))
	{
		$maintenance_messages[]['message'] = MAINT_PAGE_MESSAGE;
	}

	if ($checkdb && empty($mysqli->connect_errno))
	{
		$time_now = date('Y-m-d H:i:s', time());

		$result = $mysqli->query("SELECT *
			FROM site_message AS dmm
			WHERE dmm.message_start <= '" . $time_now . "'
			AND dmm.message_end >= '" . $time_now . "'
			AND dmm.is_active = '1'
			AND dmm.is_deleted = '0'
			AND dmm.audience != 'STORE'
			AND dmm.message_type = 'SITE_MESSAGE'
			ORDER BY dmm.message_start ASC");

		if (!empty($result->num_rows))
		{
			while ($row = $result->fetch_assoc())
			{
				if ($row['audience'] == 'SITE_WIDE' || $row['audience'] == 'CUSTOMER')
				{
					$maintenance_messages[] = $row;
				}

				if (!empty($row['disable_site_start']) && $row['disable_site_start'] != '1970-01-01 00:00:01' && $time_now >= $row['disable_site_start'])
				{
					$site_disabled = true;
				}
			}

			$result->close();
		}
	}
	else
	{
		// database connection error, site disabled
		$site_disabled = true;
	}

	// If the site is not disabled, send them to the home page
	if ($site_disabled == false)
	{
		header('Location: ' . WEB_BASE);
	}

	// if the IP is excluded, send them home
	if (!empty($g_IP_ExclusionList) && in_array($_SERVER['REMOTE_ADDR'], $g_IP_ExclusionList))
	{
		header('Location: ' . WEB_BASE);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>503 Down For Maintenance - Dream Dinners</title>
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="x-apple-disable-message-reformatting">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style type="text/css">
		body, table, td {
			font-family: Helvetica, Arial, sans-serif !important
			}

		.ExternalClass {
			width: 100%
			}

		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
			line-height: 150%
			}

		a {
			text-decoration: none
			}

		* {
			color: inherit
			}

		a[x-apple-data-detectors], u + #body a, #MessageViewBody a {
			color: inherit;
			text-decoration: none;
			font-size: inherit;
			font-family: inherit;
			font-weight: inherit;
			line-height: inherit
			}

		img {
			-ms-interpolation-mode: bicubic
			}

		table:not([class^=s-]) {
			font-family: Helvetica, Arial, sans-serif;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
			border-spacing: 0px;
			border-collapse: collapse
			}

		table:not([class^=s-]) td {
			border-spacing: 0px;
			border-collapse: collapse
			}

		@media screen and (max-width: 600px) {
			.w-full, .w-full > tbody > tr > td {
				width: 100% !important
				}

			*[class*=s-lg-] > tbody > tr > td {
				font-size: 0 !important;
				line-height: 0 !important;
				height: 0 !important
				}

			.s-2 > tbody > tr > td {
				font-size: 8px !important;
				line-height: 8px !important;
				height: 8px !important
				}

			.s-3 > tbody > tr > td {
				font-size: 12px !important;
				line-height: 12px !important;
				height: 12px !important
				}

			.s-5 > tbody > tr > td {
				font-size: 20px !important;
				line-height: 20px !important;
				height: 20px !important
				}

			.s-10 > tbody > tr > td {
				font-size: 40px !important;
				line-height: 40px !important;
				height: 40px !important
				}
			}
	</style>
	<script type="text/javascript">
		//<![CDATA[
		var _gaq = _gaq || [];
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

		(function () {
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();
		//]]>
	</script>
</head>
<body class="bg-light" style="outline: 0; width: 100%; min-width: 100%; height: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; font-family: Helvetica, Arial, sans-serif; line-height: 24px; font-weight: normal; font-size: 16px; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; color: #000000; margin: 0; padding: 0; border-width: 0;" bgcolor="#f7fafc">
<table class="bg-light body" valign="top" role="presentation" border="0" cellpadding="0" cellspacing="0" style="outline: 0; width: 100%; min-width: 100%; height: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; font-family: Helvetica, Arial, sans-serif; line-height: 24px; font-weight: normal; font-size: 16px; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; color: #000000; margin: 0; padding: 0; border-width: 0;" bgcolor="#f7fafc">
	<tbody>
	<tr>
		<td valign="top" style="line-height: 24px; font-size: 16px; margin: 0;" align="left" bgcolor="#f7fafc">
			<table class="container" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
				<tbody>
				<tr>
					<td align="center" style="line-height: 24px; font-size: 16px; margin: 0; padding: 0 16px;">
						<table align="center" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
							<tbody>
							<tr>
								<td style="line-height: 24px; font-size: 16px; margin: 0;" align="left">
									<table class="s-10 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
										<tbody>
										<tr>
											<td style="line-height: 40px; font-size: 40px; width: 100%; height: 40px; margin: 0;" align="left" width="100%" height="40">
												&#160;
											</td>
										</tr>
										</tbody>
									</table>
									<table class="card" role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-radius: 6px; border-collapse: separate !important; width: 100%; overflow: hidden; border: 1px solid #e2e8f0;" bgcolor="#ffffff">
										<tbody>
										<tr>
											<td style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left" bgcolor="#ffffff">
												<table class="card-body" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
													<tbody>
													<tr>
														<td style="line-height: 24px; font-size: 16px; width: 100%; margin: 0; padding: 20px;" align="left">
															<h1 class="h3" style="padding-top: 0; padding-bottom: 0; font-weight: 500; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
																<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1500.000000 276.000000" preserveAspectRatio="xMidYMid meet">
																	<g transform="translate(0.000000,276.000000) scale(0.100000,-0.100000)" fill="#5E6670" stroke="none">
																		<path d="M1628 1848 c16 -16 17 -1059 1 -1075 -8 -8 36 -13 157 -18 187 -8 313 -2 400 21 234 61 374 282 374 591 0 255 -120 428 -331 478 -84 19 -620 22 -601 3z m508 -99 c107 -40 179 -137 205 -276 15 -76 5 -272 -16 -343 -38 -123 -101 -207 -190 -250 -86 -42 -244 -49 -280 -13 -13 13 -15 77 -13 456 l3 442 40 7 c63 10 194 -2 251 -23z" />
																		<path d="M7139 1846 c16 -19 16 -1067 0 -1077 -17 -10 312 -23 412 -15 162 12 257 49 353 137 66 60 124 170 152 284 24 98 24 281 0 360 -51 169 -173 284 -336 315 -29 5 -174 10 -323 10 -227 0 -268 -2 -258 -14z m430 -76 c124 -21 218 -94 262 -206 31 -81 39 -288 14 -399 -47 -212 -166 -316 -366 -319 -60 -1 -91 3 -102 13 -15 12 -17 57 -17 462 l0 448 43 4 c82 7 103 7 166 -3z" />
																		<path d="M13063 1690 c-134 -28 -222 -132 -211 -250 8 -77 42 -119 165 -201 119 -79 168 -120 189 -161 32 -61 4 -155 -58 -199 -43 -31 -133 -38 -192 -15 -24 9 -61 33 -81 53 l-38 37 5 -72 c3 -39 11 -78 18 -86 24 -30 122 -58 200 -58 186 1 317 115 328 287 4 58 0 80 -18 123 -27 63 -98 130 -197 190 -81 48 -143 111 -143 146 0 14 12 38 26 55 59 70 208 64 290 -11 l22 -19 -6 68 c-2 37 -10 71 -16 76 -46 31 -209 53 -283 37z" />
																		<path d="M2849 1656 c8 -10 11 -134 9 -457 l-3 -444 98 0 c90 0 112 5 87 20 -6 4 -10 80 -10 195 l0 189 33 5 c17 3 53 8 79 11 l47 6 70 -138 c118 -233 206 -315 334 -315 45 0 75 6 105 21 50 26 54 38 10 33 -38 -4 -98 32 -143 86 -46 55 -120 185 -165 290 l-42 100 45 42 c63 57 97 128 97 200 0 71 -27 116 -90 148 -43 21 -54 22 -308 22 -225 0 -263 -2 -253 -14z m433 -103 c18 -10 39 -31 46 -46 44 -91 -107 -237 -245 -237 l-53 0 0 149 c0 175 -11 161 132 157 63 -3 96 -9 120 -23z" />
																		<path d="M4948 1658 c7 -7 12 -20 12 -29 0 -16 -141 -551 -202 -769 -12 -41 -28 -84 -37 -94 -15 -18 -15 -19 2 -13 10 4 47 7 81 7 60 0 63 1 60 23 -2 12 16 94 39 182 l42 160 30 -3 c47 -6 124 -52 161 -97 48 -59 96 -168 98 -220 l1 -44 108 -3 c59 -2 107 -2 107 0 0 2 -6 9 -13 16 -7 7 -66 212 -131 454 l-119 442 -126 0 c-97 0 -122 -3 -113 -12z m154 -336 c26 -97 45 -178 44 -180 -2 -2 -19 11 -37 28 -19 17 -56 39 -84 49 -27 11 -49 23 -49 28 9 50 68 262 73 258 3 -4 27 -86 53 -183z" />
																		<path d="M5790 1650 c8 -15 1 -110 -25 -367 -45 -442 -53 -503 -64 -517 -7 -8 20 -11 99 -11 82 0 106 3 99 12 -7 8 -2 116 15 317 14 168 26 315 26 326 0 11 3 20 8 20 4 0 47 -64 96 -141 49 -78 91 -137 95 -132 3 5 44 69 90 141 46 73 87 132 92 132 5 0 9 -21 9 -47 0 -27 11 -174 25 -328 14 -159 20 -284 15 -290 -6 -7 25 -10 96 -10 57 0 104 1 104 3 0 1 -4 10 -10 20 -5 9 -25 175 -45 367 -51 508 -50 492 -35 510 11 13 1 15 -86 15 l-99 -1 -79 -139 c-43 -77 -80 -140 -82 -140 -6 0 -147 242 -152 262 -4 16 -16 18 -104 18 -95 0 -98 -1 -88 -20z" />
																		<path d="M8390 1651 c13 -26 14 -873 1 -886 -6 -7 26 -10 87 -10 l97 0 -3 444 c-2 323 1 447 9 457 10 12 -5 14 -95 14 -102 0 -106 -1 -96 -19z" />
																		<path d="M8948 1658 c15 -15 17 -879 3 -893 -6 -6 17 -10 64 -10 47 0 71 3 65 10 -6 5 -10 151 -10 359 l0 350 31 -30 c126 -117 275 -403 329 -631 l12 -53 74 0 74 0 0 443 c0 331 3 446 12 455 9 9 -4 12 -61 12 -66 0 -71 -1 -57 -16 14 -13 16 -51 16 -260 0 -225 -7 -287 -25 -217 -26 104 -145 298 -256 417 l-71 76 -106 0 c-82 0 -103 -3 -94 -12z" />
																		<path d="M9969 1656 c8 -10 11 -134 9 -457 l-3 -444 67 0 c42 0 63 3 57 10 -5 5 -9 151 -9 359 l0 350 41 -39 c129 -126 262 -379 316 -604 l17 -71 73 0 73 0 0 443 c0 331 3 446 12 455 9 9 -3 12 -56 12 -57 0 -67 -2 -57 -14 8 -9 10 -87 8 -262 -1 -163 -6 -240 -11 -224 -5 14 -33 72 -62 130 -57 115 -115 199 -208 303 l-60 67 -109 0 c-92 0 -108 -2 -98 -14z" />
																		<path d="M10984 1654 c14 -14 16 -68 16 -444 0 -275 -4 -431 -10 -435 -5 -3 -10 -9 -10 -13 0 -4 116 -5 257 -2 l256 5 29 69 c24 60 25 67 8 51 -37 -34 -309 -44 -347 -13 -9 8 -13 50 -13 148 0 182 1 184 135 188 l96 4 25 40 c40 62 41 70 9 58 -14 -5 -68 -10 -118 -10 -59 0 -102 -5 -119 -14 -15 -8 -28 -13 -30 -11 -2 2 -2 69 0 150 l3 146 183 -3 184 -3 -19 48 c-11 26 -19 50 -19 52 0 3 -120 5 -266 5 -241 0 -264 -1 -250 -16z" />
																		<path d="M11829 1656 c8 -10 11 -134 9 -457 l-3 -444 98 0 c83 0 96 2 90 15 -5 8 -9 99 -11 202 l-3 186 28 5 c15 3 53 8 83 11 l55 6 67 -138 c112 -230 201 -314 333 -314 44 -1 76 6 101 19 42 21 57 45 25 36 -85 -22 -205 116 -313 358 l-49 111 51 54 c70 74 93 129 88 210 -3 56 -8 68 -37 98 -50 51 -80 56 -366 56 -219 0 -256 -2 -246 -14z m429 -100 c18 -9 39 -28 48 -41 57 -87 -100 -245 -242 -245 l-54 0 0 149 0 150 28 4 c55 10 188 0 220 -17z" />
																		<path d="M3919 1646 c8 -10 11 -135 9 -455 l-3 -441 251 0 251 0 21 53 c11 28 24 61 28 72 l7 20 -22 -20 c-21 -18 -40 -20 -178 -23 -109 -2 -158 0 -169 9 -11 9 -14 42 -14 149 0 182 1 183 136 190 l98 5 34 54 c23 36 29 52 18 48 -9 -4 -64 -9 -123 -13 -60 -3 -120 -12 -135 -19 l-28 -13 0 149 0 150 185 -3 c207 -3 192 -10 159 69 l-14 33 -261 0 c-221 0 -260 -2 -250 -14z" />
																		<path d="M13536 879 c-14 -17 -26 -41 -26 -54 0 -13 12 -37 26 -54 52 -63 146 -28 146 54 0 82 -94 117 -146 54z m114 -1 c42 -45 13 -128 -44 -128 -39 0 -57 11 -71 44 -32 76 59 144 115 84z" />
																		<path d="M13571 823 c1 -26 4 -38 6 -25 2 12 10 22 17 22 7 0 18 -10 23 -22 9 -20 11 -16 9 22 -1 41 -3 45 -28 48 -27 3 -28 2 -27 -45z m49 22 c0 -8 -9 -15 -20 -15 -11 0 -20 7 -20 15 0 8 9 15 20 15 11 0 20 -7 20 -15z" />
																	</g>
																</svg>
															</h1>
															<table class="s-2 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
																<tbody>
																<tr>
																	<td style="line-height: 8px; font-size: 8px; width: 100%; height: 8px; margin: 0;" align="left" width="100%" height="8">
																		&#160;
																	</td>
																</tr>
																</tbody>
															</table>
															<h5 class="text-teal-700" style="color: #13795b; padding-top: 0; padding-bottom: 0; font-weight: 500; vertical-align: baseline; font-size: 20px; line-height: 24px; margin: 0;" align="center">Our website is temporarily down for maintenance!</h5>
															<table class="s-5 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
																<tbody>
																<tr>
																	<td style="line-height: 20px; font-size: 20px; width: 100%; height: 20px; margin: 0;" align="left" width="100%" height="20">
																		&#160;
																	</td>
																</tr>
																</tbody>
															</table>
															<table class="hr" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
																<tbody>
																<tr>
																	<td style="line-height: 24px; font-size: 16px; border-top-width: 1px; border-top-color: #e2e8f0; border-top-style: solid; height: 1px; width: 100%; margin: 0;" align="left">
																	</td>
																</tr>
																</tbody>
															</table>
															<table class="s-5 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
																<tbody>
																<tr>
																	<td style="line-height: 20px; font-size: 20px; width: 100%; height: 20px; margin: 0;" align="left" width="100%" height="20">
																		&#160;
																	</td>
																</tr>
																</tbody>
															</table>
															<div class="space-y-3">
																<?php foreach ($maintenance_messages as $message) { ?>
																	<p class="text-gray-700" style="line-height: 24px; font-size: 16px; color: #4a5568; width: 100%; margin: 0;" align="left"><?php echo $message["message"]; ?></p>
																<?php } ?>
																<p class="text-gray-700" style="line-height: 24px; font-size: 16px; color: #4a5568; width: 100%; margin: 0;" align="left">We apologize for any inconvenience!</p>
																<table class="s-3 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
																	<tbody>
																	<tr>
																		<td style="line-height: 12px; font-size: 12px; width: 100%; height: 12px; margin: 0;" align="left" width="100%" height="12">
																			&#160;
																		</td>
																	</tr>
																	</tbody>
																</table>
																<p class="text-gray-700" style="line-height: 24px; font-size: 16px; color: #4a5568; width: 100%; margin: 0;" align="left">
																	In the meantime, please come visit us on <a href="http://www.facebook.com/dreamdinners" target="_blank" style="color: #0d6efd;">Facebook!</a>
																</p>
															</div>
														</td>
													</tr>
													</tbody>
												</table>
											</td>
										</tr>
										</tbody>
									</table>
									<table class="s-10 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
										<tbody>
										<tr>
											<td style="line-height: 40px; font-size: 40px; width: 100%; height: 40px; margin: 0;" align="left" width="100%" height="40">
												&#160;
											</td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>
</body>
</html>