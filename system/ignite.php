<?php if( ! defined('ROOT')) die('Rejected.');


// error_reporting(E_ALL);
// ini_set('display_errors', 1);

ini_set('error_log', SYSTEM . DS . 'log' . DS . 'errors.log');
ini_set('session.gc_probability', 1);


/**
 * => `http://www.php.net/manual/en/security.magicquotes.disabling.php`
 * --------------------------------------------------------------------
 */

$gpc = array(&$_GET, &$_POST, &$_REQUEST, &$_COOKIE);

array_walk_recursive($gpc, function(&$value) {
    $value = str_replace("\r", "", $value);
    if(get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
});


/**
 * Loading Workers
 * ---------------
 */

spl_autoload_register(function($worker) {
    $path = SYSTEM . DS . 'kernel' . DS . strtolower($worker) . '.php';
    if(file_exists($path)) require $path;
});


/**
 * Set Default Validators
 * ----------------------
 */

// Check for IP address
Guardian::checker('this_is_IP', function($input) {
    return filter_var($input, FILTER_VALIDATE_IP);
});

// Check for URL address
Guardian::checker('this_is_URL', function($input) {
    return filter_var($input, FILTER_VALIDATE_URL);
});

// Check for email address
Guardian::checker('this_is_email', function($input) {
    return filter_var($input, FILTER_VALIDATE_EMAIL);
});

// Check for boolean value
Guardian::checker('this_is_boolean', function($input) {
    return filter_var($input, FILTER_VALIDATE_BOOLEAN);
});

// Check whether the input value is too large
Guardian::checker('this_is_too_large', function($input, $max = 3000) {
    return is_numeric($input) ? $input > $max : false;
});

// Check whether the input value is too small
Guardian::checker('this_is_too_small', function($input, $min = 0) {
    return is_numeric($input) ? $input < $min : false;
});

// Check whether the input value is too long
Guardian::checker('this_is_too_long', function($input, $max = 3000) {
    return is_string($input) ? strlen($input) > $max : false;
});

// Check whether the input value is too short
Guardian::checker('this_is_too_short', function($input, $min = 0) {
    return is_string($input) ? strlen($input) < $min : false;
});

// Check whether the answer is incorrect
Guardian::checker('this_is_correct', function($a = true, $b = false) {
    return $a === $b;
});


/**
 * Set Default Parsers
 * -------------------
 */

// Convert text to ASCII character
Text::parser('to_ascii', function($input) {
    if( ! is_string($input)) return $input;
    $results = "";
    for($i = 0, $length = strlen($input); $i < $length; ++$i) {
        $results .= '&#' . ord($input[$i]) . ';';
    }
    return $results;
});

// Convert decoded URL to encoded URL
Text::parser('to_encoded_url', function($input) {
    return is_string($input) ? urlencode($input) : $input;
});

// Convert encoded URL to decoded URL
Text::parser('to_decoded_url', function($input) {
    return is_string($input) ? urldecode($input) : $input;
});

// Convert decoded HTML to encoded HTML
Text::parser('to_encoded_html', function($input, $a = ENT_QUOTES, $b = 'UTF-8') {
    return is_string($input) ? htmlentities($input, $a, $b) : $input;
});

// Convert encoded HTML to decoded HTML
Text::parser('to_decoded_html', function($input, $a = ENT_QUOTES, $b = 'UTF-8') {
    return is_string($input) ? html_entity_decode($input, $a, $b) : $input;
});

// Convert decoded JSON to encoded JSON
Text::parser('to_encoded_json', function($input) {
    return json_encode($input);
});

// Convert encoded JSON to decoded JSON
Text::parser('to_decoded_json', function($input, $a = false) {
    return is_string($input) && ! is_null(json_decode($input, $a)) ? json_decode($input, $a) : $input;
});

function do_slug($text, $lower = true, $strip_underscores_and_dots = true, $connector = '-') {
    $text_accents = array(
        // Numeric characters
        '¹' => 1,
        '²' => 2,
        '³' => 3,
        // Latin
        '°' => 0,
        'æ' => 'ae',
        'ǽ' => 'ae',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Å' => 'A',
        'Ǻ' => 'A',
        'Ă' => 'A',
        'Ǎ' => 'A',
        'Æ' => 'AE',
        'Ǽ' => 'AE',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'å' => 'a',
        'ǻ' => 'a',
        'ă' => 'a',
        'ǎ' => 'a',
        'ª' => 'a',
        '@' => 'at',
        'Ĉ' => 'C',
        'Ċ' => 'C',
        'ĉ' => 'c',
        'ċ' => 'c',
        '©' => 'c',
        'Ð' => 'Dj',
        'Đ' => 'D',
        'ð' => 'dj',
        'đ' => 'd',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ĕ' => 'E',
        'Ė' => 'E',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ĕ' => 'e',
        'ė' => 'e',
        'ƒ' => 'f',
        'Ĝ' => 'G',
        'Ġ' => 'G',
        'ĝ' => 'g',
        'ġ' => 'g',
        'Ĥ' => 'H',
        'Ħ' => 'H',
        'ĥ' => 'h',
        'ħ' => 'h',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ĩ' => 'I',
        'Ĭ' => 'I',
        'Ǐ' => 'I',
        'Į' => 'I',
        'Ĳ' => 'IJ',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ĩ' => 'i',
        'ĭ' => 'i',
        'ǐ' => 'i',
        'į' => 'i',
        'ĳ' => 'ij',
        'Ĵ' => 'J',
        'ĵ' => 'j',
        'Ĺ' => 'L',
        'Ľ' => 'L',
        'Ŀ' => 'L',
        'ĺ' => 'l',
        'ľ' => 'l',
        'ŀ' => 'l',
        'Ñ' => 'N',
        'ñ' => 'n',
        'ŉ' => 'n',
        'Ò' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ō' => 'O',
        'Ŏ' => 'O',
        'Ǒ' => 'O',
        'Ő' => 'O',
        'Ơ' => 'O',
        'Ø' => 'O',
        'Ǿ' => 'O',
        'Œ' => 'OE',
        'ò' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ō' => 'o',
        'ŏ' => 'o',
        'ǒ' => 'o',
        'ő' => 'o',
        'ơ' => 'o',
        'ø' => 'o',
        'ǿ' => 'o',
        'º' => 'o',
        'œ' => 'oe',
        'Ŕ' => 'R',
        'Ŗ' => 'R',
        'ŕ' => 'r',
        'ŗ' => 'r',
        'Ŝ' => 'S',
        'Ș' => 'S',
        'ŝ' => 's',
        'ș' => 's',
        'ſ' => 's',
        'Ţ' => 'T',
        'Ț' => 'T',
        'Ŧ' => 'T',
        'Þ' => 'TH',
        'ţ' => 't',
        'ț' => 't',
        'ŧ' => 't',
        'þ' => 'th',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ũ' => 'U',
        'Ŭ' => 'U',
        'Ű' => 'U',
        'Ų' => 'U',
        'Ư' => 'U',
        'Ǔ' => 'U',
        'Ǖ' => 'U',
        'Ǘ' => 'U',
        'Ǚ' => 'U',
        'Ǜ' => 'U',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ũ' => 'u',
        'ŭ' => 'u',
        'ű' => 'u',
        'ų' => 'u',
        'ư' => 'u',
        'ǔ' => 'u',
        'ǖ' => 'u',
        'ǘ' => 'u',
        'ǚ' => 'u',
        'ǜ' => 'u',
        'Ŵ' => 'W',
        'ŵ' => 'w',
        'Ý' => 'Y',
        'Ÿ' => 'Y',
        'Ŷ' => 'Y',
        'ý' => 'y',
        'ÿ' => 'y',
        'ŷ' => 'y',
        // Russian
        'Ъ' => '',
        'Ь' => '',
        'А' => 'A',
        'Б' => 'B',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Э' => 'E',
        'Ф' => 'F',
        'Г' => 'G',
        'Х' => 'H',
        'И' => 'I',
        'Й' => 'J',
        'Я' => 'Ja',
        'Ю' => 'Ju',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Ш' => 'Sh',
        'Щ' => 'Shch',
        'Т' => 'T',
        'У' => 'U',
        'В' => 'V',
        'Ы' => 'Y',
        'З' => 'Z',
        'Ж' => 'Zh',
        'ъ' => '',
        'ь' => '',
        'а' => 'a',
        'б' => 'b',
        'ц' => 'c',
        'ч' => 'ch',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'э' => 'e',
        'ф' => 'f',
        'г' => 'g',
        'х' => 'h',
        'и' => 'i',
        'й' => 'j',
        'я' => 'ja',
        'ю' => 'ju',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'ш' => 'sh',
        'щ' => 'shch',
        'т' => 't',
        'у' => 'u',
        'в' => 'v',
        'ы' => 'y',
        'з' => 'z',
        'ж' => 'zh',
        // German characters
        'Ä' => 'AE',
        'Ö' => 'OE',
        'Ü' => 'UE',
        'ß' => 'ss',
        'ä' => 'ae',
        'ö' => 'oe',
        'ü' => 'ue',
        // Turkish characters
        'Ç' => 'C',
        'Ğ' => 'G',
        'İ' => 'I',
        'Ş' => 'S',
        'ç' => 'c',
        'ğ' => 'g',
        'ı' => 'i',
        'ş' => 's',
        // Latvian
        'Ā' => 'A',
        'Ē' => 'E',
        'Ģ' => 'G',
        'Ī' => 'I',
        'Ķ' => 'K',
        'Ļ' => 'L',
        'Ņ' => 'N',
        'Ū' => 'U',
        'ā' => 'a',
        'ē' => 'e',
        'ģ' => 'g',
        'ī' => 'i',
        'ķ' => 'k',
        'ļ' => 'l',
        'ņ' => 'n',
        'ū' => 'u',
        // Ukrainian
        'Ґ' => 'G',
        'І' => 'I',
        'Ї' => 'Ji',
        'Є' => 'Ye',
        'ґ' => 'g',
        'і' => 'i',
        'ї' => 'ji',
        'є' => 'ye',
        // Czech
        'Č' => 'C',
        'Ď' => 'D',
        'Ě' => 'E',
        'Ň' => 'N',
        'Ř' => 'R',
        'Š' => 'S',
        'Ť' => 'T',
        'Ů' => 'U',
        'Ž' => 'Z',
        'č' => 'c',
        'ď' => 'd',
        'ě' => 'e',
        'ň' => 'n',
        'ř' => 'r',
        'š' => 's',
        'ť' => 't',
        'ů' => 'u',
        'ž' => 'z',
        // Polish
        'Ą' => 'A',
        'Ć' => 'C',
        'Ę' => 'E',
        'Ł' => 'L',
        'Ń' => 'N',
        'Ó' => 'O',
        'Ś' => 'S',
        'Ź' => 'Z',
        'Ż' => 'Z',
        'ą' => 'a',
        'ć' => 'c',
        'ę' => 'e',
        'ł' => 'l',
        'ń' => 'n',
        'ó' => 'o',
        'ś' => 's',
        'ź' => 'z',
        'ż' => 'z',
        // Greek
        'Α' => 'A',
        'Β' => 'B',
        'Γ' => 'G',
        'Δ' => 'D',
        'Ε' => 'E',
        'Ζ' => 'Z',
        'Η' => 'E',
        'Θ' => 'Th',
        'Ι' => 'I',
        'Κ' => 'K',
        'Λ' => 'L',
        'Μ' => 'M',
        'Ν' => 'N',
        'Ξ' => 'X',
        'Ο' => 'O',
        'Π' => 'P',
        'Ρ' => 'R',
        'Σ' => 'S',
        'Τ' => 'T',
        'Υ' => 'Y',
        'Φ' => 'Ph',
        'Χ' => 'Ch',
        'Ψ' => 'Ps',
        'Ω' => 'O',
        'Ϊ' => 'I',
        'Ϋ' => 'Y',
        'ά' => 'a',
        'έ' => 'e',
        'ή' => 'e',
        'ί' => 'i',
        'ΰ' => 'Y',
        'α' => 'a',
        'β' => 'b',
        'γ' => 'g',
        'δ' => 'd',
        'ε' => 'e',
        'ζ' => 'z',
        'η' => 'e',
        'θ' => 'th',
        'ι' => 'i',
        'κ' => 'k',
        'λ' => 'l',
        'μ' => 'm',
        'ν' => 'n',
        'ξ' => 'x',
        'ο' => 'o',
        'π' => 'p',
        'ρ' => 'r',
        'ς' => 's',
        'σ' => 's',
        'τ' => 't',
        'υ' => 'y',
        'φ' => 'ph',
        'χ' => 'ch',
        'ψ' => 'ps',
        'ω' => 'o',
        'ϊ' => 'i',
        'ϋ' => 'y',
        'ό' => 'o',
        'ύ' => 'y',
        'ώ' => 'o',
        'ϐ' => 'b',
        'ϑ' => 'th',
        'ϒ' => 'Y',
        // Arabic
        'أ' => 'a',
        'ب' => 'b',
        'ت' => 't',
        'ث' => 'th',
        'ج' => 'g',
        'ح' => 'h',
        'خ' => 'kh',
        'د' => 'd',
        'ذ' => 'th',
        'ر' => 'r',
        'ز' => 'z',
        'س' => 's',
        'ش' => 'sh',
        'ص' => 's',
        'ض' => 'd',
        'ط' => 't',
        'ظ' => 'th',
        'ع' => 'aa',
        'غ' => 'gh',
        'ف' => 'f',
        'ق' => 'k',
        'ك' => 'k',
        'ل' => 'l',
        'م' => 'm',
        'ن' => 'n',
        'ه' => 'h',
        'و' => 'o',
        'ي' => 'y',
        // Vietnamese
        'ạ' => 'a',
        'ả' => 'a',
        'ầ' => 'a',
        'ấ' => 'a',
        'ậ' => 'a',
        'ẩ' => 'a',
        'ẫ' => 'a',
        'ằ' => 'a',
        'ắ' => 'a',
        'ặ' => 'a',
        'ẳ' => 'a',
        'ẵ' => 'a',
        'ẹ' => 'e',
        'ẻ' => 'e',
        'ẽ' => 'e',
        'ề' => 'e',
        'ế' => 'e',
        'ệ' => 'e',
        'ể' => 'e',
        'ễ' => 'e',
        'ị' => 'i',
        'ỉ' => 'i',
        'ọ' => 'o',
        'ỏ' => 'o',
        'ồ' => 'o',
        'ố' => 'o',
        'ộ' => 'o',
        'ổ' => 'o',
        'ỗ' => 'o',
        'ờ' => 'o',
        'ớ' => 'o',
        'ợ' => 'o',
        'ở' => 'o',
        'ỡ' => 'o',
        'ụ' => 'u',
        'ủ' => 'u',
        'ừ' => 'u',
        'ứ' => 'u',
        'ự' => 'u',
        'ử' => 'u',
        'ữ' => 'u',
        'ỳ' => 'y',
        'ỵ' => 'y',
        'ỷ' => 'y',
        'ỹ' => 'y',
        'Ạ' => 'A',
        'Ả' => 'A',
        'Ầ' => 'A',
        'Ấ' => 'A',
        'Ậ' => 'A',
        'Ẩ' => 'A',
        'Ẫ' => 'A',
        'Ằ' => 'A',
        'Ắ' => 'A',
        'Ặ' => 'A',
        'Ẳ' => 'A',
        'Ẵ' => 'A',
        'Ẹ' => 'E',
        'Ẻ' => 'E',
        'Ẽ' => 'E',
        'Ề' => 'E',
        'Ế' => 'E',
        'Ệ' => 'E',
        'Ể' => 'E',
        'Ễ' => 'E',
        'Ị' => 'I',
        'Ỉ' => 'I',
        'Ọ' => 'O',
        'Ỏ' => 'O',
        'Ồ' => 'O',
        'Ố' => 'O',
        'Ộ' => 'O',
        'Ổ' => 'O',
        'Ỗ' => 'O',
        'Ờ' => 'O',
        'Ớ' => 'O',
        'Ợ' => 'O',
        'Ở' => 'O',
        'Ỡ' => 'O',
        'Ụ' => 'U',
        'Ủ' => 'U',
        'Ừ' => 'U',
        'Ứ' => 'U',
        'Ự' => 'U',
        'Ử' => 'U',
        'Ữ' => 'U',
        'Ỳ' => 'Y',
        'Ỵ' => 'Y',
        'Ỷ' => 'Y',
        'Ỹ' => 'Y'
    );
    $slug = str_replace(array_keys($text_accents), array_values($text_accents), strip_tags($text));
    $slug = preg_replace(
        array(
            '#&(?:[a-z0-9]+|\#[0-9]+|\#x[a-f0-9]+);#i',
            '#[^a-z0-9' . preg_quote($connector, '#') . ( ! $strip_underscores_and_dots ? '_.' : "") . ']#i',
            '#' . $connector . '+#',
            '#^' . $connector . '|' . $connector . '$#'
        ),
        array(
            ' ',
            $connector,
            $connector,
            ""
        ),
    $slug);
    if($lower) $slug = strtolower($slug);
    return ! empty($slug) ? $slug : str_repeat($connector, 2);
}

// Convert text to slug pattern
Text::parser('to_slug', function($input) {
    return is_string($input) ? do_slug($input) : $input;
});

// Convert text to moderate slug pattern (for file name)
Text::parser('to_slug_moderate', function($input) {
    return is_string($input) ? do_slug($input, true, false) : $input;
});

// Convert slug pattern to text
Text::parser('to_text', function($input) {
    if( ! is_string($input)) return $input;
    // 1. Replace `+` to ` `
    // 2. Replace `-` to ` `
    // 3. Replace `---` to `-`
    return preg_replace(
        array(
            '#-{3}#',
            '#-#',
            '# +#',
            '#``\.``#'
        ),
        array(
            '``.``',
            ' ',
            ' ',
            '-'
        ),
    urldecode($input));
});

// Convert text to array key pattern
Text::parser('to_array_key', function($input) {
    return is_string($input) ? do_slug($input, false, true, '_') : $input;
});

// Convert plain text to HTML
Text::parser('to_html', function($input) {
    // Suppose that there is no HTML parser engine ...
    return $input;
});

define('SEPARATOR_ENCODED', Text::parse(SEPARATOR, '->ascii'));


/**
 * Start the Sessions
 * ------------------
 */

session_save_path(SYSTEM . DS . 'log' . DS . 'sessions');
session_start();


/**
 * Load the Configuration Data
 * ---------------------------
 */

Config::load();

$config = Config::get();
$speak = Config::speak();


/**
 * Remove Query String of the Current Page Path
 * --------------------------------------------
 */

if($config->page_type != 'home') {
    array_shift($_GET);
}


/**
 * First Installation
 * ------------------
 */

if(File::exist(ROOT . DS . 'install.php')) {
    Guardian::kick($config->url . '/install.php');
}


/**
 * Set Default Time Zone Before Launch
 * -----------------------------------
 */

date_default_timezone_set($config->timezone);


/**
 * Inject Widget's CSS and JavaScript
 * ----------------------------------
 */

Weapon::add('shell_before', function() {
    echo Asset::stylesheet('cabinet/shields/widgets.css', "", 'widgets.min.css');
});

Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
    echo Asset::javascript('cabinet/shields/widgets.js', "", 'widgets.min.js');
});


