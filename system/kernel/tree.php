<?php

/**
 * ====================================================================
 *  CONVERT ARRAY INTO HTML LIST WITH/WITHOUT LINK(S)
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    $seeds = array(
 *        'Example 1' => '/',
 *        'Example 2' => '#example',
 *        'Example 3' => '/example',
 *        'Example 4' => array(
 *            'Example 4.1' => '/example/example'
 *        ),
 *        'Example 5 (/parent)' => array(
 *            'Example 5.1' => '/parent/child-1',
 *            'Example 5.2' => '/parent/child-2'
 *        ),
 *        '|',
 *        'Text 1',
 *        'Text 2' => null
 *    );
 *
 *    Tree::$config['element']['trunk'] = 'ul';
 *    echo Tree::grow($seeds);
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $array    | array  | Array of tree item
 *  $indent   | string | Depth extra before each tree group/tree item
 *  $FP       | string | Filter prefix for the generated tree output
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Tree extends Base {

    public static $config = array(
        'elements' => array(
            'trunk' => 'ul',
            'branch' => 'ul',
            'twig' => 'li'
        ),
        'classes' => array(
            'trunk' => 'trunk',
            'branch' => 'branch branch-%d',
            'twig' => 'twig',
            'current' => 'current',
            'hole' => 'hole'
        )
    );

    protected static function create($array, $indent = "", $FP = "", $i = 0) {
        $c_url = Config::get('url');
        $c_url_current = Config::get('url_current');
        $c_element = self::$config['elements'];
        $c_class = self::$config['classes'];
        $html = $indent . str_repeat(TAB, $i) . '<' . $c_element[$i === 0 ? 'trunk' : 'branch'] . ($i === 0 ? ($c_class['trunk'] !== false ? ' class="' . $c_class['trunk'] . '"' : "") : ($c_class['branch'] !== false ? ' class="' . sprintf($c_class['branch'], $i / 2) . '"' : "")) . '>' . NL;
        foreach($array as $key => $value) {
            if( ! is_array($value)) {
                $url = Converter::url($value);
                $hole = $value === "" ? ' ' . $c_class['hole'] : "";
                $current = $url === $c_url_current || ($url !== $c_url && strpos($c_url_current . '/', $url . '/') === 0) ? ' ' . $c_class['current'] : "";
                $c = trim(($c_class['twig'] !== false ? $c_class['twig'] : "") . $hole . $current);
                $twig = '<' . $c_element['twig'] . ($c ? ' class="' . $c . '"' : "") . '>';
                if($value !== "") {
                    // List item without link: `array('foo')`
                    // List item without link: `array('foo' => null)`
                    if(is_int($key) || is_null($value)) {
                        $twig .= '<span class="a">' . $value . '</span>';
                    // List item with link: `array('foo' => '/')`
                    } else {
                        $url = Filter::apply($FP . 'url', Filter::apply('url', $url));
                        $twig .= '<a href="' . $url . '">' . $key . '</a>';
                    }
                }
                $s = explode(' ', $c_element['twig']);
                $s = $s[0];
                $html .= $indent . str_repeat(TAB, $i + 1) . Filter::apply($FP . 'twig', $twig . '</' . $s . '>', $i + 1) . NL;
            } else {
                // `text (path/to/url)`
                if(preg_match('#^\s*(.*?)\s*\((.*?)\)\s*$#', $key, $match)) {
                    $_key = $match[1];
                    $_value = trim($match[2]) !== "" ? Converter::url($match[2]) : '#';
                } else {
                    $_key = $key;
                    $_value = null;
                }
                $url = Filter::apply($FP . 'url', Filter::apply('url', $_value));
                $s = explode(' ', $c_element['branch']);
                $s = ' ' . $s[0];
                $current = $url === $c_url_current || ($url !== $c_url && strpos($c_url_current . '/', $url . '/') === 0) ? ' ' . $c_class['current'] : "";
                $c = trim(($c_class['twig'] !== false ? $c_class['twig'] : "") . $current . $s);
                $twig = '<' . $c_element['twig'] . ($c ? ' class="' . $c . '"' : "") . '>';
                $twig .= NL . $indent . str_repeat(TAB, $i + 2);
                $twig .= $_value !== null ? '<a href="' . $url . '">' . $_key . '</a>' : '<span class="a">' . $_key . '</span>';
                $twig .= NL . self::create($value, $indent, $FP, $i + 2);
                $twig .= $indent . str_repeat(TAB, $i + 1);
                $s = explode(' ', $c_element['twig']);
                $s = $s[0];
                $html .= $indent . str_repeat(TAB, $i + 1) . Filter::apply($FP . 'twig', $twig . '</' . $s . '>', $i + 1) . NL;
            }
        }
        $s = explode(' ', $c_element[$i === 0 ? 'trunk' : 'branch']);
        $s = $s[0];
        return Filter::apply($FP . 'branch', rtrim($html, NL) . ( ! empty($array) ? NL . $indent . str_repeat(TAB, $i) : "") . '</' . $s . '>', $i) . NL;
    }

    public static function grow($array = null, $indent = "", $FP = 'tree:') {
        return O_BEGIN . Filter::apply($FP . 'trunk', rtrim(self::create($array, $indent, $FP, 0), NL)) . O_END;
    }

}