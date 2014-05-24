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

    public static function get($array = null, $type = 'ul') {
        $config = Config::get();
        $current = $config->url_current;
        // Use menu file from the cabinet if `$array` is not defined
        if(is_null($array)) {
            if($file = File::exist(STATE . '/menus.txt')) {
                $array = Text::toArray(File::open($file)->read());
            } else {
                $array = array('Home' => '/', 'About' => '/about');
            }
        }
        $html = '<' . $type . '>';
        foreach($array as $key => $value) {
            if(is_array($value)) {
                if(preg_match('/(.*?)\((.*?)\)$/', $key, $matches)) {
                    $url = trim($matches[2], '/');
                    // Create full URL from value if the value does not contain a `://`
                    if(strpos($url, '://') === false && strpos($url, '#') !== 0) {
                        $url = str_replace('/#', '#', trim($config->url . '/' . $url, '/'));
                    }
                    $html .= '<li' . ($value == $current ? ' class="selected"' : "") . '><a href="' . $url . '">' . trim($matches[1]) . '</a>' . self::get($value, $type) . '</li>';

                } else {
                    $html .= '<li><a href="#">' . $key . '</a>' . self::get($value, $type) . '</li>';
                }
            } else {
                // Create full URL from value if the value does not contain a `://`
                if(strpos($value, '://') === false && strpos($value, '#') !== 0) {
                    $value = str_replace('/#', '#', trim($config->url . '/' . trim($value, '/'), '/'));
                }
                $html .= '<li' . ($value == $current ? ' class="selected"' : "") . '><a href="' . $value . '">' . $key . '</a></li>';
            }
        }
        return $html . '</' . $type . '>';
    }

}