<?php $cache = Guardian::wayback(); echo Notify::read(); ?>
<form class="form-config" action="<?php echo $config->url_current; ?>" method="post">

  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">

  <fieldset>

    <legend><?php echo $speak->manager->title_general; ?></legend>

    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->timezone; ?></span>
      <span class="grid span-4">
        <select name="timezone" class="input-block">
        <?php

        $options = array(
            'Kwajalein' => '(GMT-12:00) International Date Line West',
            'Pacific/Samoa' => '(GMT-11:00) Midway Island, Samoa',
            'Pacific/Honolulu' => '(GMT-10:00) Hawaii',
            'America/Anchorage' => '(GMT-09:00) Alaska',
            'America/Los_Angeles' => '(GMT-08:00) Pacific Time (US &amp; Canada)',
            'America/Tijuana' => '(GMT-08:00) Tijuana, Baja California',
            'America/Denver' => '(GMT-07:00) Mountain Time (US &amp; Canada)',
            'America/Chihuahua' => '(GMT-07:00) Chihuahua, La Paz, Mazatlan',
            'America/Phoenix' => '(GMT-07:00) Arizona',
            'America/Regina' => '(GMT-06:00) Saskatchewan',
            'America/Tegucigalpa' => '(GMT-06:00) Central America',
            'America/Chicago' => '(GMT-06:00) Central Time (US &amp; Canada)',
            'America/Mexico_City' => '(GMT-06:00) Guadalajara, Mexico City, Monterrey',
            'America/New_York' => '(GMT-05:00) Eastern Time (US &amp; Canada)',
            'America/Bogota' => '(GMT-05:00) Bogota, Lima, Quito, Rio Branco',
            'America/Indiana/Indianapolis' => '(GMT-05:00) Indiana (East)',
            'America/Caracas' => '(GMT-04:30) Caracas',
            'America/Halifax' => '(GMT-04:00) Atlantic Time (Canada)',
            'America/Manaus' => '(GMT-04:00) Manaus',
            'America/Santiago' => '(GMT-04:00) Santiago',
            'America/La_Paz' => '(GMT-04:00) La Paz',
            'America/St_Johns' => '(GMT-03:30) Newfoundland',
            'America/Argentina/Buenos_Aires' => '(GMT-03:00) Buenos Aires',
            'America/Sao_Paulo' => '(GMT-03:00) Brasilia',
            'America/Godthab' => '(GMT-03:00) Greenland',
            'America/Montevideo' => '(GMT-03:00) Montevideo',
            'America/Argentina/Buenos_Aires' => '(GMT-03:00) Georgetown',
            'Atlantic/South_Georgia' => '(GMT-02:00) Mid-Atlantic',
            'Atlantic/Azores' => '(GMT-01:00) Azores',
            'Atlantic/Cape_Verde' => '(GMT-01:00) Cape Verde Is.',
            'Europe/London' => '(GMT) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London',
            'Atlantic/Reykjavik' => '(GMT) Monrovia, Reykjavik',
            'Africa/Casablanca' => '(GMT) Casablanca',
            'Europe/Belgrade' => '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague',
            'Europe/Sarajevo' => '(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb',
            'Europe/Brussels' => '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris',
            'Africa/Algiers' => '(GMT+01:00) West Central Africa',
            'Europe/Amsterdam' => '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
            'Africa/Cairo' => '(GMT+02:00) Cairo',
            'Europe/Helsinki' => '(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius',
            'Europe/Athens' => '(GMT+02:00) Athens, Bucharest, Istanbul',
            'Asia/Jerusalem' => '(GMT+02:00) Jerusalem',
            'Asia/Amman' => '(GMT+02:00) Amman',
            'Asia/Beirut' => '(GMT+02:00) Beirut',
            'Africa/Windhoek' => '(GMT+02:00) Windhoek',
            'Africa/Harare' => '(GMT+02:00) Harare, Pretoria',
            'Asia/Kuwait' => '(GMT+03:00) Kuwait, Riyadh',
            'Asia/Baghdad' => '(GMT+03:00) Baghdad',
            'Europe/Minsk' => '(GMT+03:00) Minsk',
            'Africa/Nairobi' => '(GMT+03:00) Nairobi',
            'Asia/Tbilisi' => '(GMT+03:00) Tbilisi',
            'Asia/Tehran' => '(GMT+03:30) Tehran',
            'Asia/Muscat' => '(GMT+04:00) Abu Dhabi, Muscat',
            'Asia/Baku' => '(GMT+04:00) Baku',
            'Europe/Moscow' => '(GMT+04:00) Moscow, St. Petersburg, Volgograd',
            'Asia/Yerevan' => '(GMT+04:00) Yerevan',
            'Asia/Karachi' => '(GMT+05:00) Islamabad, Karachi',
            'Asia/Tashkent' => '(GMT+05:00) Tashkent',
            'Asia/Kolkata' => '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi',
            'Asia/Colombo' => '(GMT+05:30) Sri Jayawardenepura',
            'Asia/Katmandu' => '(GMT+05:45) Kathmandu',
            'Asia/Dhaka' => '(GMT+06:00) Astana, Dhaka',
            'Asia/Yekaterinburg' => '(GMT+06:00) Ekaterinburg',
            'Asia/Rangoon' => '(GMT+06:30) Yangon (Rangoon)',
            'Asia/Novosibirsk' => '(GMT+07:00) Almaty, Novosibirsk',
            'Asia/Jakarta' => '(GMT+07:00) Bangkok, Hanoi, Jakarta',
            'Asia/Beijing' => '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi',
            'Asia/Krasnoyarsk' => '(GMT+08:00) Krasnoyarsk',
            'Asia/Ulaanbaatar' => '(GMT+08:00) Irkutsk, Ulaan Bataar',
            'Asia/Kuala_Lumpur' => '(GMT+08:00) Kuala Lumpur, Singapore',
            'Asia/Taipei' => '(GMT+08:00) Taipei',
            'Australia/Perth' => '(GMT+08:00) Perth',
            'Asia/Seoul' => '(GMT+09:00) Seoul',
            'Asia/Tokyo' => '(GMT+09:00) Osaka, Sapporo, Tokyo',
            'Australia/Darwin' => '(GMT+09:30) Darwin',
            'Australia/Adelaide' => '(GMT+09:30) Adelaide',
            'Australia/Sydney' => '(GMT+10:00) Canberra, Melbourne, Sydney',
            'Australia/Brisbane' => '(GMT+10:00) Brisbane',
            'Australia/Hobart' => '(GMT+10:00) Hobart',
            'Asia/Yakutsk' => '(GMT+10:00) Yakutsk',
            'Pacific/Guam' => '(GMT+10:00) Guam, Port Moresby',
            'Asia/Vladivostok' => '(GMT+11:00) Vladivostok',
            'Pacific/Fiji' => '(GMT+12:00) Fiji, Kamchatka, Marshall Is.',
            'Asia/Magadan' => '(GMT+12:00) Magadan, Solomon Is., New Caledonia',
            'Pacific/Auckland' => '(GMT+12:00) Auckland, Wellington',
            'Pacific/Tongatapu' => '(GMT+13:00) Nukualofa'
        );

        foreach($options as $key => $value) {
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
        <select name="language" class="input-block">
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
        <select name="language_direction" class="input-block">
          <option value="LTR"<?php echo ($cache['language_direction'] == 'LTR' ? ' selected' : ""); ?>>Left to Right (LTR)</option>
          <option value="RTL"<?php echo ($cache['language_direction'] == 'RTL' ? ' selected' : ""); ?>>Right to Left (RTL)</option>
        </select>
      </span>
    </label>

    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->shield; ?></span>
      <span class="grid span-4">
        <select name="shield" class="input-block">
        <?php

        foreach(glob(SHIELD . DS . '*', GLOB_ONLYDIR) as $folder) {
            $shield = basename($folder);
            $info = Shield::info($shield);
            echo '<option value="' . $shield . '"' . ($cache['shield'] == $shield ? ' selected' : "") . '>' . $info->name . ', ' . strtolower($speak->by) . ' ' . $info->author . '</option>';
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
        <div><label><input name="comments" type="checkbox" value="true"<?php echo $cache['comments'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_allow_comment; ?></span></label></div>
        <div><label><input name="email_notification" type="checkbox" value="true"<?php echo $cache['email_notification'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_allow_comment_notification; ?></span></label></div>
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
        <div><label><input name="resource_versioning" type="checkbox" value="true"<?php echo $cache['resource_versioning'] ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_allow_resource_versioning; ?></span></label></div>
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
      <span class="grid span-4"><textarea name="slogan" class="input-block"><?php echo $cache['slogan']; ?></textarea></span>
    </label>

    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->description; ?></span>
      <span class="grid span-4"><textarea name="description" class="input-block"><?php echo $cache['description']; ?></textarea></span>
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
      <span class="grid span-2 form-label">Spam Keywords</span>
      <span class="grid span-4"><textarea name="spam_keywords" class="input-block" placeholder="<?php echo $speak->manager->placeholder_spam_keywords; ?>"><?php echo $cache['spam_keywords']; ?></textarea></span>
    </label>

    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_title; ?></span>
      <span class="grid span-4"><input name="defaults[page_title]" class="input-block" value="<?php echo $cache['defaults']['page_title']; ?>"></span>
    </label>

    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_content ?></span>
      <span class="grid span-4"><textarea name="defaults[page_content]" class="input-block"><?php echo $cache['defaults']['page_content']; ?></textarea></span>
    </label>

    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_custom_css; ?></span>
      <span class="grid span-4"><textarea name="defaults[page_custom_css]" class="input-block"><?php echo $cache['defaults']['page_custom_css']; ?></textarea></span>
    </label>

    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_custom_js; ?></span>
      <span class="grid span-4"><textarea name="defaults[page_custom_js]" class="input-block"><?php echo $cache['defaults']['page_custom_js']; ?></textarea></span>
    </label>

  </fieldset>

  <div class="grid-group">
    <span class="grid span-6"><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></span>
  </div>

</form>