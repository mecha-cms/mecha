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
 *        <ul>
 *          <li><a href="http://example.com/example/example">Example 4.1</a></li>
 *        </ul>
 *      </li>
 *      <li><a href="http://example.com/parent">Example 5</a>
 *        <ul>
 *          <li><a href="http://example.com/parent/children-1">Example 5.1</a></li>
 *          <li><a href="http://example.com/parent/children-2">Example 5.2</a></li>
 *        </ul>
 *      </li>
 *    </ul>
 *
 * --------------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------------------
 *  $array    | array  | Array of menu
 *  $type     | string | The list type ... `<ul>` or `<ol>` ?
 *  --------- | ------ | ----------------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Menu {

    public static function get($array = null, $type = 'ul', $filter_prefix = 'menu:') {
        $config = Config::get();
        $current = $config->url_current;
        // Use menu file from the cabinet if `$array` is not defined
        if(is_null($array)) {
            if($file = File::exist(STATE . DS . 'menus.txt')) {
                $array = Text::toArray(File::open($file)->read());
            } else {
                $array = array('Home' => '/', 'About' => '/about');
            }
            $filter_prefix = 'navigation:';
        }
        $html = '<' . $type . '>';
        foreach($array as $text => $url) {
            if(is_array($url)) {
                if(preg_match('#(.*?)\((.*?)\)$#', $text, $matches)) {
                    $_url = trim($matches[2], '/');
                    // Create full URL from value if the value does not contain a `://`
                    if(strpos($_url, '://') === false && strpos($_url, '#') !== 0) {
                        $_url = str_replace('/#', '#', trim($config->url . '/' . $_url, '/'));
                    }
                    $html .= Filter::apply($filter_prefix . 'list.item', '<li' . ($_url == $current ? ' class="selected"' : "") . '><a href="' . $_url . '">' . trim($matches[1]) . '</a>' . self::get($url, $type, $filter_prefix) . '</li>');
                } else {
                    $html .= Filter::apply($filter_prefix . 'list.item', '<li><a href="#">' . $text . '</a>' . self::get($url, $type, $filter_prefix) . '</li>');
                }
            } else {
                // Create full URL from value if the value does not contain a `://`
                if(strpos($url, '://') === false && strpos($url, '#') !== 0) {
                    $url = str_replace('/#', '#', trim($config->url . '/' . trim($url, '/'), '/'));
                }
                $html .= Filter::apply($filter_prefix . 'list.item', '<li' . ($url == $current ? ' class="selected"' : "") . '><a href="' . $url . '">' . $text . '</a></li>');
            }
        }
        return Filter::apply($filter_prefix . 'list', $html . '</' . $type . '>');
    }

}