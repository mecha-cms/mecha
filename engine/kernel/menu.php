<?php

/**
 * ====================================================================
 *  CONVERT ARRAY INTO MENU LIST
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    // Add
 *    Menu::add('my_menu', array(
 *        'Home' => '/',
 *        'About' => '/about',
 *        'Contact' => '/contact'
 *    ));
 *
 *    // Render
 *    echo Menu::my_menu();
 *
 * --------------------------------------------------------------------
 *
 */

class Menu extends __ {

    protected static $menus = array();
    protected static $menus_x = array();

    public static $config = array(
        'classes' => array(
            'parent' => 'parent',
            'child' => 'child child-%d',
            'current' => 'current',
            'separator' => 'separator'
        )
    );

    // Add
    public static function add($id, $array = array()) {
        $c = get_called_class();
        if( ! isset(self::$menus_x[$c][$id])) {
            self::$menus[$c][$id] = $array;
        }
    }

    // Remove
    public static function remove($id = null) {
        $c = get_called_class();
        self::$menus_x[$c][$id] = isset(self::$menus[$c][$id]) ? self::$menus[$c][$id] : 1;
        if( ! is_null($id)) {
            unset(self::$menus[$c][$id]);
        } else {
            self::$menus[$c] = array();
        }
    }

    // Check
    public static function exist($id = null, $fallback = false) {
        $c = get_called_class();
        if( ! is_null($id)) {
            return isset(self::$menus[$c][$id]) ? self::$menus[$c][$id] : $fallback;
        }
        return ! empty(self::$menus[$c]) ? self::$menus[$c] : $fallback;
    }

    // Render as HTML
    public static function __callStatic($id, $arguments = array()) {
        $c = get_called_class();
        $d = Tree::$config;
        $dd = self::$config['classes'];
        if( ! isset(self::$menus[$c][$id])) {
            return false;
        }
        $AD = array('ul', "", $id . ':');
        $arguments = Mecha::extend($AD, $arguments);
        $type = $arguments[0];
        $arguments[0] = Filter::apply('menu:input', self::$menus[$c][$id], $id);
        if( ! is_array($arguments[0])) return "";
        Tree::$config['trunk'] = $type;
        Tree::$config['branch'] = $type;
        Tree::$config['twig'] = 'li';
        Tree::$config['classes']['trunk'] = $dd['parent'];
        Tree::$config['classes']['branch'] = $dd['child'];
        Tree::$config['classes']['twig'] = false;
        Tree::$config['classes']['current'] = $dd['current'];
        Tree::$config['classes']['chink'] = $dd['separator'];
        $output = call_user_func_array('Tree::grow', $arguments);
        Tree::$config = $d; // reset to the previous state
        return Filter::apply('menu:output', $output, $id);
    }

}