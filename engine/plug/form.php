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
    $attr_o = array('checked' => $check ? true : null);
    $indent = $indent ? str_repeat(TAB, $indent) : "";
    if($value === true) $value = 'true';
    Mecha::extend($attr_o, $attr);
    $text = $text ? '&nbsp;<span>' . Text::parse($text, '->text', WISE_CELL_I) . '</span>' : "";
    return $indent . '<label>' . Form::input('checkbox', $name, $value, null, $attr_o) . $text . '</label>';
});

// `<input type="radio">`
Form::add('radio', function($name = null, $option = array(), $select = null, $attr = array(), $indent = 0) {
    $output = array();
    $indent = $indent ? str_repeat(TAB, $indent) : "";
    $select = (string) $select;
    foreach($option as $key => $value) {
        $attr_o = array('disabled' => null);
        if(strpos($key, '.') === 0) {
            $attr_o['disabled'] = true;
            $key = substr($key, 1);
        }
        $key = (string) $key;
        $attr_o['checked'] = $select === $key || $select === '.' . $key ? true : null;
        Mecha::extend($attr_o, $attr);
        $value = $value ? '&nbsp;<span>' . Text::parse($value, '->text', WISE_CELL_I) . '</span>' : "";
        $output[] = $indent . '<label>' . Form::input('radio', $name, $key, null, $attr_o) . $value . '</label>';
    }
    return implode(' ', $output);
});

// `<input type="(color|date|email|number|password|range|search|tel|text|url)">`
foreach(array('color', 'date', 'email', 'number', 'password', 'range', 'search', 'tel', 'text', 'url') as $unit) {
    Form::add($unit, function($name = null, $value = null, $placeholder = null, $attr = array(), $indent = 0) use($unit) {
        return Form::input($unit, $name, $value, $placeholder, $attr, $indent);
    });
}