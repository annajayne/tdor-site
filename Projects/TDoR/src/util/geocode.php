<?php
    /**
     * Geocoding functions.
     *
     */

    require_once('util/utils.php');              // For get_config()



    function get_country_codes()
    {
        $country_codes = array(
                          'Afghanistan' => 'AF',
                            'Aland Islands' => 'AX',
                            'Albania' => 'AL',
                            'Algeria' => 'DZ',
                            'American Samoa' => 'AS',
                            'Andorra' => 'AD',
                            'Angola' => 'AO',
                            'Anguilla' => 'AI',
                            'Antarctica' => 'AQ',
                            'Antigua And Barbuda' => 'AG',
                            'Argentina' => 'AR',
                            'Armenia' => 'AM',
                            'Aruba' => 'AW',
                            'Australia' => 'AU',
                            'Austria' => 'AT',
                            'Azerbaijan' => 'AZ',
                            'Bahamas' => 'BS',
                            'Bahrain' => 'BH',
                            'Bangladesh' => 'BD',
                            'Barbados' => 'BB',
                            'Belarus' => 'BY',
                            'Belgium' => 'BE',
                            'Belize' => 'BZ',
                            'Benin' => 'BJ',
                            'Bermuda' => 'BM',
                            'Bhutan' => 'BT',
                            'Bolivia' => 'BO',
                            'Bosnia And Herzegovina' =>'BA' ,
                            'Botswana' => 'BW',
                            'Bouvet Island' => 'BV',
                            'Brazil' => 'BR',
                            'British Indian Ocean Territory' => 'IO',
                            'Brunei Darussalam' => 'BN',
                            'Bulgaria' => 'BG',
                            'Burkina Faso' => 'BF',
                            'Burundi' => 'BI',
                            'Cambodia' => 'KH',
                            'Cameroon' => 'CM',
                            'Canada' => 'CA',
                            'Cape Verde' => 'CV',
                            'Cayman Islands' => 'KY',
                            'Central African Republic' => 'CF',
                            'Chad' => 'TD',
                            'Chile' => 'CL',
                            'China' => 'CN',
                            'Christmas Island' => 'CX',
                            'Cocos (Keeling) Islands' => 'CC',
                            'Colombia' => 'CO',
                            'Comoros' => 'KM',
                            'Congo' => 'CG',
                            'Congo, Democratic Republic' => 'CD',
                            'Cook Islands' => 'CK',
                            'Costa Rica' => 'CR',
                            'Côte d\'Ivoire' => 'CI',
                            'Croatia' => 'HR',
                            'Cuba' => 'CU',
                            'Cyprus' => 'CY',
                            'Czech Republic' => 'CZ',
                            'Denmark' => 'DK',
                            'Djibouti' => 'DJ',
                            'Dominica' => 'DM',
                            'Dominican Republic' => 'DO',
                            'Ecuador' => 'EC',
                            'Egypt' => 'EG',
                            'El Salvador' => 'SV',
                            'Equatorial Guinea' => 'GQ',
                            'Eritrea' => 'ER',
                            'Estonia' => 'EE',
                            'Ethiopia' => 'ET',
                            'Falkland Islands (Malvinas)' => 'FK',
                            'Faroe Islands' => 'FO',
                            'Fiji' => 'FJ',
                            'Finland' => 'FI',
                            'France' => 'FR',
                            'French Guiana' => 'GF',
                            'French Polynesia' => 'PF',
                            'French Southern Territories' => 'TF',
                            'Gabon' => 'GA',
                            'Gambia' => 'GM',
                            'Georgia' => 'GE',
                            'Germany' => 'DE',
                            'Ghana' => 'GH',
                            'Gibraltar' => 'GI',
                            'Greece' => 'GR',
                            'Greenland' =>'GL',
                            'Grenada' => 'GD',
                            'Guadeloupe' => 'GP',
                            'Guam' => 'GU',
                            'Guatemala' => 'GT',
                            'Guernsey' => 'GG',
                            'Guinea' => 'GN',
                            'Guinea-Bissau' => 'GW',
                            'Guyana' => 'GY',
                            'Haiti' => 'HT',
                            'Heard Island & Mcdonald Islands' => 'HM',
                            'Holy See (Vatican City State)' => 'VA',
                            'Honduras' => 'HN',
                            'Hong Kong' => 'HK',
                            'Hungary' => 'HU',
                            'Iceland' => 'IS',
                            'India' => 'IN',
                            'Indonesia' => 'ID',
                            'Iran' => 'IR',
                            'Iraq' => 'IQ',
                            'Ireland' => 'IE',
                            'Isle Of Man' => 'IM',
                            'Israel' => 'IL',
                            'Italy' => 'IT',
                            'Jamaica' => 'JM',
                            'Japan' => 'JP',
                            'Jersey' => 'JE',
                            'Jordan' => 'JO',
                            'Kazakhstan' => 'KZ',
                            'Kenya' => 'KE',
                            'Kiribati' => 'KI',
                            'Korea' => 'KR',
                            'Kuwait' => 'KW',
                            'Kyrgyzstan' => 'KG',
                            'Lao People\'s Democratic Republic' => 'LA',
                            'Laos' => 'LA',
                            'Latvia' => 'LV',
                            'Lebanon' => 'LB',
                            'Lesotho' => 'LS',
                            'Liberia' => 'LR',
                            'Libyan Arab Jamahiriya' => 'LY',
                            'Libya' => 'LY',
                            'Liechtenstein' => 'LI',
                            'Lithuania' => 'LT',
                            'Luxembourg' => 'LU',
                            'Macao' => 'MO',
                            'Macedonia' => 'MK',
                            'Madagascar' => 'MG',
                            'Malawi' => 'MW',
                            'Malaysia' => 'MY',
                            'Maldives' => 'MV',
                            'Mali' => 'ML',
                            'Malta' => 'MT',
                            'Marshall Islands' => 'MH',
                            'Martinique' => 'MQ',
                            'Mauritania' => 'MR',
                            'Mauritius' => 'MU',
                            'Mayotte' => 'YT',
                            'Mexico' => 'MX',
                            'Micronesia, Federated States Of' => 'FM',
                            'Moldova' => 'MD',
                            'Monaco' => 'MC',
                            'Mongolia' => 'MN',
                            'Montenegro' => 'ME',
                            'Montserrat' => 'MS',
                            'Morocco' => 'MA',
                            'Mozambique' => 'MZ',
                            'Myanmar' => 'MM',
                            'Namibia' => 'NA',
                            'Nauru' => 'NR',
                            'Nepal' => 'NP',
                            'Netherlands' => 'NL',
                            'Netherlands Antilles' => 'AN',
                            'New Caledonia' => 'NC',
                            'New Zealand' => 'NZ',
                            'Nicaragua' => 'NI',
                            'Niger' => 'NE',
                            'Nigeria' => 'NG',
                            'Niue' => 'NU',
                            'Norfolk Island' => 'NF',
                            'Northern Mariana Islands' => 'MP',
                            'Norway' => 'NO',
                            'Oman' => 'OM',
                            'Pakistan' => 'PK',
                            'Palau' => 'PW',
                            'Palestinian Territory, Occupied' => 'PS',
                            'Panama' => 'PA',
                            'Papua New Guinea' => 'PG',
                            'Paraguay' => 'PY',
                            'Peru' => 'PE',
                            'Philippines' => 'PH',
                            'Pitcairn' => 'PN',
                            'Poland' => 'PL',
                            'Portugal' => 'PT',
                            'Puerto Rico' => 'PR',
                            'Qatar' => 'QA',
                            'Reunion' => 'RE',
                            'Romania' => 'RO',
                            'Russia' => 'RU',
                            'Russian Federation' => 'RU',
                            'Rwanda' => 'RW',
                            'Saint Barthelemy' => 'BL',
                            'Saint Helena' => 'SH',
                            'Saint Kitts And Nevis' => 'KN',
                            'Saint Lucia' => 'LC',
                            'Saint Martin' => 'MF',
                            'Saint Pierre And Miquelon' => 'PM',
                            'Saint Vincent And Grenadines' => 'VC',
                            'Samoa' => 'WS',
                            'San Marino' => 'SM',
                            'Sao Tome And Principe' => 'ST',
                            'Saudi Arabia' => 'SA',
                            'Senegal' => 'SN',
                            'Serbia' => 'RS',
                            'Seychelles' => 'SC',
                            'Sierra Leone' => 'SL',
                            'Singapore' => 'SG',
                            'Slovakia' => 'SK',
                            'Slovenia' => 'SI',
                            'Solomon Islands' => 'SB',
                            'Somalia' => 'SO',
                            'South Africa' => 'ZA',
                            'South Georgia And Sandwich Isl.' => 'GS',
                            'South Korea' => 'KR',
                            'Spain' => 'ES',
                            'Sri Lanka' => 'LK',
                            'Sudan' => 'SD',
                            'Suriname' => 'SR',
                            'Svalbard And Jan Mayen' => 'SJ',
                            'Swaziland' => 'SZ',
                            'Sweden' => 'SE',
                            'Switzerland' => 'CH',
                            'Syria' => 'SY',
                            'Taiwan' => 'TW',
                            'Tajikistan' => 'TJ',
                            'Tanzania' => 'TZ',
                            'Thailand' => 'TH',
                            'Timor-Leste' => 'TL',
                            'Togo' => 'TG',
                            'Tokelau' => 'TK',
                            'Tonga' => 'TO',
                            'Trinidad And Tobago' => 'TT',
                            'Tunisia' => 'TN',
                            'Turkey' => 'TR',
                            'Turkmenistan' => 'TM',
                            'Turks And Caicos Islands' => 'TC',
                            'Tuvalu' => 'TV',
                            'Uganda' => 'UG',
                            'Ukraine' => 'UA',
                            'United Arab Emirates' => 'AE',
                            'United Kingdom' => 'GB',
                            'United States' => 'US',
                            'USA' => 'US',
                            'United States Outlying Islands' => 'UM',
                            'Uruguay' => 'UY',
                            'Uzbekistan' => 'UZ',
                            'Vanuatu' => 'VU',
                            'Venezuela' => 'VE',
                            'Vietnam' => 'VN',
                            'Virgin Islands, British' => 'VG',
                            'Virgin Islands, U.S.' => 'VI',
                            'Wallis And Futuna' => 'WF',
                            'Western Sahara' => 'EH',
                            'Yemen' => 'YE',
                            'Zambia' => 'ZM',
                            'Zimbabwe' => 'ZW');

        return $country_codes;
    }


    function get_country_code($country)
    {
         static $country_codes = array();

         if (empty($country_codes) )
         {
             $country_codes = get_country_codes();
         }
         return $country_codes[$country];
    }


    function geocode($places)
    {
        $site_config    = get_config();

        $items          = count($places);

        $key            = $site_config['MapQuest']['api_key'];

        $url            = "http://www.mapquestapi.com/geocoding/v1/batch?key=$key";

        foreach ($places as $place)
        {
            $location   = $place['location'];
            $country    = $place['country'];

            $text       = !empty($location) ? ($location.', '.$country) : $country;

            $url       .= '&location='.urlencode($text);
        }

        // Query MapQuest using curl
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        curl_close($ch);

        $geo =  json_decode($output, TRUE);

        $result = array();

        if (!empty($geo['results']) )
        {
            for ($n = 0; $n < $items; ++$n)
            {
                $keys                 = array_keys($places);

                $place                = $places[$keys[$n] ];

                $place_country_code   = get_country_code($place['country']);

                if ($place_country_code == 'PR')
                {
                    // Special case for Puerto Rico
                    $place_country_code = 'US';
                }

                $locations            = $geo['results'][$n]['locations'];

                foreach ($locations as $location)
                {
                    if ( ($location['adminArea1Type'] == 'Country') && ($location['adminArea1Type'] == 'Country') && ($location['adminArea1'] == $place_country_code) )
                    {
                        $coords = $location['latLng'];

                        $place['lat'] = $coords['lat'];
                        $place['lon'] = $coords['lng'];

                        $places[$keys[$n]] = $place;
                        break;
                    }
                }
            }
            return $places;
        }
        return array();
    }

?>