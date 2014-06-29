<?php

class Filter {

    protected static $filters = array();

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
     *  Parameter      | Type    | Description
     *  -------------- | ------- | ---------------------------------------
     *  $name          | string  | The name of the filter to hook
     *  $function      | mixed   | The name of the function to be called
     *  $priority      | integer | Priority of function to add
     *  $accepted_args | integer | The number of arguments function accept
     *  -------------- | ------- | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function add($name, $function, $priority = 10) {
        // Kill duplicates
        if(isset(self::$filters[$name]) && is_array(self::$filters[$name])) {
            foreach(self::$filters[$name] as $filter) {
                if($filter['function'] == $function) return true;
            }
        }
        self::$filters[$name][] = array(
            'function' => $function,
            'priority' => ! is_null($priority) ? (int) $priority : 10
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
     *  $name     | string | The name of the filter hook
     *  $value    | string | String of value to be manipulated
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function apply($name, $value) {
        $args = array_slice(func_get_args(), 1);
        if( ! isset(self::$filters[$name])) {
            self::$filters[$name] = false;
            return $value;
        }
        $filters = Mecha::eat(self::$filters[$name])->order('ASC', 'priority')->vomit();
        foreach($filters as $filter => $cargo) {
            $value = call_user_func_array($cargo['function'], $args);
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
     *  $name     | string | The name of the filter hook
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function remove($name = null) {
        if(is_null($name)) {
            self::$filters = array();
        } else {
            unset(self::$filters[$name]);
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
     *    var_dump(Filter::exist()); // inspect!
     *
     * -------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------
     *  $name     | string | The name of the filter hook
     *  $fallback | mixed  | Fallback value if name does not exist
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function exist($name = null, $fallback = false) {
        if(is_null($name)) {
            return ! empty(self::$filters) ? self::$filters : $fallback;
        }
        return isset(self::$filters[$name]) ? self::$filters[$name] : $fallback;
    }

}