/**
 * Loading Plugins
 * ---------------
 */

if($plugins_order = File::exist(CACHE . DS . 'plugins.order.cache')) {
    $plugins = File::open($plugins_order)->unserialize();
} else {
    $plugins = array();
    $plugins_list = glob(PLUGIN . DS . '*' . DS . 'launch.php');
    $plugins_payload = count($plugins_list);
    sort($plugins_list);
    for($i = 0; $i < $plugins_payload; ++$i) {
        $plugins[] = false; // $plugins[] = '-- ' . dirname($plugins_list[$i]);
    }
    for($j = 0; $j < $plugins_payload; ++$j) {
        if($overtake = File::exist(dirname($plugins_list[$j]) . DS . '__overtake.txt')) {
            $to_index = ((int) file_get_contents($overtake)) - 1;
            if($to_index < 0) $to_index = 0;
            if($to_index > $plugins_payload - 1) $to_index = $plugins_payload - 1;
            array_splice($plugins, $to_index, 0, array(dirname($plugins_list[$j])));
        } else {
            $plugins[$j] = dirname($plugins_list[$j]);
        }
    }
    File::serialize($plugins)->saveTo(CACHE . DS . 'plugins.order.cache');
}

for($k = 0, $plugins_launched = count($plugins); $k < $plugins_launched; ++$k) {
    if($plugins[$k]) {
        if( ! $language = File::exist($plugins[$k] . DS . 'languages' . DS . $config->language . DS . 'speak.txt')) {
            $language = $plugins[$k] . DS . 'languages' . DS . 'en_US' . DS . 'speak.txt';
        }
        if(File::exist($language)) {
            Config::merge('speak', Text::toArray(File::open($language)->read(), ':', '  '));
        }
        if($launch = File::exist($plugins[$k] . DS . 'launch.php')) {
            include $launch;
        }
    }
}


