<?php

// `<a href="{{url:foo}}">`
// `<a href="{{url}}foo">` ← deprecated
// `<a href="{{url}}">`

return array(

    '{{url:%s}}' => '~do_shortcode_url',
    '{{url}}%s' => '~do_shortcode_url',
    '{{url}}' => '~do_shortcode_url',

    '{{url.current:%s}}' => '~do_shortcode_url_current',
    '{{url.current}}%s' => '~do_shortcode_url_current',
    '{{url.current}}' => '~do_shortcode_url_current',

    '{{url.article:%s}}' => '~do_shortcode_url_article',
    '{{url.article}}%s' => '~do_shortcode_url_article',
    '{{url.article}}' => '~do_shortcode_url_article',

    '{{url.page:%s}}' => '~do_shortcode_url_page',
    '{{url.page}}%s' => '~do_shortcode_url_page',
    '{{url.page}}' => '~do_shortcode_url_page',

    '{{url.%s:%s}}' => '~do_shortcode_url_',
    '{{url.%s}}%s' => '~do_shortcode_url_',
    '{{url.%s}}' => '~do_shortcode_url_',

    '{{asset:%s}}' => '~do_shortcode_asset',
    '{{asset}}%s' => '~do_shortcode_asset',
    '{{asset}}' => '~do_shortcode_asset'

);