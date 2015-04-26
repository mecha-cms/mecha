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

    // Encode all HTML entities
    public static function protect($value) {
        if( ! is_string($value)) return $value;
        return Text::parse($value, '->encoded_html');
    }

    // Setup HTML attributes ...
    public static function bond($array) {
        if(is_string($array)) {
            $attr = trim($array);
            return strlen($attr) ? ' ' . $attr : "";
        }
        $output = "";
        $array = array_replace(self::$attr_order, $array);
        // HTML5 `data-*` attribute
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
                    // Inline CSS via `style` attribute
                    if($attr === 'style') {
                        $css = "";
                        foreach($value as $k => $v) {
                            if( ! is_null($v)) {
                                $css .= $k . ': ' . str_replace('"', '&quot;', $v) . '; ';
                            }
                        }
                        $value = rtrim($css);
                    } else {
                        $value = implode(' ', $value);
                    }
                }
                $q = is_string($value) && strpos($value, '"') !== false ? "'" : '"';
                $output .= ' ' . ($value !== true ? $attr . '=' . $q . $value . $q : $attr);
            }
        }
        return $output;
    }

    // Base HTML tag constructor
    public static function unit($tag = 'html', $content = "", $attr = array(), $indent = 0) {
        $indent = $indent ? str_repeat(TAB, $indent) : "";
        if($content === false) {
            return $indent . '<' . $tag . self::bond($attr) . ES;
        }
        return $indent . '<' . $tag . self::bond($attr) . '>' . $content . '</' . $tag . '>';
    }

    // Base HTML tag open
    public static function begin($tag = 'html', $attr = array(), $indent = 0) {
        $indent = $indent ? str_repeat(TAB, $indent) : "";
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
        } else {
            $indent = $indent ? str_repeat(TAB, $indent) : "";
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