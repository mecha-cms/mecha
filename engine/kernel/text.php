<?php

class Text extends __ {

    protected static $text = array();
    protected static $parser = array();

    /**
     * =====================================================================
     *  TEXT PARSER CREATOR
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    Text::parser('to_upper_case', function($input) {
     *        return strtoupper($input);
     *    });
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function parser($name, $action) {
        $name = strtolower($name);
        if(strpos($name, 'to_') !== 0) $name = 'to_' . $name;
        self::$parser[get_called_class()][$name] = $action;
    }

    /**
     * =====================================================================
     *  TEXT PARSER EXISTENCE
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if( ! Text::parserExist('to_upper_case')) {
     *        Text::parser('to_upper_case', function($input) { ... });
     *    }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function parserExist($name = null, $fallback = false) {
        $c = get_called_class();
        if(is_null($name)) {
            return isset(self::$parser[$c]) && ! empty(self::$parser[$c]) ? self::$parser[$c] : $fallback;
        }
        $name = strtolower($name);
        if(strpos($name, 'to_') !== 0) $name = 'to_' . $name;
        return isset(self::$parser[$c][$name]) ? self::$parser[$c][$name] : $fallback;
    }

    /**
     * =====================================================================
     *  TEXT PARSER OUTPUT
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    var_dump(Text::parse($input));
     *
     * ---------------------------------------------------------------------
     *
     *    echo Text::parse($input)->to_upper_case;
     *
     * ---------------------------------------------------------------------
     *
     *    echo Text::parse($input, '->upper_case');
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function parse() {
        $arguments = func_get_args();
        $c = get_called_class();
        // Alternate function for faster parsing process => `Text::parse('foo', '->html')`
        if(count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $parser = str_replace('->', 'to_', strtolower($arguments[1]));
            unset($arguments[1]);
            return isset(self::$parser[$c][$parser]) ? call_user_func_array(self::$parser[$c][$parser], $arguments) : $arguments[0];
        }
        // Default function for complete parsing process => `Text::parse('foo')->to_html`
        $results = array();
        if( ! isset(self::$parser[$c])) {
            self::$parser[$c] = array();
        }
        foreach(self::$parser[$c] as $name => $action) {
            $results[$name] = call_user_func_array($action, $arguments);
        }
        return (object) $results;
    }

    /**
     * =====================================================================
     *  INITIALIZE THE TEXT CHECKER
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    Text::check($text)-> ...
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function check($text) {
        self::$text = is_array($text) ? $text : func_get_args();
        return new static;
    }

    /**
     * =====================================================================
     *  CHECK IF TEXT CONTAIN(S) `A` AND `B`
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if(Text::check($text)->has('A', 'B')) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function has($text) {
        $arguments = is_array($text) ? $text : func_get_args();
        if(count($arguments) === 1) {
            return strpos(self::$text[0], $arguments[0]) !== false;
        }
        $text_v = 0;
        foreach($arguments as $v) {
            if(strpos(self::$text[0], $v) !== false) {
                $text_v++;
            }
        }
        return $text_v === count($arguments);
    }

    /**
     * =====================================================================
     *  CHECK IF TEXT CONTAIN(S) `A` OR `B`
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if(Text::check('A', 'B')->in($text)) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function in($text) {
        $arguments = is_array($text) ? $text : func_get_args();
        if(count(self::$text) === 1) {
            if(count($arguments) === 1) {
                if($arguments[0] === "") return self::$text[0] === "";
                if(self::$text[0] === "") return $arguments[0] === "";
                return strpos($arguments[0], self::$text[0]) !== false;
            }
            foreach($arguments as $v) {
                if(strpos(self::$text[0], $v) !== false) return true;
            }
        }
        foreach(self::$text as $v) {
            if(strpos($arguments[0], $v) !== false) return true;
        }
        return false;
    }

    /**
     * =====================================================================
     *  CHECK OFFSET OF A STRING INSIDE A TEXT
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if(Text::check($text)->offset('A')->start === 0) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function offset($text) {
        $output = array('start' => -1, 'end' => -1);
        if(($offset = strpos(self::$text[0], $text)) !== false) {
            $output['start'] = $offset;
            $output['end'] = $offset + strlen($text) - 1;
        }
        return (object) $output;
    }

}