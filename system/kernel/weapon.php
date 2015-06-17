<?php

class Weapon extends Base {

    protected static $armaments = array();
    protected static $armaments_e = array();

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
     * --------------------------------------------------------------
     *
     *    Weapon::add('jet', function($color, $version) {
     *        echo $color . ' version ' . $version . ' jet added!';
     *    });
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------
     *  $name     | string | Hook name
     *  $fn       | mixed  | Hook function
     *  $stack    | float  | Hook function priority
     *  --------- | ------ | ----------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function add($name, $fn, $stack = 10) {
        $name = get_called_class() . '::' . $name;
        if( ! isset(self::$armaments[$name])) {
            self::$armaments[$name] = array();
        }
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
     * --------------------------------------------------------------
     *
     *    Weapon::fire('jet', array('Blue', '1.1.0'));
     *
     * --------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------
     *  $name      | string | Hook name
     *  $arguments | array  | Hook function argument(s)
     *  ---------- | ------ | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function fire($name, $arguments = array()) {
        $name = get_called_class() . '::' . $name;
        if(isset(self::$armaments[$name])) {
            if(func_num_args() > 2) {
                $arguments = array_slice(func_get_args(), 1);
            }
            $weapons = Mecha::eat(self::$armaments[$name])->order('ASC', 'stack')->vomit();
            foreach($weapons as $weapon => $cargo) {
                if( ! isset(self::$armaments_e[$name . '->' . $cargo['stack']])) {
                    call_user_func_array($cargo['fn'], $arguments);
                }
            }
        } else {
            self::$armaments[$name] = array();
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
     *  $name     | string | Hook name
     *  $stack    | float  | Hook function priority
     *  --------- | ------ | ----------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function eject($name = null, $stack = null) {
        $name = ! is_null($name) ? get_called_class() . '::' . $name : false;
        if($name) {
            self::$armaments_e[$name . '->' . ( ! is_null($stack) ? $stack : 10)] = 1;
            if( ! isset(self::$armaments[$name])) return;
            if( ! is_null($stack)) {
                for($i = 0, $count = count(self::$armaments[$name]); $i < $count; ++$i) {
                    if(self::$armaments[$name][$i]['stack'] === (float) $stack) {
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
     * --------------------------------------------------------------
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
        $name = ! is_null($name) ? get_called_class() . '::' . $name : false;
        if( ! $name) {
            return ! empty(self::$armaments) ? self::$armaments : $fallback;
        }
        return isset(self::$armaments[$name]) ? self::$armaments[$name] : $fallback;
    }

}