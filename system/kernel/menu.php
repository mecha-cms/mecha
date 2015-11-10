<?php

/**
 * ====================================================================
 *  CONVERT ARRAY INTO MENU LIST
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    // Set
 *    Menu::set('my_menu', array(
 *        'Home' => '/',
 *        'About' => '/about',
 *        'Contact' => '/contact'
 *    ));
 *
 *    // Get
 *    echo Menu::get('my_menu');
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

    protected static $menus = array();

    public static $config = array(
        'classes' => array(
            'parent' => false,
            'child' => 'child child-%d',
            'current' => 'current',
            'separator' => 'separator'
        )
    );

    // Set
    public static function set($id, $array = array()) {
        self::$menus[$id] = $array;
    }

    // Get
    public static function get($id = null, $fallback = false) {
        if( ! is_null($id)) {
            return isset(self::$menus[$id]) ? self::$menus[$id] : $fallback;
        }
        return ! empty(self::$menus) ? self::$menus : $fallback;
    }

    // Reset
    public static function reset($id = null) {
        if( ! is_null($id)) {
            unset(self::$menus[$id]);
        } else {
            self::$menus = array();
        }
    }

    // Render as HTML
    public static function render($id = 'navigation', $type = 'ul', $indent = "") {
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
        $output = isset(self::$menus[$id]) ? Tree::grow(self::$menus[$id], $indent, $id . ':') : false;
        Tree::$config = $c; // reset to the previous state
        return $output;
    }

}