<?php

class Filter extends Base {

    protected static $filters = array();
    protected static $filters_x = array();

    /**
     * ===================================================================
     *  ADD FILTER
     * ===================================================================
     *
     * -- CODE: ----------------------------------------------------------
     *
     *    Filter::add('content', function($content) {
     *        return str_replace('foo', 'bar', $content);
     *    });
     *
     * -------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------
     *  $name     | string | Filter name
     *  $fn       | mixed  | Filter function
     *  $stack    | float  | Filter function priority
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function add($name, $fn, $stack = 10) {
        $c = get_called_class();
        if( ! isset(self::$filters[$c][$name])) {
            self::$filters[$c][$name] = array();
        }
        self::$filters[$c][$name][] = array(
            'fn' => $fn,
            'stack' => (float) ( ! is_null($stack) ? $stack : 10)
        );
    }

    /**
     * ===================================================================
     *  APPLY FILTER
     * ===================================================================
     *
     * -- CODE: ----------------------------------------------------------
     *
     *    Filter::apply('content', $content);
     *
     * -------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------
     *  $name     | string | Filter name
     *  $value    | string | String to be manipulated
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function apply($name, $value) {
        $c = get_called_class();
        if( ! isset(self::$filters[$c][$name])) {
            self::$filters[$c][$name] = array();
            return $value;
        }
        $params = array_slice(func_get_args(), 2);
        $filters = Mecha::eat(self::$filters[$c][$name])->order('ASC', 'stack')->vomit();
        foreach($filters as $filter => $cargo) {
            if( ! isset(self::$filters_x[$c][$name . '->' . $cargo['stack']])) {
                $arguments = array_merge(array($value), $params);
                $value = call_user_func_array($cargo['fn'], $arguments);
            }
        }
        return $value;
    }

    /**
     * ===================================================================
     *  REMOVE FILTER
     * ===================================================================
     *
     * -- CODE: ----------------------------------------------------------
     *
     *    Filter::remove('content');
     *
     * -------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------
     *  $name     | string | Filter name
     *  $stack    | float  | Filter function priority
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function remove($name = null, $stack = null) {
        $c = get_called_class();
        if( ! is_null($name)) {
            self::$filters_x[$c][$name . '->' . ( ! is_null($stack) ? $stack : 10)] = 1;
            if( ! isset(self::$filters[$c][$name])) return;
            if( ! is_null($stack)) {
                for($i = 0, $count = count(self::$filters[$c][$name]); $i < $count; ++$i) {
                    if(self::$filters[$c][$name][$i]['stack'] === (float) $stack) {
                        unset(self::$filters[$c][$name][$i]);
                    }
                }
            } else {
                unset(self::$filters[$c][$name]);
            }
        } else {
            self::$filters[$c] = array();
        }
    }

    /**
     * ===================================================================
     *  CHECK IF FILTER ALREADY EXIST/ADDED
     * ===================================================================
     *
     * -- CODE: ----------------------------------------------------------
     *
     *    if(Filter::exist('content')) {
     *        echo 'OK.';
     *    }
     *
     * -------------------------------------------------------------------
     *
     *    var_dump(Filter::exist()); // inspect!
     *
     * -------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------
     *  $name     | string | Filter name
     *  $fallback | mixed  | Fallback value if filter does not exist
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function exist($name = null, $fallback = false) {
        $c = get_called_class();
        if(is_null($name)) {
            return ! empty(self::$filters[$c]) ? self::$filters[$c] : $fallback;
        }
        return isset(self::$filters[$c][$name]) ? self::$filters[$c][$name] : $fallback;
    }

}