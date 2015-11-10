<?php


/**
 * ==========================================================================
 *  GET ARTICLE PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::articlePath('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type  | Description
 *  --------- | ----- | -----------------------------------------------------
 *  $detector | mixed | Slug, ID or time of the article
 *  --------- | ----- | -----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('articlePath', function($detector) {
    return Get::pagePath($detector, ARTICLE);
});


/**
 * ==========================================================================
 *  GET LIST OF ARTICLE DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::articleExtract($input));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('articleExtract', function($input) {
    return Get::pageExtract($input, 'article:');
});


/**
 * ==========================================================================
 *  GET LIST OF ARTICLE(S) PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::articles() as $path) {
 *        echo $path . '<br>';
 *    }
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('articles', function($order = 'DESC', $filter = "", $e = 'txt') {
    return Get::pages($order, $filter, $e, ARTICLE);
});


/**
 * ==========================================================================
 *  GET LIST OF ARTICLE(S) DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::articlesExtract() as $file) {
 *        echo $file['path'] . '<br>';
 *    }
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('articlesExtract', function($order = 'DESC', $sorter = 'time', $filter = "", $e = 'txt') {
    return Get::pagesExtract($order, $sorter, $filter, $e, 'article:', ARTICLE);
});


/**
 * ==========================================================================
 *  GET MINIMUM DATA OF AN ARTICLE
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::articleAnchor('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('articleAnchor', function($path) {
    return Get::pageAnchor($path, ARTICLE, '/' . Config::get('index.slug') . '/', 'article:');
});


/**
 * ==========================================================================
 *  GET ARTICLE HEADER(S) ONLY
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::articleHeader('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('articleHeader', function($path) {
    return Get::pageHeader($path, ARTICLE, '/' . Config::get('index.slug') . '/', 'article:');
});


/**
 * ==========================================================================
 *  EXTRACT ARTICLE FILE INTO LIST OF ARTICLE DATA FROM ITS PATH/SLUG/ID
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::article('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('article', function($reference, $excludes = array()) {
    return Get::page($reference, $excludes, ARTICLE, '/' . Config::get('index.slug') . '/', 'article:');
});


/**
 * ==========================================================================
 *  GET COMMENT PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::commentPath('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type  | Description
 *  --------- | ----- | -----------------------------------------------------
 *  $detector | mixed | Slug, ID or time of the page
 *  --------- | ----- | -----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('commentPath', function($detector) {
    return Get::responsePath($detector, RESPONSE);
});


/**
 * ==========================================================================
 *  GET LIST OF COMMENT DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::commentExtract($input));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('commentExtract', function($input) {
    return Get::responseExtract($input, 'comment:');
});


/**
 * ===========================================================================
 *  GET LIST OF COMMENT(S) PATH
 * ===========================================================================
 *
 * -- CODE: ------------------------------------------------------------------
 *
 *    foreach(Get::comments() as $path) {
 *        echo $path . '<br>';
 *    }
 *
 * ---------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type    | Description
 *  --------- | ------- | ----------------------------------------------------
 *  $order    | string  | Ascending or descending? ASC/DESC?
 *  $filter   | string  | The result(s) filter
 *  $e        | boolean | The file extension(s)
 *  --------- | ------- | ----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('comments', function($order = 'ASC', $filter = "", $e = 'txt') {
    return Get::responses($order, $filter, $e, RESPONSE);
});


/**
 * ==========================================================================
 *  GET LIST OF COMMENT(S) DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::commentsExtract() as $file) {
 *        echo $file['path'] . '<br>';
 *    }
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------------
 *  $sorter   | string | The key of array item as sorting reference
 *  --------- | ------ | ----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('commentsExtract', function($order = 'ASC', $sorter = 'time', $filter = "", $e = 'txt') {
    return Get::responsesExtract($order, $sorter, $filter, $e, 'comment:', RESPONSE);
});


/**
 * ==========================================================================
 *  EXTRACT COMMENT FILE INTO LIST OF COMMENT DATA FROM ITS PATH/ID/TIME
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::comment(1399334470));
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter  | Type   | Description
 *  ---------- | ------ | ---------------------------------------------------
 *  $reference | string | Comment path, ID or time
 *  $excludes  | array  | Exclude some field(s) from result(s)
 *  ---------- | ------ | ---------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('comment', function($reference, $excludes = array()) {
    return Get::response($reference, $excludes, array(RESPONSE, ARTICLE), '/' . Config::get('index.slug') . '/', 'comment:');
});


/**
 * ==========================================================================
 *  GET CLIENT IP ADDRESS
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    echo Get::IP();
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('IP', function() {
    $ip = 'N/A';
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
            $addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($addresses[0]);
        } else {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return Guardian::check($ip, '->ip') ? $ip : 'N/A';
});


/**
 * ==========================================================================
 *  GET CLIENT USER AGENT INFO
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    echo Get::UA();
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('UA', function() {
    return $_SERVER['HTTP_USER_AGENT'];
});


/**
 * ==========================================================================
 *  GET TIMEZONE LIST
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::timezone());
 *    var_dump(Get::timezone('Asia/Jakarta'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('timezone', function($identifier = null, $fallback = false, $format = '(UTC%1$s) %2$s &ndash; %3$s') {
    // http://pastebin.com/vBmW1cnX
    static $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC
    );
    $timezones = array();
    $timezone_offsets = array();
    foreach($regions as $region) {
        $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
    }
    foreach($timezones as $timezone) {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }
    $a = $b = array();
    foreach($timezone_offsets as $timezone => $offset) {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));
        $pretty_offset = $offset_prefix . $offset_formatted;
        $t = new DateTimeZone($timezone);
        $c = new DateTime(null, $t);
        $current_time = $c->format('g:i A');
        $text = sprintf($format, $pretty_offset, str_replace('_', ' ', $timezone), $current_time);
        if($offset < 0) {
            $b[$timezone] = $text;
        } else {
            $a[$timezone] = $text;
        }
    }
    asort($a);
    arsort($b);
    $timezone_list = $b + $a;
    if( ! is_null($identifier)) {
        return isset($timezone_list[$identifier]) ? $timezone_list[$identifier] : $fallback;
    }
    return $timezone_list;
});


// DEPRECATED. Please use `Mecha::A(Get::tags())`
Get::plug('rawTags', function($order = 'ASC', $sorter = 'name') {
    return Mecha::A(Get::tags($order, $sorter));
});

// DEPRECATED. Please use `Mecha::A(Get::tag())`
Get::plug('rawTag', function($filter, $output = null, $fallback = false) {
    return Mecha::A(Get::tag($filter, $output, $fallback));
});

// DEPRECATED. Please use `Converter::curt()`
Get::plug('summary', function($input, $chars = 100, $tail = '&hellip;', $charset = "") {
    return Converter::curt($input, $chars, $tail, $charset);
});