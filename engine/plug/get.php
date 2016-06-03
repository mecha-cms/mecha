<?php


/**
 * ==========================================================================
 *  GET PAGE/ARTICLE TAG(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::pageTags() as $tag) { ... }
 *    foreach(Get::articleTags() as $tag) { ... }
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageTags', function($order = 'ASC', $sorter = 'name') {
    return Get::tags($order, $sorter, 'page');
});

Get::plug('articleTags', function($order = 'ASC', $sorter = 'name') {
    return Get::tags($order, $sorter, 'article');
});

/**
 * ==========================================================================
 *  RETURN SPECIFIC PAGE/ARTICLE TAG ITEM FILTERED BY ITS AVAILABLE DATA
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    $tag = Get::pageTag('lorem-ipsum');
 *    $tag = Get::articleTag('lorem-ipsum');
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageTag', function($filter, $output = null, $fallback = false) {
    return Get::tag($filter, $output, $fallback, 'page');
});

Get::plug('articleTag', function($filter, $output = null, $fallback = false) {
    return Get::tag($filter, $output, $fallback, 'article');
});


/**
 * ==========================================================================
 *  GET PAGE/ARTICLE PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pagePath('lorem-ipsum'));
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

Get::plug('pagePath', function($detector) {
    return Get::postPath($detector, PAGE);
});

Get::plug('articlePath', function($detector) {
    return Get::postPath($detector, ARTICLE);
});


/**
 * ==========================================================================
 *  GET LIST OF PAGE/ARTICLE DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pageExtract($input));
 *    var_dump(Get::articleExtract($input));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageExtract', function($input) {
    return Get::postExtract($input, 'page:');
});

Get::plug('articleExtract', function($input) {
    return Get::postExtract($input, 'article:');
});


/**
 * ==========================================================================
 *  GET LIST OF PAGE(S)/ARTICLE(S) PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::pages() as $path) { ... }
 *    foreach(Get::articles() as $path) { ... }
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pages', function($order = 'DESC', $filter = "", $e = 'txt') {
    return Get::posts($order, $filter, $e, PAGE);
});

Get::plug('articles', function($order = 'DESC', $filter = "", $e = 'txt') {
    return Get::posts($order, $filter, $e, ARTICLE);
});


/**
 * ==========================================================================
 *  GET LIST OF PAGES(S)/ARTICLE(S) DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::pagesExtract() as $file) { ... }
 *    foreach(Get::articlesExtract() as $file) { ... }
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pagesExtract', function($order = 'DESC', $sorter = 'time', $filter = "", $e = 'txt') {
    return Get::postsExtract($order, $sorter, $filter, $e, PAGE, 'page:');
});

Get::plug('articlesExtract', function($order = 'DESC', $sorter = 'time', $filter = "", $e = 'txt') {
    return Get::postsExtract($order, $sorter, $filter, $e, ARTICLE, 'article:');
});


/**
 * ==========================================================================
 *  GET MINIMUM DATA OF A PAGE/ARTICLE
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pageAnchor('lorem-ipsum'));
 *    var_dump(Get::articleAnchor('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageAnchor', function($path) {
    $c = Config::get('index.slug');
    Config::set('index.slug', false);
    $post = Get::postAnchor($path, PAGE, 'page:');
    Config::set('index.slug', $c);
    return $post;
});

Get::plug('articleAnchor', function($path) {
    return Get::postAnchor($path, ARTICLE, 'article:');
});


/**
 * ==========================================================================
 *  GET PAGE/ARTICLE HEADER(S) ONLY
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pageHeader('lorem-ipsum'));
 *    var_dump(Get::articleHeader('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageHeader', function($path) {
    $c = Config::get('index.slug');
    Config::set('index.slug', false);
    $post = Get::postHeader($path, PAGE, 'page:');
    Config::set('index.slug', $c);
    return $post;
});

Get::plug('articleHeader', function($path) {
    return Get::postHeader($path, ARTICLE, 'article:');
});


/**
 * ==========================================================================
 *  EXTRACT PAGE/ARTICLE FILE INTO LIST OF PAGE/ARTICLE DATA
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::page('lorem-ipsum'));
 *    var_dump(Get::article('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('page', function($path, $excludes = array()) {
    $c = Config::get('index.slug');
    Config::set('index.slug', false);
    $post = Get::post($path, $excludes, PAGE, 'page:');
    Config::set('index.slug', $c);
    return $post;
});

Get::plug('article', function($path, $excludes = array()) {
    return Get::post($path, $excludes, ARTICLE, 'article:');
});


/**
 * ==========================================================================
 *  GET COMMENT PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::commentPath(1399334470));
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
    return Get::responsePath($detector, COMMENT);
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
    return Get::responses($order, $filter, $e, COMMENT);
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
    return Get::responsesExtract($order, $sorter, $filter, $e, COMMENT, 'comment:');
});


/**
 * ==========================================================================
 *  GET MINIMUM DATA OF A COMMENT
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::commentAnchor(1399334470));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('commentAnchor', function($path, $folder = ARTICLE, $FP = 'article:') {
    return Get::responseAnchor($path, array(COMMENT, $folder), array('comment:', $FP));
});


/**
 * ==========================================================================
 *  GET COMMENT HEADER(S) ONLY
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::commentHeader(1399334470));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('commentHeader', function($path, $folder = ARTICLE, $FP = 'article:') {
    return Get::responseHeader($path, array(COMMENT, $folder), array('comment:', $FP));
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
 *  Parameter  | Type  | Description
 *  ---------- | ----- | ----------------------------------------------------
 *  $path      | mixed | Comment path, ID or time
 *  $excludes  | array | Exclude some field(s) from result(s)
 *  ---------- | ----- | ----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('comment', function($path, $excludes = array(), $folder = ARTICLE, $FP = 'article:') {
    return Get::response($path, $excludes, array(COMMENT, $folder), array('comment:', $FP));
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

Get::plug('IP', function($fallback = false) {
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
    return Guardian::check($ip, '->ip') ? $ip : $fallback;
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