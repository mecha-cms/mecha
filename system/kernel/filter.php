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
     *  Parameter  | Type    | Description
     *  ---------- | ------- | -------------------------------------------
     *  $name      | string  | Filter name
     *  $function  | mixed   | Filter function
     *  $priority  | integer | Filter function priority
     *  ---------- | ------- | -------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function add($name, $function, $priority = 10) {
        // Kill duplicates
        if(isset(self::$filters[$name]) && is_array(self::$filters[$name])) {
            foreach(self::$filters[$name] as $filter) {
                if($filter['function'] == $function && $filter['priority'] === $priority) return true;
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
     *  $name     | string | Filter name
     *  $value    | string | String to be manipulated
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function apply($name, $value) {
        if( ! isset(self::$filters[$name])) {
            self::$filters[$name] = false;
            return $value;
        }
        $filters = Mecha::eat(self::$filters[$name])->order('ASC', 'priority')->vomit();
        foreach($filters as $filter => $cargo) {
            $arguments = array($value) + array_slice(func_get_args(), 2);
            $value = call_user_func_array($cargo['function'], $arguments);
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
     *  Parameter | Type    | Description
     *  --------- | ------- | --------------------------------------------
     *  $name     | string  | Filter name
     *  $priority | integer | Filter function priority
     *  --------- | ------- | --------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function remove($name = null, $priority = null) {
        if(is_null($name)) {
            self::$filters = array();
        } else {
            if(is_null($priority)) {
                unset(self::$filters[$name]);
            } else {
                for($i = 0, $length = count(self::$filters[$name]); $i < $length; ++$i) {
                    if(self::$filters[$name][$i]['priority'] === $priority) {
                        unset(self::$filters[$name][$i]);
                    }
                }
            }
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
     *  $name     | string | Filter name
     *  $fallback | mixed  | Fallback value if filter does not exist
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