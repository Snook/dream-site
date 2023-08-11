<?php

/**
 * A collection of static methods for getting the list of states and abbreviations
 */
class CStatesAndProvinces
{

	static private $_countries = null;
	static private $_states = null;
	static private $_state_abbrev = null;

	public static function GetCountriesArray()
	{
		if (self::$_countries == null)
		{
			self::$_countries = array(
				//					'AF' => 'Afghanistan',
				//					'AL' => 'Albania',
				//					'DZ' => 'Algeria',
				//					'AS' => 'American Samoa',
				//					'AD' => 'Andorra',
				//					'AO' => 'Angola',
				//					'AI' => 'Anguilla',
				//					'AQ' => 'Antarctica',
				//					'AG' => 'Antigua and Barbuda',
				//					'AR' => 'Argentina',
				//					'AM' => 'Armenia',
				//					'AW' => 'Aruba',
				//					'AU' => 'Australia',
				//					'AT' => 'Austria',
				//					'AZ' => 'Azerbaijan',
				//					'BS' => 'Bahamas, The',
				//					'BH' => 'Bahrain',
				//					'BD' => 'Bangladesh',
				//					'BB' => 'Barbados',
				//					'BY' => 'Belarus',
				//					'BE' => 'Belgium',
				//					'BZ' => 'Belize',
				//					'BJ' => 'Benin',
				//					'BM' => 'Bermuda',
				//					'BT' => 'Bhutan',
				//					'BO' => 'Bolivia',
				//					'BA' => 'Bosnia and Herzegovina',
				//					'BW' => 'Botswana',
				//					'BV' => 'Bouvet Island',
				//					'BR' => 'Brazil',
				//					'IO' => 'British Indian Ocean Territory',
				//					'VG' => 'British Virgin Islands',
				//					'BN' => 'Brunei Darussalam',
				//					'BG' => 'Bulgaria',
				//					'BF' => 'Burkina Faso',
				//					'MM' => 'Burma',
				//					'BI' => 'Burundi',
				//					'KH' => 'Cambodia',
				//					'CM' => 'Cameroon',
				'CA' => 'Canada',
				//					'CV' => 'Cape Verde',
				//					'KY' => 'Cayman Islands',
				//					'CF' => 'Central African Republic',
				//					'TD' => 'Chad',
				//					'CL' => 'Chile',
				//					'CN' => 'China',
				//					'CX' => 'Christmas Island',
				//					'CC' => 'Cocos (Keeling) Islands',
				//					'CO' => 'Colombia',
				//					'KM' => 'Comoros',
				//					'CG' => 'Congo, Democratic Republic of the',
				//					'CF' => 'Congo, Republic of the',
				//					'CK' => 'Cook Islands',
				//					'CR' => 'Costa Rica',
				//					'CI' => "Cote d'Ivoire",
				//					'HR' => 'Croatia',
				//					'CU' => 'Cuba',
				//					'CY' => 'Cyprus',
				//					'CZ' => 'Czech Republic',
				//					'DK' => 'Denmark',
				//					'DJ' => 'Djibouti',
				//					'DM' => 'Dominica',
				//					'DO' => 'Dominican Republic',
				//					'TP' => 'East Timor',
				//					'EC' => 'Ecuador',
				//					'EG' => 'Egypt',
				//					'SV' => 'El Salvador',
				//					'GQ' => 'Equatorial Guinea',
				//					'ER' => 'Eritrea',
				//					'EE' => 'Estonia',
				//					'ET' => 'Ethiopia',
				//					'EU' => 'European Union',
				//					'FK' => 'Falkland Islands (Islas Malvinas)',
				//					'FO' => 'Faroe Islands',
				//					'FJ' => 'Fiji',
				//					'FI' => 'Finland',
				//					'FR' => 'France',
				//					'GF' => 'French Guiana',
				//					'PF' => 'French Polynesia',
				//					'TF' => 'French Southern and Antarctic Lands',
				//					'GA' => 'Gabon',
				//					'GM' => 'Gambia',
				//					'GE' => 'Georgia',
				//					'DE' => 'Germany',
				//					'GH' => 'Ghana',
				//					'GI' => 'Gibraltar',
				//					'GR' => 'Greece',
				//					'GL' => 'Greenland',
				//					'GD' => 'Grenada',
				//					'GP' => 'Guadeloupe',
				//					'GU' => 'Guam',
				//					'GT' => 'Guatemala',
				//					'GN' => 'Guinea',
				//					'GW' => 'Guinea-Bissau',
				//					'GY' => 'Guyana',
				//					'HT' => 'Haiti',
				//					'HM' => 'Heard Island and McDonald Islands',
				//					'VA' => 'Holy See (Vatican City)',
				//					'HN' => 'Honduras',
				//					'HK' => 'Hong Kong (SAR)',
				//					'HU' => 'Hungary',
				//					'IS' => 'Iceland',
				//					'IN' => 'India',
				//					'ID' => 'Indonesia',
				//					'IR' => 'Iran',
				//					'IQ' => 'Iraq',
				//					'IE' => 'Ireland',
				//					'IL' => 'Israel',
				//					'IT' => 'Italy',
				//					'JM' => 'Jamaica',
				//					'JP' => 'Japan',
				//					'JO' => 'Jordan',
				//					'KZ' => 'Kazakhstan',
				//					'KE' => 'Kenya',
				//					'KI' => 'Kiribati',
				//					'KP' => 'Korea, North',
				//					'KR' => 'Korea, South',
				//					'KW' => 'Kuwait',
				//					'KG' => 'Kyrgyzstan',
				//					'LA' => 'Laos',
				//					'LV' => 'Latvia',
				//					'LB' => 'Lebanon',
				//					'LS' => 'Lesotho',
				//					'LR' => 'Liberia',
				//					'LY' => 'Libya',
				//					'LI' => 'Liechtenstein',
				//					'LT' => 'Lithuania',
				//					'LU' => 'Luxembourg',
				//					'MO' => 'Macao',
				//					'MK' => 'Macedonia, The Former Yugoslav Republic of',
				//					'MG' => 'Madagascar',
				//					'MW' => 'Malawi',
				//					'MY' => 'Malaysia',
				//					'MV' => 'Maldives',
				//					'ML' => 'Mali',
				//					'MT' => 'Malta',
				//					'MH' => 'Marshall Islands',
				//					'MQ' => 'Martinique',
				//					'MR' => 'Mauritania',
				//					'MU' => 'Mauritius',
				//					'YT' => 'Mayotte',
				'MX' => 'Mexico',
				//					'FM' => 'Micronesia, Federated States of',
				//					'MD' => 'Moldova',
				//					'MC' => 'Monaco',
				//					'MN' => 'Mongolia',
				//					'MS' => 'Montserrat',
				//					'MA' => 'Morocco',
				//					'MZ' => 'Mozambique',
				//					'NA' => 'Namibia',
				//					'NR' => 'Nauru',
				//					'NP' => 'Nepal',
				//					'AN' => 'Netherlands Antilles',
				//					'NL' => 'Netherlands',
				//					'NC' => 'New Caledonia',
				//					'NZ' => 'New Zealand',
				//					'NI' => 'Nicaragua',
				//					'NE' => 'Niger',
				//					'NG' => 'Nigeria',
				//					'NU' => 'Niue',
				//					'NF' => 'Norfolk Island',
				//					'MP' => 'Northern Mariana Islands',
				//					'NO' => 'Norway',
				//					'OM' => 'Oman',
				//					'PK' => 'Pakistan',
				//					'PW' => 'Palau',
				//					'PS' => 'Palestinian Territory, Occupied',
				//					'PA' => 'Panama',
				//					'PG' => 'Papua New Guinea',
				//					'PY' => 'Paraguay',
				//					'PE' => 'Peru',
				//					'PH' => 'Philippines',
				//					'PN' => 'Pitcairn Islands',
				//					'PL' => 'Poland',
				//					'PT' => 'Portugal',
				//					'PR' => 'Puerto Rico',
				//					'QA' => 'Qatar',
				//					'RO' => 'Romania',
				//					'RU' => 'Russian Federation',
				//					'RW' => 'Rwanda',
				//					'RE' => 'R�union',
				//					'SH' => 'Saint Helena',
				//					'KN' => 'Saint Kitts and Nevis',
				//					'LC' => 'Saint Lucia',
				//					'PM' => 'Saint Pierre and Miquelon',
				//					'VC' => 'Saint Vincent and the Grenadines',
				//					'WS' => 'Samoa',
				//					'SM' => 'San Marino',
				//					'SA' => 'Saudi Arabia',
				//					'SN' => 'Senegal',
				//					'SC' => 'Seychelles',
				//					'SL' => 'Sierra Leone',
				//					'SG' => 'Singapore',
				//					'SK' => 'Slovakia',
				//					'SI' => 'Slovenia',
				//					'SB' => 'Solomon Islands',
				//					'SO' => 'Somalia',
				//					'ZA' => 'South Africa',
				//					'GS' => 'South Georgia and the South Sandwich Islands',
				//					'ES' => 'Spain',
				//					'LK' => 'Sri Lanka',
				//					'SD' => 'Sudan',
				//					'SR' => 'Suriname',
				//					'SJ' => 'Svalbard',
				//					'SZ' => 'Swaziland',
				//					'SE' => 'Sweden',
				//					'CH' => 'Switzerland',
				//					'SY' => 'Syria',
				//					'ST' => 'S�o Tom� and Pr�ncipe',
				//					'TW' => 'Taiwan',
				//					'TJ' => 'Tajikistan',
				//					'TZ' => 'Tanzania',
				//					'TH' => 'Thailand',
				//					'TG' => 'Togo',
				//					'TK' => 'Tokelau',
				//					'TO' => 'Tonga',
				//					'TT' => 'Trinidad and Tobago',
				//					'TN' => 'Tunisia',
				//					'TR' => 'Turkey',
				//					'TM' => 'Turkmenistan',
				//					'TC' => 'Turks and Caicos Islands',
				//					'TV' => 'Tuvalu',
				//					'UG' => 'Uganda',
				//					'UA' => 'Ukraine',
				//					'AE' => 'United Arab Emirates',
				//					'GB' => 'United Kingdom',
				'US' => 'United States',
				//					'UM' => 'United States Minor Outlying Islands',
				//					'UY' => 'Uruguay',
				//					'UZ' => 'Uzbekistan',
				//					'VU' => 'Vanuatu',
				//					'VE' => 'Venezuela',
				//					'VN' => 'Vietnam',
				//					'VI' => 'Virgin Islands, U.S.',
				//					'WF' => 'Wallis and Futuna',
				//					'EH' => 'Western Sahara',
				//					'YE' => 'Yemen',
				//					'YU' => 'Yugoslavia',
				//					'ZM' => 'Zambia',
				//					'ZW' => 'Zimbabwe'
			);
		}

		return self::$_countries;
	}

