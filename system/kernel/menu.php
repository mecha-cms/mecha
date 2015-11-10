<?php

/**
 * ====================================================================
 *  CONVERT ARRAY INTO MENU LIST
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    echo Menu::get(array(
 *        'Home' => '/',
 *        'About' => '/about',
 *        'Contact' => '/contact'
 *    ));
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $array    | array  | Array of list item
 *  $$type    | string | Type of the list item `<ol>` or `<ul>` ?
 *  $indent   | string | Depth extra before each list group/list item
 *  $FP       | string | Filter prefix for the generated HTML output
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Menu extends Base {

    public static $config = array(
        'classes' => array(
            'parent' => false,
            'child' => 'child child-%d',
            'current' => 'current',
            'separator' => 'separator'
        )
    );

    public static function get($array = null, $type = 'ul', $indent = "", $FP = 'menu:') {
        if(is_null($array)) {
            $FP = 'navigation:';
            $array = Get::state_menu();
        }
        $c = Tree::$config;
        $cc = self::$config['classes'];
        Tree::$config['elements']['trunk'] = $type;
        Tree::$config['elements']['branch'] = $type;
        Tree::$config['elements']['twig'] = 'li';
        Tree::$config['classes']['trunk'] = $cc['parent'];
        Tree::$config['classes']['branch'] = $cc['child'];
        Tree::$config['classes']['twig'] = false;
        Tree::$config['classes']['current'] = $cc['current'];
        Tree::$config['classes']['hole'] = $cc['separator'];
        $output = Tree::grow($array, $indent, $FP);
        Tree::$config = $c;
        return $output;
    }

}