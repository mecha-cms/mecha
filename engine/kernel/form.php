<?php

class Form extends HTML {

    // `<button>`
    public static function button($name = null, $value = null, $text = "", $attr = [], $dent = 0) {
        if (!array_key_exists('type', $attr)) {
            $attr['type'] = 'button';
        }
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        } else if (strpos($name, '\\.') === 0) { // escaped
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['value'] = $value;
        return self::unite('button', $text, Anemon::extend($attr_o, $attr), $dent);
    }

    // `<input>`
    public static function input($name = null, $type = 'text', $value = null, $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        } else if (strpos($name, '\\.') === 0) { // escaped
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['value'] = Request::restore('post', $name, $value);
        $attr_o['placeholder'] = $placeholder;
        $attr_o['type'] = $type;
        return self::unite('input', false, Anemon::extend($attr_o, $attr), $dent);
    }

    // `<select>`
    public static function select($name = null, $option = [], $select = null, $attr = [], $dent = 0) {
        $o = "";
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        } else if (strpos($name, '\\.') === 0) { // escaped
            $name = substr($name, 1);
        }
        $select = (string) Request::restore('post', $name, $select);
        $attr_o['name'] = $name;
        Anemon::extend($attr_o, $attr);
        foreach ($option as $key => $value) {
            // option list group
            if (is_array($value)) {
                $s = [];
                if (strpos($key, '.') === 0) {
                    $s['disabled'] = true;
                    $key = substr($key, 1);
                } else if (strpos($key, '\\.') === 0) { // escaped
                    $key = substr($key, 1);
                }
                $s['label'] = $key;
                $o .= N . self::begin('optgroup', $s, $dent + 1);
                foreach ($value as $k => $v) {
                    $s = [];
                    if (strpos($k, '.') === 0) {
                        $s['disabled'] = true;
                        $k = substr($k, 1);
                    } else if (strpos($k, '\\.') === 0) { // escaped
                        $k = substr($k, 1);
                    }
                    $k = (string) $k;
                    if ($select === $k || $select === '.' . $k) {
                        $s['selected'] = true;
                    }
                    $s['value'] = $k;
                    $o .= N . self::unite('option', $v, $s, $dent + 2);
                }
                $o .= N . self::end();
            // option list
            } else {
                $s = [];
                if (strpos($key, '.') === 0) {
                    $s['disabled'] = true;
                    $key = substr($key, 1);
                } else if (strpos($key, '\\.') === 0) { // escaped
                    $key = substr($key, 1);
                }
                $key = (string) $key;
                if ($select === $key || $select === '.' . $key) {
                    $s['selected'] = true;
                }
                $s['value'] = $key;
                $o .= N . self::unite('option', $value, $s, $dent + 1);
            }
        }
        return self::unite('select', $o . N . self::dent($dent), $attr_o, $dent);
    }

    // `<textarea>`
    public static function textarea($name = null, $value = "", $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        } else if (strpos($name, '\\.') === 0) { // escaped
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        // <https://www.w3.org/TR/html5/forms.html#the-placeholder-attribute>
        // The `placeholder` attribute represents a short hint (a word or short phrase) intended
        // to aid the user with data entry when the control has no value. A hint could be a sample
        // value or a brief description of the expected format. The attribute, if specified, must
        // have a value that contains no “LF” (U+000A) or “CR” (U+000D) character(s).
        if (isset($placeholder)) {
            $placeholder = explode("\n", n($placeholder), 2)[0];
        }
        $attr_o['placeholder'] = $placeholder;
        return self::unite('textarea', self::x(Request::restore('post', $name, $value)), Anemon::extend($attr_o, $attr), $dent);
    }

}