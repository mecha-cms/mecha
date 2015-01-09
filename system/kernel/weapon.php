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
     *  $fn        | mixed   | Hook function
     *  $stack     | float   | Hook function priority
     *  ---------- | ------- | --------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function add($name, $fn, $stack = 10) {
        self::$armaments[$name][] = array(
            'fn' => $fn,
            'stack' => (float) ( ! is_null($stack) ? $stack : 10)
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
            $weapons = Mecha::eat(self::$armaments[$name])->order('ASC', 'stack')->vomit();
            foreach($weapons as $weapon => $cargo) {
                call_user_func_array($cargo['fn'], $arguments);
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
     *  $stack    | float   | Hook function priority
     *  --------- | ------- | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function eject($name = null, $stack = null) {
        if( ! is_null($stack)) {
            $stack = (float) $stack;
        }
        if( ! is_null($name)) {
            if( ! is_null($stack)) {
                for($i = 0, $length = count(self::$armaments[$name]); $i < $length; ++$i) {
                    if(self::$armaments[$name][$i]['stack'] === $stack) {
                        unset(self::$armaments[$name][$i]);
                    }
                }
            } else {
                unset(self::$armaments[$name]);
            }
        } else {
            self::$armaments = array();
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