	public static function getCountryName($country_abbrev)
	{

		if ($country_abbrev == 'US')
		{
			return 'USA';
		}

		$countries = self::GetCountriesArray();
		if (array_key_exists($country_abbrev, $countries))
		{
			return $countries[$country_abbrev];
		}

		return null;
	}

	/**
	 * Pass an id in to return the state abbreviation.
	 */
	public static function toAbbrev($id, $country_abbrev = 'US')
	{
		if (!self::$_state_abbrev)
		{
			self::$_state_abbrev = array(
				'1000' => 'AL',
				'1001' => 'AK',
				'1002' => 'AZ',
				'1003' => 'AR',
				'1004' => 'CA',
				'1005' => 'CO',
				'1006' => 'CT',
				'1007' => 'DE',
				'1008' => 'FL',
				'1009' => 'GA',
				'1010' => 'HI',
				'1011' => 'ID',
				'1012' => 'IL',
				'1013' => 'IN',
				'1014' => 'IA',
				'1015' => 'KS',
				'1016' => 'KY',
				'1017' => 'LA',
				'1018' => 'ME',
				'1019' => 'MD',
				'1020' => 'MA',
				'1021' => 'MI',
				'1022' => 'MN',
				'1023' => 'MS',
				'1024' => 'MO',
				'1025' => 'MT',
				'1026' => 'NE',
				'1027' => 'NV',
				'1028' => 'NH',
				'1029' => 'NJ',
				'1030' => 'NM',
				'1031' => 'NY',
				'1032' => 'NC',
				'1033' => 'ND',
				'1034' => 'OH',
				'1035' => 'OK',
				'1036' => 'OR',
				'1037' => 'PA',
				'1038' => 'RI',
				'1039' => 'SC',
				'1040' => 'SD',
				'1041' => 'TN',
				'1042' => 'TX',
				'1043' => 'UT',
				'1044' => 'VT',
				'1045' => 'VA',
				'1046' => 'WA',
				'1047' => 'WV',
				'1048' => 'WI',
				'1049' => 'WY',
				'1050' => 'DC',
				'1053' => 'GU',
				'1054' => 'PR',
				'1055' => 'VI'
			);
		}

		if (array_key_exists($id, self::$_state_abbrev))
		{
			return self::$_state_abbrev[$id];
		}

		return null;
	}

