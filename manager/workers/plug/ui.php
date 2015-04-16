<?php

// `<i class="fa fa-check-circle">`
UI::add('icon', function($kind, $modifier = null) {
    return Cell::i("", array(
        'class' => 'fa fa-' . $kind . (is_string($modifier) ? ' fa-' . trim(str_replace('.', ' fa-', $modifier), '.') : "")
    ));
});

// `<button class="btn">`
UI::add('button', function($kind = 'default', $text = "", $name = null, $type = 'submit', $attr = array(), $indent = 0) {
    $kind = explode(':', $kind);
    $k = explode('.', trim($kind[0], '.'));
    $icon = count($kind) === 1 ? Mecha::alter($k[0], array(
        'action' => 'check-circle',
        'accept' => 'check-circle',
        'begin' => 'plus-circle',
        'construct' => 'check-circle',
        'danger' => 'times-circle',
        'destruct' => 'times-circle',
        'error' => 'exclamation-circle',
        'reject' => 'times-circle'
    ), "") : $kind[1];
    if($icon !== "") {
        $text = UI::icon($icon) . ' ' . $text;
    }
    if( ! isset($attr['class'])) {
        $attr['class'] = array();
    } else {
        if( ! is_array($attr['class'])) {
            $attr['class'] = array($attr['class']);
        }
    }
    $s = is_string($name) ? explode(':', $name, 2) : array(null, null);
    $attr['class'] = array_merge(array('btn btn-' . str_replace('.', ' btn-', $kind[0])), $attr['class']);
    $attr['disabled'] = strpos($kind[0], '.disabled') !== false ? true : null;
    return Form::button($text, $s[0], isset($s[1]) ? $s[1] : null, $type, $attr, $indent);
});

// `<a class="btn">`
UI::add('btn', function($kind = 'default', $text = "", $href = null, $attr = array(), $indent = 0) {
    $kind = explode(':', $kind);
    $k = explode('.', trim($kind[0], '.'));
    $icon = count($kind) === 1 ? Mecha::alter($k[0], array(
        'action' => 'check-circle',
        'accept' => 'check-circle',
        'begin' => 'plus-circle',
        'construct' => 'check-circle',
        'danger' => 'times-circle',
        'destruct' => 'times-circle',
        'error' => 'exclamation-circle',
        'reject' => 'times-circle'
    ), "") : $kind[1];
    if($icon !== "") {
        $text = UI::icon($icon) . ' ' . $text;
    }
    if( ! isset($attr['class'])) {
        $attr['class'] = array();
    } else {
        if( ! is_array($attr['class'])) {
            $attr['class'] = array($attr['class']);
        }
    }
    $active = strpos($kind[0], '.disabled') === false;
    $attr['href'] = $active ? Converter::url($href) : null;
    $attr['class'] = array_merge(array('btn btn-' . str_replace('.', ' btn-', $kind[0])), $attr['class']);
    return Cell::unit($active ? 'a' : 'span', $text, $attr, $indent);
});

// `<b|em|i|span|strong class="text-error">`
foreach(array('b', 'em', 'i', 'span', 'strong') as $tag) {
    UI::add($tag, function($kind = 'default', $text = "", $attr = array(), $indent = 0) use($tag) {
        if( ! isset($attr['class'])) {
            $attr['class'] = array();
        } else {
            if( ! is_array($attr['class'])) {
                $attr['class'] = array($attr['class']);
            }
        }
        $attr['class'] = array_merge(array('text-' . $kind), $attr['class']);
        return Cell::unit($tag, $text, $attr, $indent);
    });
}

// `<a class="text-error">`
UI::add('a', function($kind = 'default', $href = null, $text = "", $attr = array(), $indent = 0) {
    $attr['href'] = Converter::url($href);
    if( ! isset($attr['class'])) {
        $attr['class'] = array();
    } else {
        if( ! is_array($attr['class'])) {
            $attr['class'] = array($attr['class']);
        }
    }
    $attr['class'] = array_merge(array('text-' . $kind), $attr['class']);
    return Cell::unit('a', $text, $attr, $indent);
});

// File uploader
UI::add('uploader', function($action, $accept = null, $fields = array()) {
    $speak = Config::speak();
    $html = Cell::begin('form', array(
        'class' => 'form-upload',
        'action' => Converter::url($action),
        'method' => 'post',
        'enctype' => 'multipart/form-data'
    )) . NL;
    $html .= Form::hidden('token', Guardian::token(), array(), 1) . NL;
    foreach($fields as $name => $value) {
        $html .= Form::hidden($name, $value, array(), 1) . NL;
    }
    $html .= Cell::begin('span', array(
        'class' => array(
            'input-outer',
            'btn',
            'btn-default'
        )
    ), 1) . NL;
    $html .= Cell::unit('span', UI::icon('folder-open') . ' ' . $speak->manager->placeholder_file, array(), 2) . NL;
    $html .= Form::file('file', array(
        'title' => $speak->manager->placeholder_file,
        'data' => array(
            'icon-ready' => 'fa fa-check',
            'icon-error' => 'fa fa-times',
            'accepted-extensions' => ! is_null($accept) ? $accept : implode(',', File::$config['file_extension_allow'])
        )
    ), 2) . NL;
    $html .= Cell::end() . ' ' . UI::button('action:cloud-upload', $speak->upload) . NL;
    $html .= Cell::end();
    return $html;
});

// File finder
UI::add('finder', function($action, $name = 'q') {
    $html = Cell::begin('form', array(
        'class' => 'form-find',
        'action' => Converter::url($action),
        'method' => 'get'
    )) . NL;
    $html .= Form::text($name, Request::get($name, null), null, array(), 1) . ' ' . UI::button('action:search', Config::speak('find')) . NL;
    $html .= Cell::end();
    return $html;
});