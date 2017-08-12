<?php

// `<input type="hidden">`
Form::plug('hidden', function($name = null, $value = null, $attr = [], $dent = 0) {
    // Do not cache request data of hidden form element(s)
    Request::delete('post', $name);
    return Form::input($name, 'hidden', $value, null, $attr, $dent);
});

// `<input type="file">`
Form::plug('file', function($name = null, $attr = [], $dent = 0) {
    return Form::input('file', $name, null, null, $attr, $dent);
});

// `<input type="checkbox">`
Form::plug('checkbox', function($name = null, $value = null, $check = false, $text = "", $attr = [], $dent = 0) {
    $attr_o = ['checked' => $check ? true : null];
    if ($value === true) {
        $value = 'true';
    }
    $text = $text ? '&#x0020;' . HTML::span($text) : "";
    return Form::dent($dent) . HTML::label(Form::input($name, 'checkbox', $value, null, array_replace_recursive($attr_o, $attr)) . $text);
});

// `<input type="radio">`
Form::plug('radio', function($name = null, $options = [], $select = null, $attr = [], $dent = 0) {
    $output = [];
    $select = (string) $select;
    foreach ($options as $k => $v) {
        $attr_o = ['disabled' => null];
        if (strpos($k, '.') === 0) {
            $attr_o['disabled'] = true;
            $k = substr($k, 1);
        }
        $k = (string) $k;
        $attr_o['checked'] = $select === $k || $select === '.' . $k ? true : null;
        $v = $v ? '&#x0020;' . HTML::span($v) : "";
        $output[] = Form::dent($dent) . HTML::label(Form::input($name, 'radio', $k, null, array_replace_recursive($attr_o, $attr)) . $v);
    }
    return implode(HTML::br(), $output);
});

// `<button type="(reset|submit)">`
foreach (['reset', 'submit'] as $unit) {
    Form::plug($unit, function($name = null, $value = null, $text = "", $attr = [], $dent = 0) use($unit) {
        $attr['type'] = $unit;
        return Form::button($name, $value, $text, $attr, $dent);
    });
}

// `<input type="(color|date|email|number|password|search|tel|text|url)">`
foreach (['color', 'date', 'email', 'number', 'password', 'search', 'tel', 'text', 'url'] as $unit) {
    Form::plug($unit, function($name = null, $value = null, $placeholder = null, $attr = [], $dent = 0) use($unit) {
        return Form::input($name, $unit, $value, $placeholder, $attr, $dent);
    });
}

// `<input type="range">`
Form::plug('range', function($name = null, $value = [0, 0, 1], $attr = [], $dent = 0) {
    if (is_array($value)) {
        if (!array_key_exists('min', $attr)) {
            $attr['min'] = $value[0];
        }
        if (!array_key_exists('max', $attr)) {
            $attr['max'] = $value[2];
        }
    }
    return Form::input($name, 'range', is_array($value) ? $value[1] : $value, null, $attr, $dent);
});