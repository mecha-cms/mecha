<?php

// `<input type="hidden">`
Form::add('hidden', function($name = null, $value = null, $attr = array(), $indent = 0) {
    return Form::input('hidden', $name, $value, null, $attr, $indent);
});

// `<input type="file">`
Form::add('file', function($name = null, $attr = array(), $indent = 0) {
    return Form::input('file', $name, null, null, $attr, $indent);
});

// `<input type="checkbox">`
Form::add('checkbox', function($name = null, $value = null, $check = false, $text = "", $attr = array(), $indent = 0) {
    $attr['checked'] = $check ? true : null;
    $indent = $indent ? str_repeat(TAB, $indent) : "";
    return $indent . '<label>' . Form::input('checkbox', $name, $value, null, $attr) . ($text ? ' <span>' . $text . '</span>' : "") . '</label>';
});

// `<input type="radio">`
Form::add('radio', function($name = null, $option = array(), $select = null, $attr = array(), $indent = 0) {
    $output = array();
    $indent = $indent ? str_repeat(TAB, $indent) : "";
    foreach($option as $key => $value) {
        $attr['disabled'] = strpos($key, '.') === 0 ? true : null;
        $attr['checked'] = ltrim($select, '.') === ltrim($key, '.') ? true : null;
        $output[] = $indent . '<label>' . Form::input('radio', $name, ltrim($key, '.'), null, $attr) . ($value ? ' <span>' . $value . '</span>' : "") . '</label>';
    }
    return implode(' ', $output);
});

// `<input type="(color|date|email|number|password|range|search|tel|text|url)">`
foreach(array('color', 'date', 'email', 'number', 'password', 'range', 'search', 'tel', 'text', 'url') as $unit) {
    Form::add($unit, function($name = null, $value = null, $placeholder = null, $attr = array(), $indent = 0) use($unit) {
        return Form::input($unit, $name, $value, $placeholder, $attr, $indent);
    });
}