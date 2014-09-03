<?php

class Weapon {

    protected static $armaments = array();

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
     *    Weapon::add('jet', function($color, $version) {
     *        echo $color . ' version ' . $version . ' jet added!';
     *    });
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | --------------------------------------
     *  $name      | string  | Hook name
     *  $function  | mixed   | Hook function
     *  $priority  | float   | Hook function priority
     *  ---------- | ------- | --------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function add($name, $function, $priority = 10) {
        self::$armaments[$name][] = array(
            'function' => $function,
            'priority' => (float) ( ! is_null($priority) ? $priority : 10)
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
     *    Weapon::fire('jet', array('Blue', '1.1.0'));
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | --------------------------------------
     *  $name      | string  | Hook name
     *  $arguments | array   | Hook function arguments
     *  ---------- | ------- | --------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function fire($name, $arguments = array()) {
        if(count(func_get_args()) > 2) {
            $arguments = array_slice(func_get_args(), 1);
        }
        if(isset(self::$armaments[$name])) {
            $weapons = Mecha::eat(self::$armaments[$name])->order('ASC', 'priority')->vomit();
            foreach($weapons as $weapon => $cargo) {
                call_user_func_array($cargo['function'], $arguments);
            }
        } else {
            self::$armaments[$name] = false;
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
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------
     *  $name     | string  | Hook name
     *  $priority | float   | Hook function priority
     *  --------- | ------- | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function eject($name = null, $priority = null) {
        if( ! is_null($priority)) {
            $priority = (float) $priority;
        }
        if(is_null($name)) {
            self::$armaments = array();
        } else {
            if(is_null($priority)) {
                unset(self::$armaments[$name]);
            } else {
                for($i = 0, $length = count(self::$armaments[$name]); $i < $length; ++$i) {
                    if(self::$armaments[$name][$i]['priority'] === $priority) {
                        unset(self::$armaments[$name][$i]);
                    }
                }
            }
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
     *  $name     | string | Hook name
     *  $fallback | mixed  | Fallback value if hook does not exist
     *  --------- | ------ | ----------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function exist($name = null, $fallback = false) {
        if(is_null($name)) {
            return ! empty(self::$armaments) ? self::$armaments : $fallback;
        }
        return isset(self::$armaments[$name]) ? self::$armaments[$name] : $fallback;
    }

}