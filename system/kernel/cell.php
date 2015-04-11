<?php

class Cell {

    protected static $attr_order = array(
        'src' => null,
        'alt' => null,
        'width' => null,
        'height' => null,
        'name' => null,
        'class' => null,
        'id' => null,
        'href' => null,
        'rel' => null,
        'target' => null,
        'type' => null,
        'action' => null,
        'method' => null,
        'enctype' => null,
        'value' => null,
        'placeholder' => null,
        'selected' => null,
        'checked' => null,
        'disabled' => null,
        'readonly' => null,
        'style' => null
    );

    protected static $o = array();
    protected static $tag = array();
    protected static $tag_indent = array();

    // Encode `&"'<>` then ignore the rest
    protected static function protect($value) {
        if( ! is_string($value)) return $value;
        return str_replace(
            array(
                '<',
                '>',
                '"',
                "'"
            ),
            array(
                '&lt;',
                '&gt;',
                '&quot;',
                '&#39;'
            ),
        // only stand-alone `&` (ampersand)
        preg_replace('#(?<=^|\s)&(?=\s|$)#', '&amp;', $value));
    }

    // Setup HTML attributes ...
    public static function bond($array) {
        $output = "";
        if( ! is_array($array)) {
            $array = array();
        }
        $array = array_replace(self::$attr_order, $array);
        // HTML5 `data-*` attributes
        if(isset($array['data']) && is_array($array['data'])) {
            foreach($array['data'] as $k => $v) {
                if( ! is_null($v)) {
                    $array['data-' . $k] = $v;
                }
            }
            unset($array['data']);
        }
        foreach($array as $attr => $value) {
            if( ! is_null($value)) {
                if(is_array($value)) {
                    // Inline CSS via `style` attributes
                    if($attr === 'style') {
                        $css = "";
                        foreach($value as $k => $v) {
                            if( ! is_null($v)) {
                                $css .= $k . ': ' . $v . '; ';
                            }
                        }
                        $value = rtrim($css);
                    } else {
                        $value = implode(' ', $value);
                    }
                }
                $q = is_string($value) && ! is_null(json_decode($value, true)) ? "'" : '"';
                $output .= ' ' . ($value !== true ? $attr . '=' . $q . $value . $q : $attr);
            }
        }
        return $output;
    }

    // Base HTML tag constructor
    public static function unit($tag = 'html', $content = "", $attr = array(), $indent = "") {
        if($content === false) return $indent . '<' . $tag . self::bond($attr) . ES;
        return $indent . '<' . $tag . self::bond($attr) . '>' . $content . '</' . $tag . '>';
    }

    // Base HTML tag open
    public static function begin($tag = 'html', $attr = array(), $indent = "") {
        self::$tag[] = $tag;
        self::$tag_indent[] = $indent;
        return $indent . '<' . $tag . self::bond($attr) . '>';
    }

    // Base HTML tag close
    public static function end($tag = null, $indent = null) {
        if(is_null($tag)) {
            $tag = ! empty(self::$tag) ? array_pop(self::$tag) : false;
        }
        if(is_null($indent)) {
            $indent = ! empty(self::$tag_indent) ? array_pop(self::$tag_indent) : "";
        }
        return $tag ? $indent . '</' . $tag . '>' : "";
    }

    // Show the added method(s)
    public static function kin($kin = null, $fallback = false) {
        $kin = ! is_null($kin) ? get_called_class() . '::' . $kin : false;
        if( ! $kin) {
            return ! empty(self::$o) ? self::$o : $fallback;
        }
        return isset(self::$o[$kin]) ? self::$o[$kin] : $fallback;
    }

    // Add new method with `Cell::add('foo')`
    public static function add($kin, $action) {
        self::$o[get_called_class() . '::' . $kin] = $action;
    }

    // Call the added method with `Cell::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        $_kin = get_called_class() . '::' . $kin;
        if( ! isset(self::$o[$_kin])) {
            $arguments = array_merge(array($kin), $arguments);
            return call_user_func_array('self::unit', $arguments);
        }
        return call_user_func_array(self::$o[$_kin], $arguments);
    }

}