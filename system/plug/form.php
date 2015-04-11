<?php

// `<input type="hidden">`
Form::add('hidden', function($name = null, $value = null, $attr = array(), $indent = "") {
    return Form::input('hidden', $name, $value, null, $attr, $indent);
});

// `<input type="file">`
Form::add('file', function($name = null, $attr = array(), $indent = "") {
    return Form::input('file', $name, null, null, $attr, $indent);
});

// `<input type="radio">`
Form::add('radio', function($name = null, $option = array(), $select = null, $attr = array(), $indent = "") {
    $output = array();
    foreach($option as $key => $value) {
        $key_o = ltrim($key, '.');
        $attr['disabled'] = strpos($key, '.') === 0 ? true : null;
        $attr['checked'] = ltrim($select, '.') === $key_o ? true : null;
        $output[] = $indent . '<label>' . Form::input('radio', $name, $key_o, null, $attr) . ($value ? ' <span>' . $value . '</span>' : "") . '</label>';
    }
    return implode(NL, $output);
});

// `<input type="checkbox">`
Form::add('checkbox', function($name = null, $value = null, $check = false, $text = "", $attr = array(), $indent = "") {
    $attr['checked'] = $check ? true : null;
    return $indent . '<label>' . Form::input('checkbox', $name, $value, null, $attr) . ($text ? ' <span>' . $text . '</span>' : "") . '</label>';
});

// `<input type="date|color|email|number|search|tel|text|password|range|url">`
foreach(array('date', 'color', 'email', 'number', 'search', 'tel', 'text', 'password', 'range', 'url') as $type) {
    Form::add($type, function($name = null, $value = null, $placeholder = null, $attr = array(), $indent = "") use($type) {
        return Form::input($type, $name, $value, $placeholder, $attr, $indent);
    });
}