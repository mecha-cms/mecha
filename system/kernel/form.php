<?php

class Form extends Cell {

    // `<input>`
    public static function input($type = 'text', $name = null, $value = null, $placeholder = null, $attr = array(), $indent = "") {
        $attr['name'] = $name;
        $attr['value'] = self::protect($value);
        $attr['placeholder'] = self::protect($placeholder);
        $attr['type'] = $type;
        return self::unit('input', false, $attr, $indent);
    }

    // `<button>`
    public static function button($text = "", $name = null, $value = null, $type = null, $attr = array(), $indent = "") {
        $attr['name'] = $name;
        $attr['type'] = $type;
        $attr['value'] = $value;
        return self::unit('button', $text, $attr, $indent);
    }

    // `<select>`
    public static function select($name = null, $option = array(), $select = null, $attr = array(), $indent = "") {
        $o = "";
        $attr['name'] = $name;
        foreach($option as $key => $value) {
            $key_o = ltrim($key, '.');
            $attr_o = array('value' => $key);
            if(strpos($key, '.') === 0) {
                $attr_o['disabled'] = true;
                $attr_o['value'] = $key_o;
            }
            if(ltrim($select, '.') === $key_o) {
                $attr_o['selected'] = true;
            }
            $o .= NL . self::unit('option', self::protect($value), $attr_o, $indent . TAB);
        }
        return self::unit('select', $o . NL . $indent, $attr, $indent);
    }

    // `<textarea>`
    public static function textarea($name = null, $content = "", $placeholder = null, $attr = array(), $indent = "") {
        $attr['name'] = $name;
        $attr['placeholder'] = $placeholder;
        return self::unit('textarea', self::protect($content), $attr, $indent);
    }

}