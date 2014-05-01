<?php

/**
 * ==================================================================
 *  FUNCTION HOOKS
 *
 *  Stealed from this monster => https://github.com/Awilum/morfy-cms
 * ==================================================================
 */

class Weapon {

    public static $armaments = array();

    /**
     * ==============================================================
     *  ADD A WEAPON
     * ==============================================================
     *
     * -- CODE: -----------------------------------------------------
     *
     *    Weapon::add('tank', function() {
     *        echo 'Tank added!';
     *    });
     *
     *    Weapon::add('jet', function($color) {
     *        return $color . ' jet added!';
     *    });
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | --------------------------------------
     *  $name      | string  | Action name
     *  $function  | mixed   | Added function
     *  $priority  | integer | Function's priority
     *  $arguments | array   | Function's arguments
     *  ---------- | ------- | --------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function add($name, $function, $priority = 10, array $arguments = null) {
        self::$armaments[] = array(
            'name' => (string) $name,
            'function' => $function,
            'priority' => (int) $priority,
            'arguments' => $arguments
        );
    }

    /**
     * ==============================================================
     *  FIRE !!!
     * ==============================================================
     *
     * -- CODE: -----------------------------------------------------
     *
     *    Weapon::fire('tank');
     *
     *    echo Weapon::fire('jet', array('Blue'), true);
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | --------------------------------------
     *  $name      | string  | Function name
     *  $arguments | array   | Function arguments
     *  $return    | boolean | Return data or not?
     *  ---------- | ------- | --------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function fire($name, $arguments = array(), $return = false) {
        $name = (string) $name;
        $return = (bool) $return;
        if(count(self::$armaments) > 0) {
            // Sort by priority
            self::$armaments = Mecha::eat(self::$armaments)->order('DESC', 'priority')->vomit();
            foreach(self::$armaments as $weapon) {
                if($weapon['name'] == $name) {
                    if(isset($arguments)) {
                        // Return or render specific action results?
                        if($return) {
                            return call_user_func_array($weapon['function'], $arguments);
                        } else {
                            call_user_func_array($weapon['function'], $arguments);
                        }
                    } else {
                        if($return) {
                            return call_user_func_array($weapon['function'], $weapon['arguments']);
                        } else {
                            call_user_func_array($weapon['function'], $weapon['arguments']);
                        }
                    }
                }
            }
        }
    }
}