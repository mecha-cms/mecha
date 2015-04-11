<?php

// `<i class="fa fa-check-circle">`
UI::add('icon', function($kind, $MOD = null) {
    return Cell::i("", array(
        'class' => 'fa fa-' . $kind . ( ! is_null($MOD) ? ' fa-' . trim(str_replace('.', ' fa-', $MOD), '.') : "")
    ));
});

// `<button class="btn">`
UI::add('button', function($kind = 'default', $text = "", $name = null, $type = 'submit', $attr = array(), $indent = "") {
    $slice = explode('.', $kind);
    $icon = strpos($text, ' class="fa ') === false ? Mecha::alter($slice[0], array(
        'action' => UI::icon('check-circle'),
        'accept' => UI::icon('check-circle'),
        'begin' => UI::icon('plus-square'),
        'construct' => UI::icon('check-circle'),
        'danger' => UI::icon('times-circle'),
        'destruct' => UI::icon('times-circle'),
        'error' => UI::icon('exclamation-triangle'),
        'reject' => UI::icon('times-circle')
    ), "") : "";
    if($icon !== "") {
        $text = $icon . ' ' . $text;
    }
    $s = is_string($name) ? explode(':', $name, 2) : array(null, null);
    $attr['class'] = 'btn btn-' . str_replace('.', ' btn-', trim(implode('.', $slice), '.'));
    $attr['disabled'] = strpos($kind, '.disabled') !== false ? true : null;
    return Form::button($text, $s[0], isset($s[1]) ? $s[1] : null, $type, $attr, $indent);
});

// `<a class="btn">`
UI::add('btn', function($kind = 'default', $text = "", $href = null, $attr = array(), $indent = "") {
    $slice = explode('.', $kind);
    $active = strpos($kind, '.disabled') === false;
    $icon = strpos($text, ' class="fa ') === false ? Mecha::alter($slice[0], array(
        'action' => UI::icon('check-circle'),
        'accept' => UI::icon('check-circle'),
        'begin' => UI::icon('plus-square'),
        'construct' => UI::icon('check-circle'),
        'danger' => UI::icon('times-circle'),
        'destruct' => UI::icon('times-circle'),
        'error' => UI::icon('exclamation-triangle'),
        'reject' => UI::icon('times-circle')
    ), "") : "";
    $attr['href'] = $active ? $href : null;
    $attr['class'] = 'btn btn-' . str_replace('.', ' btn-', trim(implode('.', $slice), '.'));
    if($icon !== "") {
        $text = $icon . ' ' . $text;
    }
    return Cell::unit($active ? 'a' : 'span', $text, $attr, $indent);
});

// `<em|span|strong class="text-error">`
foreach(array('em', 'span', 'strong') as $tag) {
    UI::add($tag, function($kind = 'default', $text = "", $attr = array(), $indent = "") use($tag) {
        $attr['class'] = 'text-' . $kind;
        return Cell::unit($tag, $text, $attr, $indent);
    });
}

// `<a class="text-error">`
UI::add('a', function($kind = 'default', $href = null, $text = "", $attr = array(), $indent = "") {
    $attr['href'] = $href;
    $attr['class'] = 'text-' . $kind;
    return Cell::unit('a', $text, $attr, $indent);
});

// File uploader
UI::add('uploader', function($action, $accept = null, $fields = array()) {
    $speak = Config::speak();
    $html = Cell::begin('form', array(
        'class' => 'form-upload',
        'action' => $action,
        'method' => 'post',
        'enctype' => 'multipart/form-data'
    )) . NL;
    $html .= Form::hidden('token', Guardian::token(), array(), TAB) . NL;
    foreach($fields as $name => $value) {
        $html .= Form::hidden($name, $value, array(), TAB) . NL;
    }
    $html .= Cell::begin('span', array(
        'class' => array(
            'input-outer',
            'btn',
            'btn-default'
        )
    ), TAB) . NL;
    $html .= Cell::unit('span', UI::icon('folder-open') . ' ' . $speak->manager->placeholder_file, array(), str_repeat(TAB, 2)) . NL;
    $html .= Form::file('file', array(
        'title' => $speak->manager->placeholder_file,
        'data' => array(
            'icon-ready' => 'fa fa-check',
            'icon-error' => 'fa fa-times',
            'accepted-extensions' => ! is_null($accept) ? $accept : implode(',', File::$config['file_extension_allow'])
        )
    ), str_repeat(TAB, 2)) . NL;
    $html .= Cell::end() . ' ' . UI::button('action', UI::icon('cloud-upload') . ' ' . $speak->upload) . NL;
    $html .= Cell::end();
    return $html;
});

// File finder
UI::add('finder', function($action, $name = 'q') {
    $html = Cell::begin('form', array(
        'class' => 'form-find',
        'action' => $action,
        'method' => 'get'
    )) . NL;
    $html .= Form::text($name, Request::get($name, null), null, array(), TAB) . ' ' . UI::button('action', UI::icon('search') . ' ' . Config::speak('find')) . NL;
    $html .= Cell::end();
    return $html;
});