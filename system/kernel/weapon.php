<?php

class Weapon extends Base {

    protected static $armaments = array();
    protected static $armaments_x = array();

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
        $c = get_called_class();
        $stack = ! is_null($stack) ? $stack : 10;
        if( ! isset(self::$armaments_x[$c][$name . '->' . $stack])) {
            if( ! isset(self::$armaments[$c][$name])) {
                self::$armaments[$c][$name] = array();
            }
            self::$armaments[$c][$name][] = array(
                'fn' => $fn,
                'stack' => (float) $stack
            );
        }
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
        $c = get_called_class();
        if(isset(self::$armaments[$c][$name])) {
            if(func_num_args() > 2) {
                $arguments = array_slice(func_get_args(), 1);
            }
            $weapons = Mecha::eat(self::$armaments[$c][$name])->order('ASC', 'stack')->vomit();
            foreach($weapons as $weapon => $cargo) {
                call_user_func_array($cargo['fn'], $arguments);
            }
        } else {
            self::$armaments[$c][$name] = array();
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
        $c = get_called_class();
        if( ! is_null($name)) {
            self::$armaments_x[$c][$name . '->' . ( ! is_null($stack) ? $stack : 10)] = 1;
            if( ! isset(self::$armaments[$c][$name])) return;
            if( ! is_null($stack)) {
                for($i = 0, $count = count(self::$armaments[$c][$name]); $i < $count; ++$i) {
                    if(self::$armaments[$c][$name][$i]['stack'] === (float) $stack) {
                        unset(self::$armaments[$c][$name][$i]);
                    }
                }
            } else {
                unset(self::$armaments[$c][$name]);
            }
        } else {
            self::$armaments[$c] = array();
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
        $c = get_called_class();
        if(is_null($name)) {
            return ! empty(self::$armaments[$c]) ? self::$armaments[$c] : $fallback;
        }
        return isset(self::$armaments[$c][$name]) ? self::$armaments[$c][$name] : $fallback;
    }

}