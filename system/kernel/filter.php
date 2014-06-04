<?php

/**
 * =================================================================
 *  FILTER HOOKS
 *
 *  Stealed from this monster => https://github.com/Awilum/morfy-cms
 * =================================================================
 */

class Filter {

    private static $bucket = array();
    private static $filters = array();

    protected function __construct() {}

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

    public static function add($name, $function, $priority = 10, $accepted_args = 1) {

        $name = (string) $name;
        $priority = (int) $priority;
        $accepted_args = (int) $accepted_args;

        self::$filters[$name] = true;

        if(isset(self::$bucket[$name][$priority])) {
            foreach(self::$bucket[$name][$priority] as $filter) {
                if($filter['function'] == $function) return true;
            }
        }

        self::$bucket[$name][$priority][] = array(
            'function' => $function,
            'accepted_args' => $accepted_args
        );

        self::$bucket[$name] = Mecha::eat(self::$bucket[$name])->order('DESC')->vomit();

        return true;

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

        $name = (string) $name;
        $args = array_slice(func_get_args(), 2);

        if ( ! isset(self::$bucket[$name])) return $value;

        foreach(self::$bucket[$name] as $priority => $functions) {
            if( ! is_null($functions)) {
                foreach($functions as $function) {
                    $all_args = array_merge(array($value), $args);
                    $function_name = $function['function'];
                    $accepted_args = $function['accepted_args'];
                    if($accepted_args === 1) {
                        $the_args = array($value);
                    } elseif($accepted_args > 1) {
                        $the_args = array_slice($all_args, 0, $accepted_args);
                    } elseif($accepted_args === 0) {
                        $the_args = null;
                    } else {
                        $the_args = $all_args;
                    }
                    $value = call_user_func_array($function_name, $the_args);
                }
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
     *  $name     | string | The name of the filter hook
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function remove($name = null) {
        if(is_null($name)) {
            self::$bucket = array();
        } else {
            unset(self::$bucket[$name]);
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
     *  --------- | ------ | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function exist($name = null) {
        if(is_null($name)) {
            return ! empty(self::$filters) ? array_keys(self::$filters) : false;
        }
        return isset(self::$filters[$name]) ? self::$filters[$name] : false;
    }

}