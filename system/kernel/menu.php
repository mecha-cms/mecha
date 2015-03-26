<?php

/**
 * ====================================================================
 *  CONVERT ARRAY INTO HTML LIST WITH LINKS
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
 *        )
 *    );
 *
 *    echo Menu::get($array, 'ul');
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type    | Description
 *  --------- | ------- | ---------------------------------------------
 *  $array    | array   | Array of menu
 *  $type     | string  | The list type ... `<ul>` or `<ol>` ?
 *  $FP       | string  | Filter prefix for the generated HTML output
 *  $depth    | integer | Starting depth
 *  --------- | ------- | ---------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Menu {

    public static $config = array(
        'classes' => array(
            'selected' => 'selected',
            'children' => 'children-%s',
            'parent' => false,
            'separator' => 'separator'
        )
    );

    public static function create($array, $type, $FP, $depth, $depth_extra = "") {
        $c_url = Config::get('url');
        $c_url_current = Config::get('url_current');
        $c_class = self::$config['classes'];
        $html = $depth_extra . str_repeat(TAB, $depth) . '<' . $type . ($depth > 0 ? ($c_class['children'] !== false ? ' class="' . sprintf($c_class['children'], $depth / 2) . '"' : "") : ($c_class['parent'] !== false ? ' class="' . $c_class['parent'] . '"' : "")) . '>' . NL;
        foreach($array as $title => $url) {
            if( ! is_array($url)) {
                if(strpos($url, '#') !== 0 && strpos($url, '://') === false) {
                    $url = preg_replace('#\/([\#?&])#', '$1', trim($c_url . '/' . trim($url, '/'), '/'));
                }
                if($title === '|') {
                    $html .= Filter::apply($FP . 'list.item.separator', Filter::apply($FP . 'list.item', $depth_extra . str_repeat(TAB, $depth + 1) . '<li class="' . $c_class['separator'] . '"></li>' . NL, $depth + 1), $depth + 1);
                } else {
                    $html .= Filter::apply($FP . 'list.item', $depth_extra . str_repeat(TAB, $depth + 1) . '<li' . ($url == $c_url_current || ($url != $c_url && strpos($c_url_current . '/', $url . '/') === 0) ? ' class="' . $c_class['selected'] . '"' : "") . '><a href="' . $url . '">' . $title . '</a></li>' . NL, $depth + 1);
                }
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
                $html .= Filter::apply($FP . 'list.item', $depth_extra . str_repeat(TAB, $depth + 1) . '<li' . ($_url == $c_url_current || ($_url != $c_url && strpos($c_url_current . '/', $_url . '/') === 0) ? ' class="' . $c_class['selected'] . '"' : "") . '><a href="' . $_url . '">' . $_title . '</a>' . NL . self::create($url, $type, $FP, $depth + 2) . $depth_extra . str_repeat(TAB, $depth + 1) . '</li>' . NL, $depth + 1);
            }
        }
        return Filter::apply($FP . 'list', $html . $depth_extra . str_repeat(TAB, $depth) . '</' . $type . '>' . NL, $depth);
    }

    public static function get($array = null, $type = 'ul', $FP = 'menu:', $depth_extra = "") {
        // Use menu file from the cabinet when `$array` is not defined
        if(is_null($array)) {
            $speak = Config::speak();
            $FP = 'navigation:';
            $array = Text::toArray(Get::state_menu($speak->home . ": /\n" . $speak->feed . ": /feed"), ':', '    ');
        }
        return O_BEGIN . rtrim(self::create($array, $type, $FP, 0, $depth_extra), NL) . O_END;
    }

    // Configure ...
    public static function configure($key, $value = null) {
        if(is_array($key)) {
            Mecha::extend(self::$config, $key);
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