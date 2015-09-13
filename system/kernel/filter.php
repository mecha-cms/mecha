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
        if( ! isset(self::$filters[get_called_class()][$name])) {
            self::$filters[get_called_class()][$name] = array();
        }
        self::$filters[get_called_class()][$name][] = array(
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
        if( ! isset(self::$filters[get_called_class()][$name])) {
            self::$filters[get_called_class()][$name] = array();
            return $value;
        }
        $params = array_slice(func_get_args(), 2);
        $filters = Mecha::eat(self::$filters[get_called_class()][$name])->order('ASC', 'stack')->vomit();
        foreach($filters as $filter => $cargo) {
            if( ! isset(self::$filters_x[get_called_class()][$name . '->' . $cargo['stack']])) {
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
        if( ! is_null($name)) {
            self::$filters_x[get_called_class()][$name . '->' . ( ! is_null($stack) ? $stack : 10)] = 1;
            if( ! isset(self::$filters[get_called_class()][$name])) return;
            if( ! is_null($stack)) {
                for($i = 0, $count = count(self::$filters[get_called_class()][$name]); $i < $count; ++$i) {
                    if(self::$filters[get_called_class()][$name][$i]['stack'] === (float) $stack) {
                        unset(self::$filters[get_called_class()][$name][$i]);
                    }
                }
            } else {
                unset(self::$filters[get_called_class()][$name]);
            }
        } else {
            self::$filters[get_called_class()] = array();
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
        if(is_null($name)) {
            return ! empty(self::$filters[get_called_class()]) ? self::$filters[get_called_class()] : $fallback;
        }
        return isset(self::$filters[get_called_class()][$name]) ? self::$filters[get_called_class()][$name] : $fallback;
    }

}