<?php

/**
 * ==================================================================
 *  FUNCTION HOOKS
 *
 *  Stealed from this monster => https://github.com/Awilum/morfy-cms
 * ==================================================================
 */

class Weapon {

    private static $armaments = array();
    private static $mounters = array();

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
     *  $name      | string  | Action name
     *  $arguments | array   | Function arguments
     *  $return    | boolean | Return data or not?
     *  ---------- | ------- | --------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function fire($name, $arguments = array(), $return = false) {
        $name = (string) $name;
        $return = (bool) $return;
        self::$mounters[$name] = true;
        if(count(self::$armaments) > 0) {
            // Sort by priority
            self::$armaments = Mecha::eat(self::$armaments)->order('ASC', 'priority')->vomit();
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

    /**
     * ==============================================================
     *  EJECT
     * ==============================================================
     *
     * -- CODE: -----------------------------------------------------
     *
     *    Weapon::eject('bazooka');
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------
     *  $name     | string | Action name
     *  --------- | ------ | ----------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function eject($name = null) {
        if(is_null($name)) {
            self::$armaments = array();
        } else {
            foreach(self::$armaments as $armament) {
                if($armament['name'] == (string) $name) {
                    unset($armament);
                }
            }
            unset(self::$mounters[$name]);
        }
    }

    /**
     * ==============================================================
     *  CHECK IF WEAPON ALREADY EXIST/MOUNTED
     * ==============================================================
     *
     * -- CODE: -----------------------------------------------------
     *
     *    if(Weapon::exist('bazooka')) {
     *        echo 'You are safe. And you are a terrorist.';
     *    }
     *
     *    var_dump(Weapon::exist()); // inspect!
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------
     *  $name     | string | Action name
     *  --------- | ------ | ----------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function exist($name = null) {
        if(is_null($name)) {
            return ! empty(self::$mounters) ? array_keys(self::$mounters) : false;
        }
        return isset(self::$mounters[$name]) ? self::$mounters[$name] : false;
    }

}