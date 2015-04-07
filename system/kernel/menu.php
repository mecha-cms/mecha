<?php

/**
 * ====================================================================
 *  CONVERT ARRAY INTO HTML LIST WITH/WITHOUT LINKS
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    $array = array(
 *        'Example 1' => '/',
 *        'Example 2' => '#example',
 *        'Example 3' => '/example',
 *        'Example 4' => array(
 *            'Example 4.1' => '/example/example'
 *        ),
 *        'Example 5 (/parent)' => array(
 *            'Example 5.1' => '/parent/children-1',
 *            'Example 5.2' => '/parent/children-2'
 *        ),
 *        '|',
 *        '<ul><li>Manual 1</li></ul>',
 *        '<li>Manual 2</li>',
 *        'Text 1',
 *        'Text 2' => null
 *    );
 *
 *    echo Menu::get($array, 'ul');
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $array    | array  | Array of menu
 *  $type     | string | The list type ... `<ul>` or `<ol>` ?
 *  $depth    | string | Depth extra before each list group/list item
 *  $FP       | string | Filter prefix for the generated HTML output
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Menu {

    private static $o = array();

    public static $config = array(
        'classes' => array(
            'selected' => 'selected',
            'parent' => false,
            'children' => 'children-%d',
            'separator' => 'separator'
        )
    );

    public static function create($array, $type = 'ul', $depth = "", $FP = "", $i = 0) {
        $c_url = Config::get('url');
        $c_url_current = Config::get('url_current');
        $c_class = self::$config['classes'];
        $html = $depth . str_repeat(TAB, $i) . '<' . $type . ($i > 0 ? ($c_class['children'] !== false ? ' class="' . sprintf($c_class['children'], $i / 2) . '"' : "") : ($c_class['parent'] !== false ? ' class="' . $c_class['parent'] . '"' : "")) . '>' . NL;
        foreach($array as $key => $value) {
            if( ! is_array($value)) {
                // Manual list group: `array('<ol></ol>')`
                if(preg_match('#^\s*<\/?(ol|ul)(>| .*?>)#i', $value)) {
                    $html .= Filter::apply($FP . 'list', $depth . str_repeat(TAB, $i + 1) . $value . NL, $i + 1);
                // Manual list item: `array('<li></li>')`
                } else if(preg_match('#^\s*<\/?li(>| .*?>)#i', $value)) {
                    $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . $value . NL, $i + 1);
                // List item separator: `array('|')`
                } else if($key === '|' || is_int($key) && $value === '|') {
                    $html .= Filter::apply($FP . 'list.item.separator', Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li class="' . $c_class['separator'] . '"></li>' . NL, $i + 1), $i + 1);
                // List item without link: `array('foo')`
                } else if(is_int($key)) {
                    $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li>' . $value . '</li>' . NL, $i + 1);
                // List item without link: `array('foo' => null)`
                } else if(is_null($value)) {
                    $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li>' . $key . '</li>' . NL, $i + 1);
                // List item with link: `array('foo' => '/')`
                } else {
                    if($value[0] !== '?' && $value[0] !== '&' && $value[0] !== '#' && strpos($value, '://') === false) {
                        $value = trim($value, '/');
                        $value = str_replace(
                            array(
                                '/?',
                                '/&',
                                '/#'
                            ),
                            array(
                                '?',
                                '&',
                                '#'
                            ),
                        trim($c_url . '/' . $value, '/'));
                    }
                    $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li' . ($value == $c_url_current || ($value != $c_url && strpos($c_url_current . '/', $value . '/') === 0) ? ' class="' . $c_class['selected'] . '"' : "") . '><a href="' . $value . '">' . $key . '</a></li>' . NL, $i + 1);
                }
            } else {
                if(preg_match('#(.*?)\s*\((.*?)\)\s*$#', $key, $matches)) {
                    $_key = $matches[1];
                    $_value = trim($matches[2], '/');
                } else {
                    $_key = $key;
                    $_value = '#';
                }
                if($_value[0] !== '?' && $_value[0] !== '&' && $_value[0] !== '#' && strpos($_value, '://') === false) {
                    $_value = str_replace(
                        array(
                            '/?',
                            '/&',
                            '/#'
                        ),
                        array(
                            '?',
                            '&',
                            '#'
                        ),
                    trim($c_url . '/' . $_value, '/'));
                }
                $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li' . ($_value == $c_url_current || ($_value != $c_url && strpos($c_url_current . '/', $_value . '/') === 0) ? ' class="' . $c_class['selected'] . '"' : "") . '>' . NL . str_repeat(TAB, $i + 2) . '<a href="' . $_value . '">' . $_key . '</a>' . NL . self::create($value, $type, $depth, $FP, $i + 2) . $depth . str_repeat(TAB, $i + 1) . '</li>' . NL, $i + 1);
            }
        }
        return Filter::apply($FP . 'list', rtrim($html, NL) . ( ! empty($array) ? NL . $depth . str_repeat(TAB, $i) : "") . '</' . $type . '>' . NL, $i);
    }

    public static function get($array = null, $type = 'ul', $depth = "", $FP = "") {
        // Use menu file from the cabinet when `$array` is not defined
        if(is_null($array)) {
            $speak = Config::speak();
            $FP = 'navigation:';
            $array = Text::toArray(Get::state_menu($speak->home . S . " /\n" . $speak->feed . S . " /feed"), S, '    ');
        }
        return O_BEGIN . rtrim(self::create($array, $type, $depth, $FP, 0), NL) . O_END;
    }

    // Configure ...
    public static function configure($key, $value = null) {
        if(is_array($key)) {
            Mecha::extend(self::$config, $key);
        } else {
            if(is_array($value)) {
                Mecha::extend(self::$config[$key], $value);
            } else {
                self::$config[$key] = $value;
            }
        }
        return new static;
    }

    // Add new method with `Menu::plug('foo')`
    public static function plug($kin, $action) {
        self::$o[$kin] = $action;
    }

    // Call the added method with `Menu::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        if( ! isset(self::$o[$kin])) {
            Guardian::abort('Method <code>Menu::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array(self::$o[$kin], $arguments);
    }

}