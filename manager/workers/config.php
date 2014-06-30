<?php $cache = Guardian::wayback(); echo Notify::read(); ?>
<form class="form-config" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <fieldset>
    <legend><?php echo $speak->manager->title_general; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->timezone; ?></span>
      <span class="grid span-4">
        <select name="timezone" class="select-block">
        <?php

        $timezones = array(
            'Pacific/Midway' => '(GMT-11:00) Midway',
            'Pacific/Niue' => '(GMT-11:00) Niue',
            'Pacific/Pago_Pago' => '(GMT-11:00) Pago Pago',
            'Pacific/Honolulu' => '(GMT-10:00) Hawaii Time',
            'Pacific/Rarotonga' => '(GMT-10:00) Rarotonga',
            'Pacific/Tahiti' => '(GMT-10:00) Tahiti',
            'Pacific/Marquesas' => '(GMT-09:30) Marquesas',
            'America/Anchorage' => '(GMT-09:00) Alaska Time',
            'Pacific/Gambier' => '(GMT-09:00) Gambier',
            'America/Los_Angeles' => '(GMT-08:00) Pacific Time',
            'America/Tijuana' => '(GMT-08:00) Pacific Time &ndash; Tijuana',
            'America/Vancouver' => '(GMT-08:00) Pacific Time &ndash; Vancouver',
            'America/Whitehorse' => '(GMT-08:00) Pacific Time &ndash; Whitehorse',
            'Pacific/Pitcairn' => '(GMT-08:00) Pitcairn',
            'America/Denver' => '(GMT-07:00) Mountain Time',
            'America/Phoenix' => '(GMT-07:00) Mountain Time &ndash; Arizona',
            'America/Mazatlan' => '(GMT-07:00) Mountain Time &ndash; Chihuahua, Mazatlan',
            'America/Dawson_Creek' => '(GMT-07:00) Mountain Time &ndash; Dawson Creek',
            'America/Edmonton' => '(GMT-07:00) Mountain Time &ndash; Edmonton',
            'America/Hermosillo' => '(GMT-07:00) Mountain Time &ndash; Hermosillo',
            'America/Yellowknife' => '(GMT-07:00) Mountain Time &ndash; Yellowknife',
            'America/Belize' => '(GMT-06:00) Belize',
            'America/Chicago' => '(GMT-06:00) Central Time',
            'America/Mexico_City' => '(GMT-06:00) Central Time &ndash; Mexico City',
            'America/Regina' => '(GMT-06:00) Central Time &ndash; Regina',
            'America/Winnipeg' => '(GMT-06:00) Central Time &ndash; Winnipeg',
            'America/El_Salvador' => '(GMT-06:00) El Salvador',
            'Pacific/Galapagos' => '(GMT-06:00) Galapagos',
            'America/Guatemala' => '(GMT-06:00) Guatemala',
            'America/Costa_Rica' => '(GMT-06:00) Kosta Rika',
            'America/Managua' => '(GMT-06:00) Managua',
            'Pacific/Easter' => '(GMT-06:00) Pulau Paskah',
            'America/Tegucigalpa' => '(GMT-06:00) Waktu Tengah &ndash; Tegucigalpa',
            'America/Bogota' => '(GMT-05:00) Bogota',
            'America/Cayman' => '(GMT-05:00) Cayman',
            'America/New_York' => '(GMT-05:00) Eastern Time',
            'America/Iqaluit' => '(GMT-05:00) Eastern Time &ndash; Iqaluit',
            'America/Montreal' => '(GMT-05:00) Eastern Time &ndash; Montreal',
            'America/Toronto' => '(GMT-05:00) Eastern Time &ndash; Toronto',
            'America/Grand_Turk' => '(GMT-05:00) Grand Turk',
            'America/Guayaquil' => '(GMT-05:00) Guayaquil',
            'America/Havana' => '(GMT-05:00) Havana',
            'America/Jamaica' => '(GMT-05:00) Jamaika',
            'America/Lima' => '(GMT-05:00) Lima',
            'America/Nassau' => '(GMT-05:00) Nassau',
            'America/Panama' => '(GMT-05:00) Panama',
            'America/Port-au-Prince' => '(GMT-05:00) Port-au-Prince',
            'America/Rio_Branco' => '(GMT-05:00) Rio Branco',
            'America/Caracas' => '(GMT-04:30) Karakas',
            'America/Antigua' => '(GMT-04:00) Antigua',
            'America/Asuncion' => '(GMT-04:00) Asuncion',
            'America/Halifax' => '(GMT-04:00) Atlantic Time &ndash; Halifax',
            'America/Barbados' => '(GMT-04:00) Barbados',
            'Atlantic/Bermuda' => '(GMT-04:00) Bermuda',
            'America/Boa_Vista' => '(GMT-04:00) Boa Vista',
            'America/Campo_Grande' => '(GMT-04:00) Campo Grande',
            'America/Cuiaba' => '(GMT-04:00) Cuiaba',
            'America/Curacao' => '(GMT-04:00) Curacao',
            'America/Guyana' => '(GMT-04:00) Guyana',
            'America/La_Paz' => '(GMT-04:00) La Paz',
            'America/Manaus' => '(GMT-04:00) Manaus',
            'America/Martinique' => '(GMT-04:00) Martinique',
            'Antarctica/Palmer' => '(GMT-04:00) Palmer',
            'America/Port_of_Spain' => '(GMT-04:00) Port of Spain',
            'America/Porto_Velho' => '(GMT-04:00) Porto Velho',
            'America/Puerto_Rico' => '(GMT-04:00) Puerto Riko',
            'America/Santiago' => '(GMT-04:00) Santiago',
            'America/Santo_Domingo' => '(GMT-04:00) Santo Domingo',
            'America/Thule' => '(GMT-04:00) Thule',
            'America/St_Johns' => '(GMT-03:30) Newfoundland Time &ndash; St. Johns',
            'America/Araguaina' => '(GMT-03:00) Araguaina',
            'America/Belem' => '(GMT-03:00) Belem',
            'America/Argentina/Buenos_Aires' => '(GMT-03:00) Buenos Aires',
            'America/Cayenne' => '(GMT-03:00) Cayenne',
            'America/Fortaleza' => '(GMT-03:00) Fortaleza',
            'America/Godthab' => '(GMT-03:00) Godthab',
            'America/Maceio' => '(GMT-03:00) Maceio',
            'America/Miquelon' => '(GMT-03:00) Miquelon',
            'America/Montevideo' => '(GMT-03:00) Montevideo',
            'America/Paramaribo' => '(GMT-03:00) Paramaribo',
            'America/Recife' => '(GMT-03:00) Recife',
            'Antarctica/Rothera' => '(GMT-03:00) Rothera',
            'America/Bahia' => '(GMT-03:00) Salvador',
            'America/Sao_Paulo' => '(GMT-03:00) Sao Paulo',
            'Atlantic/Stanley' => '(GMT-03:00) Stanley',
            'America/Noronha' => '(GMT-02:00) Noronha',
            'Atlantic/South_Georgia' => '(GMT-02:00) South Georgia',
            'Atlantic/Azores' => '(GMT-01:00) Azores',
            'Atlantic/Cape_Verde' => '(GMT-01:00) Cape Verde',
            'America/Scoresbysund' => '(GMT-01:00) Scoresbysund',
            'Africa/Abidjan' => '(GMT+00:00) Abidjan',
            'Africa/Accra' => '(GMT+00:00) Akra',
            'Africa/Bamako' => '(GMT+00:00) Bamako',
            'Africa/Banjul' => '(GMT+00:00) Banjul',
            'Africa/Bissau' => '(GMT+00:00) Bissau',
            'Africa/Casablanca' => '(GMT+00:00) Casablanca',
            'Africa/Conakry' => '(GMT+00:00) Conakry',
            'Africa/Dakar' => '(GMT+00:00) Dakar',
            'America/Danmarkshavn' => '(GMT+00:00) Danmarkshavn',
            'Europe/Dublin' => '(GMT+00:00) Dublin',
            'Africa/El_Aaiun' => '(GMT+00:00) El Aaiun',
            'Atlantic/Faroe' => '(GMT+00:00) Faeroe',
            'Africa/Freetown' => '(GMT+00:00) Freetown',
            'Atlantic/Canary' => '(GMT+00:00) Kepulauan Canary',
            'Europe/Lisbon' => '(GMT+00:00) Lisabon',
            'Africa/Lome' => '(GMT+00:00) Lome',
            'Europe/London' => '(GMT+00:00) London',
            'Africa/Monrovia' => '(GMT+00:00) Monrovia',
            'Africa/Nouakchott' => '(GMT+00:00) Nouakchott',
            'Africa/Ouagadougou' => '(GMT+00:00) Ouagadougou',
            'Atlantic/Reykjavik' => '(GMT+00:00) Reykjavik',
            'Africa/Sao_Tome' => '(GMT+00:00) Sao Tome',
            'Atlantic/St_Helena' => '(GMT+00:00) St Helena',
            'Africa/Algiers' => '(GMT+01:00) Aljir',
            'Europe/Amsterdam' => '(GMT+01:00) Amsterdam',
            'Europe/Andorra' => '(GMT+01:00) Andorra',
            'Africa/Bangui' => '(GMT+01:00) Bangui',
            'Europe/Berlin' => '(GMT+01:00) Berlin',
            'Africa/Brazzaville' => '(GMT+01:00) Brazzaville',
            'Europe/Brussels' => '(GMT+01:00) Brussel',
            'Europe/Budapest' => '(GMT+01:00) Budapest',
            'Africa/Ceuta' => '(GMT+01:00) Ceuta',
            'Africa/Douala' => '(GMT+01:00) Douala',
            'Europe/Gibraltar' => '(GMT+01:00) Gibraltar',
            'Africa/Kinshasa' => '(GMT+01:00) Kinshasa',
            'Europe/Copenhagen' => '(GMT+01:00) Kopenhagen',
            'Africa/Lagos' => '(GMT+01:00) Lagos',
            'Africa/Libreville' => '(GMT+01:00) Libreville',
            'Africa/Luanda' => '(GMT+01:00) Luanda',
            'Europe/Luxembourg' => '(GMT+01:00) Luksemburg',
            'Europe/Madrid' => '(GMT+01:00) Madrid',
            'Africa/Malabo' => '(GMT+01:00) Malabo',
            'Europe/Malta' => '(GMT+01:00) Malta',
            'Europe/Monaco' => '(GMT+01:00) Monako',
            'Africa/Ndjamena' => '(GMT+01:00) Ndjamena',
            'Africa/Niamey' => '(GMT+01:00) Niamey',
            'Europe/Oslo' => '(GMT+01:00) Oslo',
            'Europe/Paris' => '(GMT+01:00) Paris',
            'Africa/Porto-Novo' => '(GMT+01:00) Porto-Novo',
            'Europe/Rome' => '(GMT+01:00) Roma',
            'Europe/Stockholm' => '(GMT+01:00) Stokholm',
            'Europe/Tirane' => '(GMT+01:00) Tirana',
            'Africa/Tunis' => '(GMT+01:00) Tunis',
            'Europe/Belgrade' => '(GMT+01:00) Waktu Eropa Tengah &ndash; Beograd',
            'Europe/Prague' => '(GMT+01:00) Waktu Eropa Tengah &ndash; Praha',
            'Europe/Warsaw' => '(GMT+01:00) Warsawa',
            'Europe/Vienna' => '(GMT+01:00) Wina',
            'Africa/Windhoek' => '(GMT+01:00) Windhoek',
            'Europe/Zurich' => '(GMT+01:00) Zurich',
            'Asia/Amman' => '(GMT+02:00) Amman',
            'Europe/Athens' => '(GMT+02:00) Athena',
            'Asia/Beirut' => '(GMT+02:00) Beirut',
            'Africa/Blantyre' => '(GMT+02:00) Blantyre',
            'Europe/Bucharest' => '(GMT+02:00) Bucharest',
            'Africa/Bujumbura' => '(GMT+02:00) Bujumbura',
            'Europe/Chisinau' => '(GMT+02:00) Chisinau',
            'Asia/Damascus' => '(GMT+02:00) Damaskus',
            'Africa/Gaborone' => '(GMT+02:00) Gaborone',
            'Asia/Gaza' => '(GMT+02:00) Gaza',
            'Africa/Harare' => '(GMT+02:00) Harare',
            'Europe/Helsinki' => '(GMT+02:00) Helsinki',
            'Europe/Istanbul' => '(GMT+02:00) Istanbul',
            'Africa/Johannesburg' => '(GMT+02:00) Johannesburg',
            'Africa/Cairo' => '(GMT+02:00) Kairo',
            'Europe/Kiev' => '(GMT+02:00) Kiev',
            'Africa/Kigali' => '(GMT+02:00) Kigali',
            'Africa/Lubumbashi' => '(GMT+02:00) Lubumbashi',
            'Africa/Lusaka' => '(GMT+02:00) Lusaka',
            'Africa/Maputo' => '(GMT+02:00) Maputo',
            'Africa/Maseru' => '(GMT+02:00) Maseru',
            'Africa/Mbabane' => '(GMT+02:00) Mbabane',
            'Asia/Nicosia' => '(GMT+02:00) Nikosia',
            'Europe/Riga' => '(GMT+02:00) Riga',
            'Europe/Sofia' => '(GMT+02:00) Sofia',
            'Europe/Tallinn' => '(GMT+02:00) Tallinn',
            'Africa/Tripoli' => '(GMT+02:00) Tripoli',
            'Europe/Vilnius' => '(GMT+02:00) Vilnius',
            'Asia/Jerusalem' => '(GMT+02:00) Yerusalem',
            'Africa/Addis_Ababa' => '(GMT+03:00) Addis Ababa',
            'Asia/Aden' => '(GMT+03:00) Aden',
            'Indian/Antananarivo' => '(GMT+03:00) Antananarivo',
            'Africa/Asmara' => '(GMT+03:00) Asmera',
            'Asia/Baghdad' => '(GMT+03:00) Bagdad',
            'Asia/Bahrain' => '(GMT+03:00) Bahrain',
            'Africa/Dar_es_Salaam' => '(GMT+03:00) Dar es Salaam',
            'Africa/Djibouti' => '(GMT+03:00) Jibuti',
            'Africa/Kampala' => '(GMT+03:00) Kampala',
            'Africa/Khartoum' => '(GMT+03:00) Khartoum',
            'Indian/Comoro' => '(GMT+03:00) Komoro',
            'Asia/Kuwait' => '(GMT+03:00) Kuwait',
            'Indian/Mayotte' => '(GMT+03:00) Mayotte',
            'Europe/Minsk' => '(GMT+03:00) Minsk',
            'Africa/Mogadishu' => '(GMT+03:00) Mogadishu',
            'Europe/Kaliningrad' => '(GMT+03:00) Moskwa-01 &ndash; Kaliningrad',
            'Africa/Nairobi' => '(GMT+03:00) Nairobi',
            'Asia/Qatar' => '(GMT+03:00) Qatar',
            'Asia/Riyadh' => '(GMT+03:00) Riyadh',
            'Antarctica/Syowa' => '(GMT+03:00) Syowa',
            'Asia/Tehran' => '(GMT+03:30) Teheran',
            'Asia/Baku' => '(GMT+04:00) Baku',
            'Asia/Dubai' => '(GMT+04:00) Dubai',
            'Indian/Mahe' => '(GMT+04:00) Mahe',
            'Indian/Mauritius' => '(GMT+04:00) Mauritius',
            'Europe/Moscow' => '(GMT+04:00) Moskwa+00',
            'Europe/Samara' => '(GMT+04:00) Moskwa+00 &ndash; Samara',
            'Asia/Muscat' => '(GMT+04:00) Muscat',
            'Indian/Reunion' => '(GMT+04:00) Reunion',
            'Asia/Tbilisi' => '(GMT+04:00) Tbilisi',
            'Asia/Yerevan' => '(GMT+04:00) Yerevan',
            'Asia/Kabul' => '(GMT+04:30) Kabul',
            'Asia/Aqtau' => '(GMT+05:00) Aqtau',
            'Asia/Aqtobe' => '(GMT+05:00) Aqtobe',
            'Asia/Ashgabat' => '(GMT+05:00) Ashgabat',
            'Asia/Dushanbe' => '(GMT+05:00) Dushanbe',
            'Asia/Karachi' => '(GMT+05:00) Karachi',
            'Indian/Kerguelen' => '(GMT+05:00) Kerguelen',
            'Indian/Maldives' => '(GMT+05:00) Maladewa',
            'Antarctica/Mawson' => '(GMT+05:00) Mawson',
            'Asia/Tashkent' => '(GMT+05:00) Tashkent',
            'Asia/Calcutta' => '(GMT+05:30) India Standard Time',
            'Asia/Colombo' => '(GMT+05:30) Kolombo',
            'Asia/Katmandu' => '(GMT+05:45) Katmandu',
            'Asia/Almaty' => '(GMT+06:00) Almaty',
            'Asia/Bishkek' => '(GMT+06:00) Bishkek',
            'Indian/Chagos' => '(GMT+06:00) Chagos',
            'Asia/Dhaka' => '(GMT+06:00) Dhaka',
            'Asia/Yekaterinburg' => '(GMT+06:00) Moskwa+02 &ndash; Yekaterinburg',
            'Asia/Thimphu' => '(GMT+06:00) Thimphu',
            'Antarctica/Vostok' => '(GMT+06:00) Vostok',
            'Indian/Cocos' => '(GMT+06:30) Cocos',
            'Asia/Rangoon' => '(GMT+06:30) Rangoon',
            'Asia/Bangkok' => '(GMT+07:00) Bangkok',
            'Antarctica/Davis' => '(GMT+07:00) Davis',
            'Asia/Saigon' => '(GMT+07:00) Hanoi',
            'Asia/Hovd' => '(GMT+07:00) Hovd',
            'Asia/Jakarta' => '(GMT+07:00) Jakarta',
            'Asia/Omsk' => '(GMT+07:00) Moskwa+03 &ndash; Omsk, Novosibirsk',
            'Asia/Phnom_Penh' => '(GMT+07:00) Phnom Penh',
            'Indian/Christmas' => '(GMT+07:00) Pulau Natal',
            'Asia/Vientiane' => '(GMT+07:00) Vientiane',
            'Asia/Brunei' => '(GMT+08:00) Brunei',
            'Antarctica/Casey' => '(GMT+08:00) Casey',
            'Asia/Shanghai' => '(GMT+08:00) China Time &ndash; Beijing',
            'Asia/Choibalsan' => '(GMT+08:00) Choibalsan',
            'Asia/Hong_Kong' => '(GMT+08:00) Hong Kong',
            'Asia/Kuala_Lumpur' => '(GMT+08:00) Kuala Lumpur',
            'Asia/Makassar' => '(GMT+08:00) Makassar',
            'Asia/Macau' => '(GMT+08:00) Makau',
            'Asia/Manila' => '(GMT+08:00) Manila',
            'Asia/Krasnoyarsk' => '(GMT+08:00) Moskwa+04 &ndash; Krasnoyarsk',
            'Asia/Singapore' => '(GMT+08:00) Singapura',
            'Asia/Taipei' => '(GMT+08:00) Taipei',
            'Asia/Ulaanbaatar' => '(GMT+08:00) Ulan Bator',
            'Australia/Perth' => '(GMT+08:00) Western Time &ndash; Perth',
            'Asia/Dili' => '(GMT+09:00) Dili',
            'Asia/Jayapura' => '(GMT+09:00) Jayapura',
            'Asia/Irkutsk' => '(GMT+09:00) Moskwa+05 &ndash; Irkutsk',
            'Pacific/Palau' => '(GMT+09:00) Palau',
            'Asia/Pyongyang' => '(GMT+09:00) Pyongyang',
            'Asia/Seoul' => '(GMT+09:00) Seoul',
            'Asia/Tokyo' => '(GMT+09:00) Tokyo',
            'Australia/Adelaide' => '(GMT+09:30) Central Time &ndash; Adelaide',
            'Australia/Darwin' => '(GMT+09:30) Central Time &ndash; Darwin',
            'Antarctica/DumontDUrville' => '(GMT+10:00) Dumont D&rsquo;Urville',
            'Australia/Brisbane' => '(GMT+10:00) Eastern Time &ndash; Brisbane',
            'Australia/Hobart' => '(GMT+10:00) Eastern Time &ndash; Hobart',
            'Australia/Sydney' => '(GMT+10:00) Eastern Time &ndash; Melbourne, Sydney',
            'Pacific/Guam' => '(GMT+10:00) Guam',
            'Asia/Yakutsk' => '(GMT+10:00) Moskwa+06 &ndash; Yakutsk',
            'Pacific/Port_Moresby' => '(GMT+10:00) Port Moresby',
            'Pacific/Saipan' => '(GMT+10:00) Saipan',
            'Pacific/Chuuk' => '(GMT+10:00) Truk',
            'Pacific/Efate' => '(GMT+11:00) Efate',
            'Pacific/Guadalcanal' => '(GMT+11:00) Guadalcanal',
            'Pacific/Kosrae' => '(GMT+11:00) Kosrae',
            'Asia/Vladivostok' => '(GMT+11:00) Moskwa+07 &ndash; Yuzhno-Sakhalinsk',
            'Pacific/Noumea' => '(GMT+11:00) Noumea',
            'Pacific/Pohnpei' => '(GMT+11:00) Ponape',
            'Pacific/Norfolk' => '(GMT+11:30) Norfolk',
            'Pacific/Auckland' => '(GMT+12:00) Auckland',
            'Pacific/Fiji' => '(GMT+12:00) Fiji',
            'Pacific/Funafuti' => '(GMT+12:00) Funafuti',
            'Pacific/Kwajalein' => '(GMT+12:00) Kwajalein',
            'Pacific/Majuro' => '(GMT+12:00) Majuro',
            'Asia/Magadan' => '(GMT+12:00) Moskwa+08 &ndash; Magadan',
            'Asia/Kamchatka' => '(GMT+12:00) Moskwa+08 &ndash; Petropavlovsk-Kamchatskiy',
            'Pacific/Nauru' => '(GMT+12:00) Nauru',
            'Pacific/Tarawa' => '(GMT+12:00) Tarawa',
            'Pacific/Wake' => '(GMT+12:00) Wake',
            'Pacific/Wallis' => '(GMT+12:00) Wallis',
            'Pacific/Apia' => '(GMT+13:00) Apia',
            'Pacific/Enderbury' => '(GMT+13:00) Enderbury',
            'Pacific/Fakaofo' => '(GMT+13:00) Fakaofo',
            'Pacific/Tongatapu' => '(GMT+13:00) Tongatapu',
            'Pacific/Kiritimati' => '(GMT+14:00) Kiritimati'
        );

        foreach($timezones as $key => $value) {
            echo '<option value="' . $key . '"' . ($cache['timezone'] == $key ? ' selected' : "") . '>' . $value . '</option>';
        }

        ?>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->charset; ?></span>
      <span class="grid span-4"><input name="charset" type="text" class="input-block" value="<?php echo $cache['charset']; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->language; ?></span>
      <span class="grid span-4">
        <select name="language" class="select-block">
        <?php

        foreach(glob(LANGUAGE . DS . '*', GLOB_ONLYDIR) as $folder) {
            $lang = basename($folder);
            echo '<option value="' . $lang . '"' . ($cache['language'] == $lang ? ' selected' : "") . '>' . $lang . '</option>';
        }

        ?>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_language_direction; ?></span>
      <span class="grid span-4">
        <select name="language_direction" class="select-block">
          <option value="ltr"<?php echo ($cache['language_direction'] == 'LTR' ? ' selected' : ""); ?>>Left to Right (LTR)</option>
          <option value="rtl"<?php echo ($cache['language_direction'] == 'RTL' ? ' selected' : ""); ?>>Right to Left (RTL)</option>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->shield; ?></span>
      <span class="grid span-4">
        <select name="shield" class="select-block">
        <?php

        foreach(glob(SHIELD . DS . '*', GLOB_ONLYDIR) as $folder) {
            $shield = basename($folder);
            $info = Shield::info($shield);
            echo '<option value="' . $shield . '"' . ($cache['shield'] == $shield ? ' selected' : "") . '>' . $info->name . '</option>';
        }

        ?>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?> (Global)</span>
      <span class="grid span-4"><input name="per_page" type="number" value="<?php echo $cache['per_page']; ?>"></span>
    </label>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><label><input name="comments" type="checkbox" value="true"<?php echo $cache['comments'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_comment_allow; ?></span></label></div>
        <div><label><input name="comment_moderation" type="checkbox" value="true"<?php echo $cache['comment_moderation'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_comment_moderation; ?></span></label></div>
        <div><label><input name="email_notification" type="checkbox" value="true"<?php echo $cache['email_notification'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_comment_notification; ?></span></label></div>
      </div>
    </div>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_length; ?></span>
      <span class="grid span-4"><input name="excerpt_length" type="number" value="<?php echo $cache['excerpt_length']; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_tail; ?></span>
      <span class="grid span-4"><input name="excerpt_tail" type="text" value="<?php echo Text::parse($cache['excerpt_tail'])->to_encoded_html; ?>"></span>
    </label>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><label><input name="widget_year_first" type="checkbox" value="true"<?php echo $cache['widget_year_first'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_widget_time; ?></span></label></div>
        <div><label><input name="resource_versioning" type="checkbox" value="true"<?php echo $cache['resource_versioning'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_resource_versioning; ?></span></label></div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_header; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
      <span class="grid span-4"><input name="title" type="text" class="input-block" value="<?php echo $cache['title']; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_title_separator; ?></span>
      <span class="grid span-4"><input name="title_separator" type="text" class="input-block" value="<?php echo Text::parse($cache['title_separator'])->to_encoded_html; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->slogan; ?></span>
      <span class="grid span-4"><textarea name="slogan" class="textarea-block"><?php echo $cache['slogan']; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->description; ?></span>
      <span class="grid span-4"><textarea name="description" class="textarea-block"><?php echo $cache['description']; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->keywords; ?></span>
      <span class="grid span-4"><input name="keywords" type="text" class="input-block" value="<?php echo $cache['keywords']; ?>"></span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_authorization; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->name; ?></span>
      <span class="grid span-4"><input name="author" type="text" class="input-block" value="<?php echo $cache['author']; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->url; ?></span>
      <span class="grid span-4"><input name="author_profile_url" type="url" class="input-block" value="<?php echo $cache['author_profile_url']; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->email; ?></span>
      <span class="grid span-4"><input name="author_email" type="email" class="input-block" value="<?php echo $cache['author_email']; ?>"></span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_page; ?></legend>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_index; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="index[title]" type="text" value="<?php echo $cache['index']['title']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="index[slug]" type="text" value="<?php echo $cache['index']['slug']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="index[per_page]" type="number" value="<?php echo $cache['index']['per_page']; ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_tag; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="tag[title]" type="text" value="<?php echo $cache['tag']['title']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="tag[slug]" type="text" value="<?php echo $cache['tag']['slug']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="tag[per_page]" type="number" value="<?php echo $cache['tag']['per_page']; ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_archive; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="archive[title]" type="text" value="<?php echo $cache['archive']['title']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="archive[slug]" type="text" value="<?php echo $cache['archive']['slug']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="archive[per_page]" type="number" value="<?php echo $cache['archive']['per_page']; ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_search; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="search[title]" type="text" value="<?php echo $cache['search']['title']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="search[slug]" type="text" value="<?php echo $cache['search']['slug']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="search[per_page]" type="number" value="<?php echo $cache['search']['per_page']; ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_manager; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="manager[title]" type="text" value="<?php echo $cache['manager']['title']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="manager[slug]" type="text" value="<?php echo $cache['manager']['slug']; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="manager[per_page]" type="number" value="<?php echo $cache['manager']['per_page']; ?>"></span>
      </label>
    </fieldset>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_other; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_keyword_spam; ?></span>
      <span class="grid span-4"><textarea name="spam_keywords" class="textarea-block" placeholder="<?php echo $speak->manager->placeholder_keyword_spam; ?>"><?php echo $cache['spam_keywords']; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_title; ?></span>
      <span class="grid span-4"><input name="defaults[page_title]" class="input-block" value="<?php echo $cache['defaults']['page_title']; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_content ?></span>
      <span class="grid span-4"><textarea name="defaults[page_content]" class="textarea-block code"><?php echo $cache['defaults']['page_content']; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_custom_css; ?></span>
      <span class="grid span-4"><textarea name="defaults[page_custom_css]" class="textarea-block code"><?php echo $cache['defaults']['page_custom_css']; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_custom_js; ?></span>
      <span class="grid span-4"><textarea name="defaults[page_custom_js]" class="textarea-block code"><?php echo $cache['defaults']['page_custom_js']; ?></textarea></span>
    </label>
  </fieldset>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></p>
</form>