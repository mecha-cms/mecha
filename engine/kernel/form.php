<?php

class Form extends HTML {

    // `<button>`
    public static function button($name = null, $value = null, $text = "", $attr = [], $dent = 0) {
        if (!array_key_exists('type', $attr)) {
            $attr['type'] = 'button';
        }
        $attr_o = ['value' => $value];
        self::__($name, $attr_o);
        unset(
            $attr_o['readonly'],
            $attr_o['required']
        );
        $attr_o['name'] = $name;
        return self::unite('button', $text, array_replace_recursive($attr_o, $attr), $dent);
    }

    // `<input>`
    public static function input($name = null, $type = 'text', $value = null, $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [
            'placeholder' => $placeholder,
            'type' => $type
        ];
        self::__($name, $attr_o);
        $attr_o['name'] = $name;
        $attr_o['value'] = Request::restore('post', $name, $value);
        return self::unite('input', false, array_replace_recursive($attr_o, $attr), $dent);
    }

    // `<select>`
    public static function select($name = null, $option = [], $select = null, $attr = [], $dent = 0) {
        $o = "";
        $attr_o = [];
        self::__($name, $attr_o);
        unset($attr_o['required']);
        $select = (string) Request::restore('post', $name, $select);
        $attr_o['name'] = $name;
        $attr_o = array_replace_recursive($attr_o, $attr);
        foreach ($option as $key => $value) {
            $tag = new static;
            // option list group
            if (is_array($value)) {
                $s = [];
                $key = (string) $key;
                self::__($key, $s);
                $s['label'] = trim(strip_tags($key));
                $o .= N . $tag->begin('optgroup', $s, $dent + 1);
                foreach ($value as $k => $v) {
                    $s = [];
                    $k = (string) $k;
                    self::__($k, $s);
                    unset($s['readonly'], $s['required']);
                    if ($select === $k) {
                        $s['selected'] = true;
                    }
                    $s['value'] = $k;
                    $o .= N . self::unite('option', trim(strip_tags($v)), $s, $dent + 2);
                }
                $o .= N . $tag->end();
            // option list
            } else {
                $s = [];
                $key = (string) $key;
                self::__($key, $s);
                unset($s['readonly'], $s['required']);
                if ($select === $key) {
                    $s['selected'] = true;
                }
                $s['value'] = $key;
                $o .= N . self::unite('option', trim(strip_tags($value)), $s, $dent + 1);
            }
        }
        return self::unite('select', $o . N . self::dent($dent), $attr_o, $dent);
    }

    // `<textarea>`
    public static function textarea($name = null, $value = "", $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [];
        self::__($name, $attr_o);
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
        return self::unite('textarea', self::x(Request::restore('post', $name, $value)), array_replace_recursive($attr_o, $attr), $dent);
    }

    private static function __(&$s, &$a) {
        if ($s && strpos('.!*', $s[0]) !== false) {
            $a[Anemon::alter($s[0], [
                '.' => 'disabled',
                '!' => 'readonly',
                '*' => 'required'
            ])] = true;
            $s = substr($s, 1);
        } else if (strlen($s) >= 2 && $s[0] === '\\' && strpos('.!*', $s[1]) !== false) { // escaped
            $s = substr($s, 1);
        }
    }

}