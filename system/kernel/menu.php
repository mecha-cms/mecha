<?php

/**
 * ================================================================================
 *  CONVERT ARRAY INTO HTML LIST WITH LINKS
 * ================================================================================
 *
 * -- CODE: -----------------------------------------------------------------------
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
 *        )
 *    );
 *
 *    echo Menu::get($array, 'ul');
 *
 * -- RESULT: ---------------------------------------------------------------------
 *
 *    <ul>
 *      <li><a href="http://example.com">Example 1</a></li>
 *      <li><a href="#example">Example 2</a></li>
 *      <li><a href="http://example.com/example">Example 3</a></li>
 *      <li><a href="#">Example 4</a>
 *        <ul class="children-1">
 *          <li><a href="http://example.com/example/example">Example 4.1</a></li>
 *        </ul>
 *      </li>
 *      <li><a href="http://example.com/parent">Example 5</a>
 *        <ul class="children-1">
 *          <li><a href="http://example.com/parent/children-1">Example 5.1</a></li>
 *          <li><a href="http://example.com/parent/children-2">Example 5.2</a></li>
 *        </ul>
 *      </li>
 *    </ul>
 *
 * --------------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter      | Type    | Description
 *  -------------- | ------- | ----------------------------------------------------
 *  $array         | array   | Array of menu
 *  $type          | string  | The list type ... `<ul>` or `<ol>` ?
 *  $filter_prefix | string  | Filter prefix for the generated HTML output
 *  $depth         | integer | Starting depth
 *  -------------- | ------- | ----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Menu {

    public static $config = array(
        'classes' => array(
            'selected' => 'selected',
            'children' => 'children-%s',
            'parent' => false
        )
    );

    public static function create($array, $type, $filter_prefix, $depth) {
        $c_url = Config::get('url');
        $c_url_current = Config::get('url_current');
        $c_class = self::$config['classes'];
        $html = str_repeat(TAB, $depth) . '<' . $type . ($depth > 0 ? ($c_class['children'] !== false ? ' class="' . sprintf($c_class['children'], $depth / 2) . '"' : "") : ($c_class['parent'] !== false ? ' class="' . $c_class['parent'] . '"' : "")) . '>' . NL;
        foreach($array as $title => $url) {
            if( ! is_array($url)) {
                if(strpos($url, '#') !== 0 && strpos($url, '://') === false) {
                    $url = preg_replace('#\/([\#?&])#', '$1', trim($c_url . '/' . trim($url, '/'), '/'));
                }
                $html .= Filter::apply($filter_prefix . 'list.item', str_repeat(TAB, $depth + 1) . '<li' . ($url == $c_url_current || ($url != $c_url && strpos($c_url_current . '/', $url . '/') === 0) ? ' class="' . $c_class['selected'] . '"' : "") . '><a href="' . $url . '">' . $title . '</a></li>' . NL, $depth + 1);
            } else {
                if(preg_match('#(.*?)\s*\((.*?)\)\s*$#', $title, $matches)) {
                    $_title = $matches[1];
                    $_url = $matches[2];
                } else {
                    $_title = $title;
                    $_url = '#';
                }
                if(strpos($_url, '#') !== 0 && strpos($_url, '://') === false) {
                    $_url = preg_replace('#\/([\#?&])#', '$1', trim($c_url . '/' . trim($_url, '/'), '/'));
                }
                $html .= Filter::apply($filter_prefix . 'list.item', str_repeat(TAB, $depth + 1) . '<li' . ($_url == $c_url_current || ($_url != $c_url && strpos($c_url_current . '/', $_url . '/') === 0) ? ' class="' . $c_class['selected'] . '"' : "") . '><a href="' . $_url . '">' . $_title . '</a>' . NL . self::create($url, $type, $filter_prefix, $depth + 2) . str_repeat(TAB, $depth + 1) . '</li>' . NL, $depth + 1);
            }
        }
        return Filter::apply($filter_prefix . 'list', $html . str_repeat(TAB, $depth) . '</' . $type . '>' . NL, $depth);
    }

    public static function get($array = null, $type = 'ul', $filter_prefix = 'menu:') {
        // Use menu file from the cabinet when `$array` is not defined
        if(is_null($array)) {
            $speak = Config::speak();
            $filter_prefix = 'navigation:';
            $array = Text::toArray(File::open(STATE . DS . 'menus.txt')->read($speak->home . ": /\n" . $speak->about . ": /about"), ':', '    ');
        }
        return O_BEGIN . rtrim(self::create($array, $type, $filter_prefix, 0), NL) . O_END;
    }

    public static function configure($key, $value = null) {
        if(is_array($key)) {
            self::$config = array_replace_recursive(self::$config, $key);
        } else {
            if(is_array($value)) {
                foreach($value as $k => $v) {
                    self::$config[$key][$k] = $v;
                }
            } else {
                self::$config[$key] = $value;
            }
        }
        return new static;
    }

}