/**
 * Check the Plugins Order
 * -----------------------
 */

// var_dump($plugins); exit;


/**
 * Include User Defined Functions
 * ------------------------------
 */

if($function = File::exist(SHIELD . DS . $config->shield . DS . 'functions.php')) {
    include $function;
}


/**
 * Handle Shortcodes in Content
 * ----------------------------
 */

Filter::add('shortcode', function($content) use($config, $speak) {
    if(strpos($content, '{{') === false) return $content;
    $d = DECK . DS . 'workers' . DS . 'repair.state.shortcodes.php';
    $shortcodes = file_exists($d) ? include $d : array();
    if($file = File::exist(STATE . DS . 'shortcodes.txt')) {
        $file_shortcodes = File::open($file)->unserialize();
        foreach($file_shortcodes as $key => $value) {
            unset($shortcodes[$key]);
        }
        $shortcodes = array_merge($shortcodes, $file_shortcodes);
    }
    $regex = array();
    foreach($shortcodes as $key => $value) {
        $regex['#(?<!`)' . str_replace(
            array(
                '%s'
            ),
            array(
                '(.*?)'
            ),
        preg_quote($key, '#')) . '(?!`)#'] = $value;
    }
    $content = preg_replace(array_keys($regex), array_values($regex), $content);
    if(strpos($content, '{{php}}') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{php\}\}(?!`)([\s\S]*?)(?<!`)\{\{\/php\}\}(?!`)#', function($matches) {
            return Converter::phpEval($matches[1]);
        }, $content);
    }
    return preg_replace('#`\{\{(.*?)\}\}`#', '{{$1}}', $content);
}, 20);


/**
 * Others
 * ------
 *
 * I'm trying to not touching the source code of the Markdown plugin at all.
 *
 * [1]. Add bordered class for tables in content.
 * [2]. Add `rel="nofollow"` attribute in external links.
 *
 */

Filter::add('content', function($content) use($config) {
    if($config->html_parser) {
        return preg_replace(
            array(
                '#<table>#',
                '#<a href="(https?\:\/\/)(?!' . preg_quote($config->host, '/') . ')#'
            ),
            array(
                '<table class="table-bordered table-full">',
                '<a rel="nofollow" href="$1'
            ),
        $content);
    }
    return $content;
}, 20);


/**
 * Set Page Metadata
 * -----------------
 */

Weapon::add('meta', function() {
    $config = Config::get();
    $speak = Config::speak();
    $html  = O_BEGIN . '<meta charset="' . $config->charset . '"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<meta name="viewport" content="width=device-width"' . ES . NL;
    if(isset($config->article->description)) {
        $html .= str_repeat(TAB, 2) . '<meta name="description" content="' . strip_tags($config->article->description) . '"' . ES . NL;
    } elseif(isset($config->page->description)) {
        $html .= str_repeat(TAB, 2) . '<meta name="description" content="' . strip_tags($config->page->description) . '"' . ES . NL;
    } else {
        $html .= str_repeat(TAB, 2) . '<meta name="description" content="' . strip_tags($config->description) . '"' . ES . NL;
    }
    $html .= str_repeat(TAB, 2) . '<meta name="author" content="' . $config->author . '"' . ES . NL;
    echo Filter::apply('meta', $html, 1);
}, 10);

Weapon::add('meta', function() {
    $config = Config::get();
    $html  = str_repeat(TAB, 2) . '<title>' . strip_tags($config->page_title) . '</title>' . NL;
    $html .= str_repeat(TAB, 2) . '<!--[if IE]><script src="' . $config->protocol . 'html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->' . NL;
    echo Filter::apply('meta', $html, 2);
}, 20);

Weapon::add('meta', function() {
    $config = Config::get();
    $speak = Config::speak();
    $html  = str_repeat(TAB, 2) . '<link href="' . $config->url . '/favicon.ico" rel="shortcut icon" type="image/x-icon"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<link href="' . $config->url_current . '" rel="canonical"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<link href="' . $config->url . '/sitemap" rel="sitemap"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<link href="' . $config->url . '/feed/rss" rel="alternate" type="application/rss+xml" title="' . $speak->feeds . $config->title_separator . $config->title . '"' . ES . O_END;
    echo Filter::apply('meta', $html, 3);
}, 30);

Weapon::add('SHIPMENT_REGION_TOP', function() {
    Weapon::fire('meta');
}, 10);