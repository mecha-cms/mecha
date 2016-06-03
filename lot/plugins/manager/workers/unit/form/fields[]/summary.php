<?php

Mecha::concat($attributes, array(
    'class' => array(
        'textarea-block'
    )
));

Mecha::extend($attributes, $data['attributes']);

$html .= '<div class="grid-group grid-group-' . $type . '">';
$html .= '<label class="grid span-2 form-label" for="' . $attributes['id'] . '">' . $title . '</label>';
$html .= '<div class="grid span-4">';
$html .= Form::textarea('fields[' . $key . ']', $value ? (is_array($value) ? Converter::toText($value) : Converter::str($value)) : null, $placeholder ? $placeholder : null, $attributes);
$html .= '</div>';
$html .= '</div>';