	public static function IsValid($abbrev)
	{
		return array_key_exists($abbrev, self::GetStatesArray());
	}

	/**
	 * Returns full name string given a two letter abbrev
	 */
	public static function GetName($abbrev)
	{

		if ($abbrev && !is_numeric($abbrev))
		{
			// CES 1/27/2017: Added rejection of numeric index. Somehow this is called without first calling IsValid(above)
			// but I haven't found that.  The Qualys scan causes 1000s of traces because by passing through an int.
			$states = self::GetStatesArray();

			return $states[$abbrev];
		}

		return null;
	}

	public static function GetStatesArray()
	{
		if (self::$_states == null)
		{
			self::$_states = array(
				'AL' => 'Alabama',
				'AK' => 'Alaska',
				'AZ' => 'Arizona',
				'AR' => 'Arkansas',
				'CA' => 'California',
				'CO' => 'Colorado',
				'CT' => 'Connecticut',
				'DE' => 'Delaware',
				'FL' => 'Florida',
				'GA' => 'Georgia',
				'HI' => 'Hawaii',
				'ID' => 'Idaho',
				'IL' => 'Illinois',
				'IN' => 'Indiana',
				'IA' => 'Iowa',
				'KS' => 'Kansas',
				'KY' => 'Kentucky',
				'LA' => 'Louisiana',
				'ME' => 'Maine',
				'MD' => 'Maryland',
				'MA' => 'Massachusetts',
				'MI' => 'Michigan',
				'MN' => 'Minnesota',
				'MS' => 'Mississippi',
				'MO' => 'Missouri',
				'MT' => 'Montana',
				'NE' => 'Nebraska',
				'NV' => 'Nevada',
				'NH' => 'New Hampshire',
				'NJ' => 'New Jersey',
				'NM' => 'New Mexico',
				'NY' => 'New York',
				'NC' => 'North Carolina',
				'ND' => 'North Dakota',
				'OH' => 'Ohio',
				'OK' => 'Oklahoma',
				'OR' => 'Oregon',
				'PA' => 'Pennsylvania',
				'RI' => 'Rhode Island',
				'SC' => 'South Carolina',
				'SD' => 'South Dakota',
				'TN' => 'Tennessee',
				'TX' => 'Texas',
				'UT' => 'Utah',
				'VT' => 'Vermont',
				'VA' => 'Virginia',
				'WA' => 'Washington',
				'WV' => 'West Virginia',
				'WI' => 'Wisconsin',
				'WY' => 'Wyoming',
				'DC' => 'District of Columbia',
				'GU' => 'Guam',
				'PR' => 'Puerto Rico',
				'VI' => 'Virgin Islands'
			);
		}

		return self::$_states;
	}

	public static function addProvinces()
	{
		if (self::$_states == null)
		{
			self::GetStatesArray();
		}

		self::$_states = array_merge(self::$_states, array(
				'--' => '--',
				// this will adda a separator in __AddStatesProvinceDropDown(
				'AB' => 'Alberta',
				'BC' => 'British Columbia',
				'MB' => 'Manitoba',
				'NB' => 'New Brunswick',
				'NL' => 'Newfoundland',
				'NS' => 'Nova Scotia',
				'NT' => 'Northwest Territories',
				'NU' => 'Nunavut',
				'ON' => 'Ontario',
				'PE' => 'Prince Edward Island',
				'QC' => 'Quebec',
				'SK' => 'Saskatchewan',
				'YT' => 'Yukon'
			));

		return self::$_states;
	}

	public static function isProvince($testAbbreviation)
	{
		$testArray = array(
			'AB',
			'BC',
			'MB',
			'NB',
			'NL',
			'NS',
			'NT',
			'NU',
			'ON',
			'PE',
			'QC',
			'SK',
			'YT'
		);

		if (in_array($testAbbreviation, $testArray))
		{
			return true;
		}

		return false;
	}
}

?>